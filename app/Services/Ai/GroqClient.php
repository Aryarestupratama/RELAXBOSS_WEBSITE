<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Contracts\ChatCompletionClient;
use App\DTOs\AiCompletion;
use App\DTOs\AiKey;
use App\Enums\AiFeature;
use App\Exceptions\AiUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SensitiveParameter;
use Throwable;

/**
 * Klien Groq (kompatibel OpenAI) di balik `ChatCompletionClient` (RULE-030, ADR-003, ADR-009).
 *
 * Urutan: model utama dengan semua key yang tersedia (round-robin), lalu model cadangan.
 * - 429: cooldown key itu di model itu, coba key berikutnya.
 * - 401/403: key dinonaktifkan 1 jam, dihitung galat, coba key berikutnya.
 * - Timeout, koneksi, 5xx, 4xx lain, atau balasan kosong: dihitung galat, langsung ke model berikutnya
 *   (tidak mengulang ke key lain agar waktu tunggu pengguna tidak berlipat).
 * - Semua gagal: AiUnavailableException; pemanggil memakai respons statis.
 *
 * Log hanya fitur, nomor key, model, kode status, token, dan durasi (RULE-040, RULE-045).
 * Isi pesan, balasan, dan key tidak pernah dicatat.
 */
final class GroqClient implements ChatCompletionClient
{
    private const ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

    private const CONNECT_TIMEOUT_SECONDS = 5;

    public function __construct(
        private readonly GroqKeyManager $keys,
        private readonly AiUsageRecorder $usage,
    ) {}

    public function complete(AiFeature $feature, array $messages): AiCompletion
    {
        /** @var array{model: string, fallback_model: ?string, max_completion_tokens: int, reasoning_effort: string, timeout: int} $config */
        $config = config("relaxboss.ai.features.{$feature->value}");

        if (! $this->keys->hasKeys($feature)) {
            Log::warning('ai.unavailable', ['feature' => $feature->value, 'reason' => 'no_keys']);

            throw new AiUnavailableException('no_keys');
        }

        $models = array_values(array_unique(array_filter([$config['model'], $config['fallback_model'] ?? null])));
        $offset = $this->keys->nextOffset($feature);
        $reason = 'no_available_key';

        foreach ($models as $model) {
            foreach ($this->keys->available($feature, $model, $offset) as $key) {
                $started = microtime(true);

                try {
                    $response = $this->send($model, $key->secret, $messages, $config);
                } catch (ConnectionException) {
                    $reason = 'timeout';
                    $this->fail($feature, $model, $key, 'timeout', $started);

                    break; // masalah jaringan atau timeout: model berikutnya, bukan key berikutnya
                } catch (Throwable $exception) {
                    $reason = 'exception';
                    $this->fail($feature, $model, $key, $exception::class, $started);

                    break;
                }

                $status = $response->status();

                if ($status === 429) {
                    $reason = 'rate_limited';
                    $this->keys->cooldown($feature, $model, $key, $this->retryAfter($response));
                    $this->usage->record($feature, $model, requests: 1, rateLimited: 1);
                    $this->logAttempt($feature, $model, $key, 'status_429', $started);

                    continue;
                }

                if ($status === 401 || $status === 403) {
                    $reason = 'key_rejected';
                    $this->keys->disable($feature, $key);
                    $this->usage->record($feature, $model, requests: 1, errors: 1);
                    $this->logAttempt($feature, $model, $key, "status_{$status}", $started);

                    continue;
                }

                if (! $response->successful()) {
                    $reason = "http_{$status}";
                    $this->fail($feature, $model, $key, "status_{$status}", $started);

                    break;
                }

                $completion = $this->parse($response, $model);

                if ($completion === null) {
                    $reason = 'empty_response';
                    $this->fail($feature, $model, $key, 'empty_response', $started);

                    break;
                }

                $this->usage->record(
                    $feature,
                    $model,
                    requests: 1,
                    promptTokens: $completion->promptTokens,
                    completionTokens: $completion->completionTokens,
                );

                Log::info('ai.completion', [
                    'feature' => $feature->value,
                    'model' => $model,
                    'key_no' => $key->number,
                    'prompt_tokens' => $completion->promptTokens,
                    'completion_tokens' => $completion->completionTokens,
                    'duration_ms' => $this->elapsed($started),
                ]);

                return $completion;
            }
        }

        Log::warning('ai.unavailable', ['feature' => $feature->value, 'reason' => $reason]);

        throw new AiUnavailableException($reason);
    }

    /**
     * Satu-satunya tempat key dipakai. `#[SensitiveParameter]` menyembunyikannya dari jejak galat.
     *
     * @param  list<array{role: string, content: string}>  $messages
     * @param  array{max_completion_tokens: int, reasoning_effort: string, timeout: int}  $config
     */
    private function send(string $model, #[SensitiveParameter] string $secret, array $messages, array $config): Response
    {
        return Http::withToken($secret)
            ->acceptJson()
            ->asJson()
            ->connectTimeout(self::CONNECT_TIMEOUT_SECONDS)
            ->timeout((int) $config['timeout'])
            ->post(self::ENDPOINT, [
                'model' => $model,
                'messages' => $messages,
                'max_completion_tokens' => (int) $config['max_completion_tokens'],
                'reasoning_effort' => $config['reasoning_effort'],
                'include_reasoning' => false,
            ]);
    }

    private function parse(Response $response, string $model): ?AiCompletion
    {
        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        return new AiCompletion(
            content: $content,
            model: $model,
            promptTokens: (int) $response->json('usage.prompt_tokens', 0),
            completionTokens: (int) $response->json('usage.completion_tokens', 0),
        );
    }

    private function retryAfter(Response $response): ?int
    {
        $header = $response->header('retry-after');

        return is_numeric($header) ? (int) ceil((float) $header) : null;
    }

    private function fail(AiFeature $feature, string $model, AiKey $key, string $code, float $started): void
    {
        $this->usage->record($feature, $model, requests: 1, errors: 1);
        $this->logAttempt($feature, $model, $key, $code, $started);
    }

    private function logAttempt(AiFeature $feature, string $model, AiKey $key, string $code, float $started): void
    {
        Log::warning('ai.attempt_failed', [
            'feature' => $feature->value,
            'model' => $model,
            'key_no' => $key->number,
            'code' => $code,
            'duration_ms' => $this->elapsed($started),
        ]);
    }

    private function elapsed(float $started): int
    {
        return (int) round((microtime(true) - $started) * 1000);
    }
}
