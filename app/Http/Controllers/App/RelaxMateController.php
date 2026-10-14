<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\Chat\StartConversation;
use App\Http\Controllers\Controller;
use App\Services\Chat\ChatPresenter;
use App\Services\Chat\ChatQuota;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** SCR-017: daftar Percakapan dan halaman Percakapan. Selalu lewat relasi pemilik (RULE-033). */
final class RelaxMateController extends Controller
{
    /** Daftar Percakapan yang sudah berisi pesan, terbaru dulu. Tanpa judul (hanya tanggal dan jam). */
    public function index(Request $request, ChatPresenter $presenter): Response
    {
        return Inertia::render('App/RelaxMate/Index', $this->conversationList($request, $presenter));
    }

    /** API-014: mulai Percakapan (butuh consent, dipasang di route). */
    public function store(Request $request, StartConversation $action): RedirectResponse
    {
        $conversation = $action->handle($request->user());

        return redirect()->route('app.relaxmate.show', ['conversation' => $conversation->id]);
    }

    /** Halaman Percakapan. Milik orang lain atau tidak ada: 404. Terbuka tanpa consent agar dialog tampil. */
    public function show(Request $request, string $conversation, ChatPresenter $presenter, ChatQuota $quota): Response
    {
        $model = $request->user()->conversations()->findOrFail($conversation);

        return Inertia::render('App/RelaxMate/Show', [
            ...$this->conversationList($request, $presenter),
            'conversation' => [
                'id' => $model->id,
                'label' => $presenter->dateLabel($model),
                'has_crisis' => $model->has_crisis,
            ],
            'messages' => $model->messages()->get()->map(fn ($message): array => $presenter->message($message))->values(),
            'unanswered' => $presenter->hasUnanswered($model),
            'limits' => [
                'message_max_chars' => (int) config('relaxboss.limits.message_max_chars'),
                'per_day' => $quota->limit(),
                'remaining_today' => $quota->remainingToday($request->user()),
            ],
        ]);
    }

    /**
     * Panel Percakapanmu (dipakai halaman daftar dan halaman Percakapan). Hanya milik pengguna (RULE-033).
     *
     * @return array{conversations: array<int, array{id: string, label: string, total_turns: int}>, prev_page_url: string|null, next_page_url: string|null}
     */
    private function conversationList(Request $request, ChatPresenter $presenter): array
    {
        $page = $request->user()->conversations()
            ->whereNotNull('last_message_at')
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->simplePaginate((int) config('relaxboss.limits.history_per_page'));

        return [
            'conversations' => $page->getCollection()->map(fn ($conversation): array => [
                'id' => $conversation->id,
                'label' => $presenter->dateLabel($conversation),
                'total_turns' => $conversation->total_turns,
            ])->values()->all(),
            'prev_page_url' => $page->previousPageUrl(),
            'next_page_url' => $page->nextPageUrl(),
        ];
    }
}
