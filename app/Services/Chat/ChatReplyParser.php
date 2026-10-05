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
 * - JSON terpotong: `reply` diselamatkan lewat pola, sisanya kosong.
 * - Bukan JSON sama sekali: seluruh teks menjadi balasan, intent kosong.
 * - Tampak JSON tetapi tanpa `reply` yang bisa dibaca: AiUnavailableException('invalid_response'),
 *   supaya pengguna tidak melihat JSON mentah (pemanggil jatuh ke respons statis).
 */
final class ChatReplyParser
{
    private const MAX_REPLY_CHARS = 4000;

    public function parse(string $raw): ParsedChatReply
    {
        $text = trim($raw);
        $data = $this->decode($text);

        if ($data !== null) {
            $reply = $data['reply'] ?? null;

            if (is_string($reply) && trim($reply) !== '') {
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

        if ($this->looksLikeJson($text)) {
            $salvaged = $this->salvageReply($text);

            if ($salvaged === null) {
                throw new AiUnavailableException('invalid_response');
            }

            return new ParsedChatReply(reply: $this->clean($salvaged));
        }

        if ($text === '') {
            throw new AiUnavailableException('empty_response');
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

    /** Mengambil nilai "reply" dari JSON yang terpotong. */
    private function salvageReply(string $text): ?string
    {
        if (preg_match('/"reply"\s*:\s*"((?:[^"\\\\]|\\\\.)*)/su', $text, $match) !== 1) {
            return null;
        }

        $decoded = json_decode('"'.$match[1].'"');

        return is_string($decoded) && trim($decoded) !== '' ? $decoded : null;
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

    private function clean(string $reply): string
    {
        $reply = (string) preg_replace('/[^\P{C}\n]+/u', '', $reply);

        return mb_substr(trim($reply), 0, self::MAX_REPLY_CHARS);
    }
}
