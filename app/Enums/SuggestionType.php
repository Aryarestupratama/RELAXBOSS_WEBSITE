<?php

declare(strict_types=1);

namespace App\Enums;

/** Jenis saran tindakan pada pesan AI (ENT-008). Null di kolom berarti tidak ada saran. */
enum SuggestionType: string
{
    case EscalateCrisis = 'escalate_crisis';
    case RecommendProfessional = 'recommend_professional';
    case RecommendSupport = 'recommend_support';
}
