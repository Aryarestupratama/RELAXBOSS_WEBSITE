<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\AdminAccessLog;
use App\Models\User;

/**
 * Mencatat pembukaan isi oleh admin (FR-031, RULE-042). Dipanggil sebelum isi dikirim ke peramban:
 * bila pencatatan gagal, isi tidak tampil. Hanya menyimpan ID admin, jenis, dan ID sumber daya.
 */
final class RecordAdminAccess
{
    public function handle(User $admin, string $resourceType, string|int $resourceId): void
    {
        AdminAccessLog::query()->create([
            'admin_id' => $admin->id,
            'resource_type' => $resourceType,
            'resource_id' => (string) $resourceId,
        ]);
    }
}
