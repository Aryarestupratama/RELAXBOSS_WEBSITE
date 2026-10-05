<?php

declare(strict_types=1);

namespace App\Enums;

/** Peran pengirim pesan (ENT-008). Turn ganjil = pengguna, genap = AI. */
enum MessageRole: string
{
    case User = 'user';
    case Assistant = 'assistant';
}
