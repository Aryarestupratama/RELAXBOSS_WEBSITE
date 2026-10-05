<?php

declare(strict_types=1);

namespace App\Enums;

/** Pilihan tenaga opsional pada Mood Entry (ENT-006, `arousal_input`). */
enum ArousalInput: string
{
    case Energized = 'energized';
    case Tired = 'tired';

    public function label(): string
    {
        return match ($this) {
            self::Energized => 'Bertenaga',
            self::Tired => 'Lelah',
        };
    }
}
