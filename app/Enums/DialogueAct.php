<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Jenis balasan RelaxMate pada satu giliran. Dipilih oleh kode (`ConversationPlanner`), bukan oleh
 * model, supaya ritme percakapan tidak berubah menjadi wawancara atau rentetan validasi.
 */
enum DialogueAct: string
{
    /** Satu pertanyaan pendek yang spesifik. */
    case Ask = 'ask';

    /** Cermin: ulang satu detail dengan kata lain. Tanpa pertanyaan. */
    case Reflect = 'reflect';

    /** Menemani singkat, membuka ruang tanpa bertanya. */
    case Invite = 'invite';

    /** Satu kali menawarkan pilihan: didengarkan saja atau mencari langkah kecil. */
    case OfferChoice = 'offer_choice';

    /** Pengguna minta atau butuh bantuan: beri langkah atau kalimat konkret. */
    case Help = 'help';

    /** Pengguna pamit atau berterima kasih. */
    case Close = 'close';

    public function allowsQuestion(): bool
    {
        return match ($this) {
            self::Ask, self::OfferChoice, self::Help => true,
            self::Reflect, self::Invite, self::Close => false,
        };
    }

    public function instruction(): string
    {
        return match ($this) {
            self::Ask => 'Jenis: TANYA. Boleh tepat SATU pertanyaan pendek yang spesifik pada detail yang baru ia sebut, dalam bahasa sehari-hari. Tanpa pembukaan validasi atau penghiburan. Jangan mengulang pertanyaan yang pernah kamu ajukan.',
            self::Reflect => 'Jenis: CERMIN. DILARANG bertanya: balasan tidak boleh memuat tanda tanya. Susun dalam dua sampai empat kalimat: (1) satu kalimat hangat yang menyentuh keadaannya, bukan menyalin kalimatnya; (2) satu hal BARU yang bukan salinan, misalnya menyorot bagian terberat dari ceritanya, memberi izin ("nggak harus beres semuanya hari ini") atau membingkai ulang bebannya; (3) opsional, satu langkah sangat kecil yang ditawarkan pelan. Pakai kata-kata yang ia pakai; jangan memakai kata yang lebih dramatis daripada ucapannya; JANGAN menebak penyebab perasaannya (tanpa "...muncul karena..."). Bila ia bertanya langsung padamu, jawab dulu.',
            self::Invite => 'Jenis: TEMANI. DILARANG bertanya: balasan tidak boleh memuat tanda tanya. Satu sampai dua kalimat pendek yang hangat dan spesifik pada keadaannya, bukan menyalin kalimatnya. Boleh berupa izin ("nggak perlu buru-buru"). Jangan menutup dengan "aku nyimak" atau "aku di sini". Tanpa saran dan tanpa kata yang lebih dramatis daripada ucapannya.',
            self::OfferChoice => 'Jenis: TAWARAN. Ia sudah bercerita beberapa giliran. Akui satu detail secara singkat, lalu tawarkan SATU kali pilihan: kamu temani mendengarkan saja, atau kalian cari langkah kecil bersama. Ini satu-satunya pertanyaan dan hanya satu. Pakai flow_action "suggest_action".',
            self::Help => 'Jenis: BANTU. Ia bingung atau meminta bantuan. Jangan bertanya "mau mulai dari mana". Beri satu atau dua langkah kecil yang konkret dan spesifik pada ceritanya, atau contoh kalimat yang bisa ia ucapkan. Boleh sampai 4 kalimat dan boleh ditutup dengan satu tawaran menyusunnya bersama. Pakai flow_action "suggest_action".',
            self::Close => 'Jenis: TUTUP. DILARANG bertanya: balasan tidak boleh memuat tanda tanya. Balas hangat dan singkat, satu kalimat, tanpa menahannya. Pakai flow_action "end_topic".',
        };
    }
}
