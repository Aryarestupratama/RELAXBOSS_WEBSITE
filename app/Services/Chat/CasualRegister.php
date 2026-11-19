<?php

declare(strict_types=1);

namespace App\Services\Chat;

/**
 * Register bahasa percakapan RelaxMate: mengenali gaya menulis pengguna, mendeteksi kata yang terlalu baku
 * di balasan, dan mengganti padanan santai yang aman diganti satu lawan satu (kata yang maknanya bergantung
 * konteks, mis. "dapat", hanya dideteksi, tidak diganti). Murni: tanpa database dan tanpa AI.
 */
final class CasualRegister
{
    /** Penanda tulisan santai dan ekspresif. Dua penanda berbeda (atau huruf berulang) dianggap santai. */
    private const CASUAL_PATTERN = '/\b(?:bgt|banget|gamau|gak|ga|nggak|ngga|engga|enggak|udah|udh|aja|doang|yg|dgn|kyk|kayak|kaya|huhu+|hehe+|wkwk+|yaa+|nih|sih|dong|deh|kok|tuh|tp|jd|krn|gw|gue)\b/u';

    /** Padanan santai yang aman diganti satu lawan satu. */
    private const SWAPS = [
        'tidak' => 'nggak',
        'namun' => 'tapi',
        'tetapi' => 'tapi',
        'hendak' => 'mau',
        'kemudian' => 'terus',
        'ingin' => 'pengin',
        'saat ini' => 'sekarang',
        'apabila' => 'kalau',
        'sehingga' => 'jadi',
        'mengapa' => 'kenapa',
        'bagaimana' => 'gimana',
        'hanya' => 'cuma',
        'sudah' => 'udah',
        'merasakan' => 'ngerasain',
        'merasa' => 'ngerasa',
        'menghadapi' => 'ngadepin',
        'mengerjakan' => 'ngerjain',
        'dikerjakan' => 'dikerjain',
    ];

    /** Kata baku yang tidak diganti otomatis tetapi dilarang lewat arahan giliran. */
    private const FORMAL_ONLY = ['sungguh', 'tersebut', 'merupakan', 'sangat', 'dapat', 'mengharukan', 'tabah', 'miris', 'apakah', 'kiranya', 'mohon'];

    public function isCasual(string $text): bool
    {
        $lower = mb_strtolower($text);

        if (preg_match('/(\p{L})\1{2,}/u', $lower) === 1) {
            return true;
        }

        preg_match_all(self::CASUAL_PATTERN, $lower, $matches);

        return count(array_unique($matches[0])) >= 2;
    }

    /**
     * Kata terlalu baku yang muncul di teks (tanpa duplikat, urutan kemunculan).
     *
     * @return list<string>
     */
    public function formalWords(string $text): array
    {
        $words = array_merge(array_keys(self::SWAPS), self::FORMAL_ONLY);
        $found = [];

        if (preg_match_all($this->pattern($words), mb_strtolower($text), $matches) > 0) {
            $found = array_values(array_unique($matches[0]));
        }

        return $found;
    }

    public function casualize(string $reply): string
    {
        $result = preg_replace_callback(
            $this->pattern(array_keys(self::SWAPS)),
            static function (array $match): string {
                $replacement = self::SWAPS[mb_strtolower($match[0])];

                return mb_strtoupper(mb_substr($match[0], 0, 1)) === mb_substr($match[0], 0, 1)
                    ? mb_strtoupper(mb_substr($replacement, 0, 1)).mb_substr($replacement, 1)
                    : $replacement;
            },
            $reply,
        );

        return $result ?? $reply;
    }

    /** @param list<string> $words */
    private function pattern(array $words): string
    {
        usort($words, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        return '/\b(?:'.implode('|', array_map(static fn (string $word): string => preg_quote($word, '/'), $words)).')\b/iu';
    }
}
