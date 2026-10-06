<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Contracts\AiDataExporter;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

/**
 * Daftar eksportir dari `config('relaxboss.exporters')` (RULE-072). Fitur AI baru cukup menambah
 * satu kelas `AiDataExporter` dan satu baris di config.
 */
final class AiDataExporterRegistry
{
    public function __construct(private readonly Container $container) {}

    /** @return list<string> Nama fitur yang terdaftar. */
    public function features(): array
    {
        return array_keys($this->all());
    }

    public function find(string $feature): ?AiDataExporter
    {
        return $this->all()[$feature] ?? null;
    }

    /** @return array<string, AiDataExporter> */
    private function all(): array
    {
        $exporters = [];

        foreach ((array) config('relaxboss.exporters', []) as $class) {
            $exporter = $this->container->make($class);

            if (! $exporter instanceof AiDataExporter) {
                throw new InvalidArgumentException("{$class} harus mengimplementasikan AiDataExporter.");
            }

            $exporters[$exporter->feature()] = $exporter;
        }

        return $exporters;
    }
}
