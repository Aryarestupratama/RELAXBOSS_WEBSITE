<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\RecordAdminAccess;
use App\Http\Controllers\Controller;
use App\Models\AdminAccessLog;
use App\Services\Admin\MonitoringPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SCR-022 (FR-031): tinjauan percakapan dan hasil Asesmen tanpa identitas, hanya baca.
 * Daftar hanya metadata. Halaman detail mencatat `admin_access_logs` sebelum isi dikirim.
 * Sengaja tanpa route ubah, balas, hapus, atau ekspor.
 */
final class MonitoringController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.monitoring.chat.index');
    }

    public function chatIndex(Request $request, MonitoringPresenter $presenter): Response
    {
        $filter = $presenter->normalizeChatFilter($request->query('saring'));

        return Inertia::render('Admin/Monitoring/ChatIndex', [
            'filter' => $filter,
            ...$presenter->conversations($filter),
        ]);
    }

    public function chatShow(Request $request, string $conversation, MonitoringPresenter $presenter, RecordAdminAccess $record): Response
    {
        $found = $presenter->conversation($conversation);
        abort_if($found === null, 404);

        $record->handle($request->user(), AdminAccessLog::RESOURCE_CONVERSATION, $conversation);

        return Inertia::render('Admin/Monitoring/ChatShow', $found);
    }

    public function attemptIndex(Request $request, MonitoringPresenter $presenter): Response
    {
        $filter = $presenter->normalizeAttemptFilter($request->query('saring'));

        return Inertia::render('Admin/Monitoring/AsesmenIndex', [
            'filter' => $filter,
            ...$presenter->attempts($filter),
        ]);
    }

    public function attemptShow(Request $request, string $attempt, MonitoringPresenter $presenter, RecordAdminAccess $record): Response
    {
        $found = $presenter->attempt((int) $attempt);
        abort_if($found === null, 404);

        $record->handle($request->user(), AdminAccessLog::RESOURCE_ATTEMPT, (int) $attempt);

        return Inertia::render('Admin/Monitoring/AsesmenShow', ['attempt' => $found]);
    }
}
