<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\AiCompletion;
use App\Enums\AiFeature;
use App\Exceptions\AiUnavailableException;

/**
 * Satu-satunya pintu ke penyedia AI (RULE-030). Dilarang memanggil `Http` ke Groq dari tempat lain.
 */
interface ChatCompletionClient
{
    /**
     * Mengirim percakapan dan mengembalikan balasan model.
     * Pemanggil menyusun system prompt sebagai pesan pertama dan bertanggung jawab atas batas masukan.
     *
     * @param  list<array{role: string, content: string}>  $messages
     *
     * @throws AiUnavailableException bila semua key dan model gagal (pemanggil jatuh ke respons statis)
     */
    public function complete(AiFeature $feature, array $messages): AiCompletion;
}
