<?php

declare(strict_types=1);

namespace App\Services\Article;

use Illuminate\Support\Str;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;

/**
 * Mengubah `body_markdown` menjadi HTML aman untuk halaman Publik (FR-021).
 * HTML mentah di Markdown dibuang (`html_input` = `strip`), tautan berskema berbahaya
 * (`javascript:` dan sejenisnya) tidak dirender, tautan keluar diberi `nofollow noopener`
 * dan dibuka di tab baru. `# H1` diturunkan menjadi `h2` karena judul artikel adalah satu-satunya `h1`.
 */
final class ArticleRenderer
{
    private const WORDS_PER_MINUTE = 200;

    public function html(string $markdown): string
    {
        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'external_link' => [
                'internal_hosts' => (string) parse_url((string) config('app.url'), PHP_URL_HOST),
                'open_in_new_window' => true,
                'nofollow' => 'external',
                'noopener' => 'external',
                'noreferrer' => 'external',
            ],
        ], [new ExternalLinkExtension]);

        return (string) preg_replace('~<(/?)h1(?=[\s>])~i', '<$1h2', $html);
    }

    /** Perkiraan menit baca (minimal 1). */
    public function readingMinutes(string $markdown): int
    {
        $words = str_word_count(strip_tags($this->html($markdown)));

        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }
}
