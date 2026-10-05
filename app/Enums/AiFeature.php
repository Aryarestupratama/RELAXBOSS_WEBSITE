<?php

declare(strict_types=1);

namespace App\Enums;

/** Fitur pemakai AI; satu pool key dan satu baris `ai_usage_daily` per fitur (ENT-010). */
enum AiFeature: string
{
    case Chat = 'chat';
    case Assessment = 'assessment';
}
