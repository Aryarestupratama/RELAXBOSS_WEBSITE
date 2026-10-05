<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\Enums\Intent;
use LogicException;

/**
 * Deteksi krisis berbasis aturan (FR-018 lapisan 1, Architecture 6.3 dan 6.6).
 *
 * Dipakai oleh ChatbotService (pesan chat, sebelum AI dipanggil) dan, di TASK-021, oleh pemeriksaan
 * jawaban PFA (RULE-047A). Kelas ini murni: tidak menyentuh database, tidak mencatat log, dan tidak
 * pernah menyimpan teks yang diperiksa (RULE-040). Pencatatan `crisis_events` dilakukan pemanggil.
 *
 * Teks dinormalisasi lalu dicocokkan dengan pola di `resources/crisis/keywords.id.php`. Bila pencocokan
 * gagal karena galat regex, hasilnya dianggap krisis (gagal aman).
 */
final class CrisisDetector
{
    /** Batas karakter yang diperiksa; chat 2000 dan PFA 1000 per jawaban, jadi ini hanya pengaman. */
    private const MAX_CHARS = 20000;

    /** Pemetaan kunci di berkas kata kunci ke Intent (suicide diperiksa lebih dulu: prioritas lebih tinggi). */
    private const CATEGORIES = [
        'suicide' => Intent::CrisisSuicide,
        'selfharm' => Intent::CrisisSelfharm,
    ];

    /** Huruf beraksen yang umum ke bentuk polos. */
    private const ACCENTS = [
        'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a',
        'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
    ];

    /** Angka dan simbol yang sering dipakai menyamarkan huruf. */
    private const LEET = [
        '0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't', '@' => 'a', '$' => 's',
    ];

    /** @var array<string, list<string>>|null */
    private ?array $rules = null;

    public function __construct(private readonly ?string $keywordsPath = null) {}

    /**
     * Intent krisis bila teks terindikasi krisis, selain itu null.
     */
    public function detect(string $text): ?Intent
    {
        $text = mb_scrub($text, 'UTF-8');

        if ($text === '') {
            return null;
        }

        $text = mb_substr($text, 0, self::MAX_CHARS, 'UTF-8');
        $rules = $this->rules();

        foreach ($this->variants($text) as $variant) {
            $variant = $this->stripIgnored($variant, $rules['ignore'] ?? []);

            foreach (self::CATEGORIES as $key => $intent) {
                foreach ($rules[$key] ?? [] as $pattern) {
                    $matched = preg_match($pattern, $variant);

                    // false = galat regex (mis. batas backtrack): gagal aman.
                    if ($matched === 1 || $matched === false) {
                        return $intent;
                    }
                }
            }
        }

        return null;
    }

    public function isCrisis(string $text): bool
    {
        return $this->detect($text) !== null;
    }

    /**
     * Teks yang diperiksa: bentuk biasa dan bentuk "leet" (angka dan simbol diganti huruf).
     *
     * @return list<string>
     */
    public function variants(string $text): array
    {
        $base = mb_strtolower($text, 'UTF-8');
        $base = strtr($base, self::ACCENTS);

        $variants = [$this->clean($base)];
        $leet = $this->clean(strtr($base, self::LEET));

        if ($leet !== $variants[0]) {
            $variants[] = $leet;
        }

        return $variants;
    }

    private function clean(string $text): string
    {
        // Huruf yang diulang 3 kali atau lebih jadi satu: "matiii" -> "mati".
        $text = preg_replace('/(\p{L})\1{2,}/u', '$1', $text) ?? $text;
        // Tanda baca jadi spasi; spasi ganda dirapikan.
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    /**
     * @param  list<string>  $patterns
     */
    private function stripIgnored(string $text, array $patterns): string
    {
        foreach ($patterns as $pattern) {
            $text = preg_replace($pattern, ' ', $text) ?? $text;
        }

        return $text;
    }

    /**
     * @return array<string, list<string>>
     */
    private function rules(): array
    {
        if ($this->rules !== null) {
            return $this->rules;
        }

        $path = $this->keywordsPath ?? resource_path('crisis/keywords.id.php');

        if (! is_file($path)) {
            throw new LogicException('Berkas kata kunci krisis tidak ditemukan.');
        }

        /** @var array<string, list<string>> $rules */
        $rules = require $path;

        return $this->rules = $rules;
    }
}
