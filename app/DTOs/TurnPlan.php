<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\DialogueAct;

/** Rencana satu giliran balasan: jenis balasan dan batas yang ditegakkan kode. */
final readonly class TurnPlan
{
    public function __construct(
        public DialogueAct $act,
        public bool $avoidValidation = false,
        public bool $listenOnly = false,
        public bool $retry = false,
    ) {}

    public function allowsQuestion(): bool
    {
        return $this->act->allowsQuestion();
    }

    /** Rencana yang sama dengan peringatan, dipakai saat balasan pertama melanggar larangan bertanya. */
    public function forRetry(): self
    {
        return new self($this->act, $this->avoidValidation, $this->listenOnly, true);
    }

    /** Bagian yang ditambahkan ke instruksi sistem (data dari sistem, bukan dari pengguna). */
    public function section(): string
    {
        $lines = ['', '', '# ARAHAN GILIRAN INI (ditentukan sistem, WAJIB diikuti; mengalahkan aturan gaya lain)', '', $this->act->instruction()];

        if ($this->listenOnly) {
            $lines[] = 'Ia meminta didengarkan saja: jangan memberi saran atau solusi.';
        }

        if ($this->avoidValidation) {
            $lines[] = 'Balasanmu sebelumnya sudah bernada validasi. Jangan membuka dengan validasi atau penghiburan (mis. "wajar", "pasti berat", "perasaanmu valid"); langsung sentuh detail ceritanya.';
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
