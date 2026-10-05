<?php

declare(strict_types=1);

/**
 * Pemicu krisis berbasis aturan (FR-018 lapisan 1, Architecture 6.3).
 *
 * Pola dicocokkan pada teks yang SUDAH dinormalisasi oleh CrisisDetector: huruf kecil, tanpa aksen,
 * huruf yang diulang 3 kali atau lebih diringkas jadi satu ("matiii" -> "mati"), tanda baca menjadi
 * spasi, spasi ganda dirapikan. Varian "leet" (4 = a, 3 = e, 0 = o, 1 = i, @ = a) juga diperiksa.
 * Jadi tulis pola dengan huruf kecil biasa dan spasi tunggal.
 *
 * Prinsip: lebih baik salah memicu (banner muncul padahal tidak perlu) daripada gagal memicu.
 * Setelah mengubah berkas ini, WAJIB jalankan `php artisan relaxboss:check-crisis` (RULE-047).
 *
 * Kunci:
 *  - ignore   : frasa yang dibuang sebelum pencocokan (idiom yang memuat kata "mati" tetapi bukan krisis).
 *  - suicide  : ide bunuh diri -> Intent crisis_suicide.
 *  - selfharm : menyakiti diri  -> Intent crisis_selfharm.
 */

// Potongan pola yang dipakai ulang.
$desire = '(?:pengen|pengin|pingin|ingin|kepingin|keinginan|pgn|pngn|pgen|pngen|pngin|pen|mau|mo|mw|mending|lebih baik|sebaiknya|harusnya|pantas|layak)';
$neg = '(?:ga|gak|nggak|ngga|tidak|tak|enggak|engga|nga|g)';
$me = '(?:aku|gue|gua|gw|saya|ane)';
$self = '(?:\s+(?:sendiri|ku|gue|gw|aku|saya))?';

return [
    'ignore' => [
        // Idiom "mati ...".
        '/\bmati\s+(?:lampu|listrik|gaya|rasa|kutu|angin|suri|total|mesin|ketawa|sinyal|baterai|api|bosan|penasaran|kaku|langkah|akal)\b/u',
        '/\b(?:mati\s+matian|setengah\s+mati)\b/u',
        // Benda yang mati.
        '/\b(?:laptop|laptopnya|hp|hpnya|ponsel|wifi|server|mesin|lampu|listrik|baterai|kipas|kompor|tv|sinyal|internet|aplikasi|website|situs|ac|charger|layar|speaker)\s+(?:\w+\s+)?mati\b/u',
    ],

    'suicide' => [
        // Istilah langsung.
        '/\b(?:bundir|suicide|suicidal|sucide|suisid)\b/u',
        '/\b(?:bunuh|membunuh|ngebunuh|menghabisi|habisi|ngabisin|habisin|menghilangkan|ngilangin|mengakhiri|akhiri|ngakhirin|akhirin|menyudahi|nyudahin|sudahi)\s+(?:nyawa|diri)(?:ku)?' . $self . '\b/u',
        '/\b(?:mengakhiri|akhiri|ngakhirin|akhirin|menyudahi|nyudahin|sudahi)\s+hidup/u',
        '/\bkill\s*(?:my\s*self|myself|me)\b/u',
        '/\bend\s+my\s+life\b/u',
        '/\b(?:want|wanna)\s+(?:to\s+)?die\b/u',
        '/\b(?:rather\s+be\s+dead|better\s+off\s+dead|don\s?t\s+want\s+to\s+live)\b/u',

        // Ingin mati / hilang.
        '/\b' . $desire . '\b(?:\s+\w+){0,3}?\s+(?:mati+|meninggal|mampus|tiada|tidur\s+selamanya|istirahat\s+selamanya|pergi\s+selamanya|pergi\s+dari\s+dunia|ninggalin\s+dunia|meninggalkan\s+dunia|pamit\s+dari\s+dunia)\b/u',
        '/\b' . $desire . '\b(?:\s+\w+){0,2}?\s+(?:menghilang|ngilang|ilang|hilang)\s+(?:aja|saja|aj|selamanya|dari\s+dunia|dari\s+muka\s+bumi|dari\s+kehidupan)\b/u',
        '/\bmati\s+(?:aja|saja|sekalian|ajalah|aj)\b/u',
        '/\b(?:biar|supaya|agar)\s+(?:bisa\s+)?(?:' . $me . '\s+)?(?:mati|meninggal|tiada)\b/u',
        '/\b(?:kalau|kalo|klo|andai|seandainya|misal)\s+' . $me . '\s+(?:mati|meninggal|tiada|menghilang|ngilang)\b/u',
        '/\b(?:mending|lebih\s+baik|andai|seandainya|kalau|kalo)\s+' . $me . '\s+' . $neg . '\s+ada(?:\s+(?:aja|saja|lagi|di\s+dunia|di\s+bumi))?\b/u',
        '/\b(?:andai|seandainya|kalau|kalo)\s+' . $me . '\s+' . $neg . '\s+(?:pernah\s+)?(?:lahir|dilahirkan)\b/u',
        '/\b(?:menyesal|nyesel)\s+(?:telah\s+|sudah\s+)?(?:lahir|dilahirkan|hidup)\b/u',

        // Tidak ingin hidup / tidak ada gunanya hidup.
        '/\b' . $neg . '\s+(?:mau|ingin|pengen|pengin|kuat|sanggup|tahan|betah)\s+(?:lagi\s+)?(?:hidup|lanjut\s+hidup|bertahan\s+hidup|bertahan)\b/u',
        '/\b' . $neg . '\s+(?:mau|ingin|pengen)\s+(?:ada|berada)\s+(?:lagi\s+)?(?:di\s+)?(?:dunia|bumi)\b/u',
        '/\b(?:capek|cape|capai|lelah|bosan|muak|jenuh|letih)\s+(?:banget\s+|bgt\s+|sekali\s+|deh\s+)?(?:dengan\s+|sama\s+|buat\s+|menjalani\s+|jalanin\s+)?(?:hidup|bernapas|bernafas)\b/u',
        '/\b' . $neg . '\s+(?:ada|punya)\s+(?:alasan|gunanya|tujuan|harapan)\s+(?:lagi\s+)?(?:untuk\s+|buat\s+|utk\s+)?(?:hidup|bertahan)\b/u',
        '/\bhidup(?:ku|nya)?(?:\s+(?:ku|gue|gw|aku|ini|saya))?\s+' . $neg . '\s+(?:ada\s+)?(?:berarti|arti|artinya|guna|gunanya|makna|maknanya)\b/u',
        '/\b(?:buat\s+apa|untuk\s+apa|utk\s+apa|ngapain|kenapa|mengapa)\s+(?:' . $me . '\s+)?(?:masih\s+|harus\s+)?(?:hidup|bertahan)\b/u',

        // Tidur selamanya / tidak bangun lagi.
        '/\b(?:tidur|terlelap|istirahat)\s+(?:aja\s+)?(?:untuk\s+)?selamanya\b/u',
        '/\b' . $neg . '\s+(?:pernah\s+|usah\s+)?(?:mau\s+|ingin\s+|pengen\s+)?bangun\s+lagi\b/u',

        // Beban bagi orang lain.
        '/\b(?:dunia|semua\s+orang|keluarga(?:ku)?|mereka|orang\s+orang|orang\s+tua(?:ku)?|ortu(?:ku)?|temen(?:ku)?|teman(?:ku)?)\s+(?:pasti\s+|akan\s+|bakal\s+)?(?:lebih\s+)?(?:baik|bahagia|tenang|senang|lega)\s+(?:tanpaku\b|(?:tanpa|kalau|kalo)\s+' . $me . '\b)/u',

        // Pamit.
        '/\b(?:pesan|surat|salam|kata)\s+terakhir(?:ku|\s+dariku|\s+dari\s+aku|\s+dari\s+gue|\s+buat\s+kalian|\s+untuk\s+kalian|\s+buat\s+semua|\s+untuk\s+semua|\s+buat\s+keluarga|\s+untuk\s+keluarga)\b/u',
        '/\bselamat\s+tinggal\s+(?:semua|semuanya|dunia)\b/u',
        '/\b' . $me . '\s+(?:mau\s+|ingin\s+|pengen\s+|akan\s+|bakal\s+)?(?:pergi|pamit|ninggalin|meninggalkan)\s+(?:dari\s+)?(?:dunia|semuanya|selamanya)\b/u',

        // Cara.
        '/\b(?:loncat|lompat|meloncat|melompat|terjun|jatuhin\s+diri|jatuhkan\s+diri)\s+(?:aja\s+)?(?:dari|ke)\s+(?:atas\s+)?(?:gedung|jembatan|atap|rooftop|lantai\s+\w+|apartemen|tebing|balkon|menara|jurang)\b/u',
        '/\b(?:gantung|menggantung|ngegantung|nggantung)\s+diri\b/u',
        '/\b(?:membakar|bakar|menenggelamkan|tenggelamkan|nenggelamin|menggorok|gorok|menusuk|tusuk)\s+diri\b/u',
        '/\b(?:nabrakin|menabrakkan|nabrakkan|tabrakin)\s+diri\b/u',
        '/\b(?:minum|nenggak|menenggak|tenggak)\s+(?:racun|baygon|pembasmi\s+serangga|obat\s+nyamuk|karbol|cairan\s+pembersih)\b/u',
        '/\b(?:minum|telan|nelen|menelan)\s+(?:semua\s+|banyak\s+)?(?:obat|pil)\s+(?:tidur\s+)?(?:sebotol|semuanya|banyak\s+banget|sekaligus|sebanyak\s+banyaknya)\b/u',
        '/\b(?:over\s?dosis|over\s?dose|overdosis|ngoverdosis)\b/u',
        '/\b(?:potong|memotong|motong|putus|memutus|mutus)\s+(?:urat\s+)?nadi\b/u',
    ],

    'selfharm' => [
        '/\bself\s*(?:harm|injur\w*|mutilat\w*)\b/u',
        '/\b(?:menyakiti|nyakitin|nyakiti|melukai|ngelukain|melukain|menyiksa|nyiksa|mencederai|nyederain)\s+(?:diri|badan|tubuh)(?:ku)?' . $self . '\b/u',
        '/\b(?:menyayat|nyayat|nyilet|menyilet|ngesilet|silet|menggores|nggores|ngiris|mengiris|iris|ngeiris)\s+(?:\w+\s+){0,2}?(?:nadi|pergelangan|lengan|tangan|paha|kulit|urat)' . $self . '\b/u',
        '/\b(?:ngecutting|cutting\s+(?:diri|tangan|lengan|paha))\b/u',
        '/\b(?:mukulin|memukuli|memukul|nonjok|menonjok|menampar|nampar|benturin|membenturkan|ngebenturin|ngebentur|nyundut|menyundut)\s+(?:diri|badan|tubuh|kepala|tangan|paha|muka|pipi|wajah)?\s*(?:ku|gue|gw|aku|saya)?\s*sendiri\b/u',
        '/\b(?:membenturkan|benturin|ngebenturin|ngebentur|membentur)\s+kepala\s+(?:ke|di)\s+(?:tembok|dinding|lantai|meja)\b/u',
    ],
];
