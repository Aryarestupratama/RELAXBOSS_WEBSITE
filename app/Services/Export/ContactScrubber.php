<?php

declare(strict_types=1);

namespace App\Services\Export;

/**
 * Mengganti kontak bantuan krisis resmi (`config('relaxboss.crisis_contacts')`) di teks ekspor dengan
 * penanda `[KONTAK_BANTUAN]` (RULE-070). Hanya kontak yang dikonfigurasi (nama, telepon, URL) yang diganti;
 * teks bebas pengguna tidak dipindai untuk email atau nomor lain (dicatat sebagai Open Question OQ-11).
 */
final class ContactScrubber
{
    public const MARKER = '[KONTAK_BANTUAN]';

    /** @var list<string> Nama, telepon, dan URL apa adanya (terpanjang dulu). */
    private array $needles;

    /** @var list<string> Pola regex untuk telepon yang boleh tertulis dengan spasi atau tanda hubung. */
    private array $phonePatterns;

    /**
     * @param  list<array<string, mixed>>|null  $contacts  bawaan: config('relaxboss.crisis_contacts')
     */
    public function __construct(?array $contacts = null)
    {
        $contacts ??= (array) config('relaxboss.crisis_contacts', []);

        $needles = [];
        $phonePatterns = [];

        foreach ($contacts as $contact) {
            if (! is_array($contact)) {
                continue;
            }

            foreach (['name', 'phone', 'url'] as $key) {
                $value = $contact[$key] ?? null;

                if (is_string($value) && trim($value) !== '') {
                    $needles[] = trim($value);
                }
            }

            $phone = $contact['phone'] ?? null;
            $digits = is_string($phone) ? (preg_replace('/\D+/', '', $phone) ?? '') : '';

            if (strlen($digits) >= 3) {
                // Cocok juga bila ditulis "(021) 500-454" atau "021500454", tapi tidak di dalam angka lebih panjang.
                $phonePatterns[] = '/(?<!\d)\(?'.implode('[\s\-\.\(\)]*', str_split($digits)).'(?!\d)/';
            }
        }

        $needles = array_values(array_unique($needles));
        usort($needles, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        $this->needles = $needles;
        $this->phonePatterns = array_values(array_unique($phonePatterns));
    }

    public function scrub(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        foreach ($this->needles as $needle) {
            $text = str_ireplace($needle, self::MARKER, $text);
        }

        foreach ($this->phonePatterns as $pattern) {
            $text = preg_replace($pattern, self::MARKER, $text) ?? $text;
        }

        return $text;
    }

    /** Terapkan ke setiap string di dalam struktur (array bersarang). Nilai non-string dibiarkan. */
    public function scrubDeep(mixed $value): mixed
    {
        if (is_string($value)) {
            return $this->scrub($value);
        }

        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->scrubDeep($item), $value);
        }

        return $value;
    }
}
