<?php

declare(strict_types=1);

namespace App\Enums;

enum SeverityLevel: string
{
    case Normal = 'normal';
    case Mild = 'mild';
    case Moderate = 'moderate';
    case Severe = 'severe';
}
