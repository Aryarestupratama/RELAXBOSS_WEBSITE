<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\SaveAnnouncement;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnouncementRequest;
use App\Http\Requests\Admin\PreviewAnnouncementRequest;
use App\Models\Announcement;
use App\Services\Admin\AnnouncementAdminPresenter;
use App\Services\Article\ArticleRenderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SCR-028 (FR-038): kelola pengumuman pembaruan website. Konten global tanpa data pengguna.
 * Tanpa hapus: pengumuman dikembalikan ke draf bila tidak ingin terbaca lagi.
 */
final class AnnouncementController extends Controller
{
    public function index(AnnouncementAdminPresenter $presenter): Response
    {
        $announcements = $presenter->paginate();

        return Inertia::render('Admin/Pengumuman/Index', [
            'announcements' => $presenter->rows($announcements),
            'prev_page_url' => $announcements->previousPageUrl(),
            'next_page_url' => $announcements->nextPageUrl(),
        ]);
    }

    public function create(AnnouncementAdminPresenter $presenter): Response
    {
        return Inertia::render('Admin/Pengumuman/Form', [
            'form' => $presenter->form(null),
        ]);
    }

    public function store(AnnouncementRequest $request, SaveAnnouncement $action): RedirectResponse
    {
        $announcement = $action->handle(null, $request->announcement());

        return redirect()->route('admin.announcements.edit', $announcement)->with('status', 'announcement-created');
    }

    public function edit(Announcement $announcement, AnnouncementAdminPresenter $presenter): Response
    {
        return Inertia::render('Admin/Pengumuman/Form', [
            'form' => $presenter->form($announcement),
        ]);
    }

    public function update(AnnouncementRequest $request, Announcement $announcement, SaveAnnouncement $action): RedirectResponse
    {
        $action->handle($announcement, $request->announcement());

        return redirect()->route('admin.announcements.edit', $announcement)->with('status', 'announcement-updated');
    }

    /** Pratinjau Markdown dengan renderer yang sama dengan artikel (HTML mentah dibuang). */
    public function preview(PreviewAnnouncementRequest $request, ArticleRenderer $renderer): JsonResponse
    {
        return response()->json([
            'data' => ['html' => $renderer->html((string) $request->validated('body_markdown'))],
        ]);
    }
}
