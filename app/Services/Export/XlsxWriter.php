<?php

declare(strict_types=1);

namespace App\Services\Export;

use App\Exceptions\SpreadsheetExportException;
use ZipArchive;

/**
 * Penulis berkas .xlsx minimal (TASK-057), tanpa paket Composer tambahan (hanya ekstensi `zip` dan `mbstring`).
 *
 * Tiap lembar ditulis bertahap ke berkas sementara sehingga memori tetap kecil berapa pun jumlah barisnya;
 * `finish()` merangkai semuanya menjadi satu berkas zip. Semua teks ditulis sebagai inline string, jadi Excel tidak
 * pernah menafsirkannya sebagai rumus (aman dari injeksi rumus meski isinya diawali `=`, `+`, `-`, atau `@`).
 * Baris judul dibekukan, diberi filter, dan kolom isi dibungkus (wrap) agar teks panjang terbaca.
 *
 * Pemakaian: `addSheet()` untuk semua lembar dulu, lalu `addRow()`, lalu `finish()`. Bila gagal di tengah jalan,
 * panggil `abort()` agar berkas sementara dan berkas tujuan tidak tertinggal.
 */
final class XlsxWriter
{
    /** Batas baris satu lembar Excel (termasuk baris judul). */
    public const MAX_ROWS = 1_048_576;

    /** Batas karakter satu sel Excel. */
    public const MAX_CELL_CHARS = 32767;

    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    private const NS_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    private const STYLE_HEADER = 1;

    private const STYLE_WRAP = 2;

    /**
     * @var array<string, array{index: int, temp: string, handle: resource, columns: int, rows: int}>
     */
    private array $sheets = [];

    private bool $done = false;

    /**
     * @param  string  $path  berkas .xlsx tujuan (dibuat saat `finish()`)
     * @param  string  $tempDir  folder non-publik untuk berkas sementara
     */
    public function __construct(private readonly string $path, private readonly string $tempDir) {}

    /**
     * Tambah lembar beserta baris judulnya.
     *
     * @param  array<string, int>  $columns  nama kolom => lebar (karakter)
     */
    public function addSheet(string $name, array $columns): void
    {
        // Aturan nama lembar Excel: 1 sampai 31 karakter, tanpa [ ] : * ? / \, tidak diawali atau diakhiri apostrof.
        if (
            isset($this->sheets[$name])
            || $name === ''
            || mb_strlen($name) > 31
            || preg_match('/[\[\]:*?\/\\\\]/', $name) === 1
            || str_starts_with($name, "'")
            || str_ends_with($name, "'")
        ) {
            throw new SpreadsheetExportException(SpreadsheetExportException::WRITE_FAILED, "Nama lembar tidak valid: {$name}.");
        }

        $temp = tempnam($this->tempDir, 'xlsx-sheet-');
        $handle = $temp === false ? false : fopen($temp, 'wb');

        if ($temp === false || $handle === false) {
            throw new SpreadsheetExportException(SpreadsheetExportException::WRITE_FAILED, 'Berkas sementara tidak bisa dibuat.');
        }

        $index = count($this->sheets) + 1;
        $this->sheets[$name] = ['index' => $index, 'temp' => $temp, 'handle' => $handle, 'columns' => count($columns), 'rows' => 0];

        $cols = '';
        $position = 0;
        foreach ($columns as $width) {
            $position++;
            $cols .= '<col min="'.$position.'" max="'.$position.'" width="'.max(6, min(120, $width)).'" customWidth="1"/>';
        }

        $this->write($name, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="'.self::NS_MAIN.'">'
            .'<sheetViews><sheetView workbookViewId="0"'.($index === 1 ? ' tabSelected="1"' : '').'>'
            .'<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>'
            .'<selection pane="bottomLeft" activeCell="A2" sqref="A2"/>'
            .'</sheetView></sheetViews>'
            .'<cols>'.$cols.'</cols>'
            .'<sheetData>');

        $this->writeRow($name, array_keys($columns), self::STYLE_HEADER);
    }

    /**
     * Tambah satu baris data. Sel null atau string kosong dibiarkan kosong.
     *
     * @param  list<string|int|float|bool|null>  $cells
     */
    public function addRow(string $sheet, array $cells): void
    {
        $this->writeRow($sheet, $cells, self::STYLE_WRAP);
    }

    /** Rangkai semua lembar menjadi berkas .xlsx lalu hapus berkas sementara. */
    public function finish(): void
    {
        if (! class_exists(ZipArchive::class)) {
            $this->abort();

            throw new SpreadsheetExportException(SpreadsheetExportException::ZIP_MISSING, 'Ekstensi PHP zip belum aktif di server.');
        }

        if ($this->sheets === []) {
            $this->abort();

            throw new SpreadsheetExportException(SpreadsheetExportException::WRITE_FAILED, 'Tidak ada lembar untuk ditulis.');
        }

        foreach ($this->sheets as $name => $sheet) {
            $last = max(1, $sheet['rows']);
            $this->write($name, '</sheetData><autoFilter ref="A1:'.$this->columnName($sheet['columns']).$last.'"/></worksheet>');
            fclose($sheet['handle']);
        }

        $zip = new ZipArchive;

        if ($zip->open($this->path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->abort();

            throw new SpreadsheetExportException(SpreadsheetExportException::WRITE_FAILED, 'Berkas Excel tidak bisa dibuat.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());

        foreach ($this->sheets as $sheet) {
            $zip->addFile($sheet['temp'], 'xl/worksheets/sheet'.$sheet['index'].'.xml');
        }

        // `close()` yang membaca berkas sementara dan menulis zip; false berarti penulisan gagal (mis. disk penuh).
        $closed = $zip->close();

        $this->cleanTemps();
        $this->done = true;

        if ($closed !== true) {
            @unlink($this->path);

            throw new SpreadsheetExportException(SpreadsheetExportException::WRITE_FAILED, 'Berkas Excel gagal ditulis (disk penuh atau tidak bisa ditulis).');
        }
    }

    /** Buang berkas sementara dan berkas tujuan. Aman dipanggil berulang. */
    public function abort(): void
    {
        $this->cleanTemps();

        if (! $this->done) {
            @unlink($this->path);
        }
    }

    /**
     * @param  list<string|int|float|bool|null>  $cells
     */
    private function writeRow(string $sheet, array $cells, int $style): void
    {
        if (! isset($this->sheets[$sheet])) {
            throw new SpreadsheetExportException(SpreadsheetExportException::WRITE_FAILED, "Lembar tidak dikenal: {$sheet}.");
        }

        $number = $this->sheets[$sheet]['rows'] + 1;

        if ($number > self::MAX_ROWS) {
            throw new SpreadsheetExportException(
                SpreadsheetExportException::TOO_MANY_ROWS,
                'Jumlah baris melebihi batas satu lembar Excel. Pilih rentang tanggal yang lebih sempit.',
            );
        }

        $this->sheets[$sheet]['rows'] = $number;

        $xml = '<row r="'.$number.'">';
        $column = 0;

        foreach ($cells as $value) {
            $column++;

            if ($value === null || $value === '') {
                continue;
            }

            $ref = $this->columnName($column).$number;

            if (is_bool($value)) {
                $xml .= '<c r="'.$ref.'" t="b"><v>'.($value ? '1' : '0').'</v></c>';
            } elseif (is_int($value) || (is_float($value) && is_finite($value))) {
                $xml .= '<c r="'.$ref.'"><v>'.(is_int($value) ? $value : sprintf('%.15g', $value)).'</v></c>';
            } else {
                $text = (string) $value;
                $xml .= '<c r="'.$ref.'" t="inlineStr" s="'.$style.'"><is><t xml:space="preserve">'.$this->escape($text).'</t></is></c>';
            }
        }

        $this->write($sheet, $xml.'</row>');
    }

    private function write(string $sheet, string $xml): void
    {
        if (fwrite($this->sheets[$sheet]['handle'], $xml) === false) {
            throw new SpreadsheetExportException(SpreadsheetExportException::WRITE_FAILED, 'Penulisan berkas sementara gagal (disk penuh?).');
        }
    }

    private function cleanTemps(): void
    {
        foreach ($this->sheets as $sheet) {
            if (is_resource($sheet['handle'])) {
                fclose($sheet['handle']);
            }

            @unlink($sheet['temp']);
        }
    }

    /** Buang karakter yang tidak sah di XML 1.0, samakan baris baru, potong sesuai batas sel, lalu escape. */
    private function escape(string $value): string
    {
        $value = mb_scrub($value, 'UTF-8');
        $value = str_replace(["\r\n", "\r"], "\n", $value);
        $value = preg_replace('/[^\x{9}\x{A}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $value) ?? '';

        if (mb_strlen($value) > self::MAX_CELL_CHARS) {
            $value = mb_substr($value, 0, self::MAX_CELL_CHARS - 1).'…';
        }

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** 1 => A, 26 => Z, 27 => AA. */
    private function columnName(int $index): string
    {
        $name = '';

        while ($index > 0) {
            $index--;
            $name = chr(65 + $index % 26).$name;
            $index = intdiv($index, 26);
        }

        return $name;
    }

    private function contentTypes(): string
    {
        $overrides = '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';

        foreach ($this->sheets as $sheet) {
            $overrides .= '<Override PartName="/xl/worksheets/sheet'.$sheet['index'].'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .$overrides
            .'</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="'.self::NS_REL.'/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbook(): string
    {
        $sheets = '';
        $names = '';

        foreach ($this->sheets as $name => $sheet) {
            $escaped = htmlspecialchars($name, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            $sheets .= '<sheet name="'.$escaped.'" sheetId="'.$sheet['index'].'" r:id="rId'.$sheet['index'].'"/>';
            // Rumus memakai nama mentah: apostrof digandakan dulu (aturan rumus Excel), baru seluruhnya di-escape XML.
            $reference = "'".str_replace("'", "''", $name)."'".'!$A$1:$'.$this->columnName($sheet['columns']).'$'.max(1, $sheet['rows']);
            $names .= '<definedName name="_xlnm._FilterDatabase" localSheetId="'.($sheet['index'] - 1).'" hidden="1">'
                .htmlspecialchars($reference, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                .'</definedName>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="'.self::NS_MAIN.'" xmlns:r="'.self::NS_REL.'">'
            .'<sheets>'.$sheets.'</sheets>'
            .'<definedNames>'.$names.'</definedNames>'
            .'</workbook>';
    }

    private function workbookRels(): string
    {
        $relations = '';

        foreach ($this->sheets as $sheet) {
            $relations .= '<Relationship Id="rId'.$sheet['index'].'" Type="'.self::NS_REL.'/worksheet" Target="worksheets/sheet'.$sheet['index'].'.xml"/>';
        }

        $relations .= '<Relationship Id="rId'.(count($this->sheets) + 1).'" Type="'.self::NS_REL.'/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.$relations.'</Relationships>';
    }

    /** Gaya 0: biasa; 1: judul (tebal, latar lavender); 2: isi dibungkus, rata atas. */
    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="'.self::NS_MAIN.'">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFE6E0F5"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="3">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top"/></xf>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }
}
