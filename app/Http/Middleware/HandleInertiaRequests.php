<?php

namespace App\Http\Middleware;

use App\Services\Chat\CrisisContactList;
use App\Services\Consent\ConsentInfo;
use App\Services\Notification\NotificationFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            // Kontak bantuan untuk CrisisBanner. Sumber tunggal: config (OQ-3); tautan hanya https.
            'crisisContacts' => fn (): array => $this->crisisContacts(),
            // Status consent milik pengguna sendiri, untuk ConsentDialog dan halaman Akun (FR-013).
            'consent' => fn (): ?array => $this->consent($request),
            // Hanya penanda peran (tanpa identitas), untuk tautan "Admin" di AppShell.
            'auth' => fn (): ?array => $request->user() === null ? null : ['is_admin' => $request->user()->isAdmin()],
            // Hitungan lonceng (CMP-045): satu angka, tanpa isi notifikasi (ADR-019). Hanya untuk pengguna yang sudah masuk.
            'notifications' => fn (): ?array => $this->notifications($request),
            // Kode `->with('status', ...)` dari redirect, untuk toast (CMP-013). Teksnya ada di resources/js/lib/toastMessages.ts.
            'flash' => fn (): ?array => $this->flash($request),
        ];
    }

    /**
     * @return array{unread: int}|null
     */
    private function notifications(Request $request): ?array
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        return ['unread' => app(NotificationFeed::class)->unreadCount($user)];
    }

    /**
     * `id` unik per respons agar dua status yang sama berturut-turut (mis. catat mood dua kali) tetap memunculkan toast.
     *
     * @return array{id: string, status: string}|null
     */
    private function flash(Request $request): ?array
    {
        $status = $request->hasSession() ? $request->session()->get('status') : null;

        if (! is_string($status) || $status === '') {
            return null;
        }

        return ['id' => Str::random(8), 'status' => $status];
    }

    /**
     * @return array{ai_granted: bool, training_choice: ?string, training_answered: bool, complete: bool, texts: array<string, string>}|null
     */
    private function consent(Request $request): ?array
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        return [
            'ai_granted' => $user->hasAiConsent(),
            'training_choice' => $user->ai_training_consent_choice?->value,
            'training_answered' => $user->hasTrainingChoice(),
            'complete' => $user->hasCompletedAiConsent(),
            // Teks dari config (sumber tunggal bersama API mobile, OQ-MA8).
            'texts' => app(ConsentInfo::class)->texts(),
        ];
    }

    /**
     * @return list<array{name: string, phone: ?string, url: ?string, note: ?string, dummy: bool}>
     */
    private function crisisContacts(): array
    {
        return app(CrisisContactList::class)->all();
    }
}
