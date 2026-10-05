<?php

declare(strict_types=1);

namespace App\DTOs;

use SensitiveParameter;

/**
 * Satu API key dalam pool. `number` (mulai 1) adalah satu-satunya identitas yang boleh masuk log
 * atau kunci cache (RULE-040, RULE-045). Isi key tidak boleh tampil di dump, string, atau jejak galat.
 */
final readonly class AiKey
{
    public function __construct(
        public int $number,
        #[SensitiveParameter] public string $secret,
    ) {}

    /** @return array<string, int> */
    public function __debugInfo(): array
    {
        return ['number' => $this->number];
    }
}
