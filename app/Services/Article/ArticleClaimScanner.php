<?php

declare(strict_types=1);

namespace App\Services\Article;

use App\Enums\ArticleStatus;
use App\Models\Article;

/**
 * Alat bantu RULE-049 (klaim publik hanya yang terkonfirmasi), dipakai `relaxboss:preflight`.
 *
 * Pemindai heuristik: menandai artikel terbit yang memuat frasa berisiko (kolaborasi UMB, validasi
 * atau pengujian, klaim diagnosis) supaya founder membacanya ulang sebelum go-live. Ini peringatan,
 * bukan pengganti membaca artikel: tidak menangkap semua kalimat dan bisa menandai kalimat yang aman.
 * Hanya mengembalikan slug dan jenis temuan, tidak pernah isi artikel.
 */
final class ArticleClaimScanner
{
    public const UMB = 'UMB';

    public const VALIDATION = 'validasi';

    public const DIAGNOSIS = 'diagnosis';

    /**
     * Kalimat yang diawali "tidak", "bukan", atau "tanpa" (mis. "tidak mendiagnosis") dianggap aman.
     *
     * @var array<string, string>
     */
    private const PATTERNS = [
        self::UMB => '~\b(?:UMB|Mercu\s+Buana|Laboratorium\s+Terapan\s+Psikologi)\b~iu',
        self::VALIDATION => '~\b(?:tervalidasi|divalidasi|teruji\s+klinis|terbukti\s+(?:secara\s+)?klinis|tersertifikasi|bersertifikat|(?:disetujui|direkomendasikan|didukung|dikembangkan)\s+(?:oleh\s+)?psikolog)\b~iu',
        self::DIAGNOSIS => '~(?<!tidak\s)(?<!bukan\s)(?<!tanpa\s)\b(?:mendiagnosis|mendiagnosa|didiagnosis|terdiagnosis)\b~iu',
    ];

    /**
     * Jenis temuan pada satu teks. Klaim UMB hanya dihitung selama `relaxboss.umb` masih kosong,
     * yaitu selama kolaborasi UMB belum terkonfirmasi (OQ-2).
     *
     * @return list<string>
     */
    public function findings(string $text, bool $umbConfirmed): array
    {
        $found = [];

        foreach (self::PATTERNS as $kind => $pattern) {
            if ($kind === self::UMB && $umbConfirmed) {
                continue;
            }

            if (preg_match($pattern, $text) === 1) {
                $found[] = $kind;
            }
        }

        return $found;
    }

    /**
     * Artikel berstatus terbit (bukan `demo-`, itu urusan pemeriksaan lain) yang perlu dibaca ulang.
     * Artikel yang tanggal terbitnya belum tiba ikut diperiksa karena akan tayang otomatis.
     *
     * @return array<string, list<string>> slug => jenis temuan
     */
    public function flaggedPublished(): array
    {
        $umbConfirmed = filled(config('relaxboss.umb'));
        $flagged = [];

        $articles = Article::query()
            ->where('status', ArticleStatus::Published->value)
            ->where('slug', 'not like', 'demo-%')
            ->orderBy('slug')
            ->get(['slug', 'title', 'excerpt', 'meta_title', 'meta_description', 'body_markdown']);

        foreach ($articles as $article) {
            $text = implode("\n", array_filter([
                $article->title,
                $article->excerpt,
                $article->meta_title,
                $article->meta_description,
                $article->body_markdown,
            ], 'is_string'));

            $found = $this->findings($text, $umbConfirmed);

            if ($found !== []) {
                $flagged[$article->slug] = $found;
            }
        }

        return $flagged;
    }
}
