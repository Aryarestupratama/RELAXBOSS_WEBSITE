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
    public function __construct(private readonly CasualRegister $register = new CasualRegister) {}

    private const QUIET_TURNS = 2;

    private const OFFER_AFTER_USER_TURNS = 4;

    private const LONG_FIRST_MESSAGE = 100;

    private const SUBSTANTIVE_MESSAGE = 25;

    /** Total karakter semua pesan pengguna di riwayat: di bawah ini cerita "tipis", di atas itu (atau >= 2 pesan berisi) "jelas". */
    private const THIN_STORY_CHARS = 60;

    private const CLEAR_STORY_CHARS = 150;

    /** Jumlah balasan tanpa pertanyaan yang diminta sebelum boleh bertanya lagi, menurut kejelasan cerita. */
    private const QUIET_THIN = 1;

    private const QUIET_CLEAR = 3;

    private const VALIDATION_STRATEGIES = ['validation', 'affirmation'];

    /** Ciri balasan yang hanya memvalidasi atau menamai perasaan (model sering tak melabelinya `validation`). */
    private const VALIDATION_PATTERN = '/\b(?:wajar|pantes|pantas|pasti\s+(?:berat|capek|lelah|susah|nggak\s+enak)|(?:nggak|gak|tidak)\s+gampang|valid|aku\s+(?:paham|ngerti|mengerti)|kedengarannya\s+(?:berat|capek|lelah)|(?:berat|capek|melelahkan)\s+banget|bikin\s+(?:kamu\s+)?(?:capek|kesel|lelah|stres|sedih|frustrasi))\b/u';

    /** Penutup generik ("aku nyimak", "teruskan ceritanya") yang membuat balasan terasa kaku bila berulang. */
    private const CLOSING_PATTERN = '/(?:\bnyimak\b|menyimak|teruskan|lanjut(?:kan)?\s+cerita|siap\s+mendengarkan|aku\s+(?:ada\s+)?di\s+sini|kapan\s*pun\s+kamu\s+mau)/u';

    /** Pembuka berpola ("Kamu ngerasa...", "Kedengarannya...") yang sering diulang model. */
    private const OPENING_PATTERN = '/\A\W*(?:kamu\s+(?:lagi\s+)?(?:ngerasa|merasa|terasa)|kedengarannya|sepertinya|kayaknya|jadi\s+kamu|dengar(?:in|kan)?|simak|tenang|sabar|ingat)\b/u';

    /** Fokus bergilir untuk giliran CERMIN supaya balasan tidak berulang pola. */
    private const REFLECT_FOCUS = [
        'Sorot bagian terberat dari yang ia ceritakan dengan kata-katanya sendiri, lalu beri satu kalimat hangat yang spesifik.',
        'Beri izin atau bingkai ulang bebannya (mis. tidak harus selesai semuanya sekarang, atau ia tidak harus menanggung semuanya sendirian), tanpa menebak penyebab perasaannya.',
        'Hubungkan dua hal yang pernah ia ceritakan dengan hati-hati ("kedengarannya..."), lalu tawarkan satu langkah sangat kecil dengan pelan, tanpa tanda tanya.',
    ];

    /**
     * @param  list<array{role: string, content: string, strategy?: ?string, flow_action?: ?string}>  $turns  urut lama ke baru, pesan pengguna terbaru di akhir
     */
    public function plan(array $turns): TurnPlan
    {
        $latest = '';
        $userCount = 0;
        $assistants = [];
        $lengths = [];

        foreach ($turns as $turn) {
            if ($turn['role'] === 'user') {
                $userCount++;
                $latest = $turn['content'];
                $lengths[] = mb_strlen(trim($turn['content']));
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

        $avoidClosing = count(array_filter($recent, fn (array $turn): bool => preg_match(self::CLOSING_PATTERN, mb_strtolower($turn['content'])) === 1)) > 0;
        $avoidOpening = count(array_filter($recent, fn (array $turn): bool => preg_match(self::OPENING_PATTERN, mb_strtolower(trim($turn['content']))) === 1)) > 0;

        $recentUsers = array_slice($lengths === [] ? [] : array_values(array_map(static fn (array $turn): string => $turn['content'], array_filter($turns, static fn (array $turn): bool => $turn['role'] === 'user'))), -2);
        $casualUser = $this->register->isCasual(implode(' ', $recentUsers));
        $formalWords = $this->register->formalWords(implode(' ', array_map(static fn (array $turn): string => $turn['content'], $recent)));

        $focus = self::REFLECT_FOCUS[count($assistants) % count(self::REFLECT_FOCUS)];

        [$thin, $clear] = $this->storyState($lengths);

        $note = $thin
            ? 'Ceritanya masih tipis: belum jelas apa yang sebenarnya terjadi.'
            : ($clear ? 'Ceritanya sudah cukup jelas dari pesan-pesan sebelumnya: pakai detail dari seluruh percakapan, bukan hanya pesan terakhir, dan jangan menanyakan hal yang sudah ia ceritakan.' : null);

        $make = static fn (DialogueAct $act, bool $listenOnly = false): TurnPlan => new TurnPlan(
            $act,
            $avoidValidation,
            $listenOnly,
            focus: $act === DialogueAct::Reflect ? $focus : null,
            note: $act === DialogueAct::Close ? null : $note,
            avoidClosing: $avoidClosing,
            avoidOpening: $avoidOpening,
            casualUser: $casualUser,
            formalWords: $formalWords,
        );

        $hasSubstance = $length >= self::SUBSTANTIVE_MESSAGE || $clear;

        if ($this->isClosing($text)) {
            return $make(DialogueAct::Close);
        }

        if ($this->wantsListeningOnly($text)) {
            return $make($hasSubstance ? DialogueAct::Reflect : DialogueAct::Invite, true);
        }

        if ($this->needsHelp($text)) {
            return $make(DialogueAct::Help);
        }

        if ($assistants === []) {
            return $make($length >= self::LONG_FIRST_MESSAGE ? DialogueAct::Reflect : DialogueAct::Ask);
        }

        $quiet = $this->quietStreak($assistants);

        if (! $thin && $userCount >= self::OFFER_AFTER_USER_TURNS && $quiet >= self::QUIET_TURNS && ! $this->offeredRecently($assistants)) {
            return $make(DialogueAct::OfferChoice);
        }

        $required = $thin ? self::QUIET_THIN : ($clear ? self::QUIET_CLEAR : self::QUIET_TURNS);

        if ($quiet < $required) {
            return $make($hasSubstance ? DialogueAct::Reflect : DialogueAct::Invite);
        }

        return $make(DialogueAct::Ask);
    }

    /**
     * Seberapa jelas ceritanya, dihitung dari SEMUA pesan pengguna di riwayat (bukan hanya bubble terakhir):
     * tipis bila totalnya sedikit atau dua pesan terakhir sangat pendek dan total belum besar; jelas bila
     * totalnya besar atau sudah ada dua pesan yang berisi.
     *
     * @param  list<int>  $lengths  panjang tiap pesan pengguna, lama ke baru
     * @return array{0: bool, 1: bool} [tipis, jelas]
     */
    private function storyState(array $lengths): array
    {
        $total = array_sum($lengths);
        $substantive = count(array_filter($lengths, static fn (int $length): bool => $length >= self::SUBSTANTIVE_MESSAGE));

        $shortStreak = 0;

        foreach (array_reverse($lengths) as $length) {
            if ($length >= self::SUBSTANTIVE_MESSAGE) {
                break;
            }

            $shortStreak++;
        }

        $thin = $total < self::THIN_STORY_CHARS || ($shortStreak >= 2 && $total < self::CLEAR_STORY_CHARS);
        $clear = ! $thin && ($total >= self::CLEAR_STORY_CHARS || $substantive >= 2);

        return [$thin, $clear];
    }

    /**
     * Bila giliran ini dilarang bertanya tetapi balasan memuat pertanyaan: buang kalimat tanya. Bila tak ada
     * yang tersisa, balasan dikembalikan apa adanya dengan `violated = true` supaya pemanggil bisa mengulang.
     *
     * @return array{reply: string, violated: bool}
     */
    public function enforce(TurnPlan $plan, string $reply): array
    {
        $reply = $this->dropCommandOpener($reply);

        if ($plan->avoidClosing) {
            $reply = $this->dropClosing($reply);
        }

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

    /** Buang pembuka perintah ("Dengar ya,", "Tenang,", "Sabar.") yang terdengar seperti memarahi, bila ada isi yang tersisa. */
    private function dropCommandOpener(string $reply): string
    {
        $stripped = preg_replace('/\A\s*(?:coba\s+)?(?:dengar(?:in|kan)?|simak|tenang|sabar|ingat)(?:\s+(?:ya|yaa|dulu|aku|deh|dong|baik-baik))*\s*[,.!:]\s*/iu', '', $reply);

        if ($stripped === null || mb_strlen(trim($stripped)) < 8 || $stripped === $reply) {
            return $reply;
        }

        $stripped = trim($stripped);

        return mb_strtoupper(mb_substr($stripped, 0, 1)).mb_substr($stripped, 1);
    }

    /** Buang kalimat penutup generik di ujung balasan, selama masih ada isi yang tersisa. */
    private function dropClosing(string $reply): string
    {
        $lines = array_values(array_filter(preg_split('/\R+/u', trim($reply)) ?: [], static fn (string $line): bool => trim($line) !== ''));

        if ($lines === []) {
            return $reply;
        }

        $sentences = $this->sentences($lines[count($lines) - 1]);

        while (count($sentences) > 1 && preg_match(self::CLOSING_PATTERN, mb_strtolower(end($sentences))) === 1) {
            array_pop($sentences);
        }

        if (count($sentences) === 1 && count($lines) > 1 && preg_match(self::CLOSING_PATTERN, mb_strtolower($sentences[0])) === 1) {
            array_pop($lines);
        } else {
            $lines[count($lines) - 1] = implode(' ', $sentences);
        }

        $result = trim(implode("\n", $lines));

        return mb_strlen($result) >= 8 ? $result : $reply;
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
