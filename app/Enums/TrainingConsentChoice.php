<?php

declare(strict_types=1);

namespace App\Enums;

/** Pilihan persetujuan pelatihan model (FR-013, RULE-071). Tanpa nilai awal: kolom null = belum memilih. */
enum TrainingConsentChoice: string
{
    case Granted = 'granted';
    case Denied = 'denied';
}
