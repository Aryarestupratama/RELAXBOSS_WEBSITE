<?php

declare(strict_types=1);

namespace App\DTOs;

/** Pesan yang dikirim ke AI beserta rencana giliran yang dipakai menyusunnya. */
final readonly class ChatContext
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function __construct(
        public array $messages,
        public TurnPlan $plan,
    ) {}
}
