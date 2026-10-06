<?php

declare(strict_types=1);

namespace App\Services\Assessment;

use App\DTOs\AssessmentRecommendation;
use App\Exceptions\AiUnavailableException;

/**
 * Membaca keluaran model untuk Rekomendasi Asesmen: `{recommendation, summary}` (Architecture 6.5).
 *
 * - JSON sah (boleh dibungkus ```json atau diberi teks di sekitarnya): dipakai. `recommendation`
 *   boleh berupa daftar langkah (digabung per baris).
 * - Bukan JSON sama sekali: seluruh teks menjadi rekomendasi.
 * - Tampak JSON tetapi tanpa `recommendation` yang terbaca: AiUnavailableException('invalid_response'),
 *   supaya pengguna tidak melihat JSON mentah (pemanggil jatuh ke rekomendasi statis).
 */
final class RecommendationParser
{
    private const MAX_RECOMMENDATION_CHARS = 4000;

    private const MAX_SUMMARY_CHARS = 1000;

    /**
     * @throws AiUnavailableException
     */
    public function parse(string $raw): AssessmentRecommendation
    {
        $text = trim($raw);
        $data = $this->decode($text);

        if ($data !== null) {
            $recommendation = $this->text($data['recommendation'] ?? null, self::MAX_RECOMMENDATION_CHARS);

            if ($recommendation === null) {
                throw new AiUnavailableException('invalid_response');
            }

            return new AssessmentRecommendation(
                $recommendation,
                $this->text($data['summary'] ?? null, self::MAX_SUMMARY_CHARS),
            );
        }

        if ($text === '') {
            throw new AiUnavailableException('empty_response');
        }

        if (str_starts_with($text, '{') || str_starts_with($text, '```')) {
            throw new AiUnavailableException('invalid_response');
        }

        return new AssessmentRecommendation($this->clean($text, self::MAX_RECOMMENDATION_CHARS), null);
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

    /** Teks, atau daftar teks (satu per baris); null bila kosong atau bentuknya lain. */
    private function text(mixed $value, int $max): ?string
    {
        if (is_array($value) && array_is_list($value)) {
            $lines = array_filter(array_map(
                static fn (mixed $item): string => is_string($item) ? trim($item) : '',
                $value,
            ), static fn (string $line): bool => $line !== '');

            $value = implode("\n", $lines);
        }

        if (! is_string($value)) {
            return null;
        }

        $clean = $this->clean($value, $max);

        return $clean === '' ? null : $clean;
    }

    private function clean(string $text, int $max): string
    {
        $text = (string) preg_replace('/[^\P{C}\n]+/u', '', $text);

        return mb_substr(trim($text), 0, $max);
    }
}
