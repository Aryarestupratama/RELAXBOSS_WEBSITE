<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Masa berlaku tautan verifikasi dari config produk (RULE-026).
        config()->set('auth.verification.expire', (int) config('relaxboss.auth.verification_expire_minutes'));

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            $minutes = (int) config('relaxboss.auth.verification_expire_minutes');

            return (new MailMessage)
                ->subject('Verifikasi email RelaxBoss')
                ->greeting('Halo, '.$notifiable->name.'!')
                ->line('Terima kasih sudah mendaftar di RelaxBoss. Klik tombol di bawah untuk memverifikasi emailmu.')
                ->action('Verifikasi email', $url)
                ->line("Tautan ini berlaku {$minutes} menit.")
                ->line('Kalau kamu tidak merasa mendaftar, abaikan saja email ini.')
                ->salutation('Salam, tim RelaxBoss');
        });

        // API-001: batas pendaftaran per IP, dikembalikan sebagai galat form `register`.
        RateLimiter::for('register', function (Request $request): Limit {
            return Limit::perMinute((int) config('relaxboss.auth.register_per_minute'))
                ->by((string) $request->ip())
                ->response(function (Request $request, array $headers) {
                    $seconds = (int) ($headers['Retry-After'] ?? 60);

                    return redirect()->route('register')->withErrors([
                        'register' => "Terlalu banyak percobaan mendaftar. Coba lagi dalam {$seconds} detik.",
                    ]);
                });
        });
    }
}
