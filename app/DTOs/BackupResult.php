<?php

declare(strict_types=1);

namespace App\DTOs;

/** Hasil satu pencadangan database (TASK-031). Hanya metadata berkas, bukan isinya. */
final readonly class BackupResult
{
    public function __construct(
        public string $fileName,
        public int $bytes,
        public int $pruned,
        public int $durationMs,
    ) {}
}
