<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use App\Contracts\ChatCompletionClient;
use App\Models\Assessment;
use App\Services\Ai\GroqClient;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Semua panggilan AI lewat kontrak ini (RULE-030).
        $this->app->bind(ChatCompletionClient::class, GroqClient::class);
    }

    public function boot(): void
    {
        // Masa berlaku tautan dari config produk (RULE-026).
        config()->set('auth.verification.expire', (int) config('relaxboss.auth.verification_expire_minutes'));
        config()->set('auth.passwords.users.expire', (int) config('relaxboss.auth.reset_expire_minutes'));

        // Teks email mengikuti bank teks Design (RULE-054).
        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            $minutes = (int) config('relaxboss.auth.verification_expire_minutes');

            return (new MailMessage)
                ->subject('Verifikasi email RelaxBoss')
                ->greeting('Halo!')
                ->line('Terima kasih sudah mendaftar di RelaxBoss. Tekan tombol di bawah untuk memverifikasi emailmu.')
                ->action('Verifikasi email', $url)
                ->line("Tautan ini berlaku {$minutes} menit.")
                ->line('Kalau kamu tidak merasa mendaftar, abaikan saja email ini.')
                ->salutation('Salam tenang, RelaxBoss');
        });

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $minutes = (int) config('relaxboss.auth.reset_expire_minutes');
            $url = route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);

            return (new MailMessage)
                ->subject('Atur ulang kata sandi RelaxBoss')
                ->greeting('Halo!')
                ->line('Kami menerima permintaan untuk mengatur ulang kata sandi akunmu.')
                ->action('Atur ulang kata sandi', $url)
                ->line("Tautan ini berlaku {$minutes} menit.")
                ->line('Kalau kamu tidak memintanya, abaikan saja email ini. Kata sandimu tidak berubah.')
                ->salutation('Salam tenang, RelaxBoss');
        });

        // `{activeAssessment}` di area aplikasi hanya Asesmen aktif, dicari lewat slug (API-008: 404 bila nonaktif).
        Route::bind('activeAssessment', function (string $value): Assessment {
            return Assessment::query()->active()->where('slug', $value)->firstOrFail();
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

        // API-015 (RULE-044): batas pesan per menit per pengguna. Respons JSON karena chat memakai fetch.
        RateLimiter::for('chat', function (Request $request): Limit {
            return Limit::perMinute((int) config('relaxboss.limits.chat_per_minute'))
                ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()))
                ->response(function (Request $request, array $headers) {
                    $seconds = (int) ($headers['Retry-After'] ?? 60);

                    return response()->json(['error' => [
                        'code' => 'rate_limited',
                        'message' => "Terlalu banyak pesan. Coba lagi dalam {$seconds} detik.",
                    ]], 429, $headers);
                });
        });

        // API-011 (RULE-044): batas entri mood per menit per pengguna, dikembalikan sebagai galat form `mood`.
        RateLimiter::for('mood', function (Request $request): Limit {
            return Limit::perMinute((int) config('relaxboss.limits.mood_per_minute'))
                ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip()))
                ->response(function (Request $request, array $headers) {
                    $seconds = (int) ($headers['Retry-After'] ?? 60);

                    return redirect()->route('app.mood.index')->withErrors([
                        'mood' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
                    ]);
                });
        });
    }
}
