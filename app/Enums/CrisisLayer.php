<?php

declare(strict_types=1);

namespace App\Enums;

/** Lapisan pendeteksi krisis (ENT-009). */
enum CrisisLayer: string
{
    /** Pesan chat. */
    case Rule = 'rule';
    /** Jawaban PFA pada Asesmen. */
    case PfaRule = 'pfa_rule';
}
