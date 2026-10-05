<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
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
