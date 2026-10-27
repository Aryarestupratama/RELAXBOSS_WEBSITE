<?php

namespace App\Http\Middleware;

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
            // Kode `->with('status', ...)` dari redirect, untuk toast (CMP-013). Teksnya ada di resources/js/lib/toastMessages.ts.
            'flash' => fn (): ?array => $this->flash($request),
        ];
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
     * @return array{ai_granted: bool, training_choice: ?string, training_answered: bool, complete: bool}|null
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
        ];
    }

    /**
     * @return list<array{name: string, phone: ?string, url: ?string, note: ?string, dummy: bool}>
     */
    private function crisisContacts(): array
    {
        $contacts = [];

        foreach ((array) config('relaxboss.crisis_contacts') as $contact) {
            if (! is_array($contact) || ! filled($contact['name'] ?? null)) {
                continue;
            }

            $url = $contact['url'] ?? null;

            $contacts[] = [
                'name' => (string) $contact['name'],
                'phone' => filled($contact['phone'] ?? null) ? (string) $contact['phone'] : null,
                'url' => is_string($url) && str_starts_with($url, 'https://') ? $url : null,
                'note' => filled($contact['note'] ?? null) ? (string) $contact['note'] : null,
                'dummy' => (bool) ($contact['dummy'] ?? false),
            ];
        }

        return $contacts;
    }
}
