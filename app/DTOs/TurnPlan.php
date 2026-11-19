<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\DialogueAct;

/** Rencana satu giliran balasan: jenis balasan dan batas yang ditegakkan kode. */
final readonly class TurnPlan
{
    /**
     * @param  list<string>  $formalWords
     */
    public function __construct(
        public DialogueAct $act,
        public bool $avoidValidation = false,
        public bool $listenOnly = false,
        public bool $retry = false,
        public ?string $focus = null,
        public ?string $note = null,
        public bool $avoidClosing = false,
        public bool $avoidOpening = false,
        public bool $casualUser = false,
        public array $formalWords = [],
    ) {}

    public function allowsQuestion(): bool
    {
        return $this->act->allowsQuestion();
    }

    /** Rencana yang sama dengan peringatan, dipakai saat balasan pertama melanggar larangan bertanya. */
    public function forRetry(): self
    {
        return new self($this->act, $this->avoidValidation, $this->listenOnly, true, $this->focus, $this->note, $this->avoidClosing, $this->avoidOpening, $this->casualUser, $this->formalWords);
    }

    /** Bagian yang ditambahkan ke instruksi sistem (data dari sistem, bukan dari pengguna). */
    public function section(): string
    {
        $lines = ['', '', '# ARAHAN GILIRAN INI (ditentukan sistem, WAJIB diikuti; mengalahkan aturan gaya lain)', '', $this->act->instruction()];

        if ($this->note !== null) {
            $lines[] = $this->note;
        }

        if ($this->focus !== null) {
            $lines[] = 'Fokus giliran ini: '.$this->focus;
        }

        if ($this->listenOnly) {
            $lines[] = 'Ia meminta didengarkan saja: jangan memberi saran atau solusi.';
        }

        if ($this->avoidValidation) {
            $lines[] = 'Balasanmu sebelumnya sudah bernada validasi. Jangan membuka dengan validasi, penghiburan, atau kalimat yang hanya menamai perasaannya (mis. "wajar", "pasti berat", "bikin kamu kesel", "perasaanmu valid"); langsung sentuh isi ceritanya.';
        }

        if ($this->avoidClosing) {
            $lines[] = 'Balasanmu sebelumnya ditutup dengan undangan generik ("aku nyimak", "teruskan ceritanya", "aku di sini"). Jangan menutup dengan kalimat seperti itu lagi; akhiri balasan dengan isi.';
        }

        if ($this->avoidOpening) {
            $lines[] = 'Jangan membuka dengan pola yang sama seperti balasan sebelumnya ("Kamu ngerasa...", "Kedengarannya...", "Sepertinya..."); buka dengan hal lain, misalnya detail spesifik atau satu kalimat hangat yang pendek.';
        }

        $lines[] = $this->casualUser
            ? 'Gaya menulis pengguna santai dan ekspresif. Balas santai setingkat itu, seperti teman dekat: boleh "huhu", "yaa", "sini", "aja", "nih", "kok". Jangan meniru singkatan atau salah ketiknya. Tanpa emoji.'
            : 'Pakai bahasa santai sehari-hari yang tetap sopan, seperti teman. Tanpa emoji.';

        if ($this->formalWords !== []) {
            $lines[] = 'Kata terlalu baku di balasanmu sebelumnya: '.implode(', ', $this->formalWords).'. Ganti dengan padanan santai (mis. merasa jadi ngerasa, menghadapi jadi ngadepin, tidak jadi nggak, sangat jadi banget).';
        }

        if (! $this->allowsQuestion()) {
            $lines[] = 'Ingat: nol tanda tanya di balasan ini.';
        }

        if ($this->retry) {
            $lines[] = 'PERINGATAN: balasan sebelumnya ditolak karena berisi pertanyaan. Tulis ulang tanpa satu pun tanda tanya.';
        }

        return implode("\n", $lines);
    }
}
