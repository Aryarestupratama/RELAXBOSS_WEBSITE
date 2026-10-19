<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Pencadangan database gagal. `detail` hanya untuk ditampilkan di konsol saat perintah dijalankan
 * manual; jangan pernah dicatat di log (RULE-040).
 */
final class BackupFailedException extends RuntimeException
{
    public function __construct(string $message, public readonly string $detail = '')
    {
        parent::__construct($message);
    }
}
