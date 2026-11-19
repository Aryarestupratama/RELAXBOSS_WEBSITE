<?php

declare(strict_types=1);

namespace App\Services\Chat;

use App\DTOs\TurnPlan;
use App\Enums\DialogueAct;
use App\Exceptions\AiUnavailableException;

/**
 * Kasus uji manual untuk parser balasan dan perencana giliran (dipakai `relaxboss:check-chat`).
 * Tanpa database, tanpa AI, tanpa log. Jalankan setiap kali `ChatReplyParser`, `ConversationPlanner`,
 * atau prompt RelaxMate berubah.
 */
final class ChatFlowChecker
{
    public function __construct(
        private readonly ChatReplyParser $parser = new ChatReplyParser,
        private readonly ConversationPlanner $planner = new ConversationPlanner,
    ) {}

    /**
     * @return array{total: int, failures: list<string>}
     */
    public function run(): array
    {
        $failures = [];
        $total = 0;

        foreach ($this->parserCases() as [$name, $raw, $reply, $intent]) {
            $total++;

            try {
                $parsed = $this->parser->parse($raw);
            } catch (AiUnavailableException $exception) {
                if ($reply !== null) {
                    $failures[] = "parser: {$name}: galat tak terduga ({$exception->reason})";
                }

                continue;
            }

            if ($reply === null) {
                $failures[] = "parser: {$name}: seharusnya galat invalid_response";
            } elseif ($parsed->reply !== $reply) {
                $failures[] = "parser: {$name}: balasan \"{$parsed->reply}\" (diharapkan \"{$reply}\")";
            } elseif (($parsed->intent?->value) !== $intent) {
                $failures[] = "parser: {$name}: intent ".($parsed->intent?->value ?? 'kosong')." (diharapkan ".($intent ?? 'kosong').')';
            }
        }

        foreach ($this->plannerCases() as [$name, $turns, $act]) {
            $total++;
            $plan = $this->planner->plan($turns);

            if ($plan->act !== $act) {
                $failures[] = "planner: {$name}: {$plan->act->value} (diharapkan {$act->value})";
            }
        }

        foreach ($this->validationCases() as [$name, $turns, $avoid]) {
            $total++;

            if ($this->planner->plan($turns)->avoidValidation !== $avoid) {
                $failures[] = "validasi: {$name}: diharapkan avoidValidation=".($avoid ? 'ya' : 'tidak');
            }
        }

        foreach ($this->flagCases() as [$name, $turns, $closing, $opening]) {
            $total++;
            $plan = $this->planner->plan($turns);

            if ($plan->avoidClosing !== $closing || $plan->avoidOpening !== $opening) {
                $failures[] = "pola: {$name}: avoidClosing=".($plan->avoidClosing ? 'ya' : 'tidak').' avoidOpening='.($plan->avoidOpening ? 'ya' : 'tidak');
            }
        }

        $register = new CasualRegister;

        foreach ($this->registerCases() as [$name, $input, $expected]) {
            $total++;
            $actual = $register->casualize($input);

            if ($actual !== $expected) {
                $failures[] = "bahasa: {$name}: \"{$actual}\" (diharapkan \"{$expected}\")";
            }
        }

        foreach ($this->styleCases() as [$name, $text, $casual]) {
            $total++;

            if ($register->isCasual($text) !== $casual) {
                $failures[] = "gaya: {$name}: diharapkan santai=".($casual ? 'ya' : 'tidak');
            }
        }

        $total++;

        if ($register->formalWords('Sungguh, kamu sangat tabah.') !== ['sungguh', 'sangat', 'tabah']) {
            $failures[] = 'bahasa: kata baku tidak terdeteksi lengkap';
        }

        foreach ($this->enforceCases() as $case) {
            [$name, $act, $reply, $expected, $violated] = $case;
            $total++;
            $result = $this->planner->enforce(new TurnPlan($act, avoidClosing: $case[5] ?? false), $reply);

            if ($result['reply'] !== $expected || $result['violated'] !== $violated) {
                $failures[] = "enforce: {$name}: \"{$result['reply']}\" violated=".($result['violated'] ? 'ya' : 'tidak');
            }
        }

        return ['total' => $total, 'failures' => $failures];
    }

    /** @return list<array{0: string, 1: string, 2: ?string, 3: ?string}> nama, mentah, balasan (null = galat), intent */
    private function parserCases(): array
    {
        return [
            ['bocor: kutip pembuka dan koma hilang', 'Beban tugasnya paling banyak di mata kuliah apa?intent":"academic_pressure"', 'Beban tugasnya paling banyak di mata kuliah apa?', 'academic_pressure'],
            ['bocor: lengkap dengan sisa JSON', 'Capek ya hari ini?intent":"venting_stress","confidence":0.8,"strategy":"active_listening","flow_action":"continue"}', 'Capek ya hari ini?', 'venting_stress'],
            ['JSON sah', '{"reply":"Halo, seneng kamu mampir.","intent":"greeting_casual","confidence":0.95,"strategy":"active_listening","flow_action":"ask_situation"}', 'Halo, seneng kamu mampir.', 'greeting_casual'],
            ['JSON sah dalam pagar kode', "```json\n{\"reply\":\"Oke.\",\"intent\":\"venting_stress\"}\n```", 'Oke.', 'venting_stress'],
            ['JSON dengan pemisah antar-kunci hilang', '{"reply":"Berat ya minggu ini?intent":"academic_pressure","confidence":0.9}', 'Berat ya minggu ini?', 'academic_pressure'],
            ['bocor: \\n literal sebelum metadata', 'Capek ya?\\nintent":"venting_stress"', 'Capek ya?', 'venting_stress'],
            ['sisa JSON gaya lama', 'Capek banget ya","intent":"venting_stress"}', 'Capek banget ya', 'venting_stress'],
            ['JSON terpotong', '{"reply":"Halo, aku di si', 'Halo, aku di si', null],
            ['baris baru ter-escape', '{"reply":"Satu.\nDua.","intent":"venting_stress"}', "Satu.\nDua.", 'venting_stress'],
            ['teks biasa', 'Aku nyimak kok.', 'Aku nyimak kok.', null],
            ['teks biasa dengan tanda kutip', 'Dia bilang "oke" tadi, terus pergi.', 'Dia bilang "oke" tadi, terus pergi.', null],
            ['JSON sah tanpa reply', '{"intent":"venting_stress"}', null, null],
            ['JSON rusak tanpa reply', '{"foo":"bar"', null, null],
            ['kosong', '   ', null, null],
        ];
    }

    /** @return list<array{0: string, 1: list<array<string, mixed>>, 2: DialogueAct}> */
    private function plannerCases(): array
    {
        $u = static fn (string $text): array => ['role' => 'user', 'content' => $text];
        $a = static fn (string $text, ?string $strategy = null, ?string $flow = null): array => ['role' => 'assistant', 'content' => $text, 'strategy' => $strategy, 'flow_action' => $flow];

        $long = 'Tugas numpuk banget minggu ini, laporan praktikum sama presentasi deadline-nya lusa semua dan aku belum mulai sama sekali. Dosennya juga nagih terus, belum tidur dari kemarin.';
        $story = 'Tugas numpuk banget minggu ini, laporan praktikum sama presentasi deadline-nya lusa semua dan aku belum mulai sama sekali.';

        return [
            ['pesan pertama pendek: tanya', [$u('Aku stres banget')], DialogueAct::Ask],
            ['pesan pertama panjang: cermin', [$u($story)], DialogueAct::Reflect],
            ['setelah pertanyaan: cermin', [$u('Aku stres banget'), $a('Stres kenapa nih?'), $u($story)], DialogueAct::Reflect],
            ['setelah pertanyaan, balasan pendek: temani', [$u('Aku stres banget'), $a('Stres kenapa nih?'), $u('tugas')], DialogueAct::Invite],
            ['satu giliran tenang: masih tanpa tanya', [$u('a'), $a('Stres kenapa nih?'), $u($story), $a('Jadi dua deadline jatuh di hari yang sama.'), $u($story)], DialogueAct::Reflect],
            ['cerita jelas: anggaran pertanyaan diperpanjang', [$u($story), $a('Dua deadline jatuh di hari yang sama.'), $u($story), $a('Belum tidur dari kemarin.'), $u($story)], DialogueAct::Reflect],
            ['cerita jelas, tiga giliran tenang: boleh tanya', [$u($story), $a('Dua deadline.'), $u($story), $a('Belum tidur.', null, 'suggest_action'), $u($story), $a('Berat.'), $u($story)], DialogueAct::Ask],
            ['cerita tipis, satu giliran tenang: boleh tanya', [$u('capek'), $a('Capek kenapa nih?'), $u('iya'), $a('Aku nyimak.'), $u('hmm')], DialogueAct::Ask],
            ['cerita tipis, belum ada giliran tenang: temani', [$u('capek'), $a('Capek kenapa nih?'), $u('iya')], DialogueAct::Invite],
            ['pesan terakhir pendek tapi cerita sudah jelas: cermin', [$u($long), $a('Dua deadline di hari yang sama.'), $u('iya')], DialogueAct::Reflect],
            ['empat giliran pengguna: tawaran pilihan', [$u('a'), $a('Stres kenapa nih?'), $u($story), $a('Dua deadline di hari yang sama.'), $u($story), $a('Belum tidur dari kemarin.'), $u($story), $a('Berat ya.'), $u($story)], DialogueAct::OfferChoice],
            ['tawaran tidak diulang, tiga giliran tenang: tanya', [$u('a'), $a('Mau didengerin aja atau cari langkah?', null, 'suggest_action'), $u($story), $a('Dua deadline.'), $u($story), $a('Belum tidur.'), $u($story), $a('Berat.'), $u($story)], DialogueAct::Ask],
            ['bingung: bantu', [$u('a'), $a('Stres kenapa nih?'), $u('yaa itu aku bingung')], DialogueAct::Help],
            ['minta saran: bantu', [$u('Aku mau minta saran dong soal skripsi')], DialogueAct::Help],
            ['pamit: tutup', [$u('a'), $a('Cerita aja.'), $u('Makasih ya, aku mau istirahat dulu.')], DialogueAct::Close],
            ['dengerin aja: tanpa tanya dan saran', [$u('a'), $a('Stres kenapa nih?'), $u('aku cuma pengen cerita, jangan dikasih saran dulu ya, dosenku nyebelin banget')], DialogueAct::Reflect],
            ['penegas "ya?" bukan pertanyaan', [$u('a'), $a('Capek banget ya?'), $u($story)], DialogueAct::Reflect],
        ];
    }

    /** @return list<array{0: string, 1: string, 2: string}> */
    private function registerCases(): array
    {
        return [
            ['kata baku diganti', 'Kamu merasa tidak sanggup menghadapi semuanya, mengapa begitu?', 'Kamu ngerasa nggak sanggup ngadepin semuanya, kenapa begitu?'],
            ['huruf kapital di awal dipertahankan', 'Tidak apa-apa kok. Namun pelan-pelan aja.', 'Nggak apa-apa kok. Tapi pelan-pelan aja.'],
            ['"dapat" tidak diganti (maknanya bergantung konteks)', 'Kamu dapat banyak tugas hari ini.', 'Kamu dapat banyak tugas hari ini.'],
            ['kata yang mengandung kata baku tidak ikut berubah', 'Perasaanmu itu tidak aneh. Itu bagian dari kesibukanmu.', 'Perasaanmu itu nggak aneh. Itu bagian dari kesibukanmu.'],
            ['bahasa Inggris tidak berubah', 'That sounds really heavy, I am here.', 'That sounds really heavy, I am here.'],
        ];
    }

    /** @return list<array{0: string, 1: string, 2: bool}> */
    private function styleCases(): array
    {
        return [
            ['pesan santai dan ekspresif', 'Aku hari ini udaah merasa overstimulated bgt huhu, gamau menghadapi hari besok', true],
            ['pesan rapi', 'Saya merasa kewalahan dengan banyaknya tugas minggu ini dan ingin bercerita.', false],
            ['singkatan dua jenis', 'tugas numpuk bgt, gak selesai selesai', true],
            ['satu penanda saja: belum cukup', 'Tugasku banyak banget minggu ini.', false],
        ];
    }

    /** @return list<array{0: string, 1: list<array<string, mixed>>, 2: bool, 3: bool}> */
    private function flagCases(): array
    {
        $u = static fn (string $text): array => ['role' => 'user', 'content' => $text];
        $a = static fn (string $text): array => ['role' => 'assistant', 'content' => $text];

        return [
            ['penutup "aku di sini nyimak" terdeteksi', [$u('capek'), $a('Berat banget hari ini. Aku di sini nyimak kalau mau lanjut cerita.'), $u('iya')], true, false],
            ['pembuka "Kamu ngerasa" terdeteksi', [$u('capek'), $a('Kamu ngerasa kewalahan sama tugas minggu ini.'), $u('iya')], false, true],
            ['pembuka "Kedengarannya" terdeteksi', [$u('capek'), $a('Kedengarannya kamu lagi kebanjiran tugas.'), $u('iya')], false, true],
            ['pembuka perintah "Dengar" terdeteksi', [$u('capek'), $a('Dengar ya, tugas itu bisa dicicil.'), $u('iya')], false, true],
            ['balasan bervariasi: tidak ada pola', [$u('capek'), $a('Dua deadline di hari yang sama, pantas kepalamu penuh.'), $u('iya')], false, false],
        ];
    }

    /** @return list<array{0: string, 1: list<array<string, mixed>>, 2: bool}> */
    private function validationCases(): array
    {
        $u = static fn (string $text): array => ['role' => 'user', 'content' => $text];
        $a = static fn (string $text, ?string $strategy = null): array => ['role' => 'assistant', 'content' => $text, 'strategy' => $strategy];

        return [
            ['label validation', [$u('capek'), $a('Itu berat.', 'validation'), $u('iya banget kayaknya')], true],
            ['validasi tanpa label (isi balasan)', [$u('capek'), $a('Kamu pasti capek banget ya.', 'active_listening'), $u('iya banget kayaknya')], true],
            ['menamai perasaan tanpa label', [$u('kesel'), $a('Jadi tugasnya menumpuk, bikin kamu kesel.', 'active_listening'), $u('iya banget kayaknya')], true],
            ['balasan berisi isi cerita: tidak perlu menghindar', [$u('capek'), $a('Jadi dua deadline jatuh di hari yang sama dan laporan praktikum yang paling menyita waktu.', 'active_listening'), $u('iya banget kayaknya')], false],
        ];
    }

    /** @return list<array<int, mixed>> */
    private function enforceCases(): array
    {
        return [
            ['cermin dengan pertanyaan di akhir: dipangkas', DialogueAct::Reflect, 'Jadi dua deadline jatuh di hari yang sama. Beban tugasnya paling banyak di mata kuliah apa?', 'Jadi dua deadline jatuh di hari yang sama.', false],
            ['hanya pertanyaan: pelanggaran', DialogueAct::Reflect, 'Yang paling bikin kesel bagian mana?', 'Yang paling bikin kesel bagian mana?', true],
            ['tanya boleh: tak diubah', DialogueAct::Ask, 'Wah, padat. Yang paling nyangkut yang mana?', 'Wah, padat. Yang paling nyangkut yang mana?', false],
            ['penegas "ya?" lolos', DialogueAct::Reflect, 'Capek banget ya?', 'Capek banget ya?', false],
            ['pembuka perintah "Dengar ya," dibuang', DialogueAct::Reflect, 'Dengar ya, harus megang semuanya sendirian itu berat.', 'Harus megang semuanya sendirian itu berat.', false],
            ['pembuka "Tenang." dibuang', DialogueAct::Invite, 'Tenang. Hari ini nggak harus beres semuanya.', 'Hari ini nggak harus beres semuanya.', false],
            ['kata "dengar" di tengah kalimat tidak disentuh', DialogueAct::Reflect, 'Dari yang kamu ceritain, kedengarannya capek banget.', 'Dari yang kamu ceritain, kedengarannya capek banget.', false],
            ['penutup generik dibuang bila ada isi', DialogueAct::Reflect, 'Harus megang semuanya sendirian itu berat. Aku di sini nyimak, teruskan kalau mau cerita lebih lanjut.', 'Harus megang semuanya sendirian itu berat.', false, true],
            ['penutup generik dipertahankan bila satu-satunya isi', DialogueAct::Invite, 'Aku di sini nyimak.', 'Aku di sini nyimak.', false, true],
            ['dua baris, pertanyaan di baris kedua', DialogueAct::Invite, "Aku nyimak.\nMau lanjut cerita apa?", 'Aku nyimak.', false],
        ];
    }
}
