<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\DTOs\TurnPlan;
use App\Enums\DialogueAct;

/**
 * Memilih jenis balasan tiap giliran dari riwayat (tanpa memanggil AI) dan menegakkan larangan bertanya.
 *
 * Aturan (angka asumsi, RULE-026): setelah satu pertanyaan, minimal `QUIET_TURNS` balasan berikutnya tanpa
 * pertanyaan (rasio cermin banding tanya sekitar 2:1). Satu tawaran pilihan ("didengarkan saja atau cari
 * langkah kecil") muncul setelah pengguna bercerita beberapa giliran. Permintaan bantuan dan pamit selalu
 * didahulukan. Pertanyaan penegas ringan di akhir kalimat ("... ya?", "... kan?") tidak dihitung pertanyaan.
 *
 * Riwayat berupa larik biasa agar dapat diuji tanpa database.
 */
final class ConversationPlanner
{
    private const QUIET_TURNS = 2;

    private const OFFER_AFTER_USER_TURNS = 4;

    private const LONG_FIRST_MESSAGE = 100;

    private const SUBSTANTIVE_MESSAGE = 25;

    private const VALIDATION_STRATEGIES = ['validation', 'affirmation'];

    /** Ciri balasan yang hanya memvalidasi atau menamai perasaan (model sering tak melabelinya `validation`). */
    private const VALIDATION_PATTERN = '/\b(?:wajar|pantes|pantas|pasti\s+(?:berat|capek|lelah|susah|nggak\s+enak)|(?:nggak|gak|tidak)\s+gampang|valid|aku\s+(?:paham|ngerti|mengerti)|kedengarannya\s+(?:berat|capek|lelah)|(?:berat|capek|melelahkan)\s+banget|bikin\s+(?:kamu\s+)?(?:capek|kesel|lelah|stres|sedih|frustrasi))\b/u';

    /** Fokus bergilir untuk giliran CERMIN supaya balasan tidak berulang pola. */
    private const REFLECT_FOCUS = [
        'Cermin satu detail spesifik dari ceritanya (orang, kejadian, atau kata yang ia pakai) dengan kata lain.',
        'Hubungkan dua hal yang pernah ia ceritakan di percakapan ini dan sebut pola atau kaitannya dengan hati-hati ("kedengarannya...", "sepertinya..."), tanpa menyimpulkan di luar yang ia katakan.',
        'Rangkum inti ceritanya sejauh ini dalam satu kalimat, lalu satu kalimat normalisasi singkat tanpa angka atau statistik, atau satu kalimat yang membuka ruang untuknya melanjutkan.',
    ];

    /**
     * @param  list<array{role: string, content: string, strategy?: ?string, flow_action?: ?string}>  $turns  urut lama ke baru, pesan pengguna terbaru di akhir
     */
    public function plan(array $turns): TurnPlan
    {
        $latest = '';
        $userCount = 0;
        $assistants = [];

        foreach ($turns as $turn) {
            if ($turn['role'] === 'user') {
                $userCount++;
                $latest = $turn['content'];
            } else {
                $assistants[] = $turn;
            }
        }

        $text = mb_strtolower(trim($latest));
        $length = mb_strlen($text);
        $recent = array_slice($assistants, -2);
        $avoidValidation = count(array_filter(
            $recent,
            static fn (array $turn): bool => in_array($turn['strategy'] ?? null, self::VALIDATION_STRATEGIES, true)
                || preg_match(self::VALIDATION_PATTERN, mb_strtolower($turn['content'])) === 1,
        )) > 0;

        $focus = self::REFLECT_FOCUS[count($assistants) % count(self::REFLECT_FOCUS)];

        $make = static fn (DialogueAct $act, bool $listenOnly = false): TurnPlan => new TurnPlan(
            $act,
            $avoidValidation,
            $listenOnly,
            focus: $act === DialogueAct::Reflect ? $focus : null,
        );

        if ($this->isClosing($text)) {
            return $make(DialogueAct::Close);
        }

        if ($this->wantsListeningOnly($text)) {
            return $make($length >= self::SUBSTANTIVE_MESSAGE ? DialogueAct::Reflect : DialogueAct::Invite, true);
        }

        if ($this->needsHelp($text)) {
            return $make(DialogueAct::Help);
        }

        if ($assistants === []) {
            return $make($length >= self::LONG_FIRST_MESSAGE ? DialogueAct::Reflect : DialogueAct::Ask);
        }

        if ($this->quietStreak($assistants) < self::QUIET_TURNS) {
            return $make($length >= self::SUBSTANTIVE_MESSAGE ? DialogueAct::Reflect : DialogueAct::Invite);
        }

        if ($userCount >= self::OFFER_AFTER_USER_TURNS && ! $this->offeredRecently($assistants)) {
            return $make(DialogueAct::OfferChoice);
        }

        return $make(DialogueAct::Ask);
    }

    /**
     * Bila giliran ini dilarang bertanya tetapi balasan memuat pertanyaan: buang kalimat tanya. Bila tak ada
     * yang tersisa, balasan dikembalikan apa adanya dengan `violated = true` supaya pemanggil bisa mengulang.
     *
     * @return array{reply: string, violated: bool}
     */
    public function enforce(TurnPlan $plan, string $reply): array
    {
        if ($plan->allowsQuestion() || ! $this->asksQuestion($reply)) {
            return ['reply' => $reply, 'violated' => false];
        }

        $kept = [];

        foreach (preg_split('/\R+/u', trim($reply)) ?: [] as $line) {
            $sentences = array_values(array_filter(
                $this->sentences($line),
                fn (string $sentence): bool => ! $this->isQuestion($sentence),
            ));

            if ($sentences !== []) {
                $kept[] = implode(' ', $sentences);
            }
        }

        $result = trim(implode("\n", $kept));

        if (mb_strlen($result) < 8) {
            return ['reply' => $reply, 'violated' => true];
        }

        return ['reply' => $result, 'violated' => false];
    }

    public function asksQuestion(string $reply): bool
    {
        foreach ($this->sentences($reply) as $sentence) {
            if ($this->isQuestion($sentence)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<string> */
    private function sentences(string $text): array
    {
        $parts = preg_split('/(?<=[.!?\x{2026}])\s+|\R+/u', trim($text)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn (string $part): bool => $part !== ''));
    }

    private function isQuestion(string $sentence): bool
    {
        if (! str_contains($sentence, '?')) {
            return false;
        }

        // "capek banget ya?" mencerminkan, bukan menginterogasi.
        return preg_match('/\b(?:ya|yaa|yah|kan)\s*\?+[^\p{L}\p{N}]*$/iu', $sentence) !== 1;
    }

    /** @param list<array{role: string, content: string, strategy?: ?string, flow_action?: ?string}> $assistants */
    private function quietStreak(array $assistants): int
    {
        $streak = 0;

        foreach (array_reverse($assistants) as $turn) {
            if ($this->asksQuestion($turn['content'])) {
                break;
            }

            $streak++;
        }

        return $streak;
    }

    /** @param list<array{role: string, content: string, strategy?: ?string, flow_action?: ?string}> $assistants */
    private function offeredRecently(array $assistants): bool
    {
        foreach (array_slice($assistants, -4) as $turn) {
            if (($turn['flow_action'] ?? null) === 'suggest_action') {
                return true;
            }
        }

        return false;
    }

    private function isClosing(string $text): bool
    {
        return mb_strlen($text) <= 80
            && preg_match('/\b(?:makasih|terima kasih|thanks|thank you|thx|pamit|udahan|udah dulu|istirahat dulu|tidur dulu|dadah|bye)\b/u', $text) === 1;
    }

    private function wantsListeningOnly(string $text): bool
    {
        return preg_match('/(?:(?:didengerin|dengerin|didengar|dengarin)\s+aja|cuma\s+(?:pengen|mau|ingin)\s+cerita|(?:nggak|gak|ga|tidak|jangan)\s+(?:usah\s+)?(?:dikasih\s+|kasih\s+|beri\s+)?(?:saran|solusi))/u', $text) === 1;
    }

    private function needsHelp(string $text): bool
    {
        return preg_match('/\b(?:bingung|harus\s+(?:apa|gimana|bagaimana)|terus\s+(?:aku\s+)?gimana|gimana\s+(?:dong|ya|caranya)|bagaimana\s+caranya|minta\s+saran|kasih\s+saran|butuh\s+saran|ada\s+saran|tips|solusinya|bantuin|bantu\s+aku|(?:nggak|gak|ga|tidak)\s+(?:tau|tahu)\s+(?:harus|cara|mau))\b/u', $text) === 1;
    }
}
