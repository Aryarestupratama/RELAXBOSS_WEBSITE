<?php

declare(strict_types=1);

namespace App\Enums;

/** Status Percakapan (ENT-007). */
enum ConversationStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
