<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Crisis Response statis (FR-018). Teks mengikuti bank teks Design 1.6; daftar kontak tidak ditulis
 * di sini, selalu dari `config('relaxboss.crisis_contacts')` (OQ-3) dan dirender oleh CrisisBanner.
 */
final class CrisisResponse
{
    public const TEXT = 'Aku mendengarmu, dan aku senang kamu mau bercerita. Kamu tidak harus menghadapi ini sendirian. Kalau kamu berpikir untuk menyakiti dirimu sendiri, tolong hubungi bantuan sekarang atau minta seseorang yang kamu percaya menemanimu.';
}
