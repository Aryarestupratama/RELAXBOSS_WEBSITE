<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\DTOs\ParsedChatReply;
use App\Enums\Intent;
use App\Exceptions\AiUnavailableException;

/**
 * Membaca keluaran model: `{reply, intent, confidence, strategy, flow_action}` (Architecture 6.3).
 *
 * - JSON sah (boleh dibungkus ```json atau diberi teks di sekitarnya): dipakai.
 * - JSON rusak atau terpotong, atau teks yang kemasukan sisa metadata (mis. `...apa?intent":"academic_pressure"`):
 *   teks dipotong pada kunci metadata pertama; bagian sebelumnya jadi balasan, nilai metadatanya diselamatkan.
 * - Bukan JSON sama sekali: seluruh teks menjadi balasan, intent kosong.
 * - Tampak JSON tetapi tanpa `reply` yang bisa dibaca: AiUnavailableException('invalid_response'),
 *   supaya pengguna tidak melihat JSON mentah (pemanggil jatuh ke respons statis).
 *
 * Apa pun jalurnya, balasan akhir tidak pernah memuat kunci metadata (jaring pengaman di `clean()`).
 */
final class ChatReplyParser
{
    private const MAX_REPLY_CHARS = 4000;

    /** Kunci metadata dengan tanda kutip penutup. Kutip pembuka boleh hilang (`apa?intent":`). */
    private const META_KEY = '/(intent|confidence|strategy|flow_action)"\s*:/u';

    private const REPLY_PREFIX = '/\A\s*(?:```(?:json)?\s*)?\{?\s*"?reply"\s*:\s*"/iu';

    public function parse(string $raw): ParsedChatReply
    {
        $text = trim($raw);
        $data = $this->decode($text);

        if ($data !== null) {
            $reply = $data['reply'] ?? null;

            if (is_string($reply) && trim($this->clean($reply)) !== '') {
                return new ParsedChatReply(
                    reply: $this->clean($reply),
                    intent: $this->intent($data['intent'] ?? null),
                    confidence: $this->confidence($data['confidence'] ?? null),
                    strategy: $this->code($data['strategy'] ?? null),
                    flowAction: $this->code($data['flow_action'] ?? null),
                );
            }

            throw new AiUnavailableException('invalid_response');
        }

        if ($text === '') {
            throw new AiUnavailableException('empty_response');
        }

        if ($this->looksLikeJson($text) || preg_match(self::META_KEY, $text) === 1 || preg_match(self::REPLY_PREFIX, $text) === 1) {
            return $this->salvage($text);
        }

        return new ParsedChatReply(reply: $this->clean($text));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decode(string $text): ?array
    {
        $candidate = trim((string) preg_replace('/\A```(?:json)?\s*|\s*```\z/i', '', $text));

        foreach ([$candidate, $this->outerObject($candidate)] as $json) {
            if ($json === null || $json === '') {
                continue;
            }

            $decoded = json_decode($json, true);

            if (is_array($decoded) && ! array_is_list($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    private function outerObject(string $text): ?string
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');

        return $start !== false && $end !== false && $end > $start
            ? substr($text, $start, $end - $start + 1)
            : null;
    }

    private function looksLikeJson(string $text): bool
    {
        return str_starts_with($text, '{') || str_starts_with($text, '```');
    }

    /**
     * Teks rusak: potong pada kunci metadata pertama. Bagian sebelumnya adalah balasan (dibuang pembuka
     * `{"reply":"` bila ada, dan sisa tanda kutip atau koma di ujung); bagian sesudahnya hanya dipakai
     * untuk mengambil intent, confidence, strategy, dan flow_action.
     */
    private function salvage(string $text): ParsedChatReply
    {
        $head = $text;
        $tail = '';

        if (preg_match(self::META_KEY, $text, $match, PREG_OFFSET_CAPTURE) === 1) {
            $head = substr($text, 0, (int) $match[0][1]);
            $tail = substr($text, (int) $match[0][1]);
        }

        $isJsonString = preg_match(self::REPLY_PREFIX, $head) === 1;
        $head = (string) preg_replace(self::REPLY_PREFIX, '', $head);
        $head = trim((string) preg_replace('/(?:[\s",}\x60]|\\\\n)+\z/u', '', $head));

        if ($isJsonString) {
            $decoded = json_decode('"'.$head.'"');
            $head = is_string($decoded) ? $decoded : str_replace(['\\n', '\\"', '\\\\'], ["\n", '"', '\\'], $head);
        }

        $reply = $this->clean($head);

        if ($reply === '' || str_starts_with($reply, '{') || str_starts_with($reply, '```')) {
            throw new AiUnavailableException('invalid_response');
        }

        return new ParsedChatReply(
            reply: $reply,
            intent: $this->intent($this->metaString($tail, 'intent')),
            confidence: $this->confidence($this->metaNumber($tail, 'confidence')),
            strategy: $this->code($this->metaString($tail, 'strategy')),
            flowAction: $this->code($this->metaString($tail, 'flow_action')),
        );
    }

    private function metaString(string $tail, string $key): ?string
    {
        return preg_match('/'.$key.'"\s*:\s*"([^"]*)"/u', $tail, $match) === 1 ? $match[1] : null;
    }

    private function metaNumber(string $tail, string $key): ?string
    {
        return preg_match('/'.$key.'"\s*:\s*([0-9]+(?:\.[0-9]+)?)/u', $tail, $match) === 1 ? $match[1] : null;
    }

    private function intent(mixed $value): ?Intent
    {
        return is_string($value) ? Intent::tryFrom(strtolower(trim($value))) : null;
    }

    private function confidence(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        if ($number > 1.0 && $number <= 100.0) { // model kadang memakai persen
            $number /= 100;
        }

        return round(max(0.0, min($number, 0.9999)), 4);
    }

    /** Kode pendek huruf kecil dan garis bawah; selain itu dibuang. */
    private function code(mixed $value): ?string
    {
        return is_string($value) && preg_match('/\A[a-z_]{1,100}\z/', $value) === 1 ? $value : null;
    }

    /** Jaring pengaman terakhir: buang sisa metadata dari balasan, apa pun jalurnya. */
    private function clean(string $reply): string
    {
        if (preg_match(self::META_KEY, $reply, $match, PREG_OFFSET_CAPTURE) === 1) {
            $reply = substr($reply, 0, (int) $match[0][1]);
            $reply = (string) preg_replace('/(?:[\s",}\x60]|\\\\n)+\z/u', '', $reply);
        }

        $reply = (string) preg_replace('/[^\P{C}\n]+/u', '', $reply);

        return mb_substr(trim($reply), 0, self::MAX_REPLY_CHARS);
    }
}
