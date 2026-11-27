<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Berkas Excel tidak bisa dibuat. `code` adalah penanda untuk klien:
 * `zip_missing` (ekstensi PHP zip tidak aktif), `too_many_rows` (melebihi batas Excel), `write_failed`.
 * Pesan berisi teks aman untuk ditampilkan; tidak pernah memuat isi data (RULE-040).
 */
final class SpreadsheetExportException extends RuntimeException
{
    public const ZIP_MISSING = 'zip_missing';

    public const TOO_MANY_ROWS = 'too_many_rows';

    public const WRITE_FAILED = 'write_failed';

    public function __construct(public readonly string $reason, string $message)
    {
        parent::__construct($message);
    }
}
