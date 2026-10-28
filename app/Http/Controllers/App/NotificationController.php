<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Notification\DismissNudge;
use App\Actions\Notification\MarkAnnouncementsRead;
use App\Enums\NotificationKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\DismissNudgeRequest;
use App\Services\Notification\NotificationFeed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** SCR-027: halaman Notifikasi. Dihitung saat dibuka (ADR-019); selalu milik pengguna yang masuk (RULE-033). */
final class NotificationController extends Controller
{
    public function index(Request $request, NotificationFeed $feed): Response
    {
        return Inertia::render('App/Notifikasi/Index', [
            'items' => $feed->for($request->user()),
        ]);
    }

    /** Tutup satu nudge untuk hari ini (WIB). */
    public function dismiss(DismissNudgeRequest $request, DismissNudge $action): RedirectResponse
    {
        $action->handle($request->user(), NotificationKind::from((string) $request->validated('kind')));

        return redirect()->route('app.notifications.index');
    }

    /** Tandai pengumuman sebagai dibaca (lonceng kembali bersih). */
    public function markRead(Request $request, MarkAnnouncementsRead $action): RedirectResponse
    {
        $action->handle($request->user());

        return redirect()->route('app.notifications.index');
    }
}
