<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Eksportir fitur AI yang juga bisa dituangkan ke lembar Excel (TASK-057). Datanya tetap berasal dari
 * `query()` dan `toRecord()` (hanya pengguna `granted`, tanpa identitas); kontrak ini hanya menambah cara
 * meratakan satu record menjadi baris tabel. Fitur AI baru yang ingin tersedia di ekspor Excel cukup
 * mengimplementasikannya (RULE-072), tanpa mengubah halaman atau perintah.
 */
interface SpreadsheetAiDataExporter extends AiDataExporter
{
    /**
     * Lembar yang dihasilkan fitur ini: nama lembar => [nama kolom => lebar kolom (karakter)].
     * Nama lembar maksimal 31 karakter tanpa `[ ] : * ? / \`. Nama kolom mengikuti kunci record JSONL.
     *
     * @return array<string, array<string, int>>
     */
    public function sheets(): array;

    /**
     * Baris tabel untuk satu record hasil `toRecord()`: nama lembar => daftar baris. Urutan sel di tiap baris
     * mengikuti urutan kolom di `sheets()`. Nilai berupa string, angka, boolean, atau null (sel kosong).
     * `$recordId` adalah ID acak per ekspor yang sama dengan ID record JSONL; satu-satunya penghubung antarlembar.
     * Tidak boleh memuat identitas pengguna.
     *
     * @param  array<string, mixed>  $record
     * @return array<string, list<list<string|int|float|bool|null>>>
     */
    public function rows(string $recordId, array $record): array;
}
