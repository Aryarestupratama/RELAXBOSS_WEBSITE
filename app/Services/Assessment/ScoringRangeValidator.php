<?php

declare(strict_types=1);

namespace App\Services\Assessment;

/**
 * FR-023, Schema ENT-004: aturan skor per subskala tidak boleh tumpang tindih dan harus menutup
 * seluruh kemungkinan skor. Kelas murni (tanpa database), dipakai FormRequest admin.
 *
 * Rentang skor mengikuti `ScoringService`: jumlah nilai butir per subskala (pembalikan tidak mengubah
 * rentang, karena `maks + min - nilai` tetap di antara min dan maks), dikali pengali, dibulatkan.
 * Rentang aturan wajib bersambung tanpa celah (min berikutnya = maks sebelumnya + 1).
 */
final class ScoringRangeValidator
{
    /** Batas kolom smallint pada `assessment_scoring_rules`. */
    public const SCORE_LIMIT = 32767;

    /**
     * @param  list<int>  $optionValues  nilai pilihan jawaban Instrumen
     * @param  list<string>  $questionSubScales  satu entri per butir
     * @param  array<int, array{sub_scale: string, min_score: int, max_score: int}>  $rules  kunci = indeks baris di form
     * @return array<string, string> galat per jalur kolom (`rules`, `rules.2.min_score`, ...)
     */
    public function validate(array $optionValues, float $multiplier, array $questionSubScales, array $rules): array
    {
        $errors = [];

        if ($optionValues === []) {
            return $errors;
        }

        $lowestOption = min($optionValues);
        $highestOption = max($optionValues);

        /** @var array<string, int> $questionCount */
        $questionCount = [];
        foreach ($questionSubScales as $subScale) {
            $questionCount[$subScale] = ($questionCount[$subScale] ?? 0) + 1;
        }

        /** @var array<string, list<int>> $rowsBySubScale indeks baris aturan per subskala */
        $rowsBySubScale = [];
        foreach ($rules as $index => $rule) {
            if (! isset($questionCount[$rule['sub_scale']])) {
                $errors["rules.{$index}.sub_scale"] = 'Subskala ini tidak dipakai oleh butir mana pun.';

                continue;
            }

            if ($rule['min_score'] > $rule['max_score']) {
                $errors["rules.{$index}.max_score"] = 'Skor maksimal tidak boleh lebih kecil dari skor minimal.';

                continue;
            }

            $rowsBySubScale[$rule['sub_scale']][] = $index;
        }

        foreach ($questionCount as $subScale => $count) {
            $lowest = (int) round($count * $lowestOption * $multiplier);
            $highest = (int) round($count * $highestOption * $multiplier);

            if ($highest > self::SCORE_LIMIT) {
                $errors['rules'] = "Skor tertinggi subskala \"{$subScale}\" ({$highest}) melebihi batas ".self::SCORE_LIMIT.'. Kurangi butir atau pengali.';

                continue;
            }

            $rows = $rowsBySubScale[$subScale] ?? [];

            if ($rows === []) {
                $errors['rules'] ??= "Subskala \"{$subScale}\" belum punya aturan skor (rentang {$lowest} sampai {$highest}).";

                continue;
            }

            usort($rows, static fn (int $a, int $b): int => $rules[$a]['min_score'] <=> $rules[$b]['min_score']);

            $first = $rows[0];
            if ($rules[$first]['min_score'] > $lowest) {
                $errors["rules.{$first}.min_score"] = "Rentang subskala \"{$subScale}\" harus mulai dari {$lowest} atau lebih rendah.";
            }

            for ($i = 1, $total = count($rows); $i < $total; $i++) {
                $previous = $rules[$rows[$i - 1]];
                $current = $rules[$rows[$i]];

                if ($current['min_score'] <= $previous['max_score']) {
                    $errors["rules.{$rows[$i]}.min_score"] = "Tumpang tindih dengan aturan sebelumnya (sampai {$previous['max_score']}).";
                } elseif ($current['min_score'] > $previous['max_score'] + 1) {
                    $gapStart = $previous['max_score'] + 1;
                    $gapEnd = $current['min_score'] - 1;
                    $errors["rules.{$rows[$i]}.min_score"] = "Ada celah: skor {$gapStart} sampai {$gapEnd} belum tercakup.";
                }
            }

            $last = $rows[count($rows) - 1];
            if ($rules[$last]['max_score'] < $highest) {
                $errors["rules.{$last}.max_score"] = "Rentang subskala \"{$subScale}\" harus mencapai {$highest} atau lebih tinggi.";
            }
        }

        return $errors;
    }
}
