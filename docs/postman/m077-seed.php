<?php

// TASK-M077: data uji untuk API-M019. Dijalankan DI DALAM tinker: include 'docs/postman/m077-seed.php';
// Hanya akun @example.test (RULE-008). Aman dijalankan ulang: akun m077-* lama dihapus dulu.

use App\Models\User;
use App\Support\Wib;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

$old = User::where('email', 'like', 'm077-%@example.test')->pluck('id');
PersonalAccessToken::whereIn('tokenable_id', $old)->delete();
User::whereIn('id', $old)->delete();

$a = User::factory()->create(['email' => 'm077-a@example.test']);
$b = User::factory()->create(['email' => 'm077-b@example.test']);
$c = User::factory()->unverified()->create(['email' => 'm077-c@example.test']);

// $daysAgo hari lalu (WIB), jam $hour WIB. `entry_date` dan `logged_at` mengikuti aturan RULE-035.
$add = function (User $user, int $daysAgo, int $hour, int $mood, ?string $note = null): void {
    $moment = Wib::now()->startOfDay()->subDays($daysAgo)->addHours($hour);
    $user->moodEntries()->create([
        'mood' => $mood,
        'note' => $note,
        'entry_date' => Wib::date($moment),
        'logged_at' => $moment->utc(),
    ]);
};

$add($a, 0, 0, 2);                      // hari ini, dini hari, mood 2 (ditimpa catatan berikutnya)
$add($a, 0, 1, 4, 'RAHASIA-M077');      // hari ini, mood 4 = catatan TERAKHIR hari ini; ada catatan terenkripsi
$add($a, 1, 9, 3);                      // kemarin, 3
$add($a, 3, 9, 5);                      // 3 hari lalu, 5 (hari terbaik)
$add($a, 10, 9, 1);                     // 10 hari lalu: hanya tampil di days=30
$add($a, 40, 9, 2);                     // 40 hari lalu: di luar 7 maupun 30
$add($b, 0, 1, 5);                      // milik B, harus tidak pernah muncul di data A

$mk = fn (User $u) => $u->createToken('mobile-android', ['relaxmate'])->plainTextToken;
echo "\nSalin tiga token ini ke Variables koleksi Postman:\n";
echo 'tokenA = '.$mk($a)."\n";
echo 'tokenB = '.$mk($b)."\n";
echo 'tokenC = '.$mk($c)."\n";
echo 'Entri A: '.$a->moodEntries()->count().' (harus 6), entri B: '.$b->moodEntries()->count()." (harus 1)\n";
