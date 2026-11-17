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

        foreach ($this->enforceCases() as [$name, $act, $reply, $expected, $violated]) {
            $total++;
            $result = $this->planner->enforce(new TurnPlan($act), $reply);

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

        $story = 'Tugas numpuk banget minggu ini, laporan praktikum sama presentasi deadline-nya lusa semua dan aku belum mulai sama sekali.';

        return [
            ['pesan pertama pendek: tanya', [$u('Aku stres banget')], DialogueAct::Ask],
            ['pesan pertama panjang: cermin', [$u($story)], DialogueAct::Reflect],
            ['setelah pertanyaan: cermin', [$u('Aku stres banget'), $a('Stres kenapa nih?'), $u($story)], DialogueAct::Reflect],
            ['setelah pertanyaan, balasan pendek: temani', [$u('Aku stres banget'), $a('Stres kenapa nih?'), $u('tugas')], DialogueAct::Invite],
            ['satu giliran tenang: masih tanpa tanya', [$u('a'), $a('Stres kenapa nih?'), $u($story), $a('Jadi dua deadline jatuh di hari yang sama.'), $u($story)], DialogueAct::Reflect],
            ['dua giliran tenang, belum empat giliran: boleh tanya', [$u($story), $a('Dua deadline jatuh di hari yang sama.'), $u($story), $a('Belum tidur dari kemarin.'), $u($story)], DialogueAct::Ask],
            ['empat giliran pengguna: tawaran pilihan', [$u('a'), $a('Stres kenapa nih?'), $u($story), $a('Dua deadline di hari yang sama.'), $u($story), $a('Belum tidur dari kemarin.'), $u($story), $a('Berat ya.'), $u($story)], DialogueAct::OfferChoice],
            ['tawaran tidak diulang', [$u('a'), $a('Mau didengerin aja atau cari langkah?', null, 'suggest_action'), $u($story), $a('Dua deadline.'), $u($story), $a('Belum tidur.'), $u($story), $a('Berat.'), $u($story)], DialogueAct::Ask],
            ['bingung: bantu', [$u('a'), $a('Stres kenapa nih?'), $u('yaa itu aku bingung')], DialogueAct::Help],
            ['minta saran: bantu', [$u('Aku mau minta saran dong soal skripsi')], DialogueAct::Help],
            ['pamit: tutup', [$u('a'), $a('Cerita aja.'), $u('Makasih ya, aku mau istirahat dulu.')], DialogueAct::Close],
            ['dengerin aja: tanpa tanya dan saran', [$u('a'), $a('Stres kenapa nih?'), $u('aku cuma pengen cerita, jangan dikasih saran dulu ya, dosenku nyebelin banget')], DialogueAct::Reflect],
            ['penegas "ya?" bukan pertanyaan', [$u('a'), $a('Capek banget ya?'), $u($story)], DialogueAct::Reflect],
        ];
    }

    /** @return list<array{0: string, 1: DialogueAct, 2: string, 3: string, 4: bool}> */
    private function enforceCases(): array
    {
        return [
            ['cermin dengan pertanyaan di akhir: dipangkas', DialogueAct::Reflect, 'Jadi dua deadline jatuh di hari yang sama. Beban tugasnya paling banyak di mata kuliah apa?', 'Jadi dua deadline jatuh di hari yang sama.', false],
            ['hanya pertanyaan: pelanggaran', DialogueAct::Reflect, 'Yang paling bikin kesel bagian mana?', 'Yang paling bikin kesel bagian mana?', true],
            ['tanya boleh: tak diubah', DialogueAct::Ask, 'Wah, padat. Yang paling nyangkut yang mana?', 'Wah, padat. Yang paling nyangkut yang mana?', false],
            ['penegas "ya?" lolos', DialogueAct::Reflect, 'Capek banget ya?', 'Capek banget ya?', false],
            ['dua baris, pertanyaan di baris kedua', DialogueAct::Invite, "Aku nyimak.\nMau lanjut cerita apa?", 'Aku nyimak.', false],
        ];
    }
}
