<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\ExportOptions;

/**
 * Kontrak eksportir data fitur AI (FR-030, RULE-072, Architecture bagian 9).
 * Fitur AI baru = satu kelas yang mengimplementasikan kontrak ini + satu baris di
 * `config('relaxboss.exporters')`; bukan perintah ekspor baru.
 */
interface AiDataExporter
{
    /** Nama fitur untuk argumen perintah, mis. `chat` atau `assessment`. */
    public function feature(): string;

    /**
     * Sumber data yang akan diekspor, urut dan dibaca bertahap. WAJIB hanya memuat pengguna dengan
     * `ai_training_consent_choice = granted` dan WAJIB memilih kolom secara eksplisit tanpa `user_id`.
     *
     * @return iterable<object>
     */
    public function query(ExportOptions $options): iterable;

    /**
     * Satu record JSON tanpa identitas. Tidak boleh memuat `id` (ID acak diberikan perintah), nama, email,
     * ID pengguna, jurusan, atau kampus; teks bebas wajib lewat `ContactScrubber`. `is_crisis` selalu ada.
     *
     * @return array<string, mixed>
     */
    public function toRecord(object $row): array;
}
