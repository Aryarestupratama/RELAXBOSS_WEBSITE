<?php

declare(strict_types=1);

namespace App\Services\Ai;

use App\DTOs\PromptTemplate;
use App\Enums\AiFeature;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Membaca prompt sistem per fitur dari `resources/prompts/` (RULE-036).
 * Isi prompt milik founder; layanan tidak menulis teks prompt di kode.
 *
 * Baris pertama berkas boleh berupa `<!-- prompt-version: X -->`. Baris itu dibuang dari isi dan
 * versinya dicatat di log (tanpa isi prompt). Berkas yang memuat `[PLACEHOLDER]` ditandai
 * `isPlaceholder`, dipakai `relaxboss:preflight` (RULE-048).
 */
final class PromptLoader
{
    private const VERSION_LINE = '/\A<!--\s*prompt-version:\s*([^\s>]+)\s*-->\R?/u';

    /** @var array<string, PromptTemplate> */
    private array $cache = [];

    public function load(AiFeature $feature): PromptTemplate
    {
        if (isset($this->cache[$feature->value])) {
            return $this->cache[$feature->value];
        }

        $file = (string) config("relaxboss.ai.features.{$feature->value}.prompt");
        $path = resource_path('prompts/'.$file);

        if ($file === '' || ! is_file($path)) {
            throw new RuntimeException("Berkas prompt untuk fitur {$feature->value} tidak ditemukan.");
        }

        $raw = (string) file_get_contents($path);
        $version = 'unversioned';

        if (preg_match(self::VERSION_LINE, $raw, $match) === 1) {
            $version = $match[1];
            $raw = (string) preg_replace(self::VERSION_LINE, '', $raw, 1);
        }

        $content = trim($raw);
        $template = new PromptTemplate($content, $version, str_contains($content, '[PLACEHOLDER]'));

        Log::debug('ai.prompt_loaded', [
            'feature' => $feature->value,
            'version' => $template->version,
            'placeholder' => $template->isPlaceholder,
        ]);

        return $this->cache[$feature->value] = $template;
    }
}
