<?php

declare(strict_types=1);

namespace App\Services\Chat;

/**
 * Daftar kontak bantuan dari config (OQ-3), satu sumber untuk web (prop bersama `crisisContacts`)
 * dan API mobile (`GET /crisis-contacts`). Tautan hanya `https`. Versi = sidik dari isi daftar,
 * jadi berubah sendiri bila kontak berubah dan klien tahu kapan menyegarkan salinannya.
 */
final class CrisisContactList
{
    /**
     * @return list<array{name: string, phone: ?string, url: ?string, note: ?string, dummy: bool}>
     */
    public function all(): array
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

    public function version(): string
    {
        return substr(sha1(json_encode($this->all(), JSON_UNESCAPED_UNICODE) ?: ''), 0, 12);
    }
}
