<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\RecordAdminAccess;
use App\Enums\TrainingConsentChoice;
use App\Exceptions\SpreadsheetExportException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportDataRequest;
use App\Models\AdminAccessLog;
use App\Models\User;
use App\Services\Export\AiDataExporterRegistry;
use App\Services\Export\AiDataSpreadsheetExport;
use App\Support\Aggregate;
use App\Support\ApiError;
use App\Support\Wib;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * TASK-057 (FR-030): ekspor dataset chatbot dan Asesmen ke Excel dari Admin. Hanya pengguna yang menyetujui
 * pelatihan, tanpa identitas (RULE-070); data diambil lewat eksportir yang sama dengan perintah Artisan.
 * Setiap unduhan dicatat di `admin_access_logs` sebelum berkas dikirim (RULE-042). Berkas sementara dihapus
 * setelah dikirim. Tanpa jadwal dan tanpa penyimpanan permanen.
 */
final class ExportController extends Controller
{
    public function show(AiDataExporterRegistry $registry): Response
    {
        $consented = User::query()
            ->where('ai_training_consent_choice', TrainingConsentChoice::Granted->value)
            ->count();

        return Inertia::render('Admin/Monitoring/Ekspor', [
            'datasets' => $registry->spreadsheetFeatures(),
            // Angka agregat untuk admin disamarkan bila kelompok kecil (RULE-042).
            'consented_users' => Aggregate::count($consented),
            'today' => Wib::date(),
        ]);
    }

    public function download(ExportDataRequest $request, AiDataSpreadsheetExport $export, RecordAdminAccess $record): BinaryFileResponse|JsonResponse
    {
        $features = $request->features();

        // Dicatat sebelum isi dikirim; bila pencatatan gagal, tidak ada berkas yang keluar.
        $record->handle($request->user(), AdminAccessLog::RESOURCE_EXPORT, implode('+', $features));

        // Pengambilan dan dekripsi ribuan pesan bisa melewati batas waktu bawaan hosting.
        @set_time_limit(180);

        try {
            $result = $export->build($features, $request->options());
        } catch (SpreadsheetExportException $e) {
            $status = $e->reason === SpreadsheetExportException::TOO_MANY_ROWS ? 422 : 500;

            return ApiError::response($e->reason, $e->getMessage(), $status);
        }

        if ($result === null) {
            return ApiError::response(
                'no_data',
                'Belum ada data untuk dataset dan rentang ini. Hanya pengguna yang menjawab "Ya" pada persetujuan pelatihan yang ikut diekspor.',
                422,
            );
        }

        $counts = collect($result->records)->map(static fn (int $count, string $feature): string => "{$feature}={$count}")->implode(',');
        $name = 'relaxboss-'.implode('-', $features).'-'.Wib::now()->format('Ymd-His').'.xlsx';

        return response()
            ->download($result->path, $name, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-store, private',
                'X-Export-Counts' => $counts,
            ])
            ->deleteFileAfterSend(true);
    }
}
