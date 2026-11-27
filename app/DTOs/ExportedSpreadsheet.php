<?php

declare(strict_types=1);

namespace App\DTOs;

/** Hasil ekspor Excel: path berkas sementara (non-publik) dan jumlah record per fitur. */
final readonly class ExportedSpreadsheet
{
    /**
     * @param  array<string, int>  $records  fitur => jumlah record (percakapan atau hasil Asesmen)
     * @param  array<string, int>  $rows  lembar => jumlah baris data
     */
    public function __construct(
        public string $path,
        public array $records,
        public array $rows,
        public int $skippedUnreadable = 0,
    ) {}

    public function totalRecords(): int
    {
        return array_sum($this->records);
    }
}
