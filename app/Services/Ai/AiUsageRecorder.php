<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\Enums\AiFeature;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Mencatat pemakaian ke `ai_usage_daily` per (tanggal UTC, model, fitur) (ENT-010, NFR-011).
 * Atomik lewat upsert dengan penambahan. Kegagalan mencatat tidak boleh menggagalkan permintaan pengguna.
 */
final class AiUsageRecorder
{
    public function record(
        AiFeature $feature,
        string $model,
        int $requests = 0,
        int $rateLimited = 0,
        int $errors = 0,
        int $promptTokens = 0,
        int $completionTokens = 0,
    ): void {
        try {
            DB::table('ai_usage_daily')->upsert(
                [[
                    'usage_date' => CarbonImmutable::now('UTC')->toDateString(),
                    'model' => $model,
                    'feature' => $feature->value,
                    'requests' => $requests,
                    'rate_limited' => $rateLimited,
                    'errors' => $errors,
                    'prompt_tokens' => $promptTokens,
                    'completion_tokens' => $completionTokens,
                ]],
                ['usage_date', 'model', 'feature'],
                [
                    'requests' => DB::raw('requests + '.$requests),
                    'rate_limited' => DB::raw('rate_limited + '.$rateLimited),
                    'errors' => DB::raw('errors + '.$errors),
                    'prompt_tokens' => DB::raw('prompt_tokens + '.$promptTokens),
                    'completion_tokens' => DB::raw('completion_tokens + '.$completionTokens),
                ],
            );
        } catch (Throwable $exception) {
            Log::warning('ai.usage_record_failed', [
                'feature' => $feature->value,
                'model' => $model,
                'exception' => $exception::class,
            ]);
        }
    }
}
