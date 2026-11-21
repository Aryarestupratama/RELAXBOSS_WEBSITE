<?php

// TASK-M078: data uji untuk API-M020 sampai M022. Dijalankan DI DALAM tinker: include 'docs/postman/m078-seed.php';
// Hanya akun @example.test (RULE-008). Aman dijalankan ulang: akun m078-* lama dihapus dulu.

use App\Models\User;
use App\Support\Wib;
use Laravel\Sanctum\PersonalAccessToken;

$old = User::where('email', 'like', 'm078-%@example.test')->pluck('id');
PersonalAccessToken::whereIn('tokenable_id', $old)->delete();
User::whereIn('id', $old)->delete();

$a = User::factory()->create(['email' => 'm078-a@example.test']);
$b = User::factory()->create(['email' => 'm078-b@example.test']);
$c = User::factory()->unverified()->create(['email' => 'm078-c@example.test']);

// $daysAgo hari lalu (WIB), jam $hour WIB. `entry_date` dan `logged_at` mengikuti aturan RULE-035.
$add = function (User $user, int $daysAgo, int $hour, int $mood, ?string $note = null, ?string $arousal = null): void {
    $moment = Wib::now()->startOfDay()->subDays($daysAgo)->addHours($hour);
    $user->moodEntries()->create([
        'mood' => $mood,
        'arousal_input' => $arousal,
        'note' => $note,
        'entry_date' => Wib::date($moment),
        'logged_at' => $moment->utc(),
    ]);
};

// A: 13 entri dengan catatan (halaman pertama 10, kedua 3) + 1 entri tanpa catatan. Tidak ada entri hari ini,
// supaya POST di folder 02 tidak terbentur batas harian.
for ($i = 1; $i <= 13; $i++) {
    $add($a, $i + 1, 9, (($i - 1) % 5) + 1, "RAHASIA-M078-A-{$i}", $i % 2 === 0 ? 'tired' : null);
}
$add($a, 1, 8, 3);                                  // tanpa catatan: tidak boleh muncul di buku harian
$add($b, 2, 9, 5, 'RAHASIA-M078-B');                // milik B: tidak boleh pernah muncul di data A

$mk = fn (User $u) => $u->createToken('mobile-android', ['relaxmate'])->plainTextToken;
echo "\nSalin tiga token ini ke Variables koleksi Postman:\n";
echo 'tokenA = '.$mk($a)."\n";
echo 'tokenB = '.$mk($b)."\n";
echo 'tokenC = '.$mk($c)."\n";
echo 'Entri A: '.$a->moodEntries()->count().' (harus 14), dengan catatan: '.$a->moodEntries()->whereNotNull('note')->count().' (harus 13), entri B: '.$b->moodEntries()->count()." (harus 1)\n";
