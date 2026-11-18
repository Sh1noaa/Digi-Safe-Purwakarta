<?php

namespace App\Services;

class SimpleXlsxExporter
{
    /**
     * @var array<int, array{
     *     name: string,
     *     title: string,
     *     subtitle: string,
     *     sections: array<int, array{
     *         heading?: string,
     *         headers: array<int, string>,
     *         rows: array<int, array<int, string|int|float>>,
     *         alignments?: array<int, string>
     *     }>
     * }>
     */
    private array $sheets = [];

    public function addSheet(string $name, string $title, string $subtitle, array $sections): void
    {
        // Nama sheet Excel maksimal 31 karakter dan tanpa karakter terlarang : \ / ? * [ ]
        $cleanName = preg_replace('/[:\\\\\\/\\?\\*\\[\\]]/', '', $name) ?: 'Sheet';
        $cleanName = mb_substr($cleanName, 0, 31);

        $this->sheets[] = [
            'name' => $cleanName,
            'title' => $title,
            'subtitle' => $subtitle,
            'sections' => $sections,
        ];
    }

    public function buildBinary(): string
    {
        $files = [];

        $files['[Content_Types].xml'] = $this->buildContentTypesXml();
        $files['_rels/.rels'] = $this->buildRootRelsXml();
        $files['xl/workbook.xml'] = $this->buildWorkbookXml();
        $files['xl/_rels/workbook.xml.rels'] = $this->buildWorkbookRelsXml();
        $files['xl/styles.xml'] = $this->buildStylesXml();

        foreach ($this->sheets as $index => $sheet) {
            $sheetNumber = $index + 1;
            $files["xl/worksheets/sheet{$sheetNumber}.xml"] = $this->buildWorksheetXml($sheet);
        }

        return $this->createZipBinary($files);
    }

    private function buildContentTypesXml(): string
    {
        $overrides = [
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>',
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>',
        ];

        foreach ($this->sheets as $index => $_) {
            $num = $index + 1;
            $overrides[] = '<Override PartName="/xl/worksheets/sheet' . $num . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . implode('', $overrides)
            . '</Types>';
    }

    private function buildRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function buildWorkbookXml(): string
    {
        $sheetTags = [];
        foreach ($this->sheets as $index => $sheet) {
            $num = $index + 1;
            $name = $this->escapeXml($sheet['name']);
            $sheetTags[] = '<sheet name="' . $name . '" sheetId="' . $num . '" r:id="rId' . $num . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . implode('', $sheetTags) . '</sheets>'
            . '</workbook>';
    }

    private function buildWorkbookRelsXml(): string
    {
        $rels = [];
        $count = count($this->sheets);

        foreach ($this->sheets as $index => $_) {
            $num = $index + 1;
            $rels[] = '<Relationship Id="rId' . $num . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $num . '.xml"/>';
        }

        $styleId = $count + 1;
        $rels[] = '<Relationship Id="rId' . $styleId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . implode('', $rels)
            . '</Relationships>';
    }

    /**
     * Styles:
     * 0: Default polos
     * 1: Judul Laporan (Bold 14pt, tanpa fill)
     * 2: Subjudul / Info Tanggal (Italic 10pt abu, tanpa fill)
     * 3: Judul Tabel / Header Kolom (Bold 11pt putih di atas fill biru #1E88E5 + border) — HANYA judul tabel yang berwarna
     * 4: Isi Data Teks Kiri (Polos putih, border tipis, wrapText)
     * 5: Isi Data Angka Kanan (Polos putih, border tipis, format #,##0)
     * 6: Isi Data Tengah (Polos putih, border tipis, align center)
     * 7: Judul Seksi (Bold 11pt gelap, tanpa fill)
     */
    private function buildStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="5">'
                . '<font><sz val="10"/><color rgb="FF111827"/><name val="Calibri"/></font>'
                . '<font><b/><sz val="14"/><color rgb="FF0F172A"/><name val="Calibri"/></font>'
                . '<font><i/><sz val="9.5"/><color rgb="FF64748B"/><name val="Calibri"/></font>'
                . '<font><b/><sz val="10.5"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
                . '<font><b/><sz val="11"/><color rgb="FF0F172A"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
                . '<fill><patternFill patternType="none"/></fill>'
                . '<fill><patternFill patternType="gray125"/></fill>'
                . '<fill><patternFill patternType="solid"><fgColor rgb="FF1E88E5"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="3">'
                . '<border><left/><right/><top/><bottom/><diagonal/></border>'
                . '<border>'
                    . '<left style="thin"><color rgb="FFCBD5E1"/></left>'
                    . '<right style="thin"><color rgb="FFCBD5E1"/></right>'
                    . '<top style="thin"><color rgb="FFCBD5E1"/></top>'
                    . '<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>'
                    . '<diagonal/>'
                . '</border>'
                . '<border>'
                    . '<left style="thin"><color rgb="FF1565C0"/></left>'
                    . '<right style="thin"><color rgb="FF1565C0"/></right>'
                    . '<top style="thin"><color rgb="FF1565C0"/></top>'
                    . '<bottom style="medium"><color rgb="FF0D47A1"/></bottom>'
                    . '<diagonal/>'
                . '</border>'
            . '</borders>'
            . '<cellStyleXfs count="1">'
                . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
            . '</cellStyleXfs>'
            . '<cellXfs count="8">'
                . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
                . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>'
                . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment vertical="center"/></xf>'
                . '<xf numFmtId="0" fontId="3" fillId="2" borderId="2" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
                . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>'
                . '<xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyNumberFormat="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
                . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
                . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }

    private function buildWorksheetXml(array $sheet): string
    {
        $rowsXml = [];
        $colWidths = [];
        $rowNum = 1;

        // Baris 1: Judul Utama Laporan
        $rowsXml[] = $this->buildRowXml($rowNum++, [
            ['value' => $sheet['title'], 'style' => 1],
        ], 26);

        // Baris 2: Subjudul / Tanggal Ekspor
        $rowsXml[] = $this->buildRowXml($rowNum++, [
            ['value' => $sheet['subtitle'], 'style' => 2],
        ], 18);

        // Baris 3: Kosong sebagai pemisah
        $rowNum++;

        foreach ($sheet['sections'] as $sectionIndex => $section) {
            if ($sectionIndex > 0) {
                $rowNum++;
            }

            if (! empty($section['heading'])) {
                $rowsXml[] = $this->buildRowXml($rowNum++, [
                    ['value' => $section['heading'], 'style' => 7],
                ], 22);
            }

            $headers = $section['headers'] ?? [];
            $alignments = $section['alignments'] ?? [];

            if (! empty($headers)) {
                $headerCells = [];
                foreach ($headers as $colIdx => $headerText) {
                    $headerCells[] = ['value' => $headerText, 'style' => 3];
                    $len = mb_strlen((string) $headerText) + 5;
                    $colWidths[$colIdx] = max($colWidths[$colIdx] ?? 10, $len);
                }
                $rowsXml[] = $this->buildRowXml($rowNum++, $headerCells, 26);
            }

            $dataRows = $section['rows'] ?? [];
            foreach ($dataRows as $dataRow) {
                $cells = [];
                $maxLines = 1;

                foreach ($dataRow as $colIdx => $val) {
                    $align = $alignments[$colIdx] ?? (is_int($val) || is_float($val) ? 'right' : 'left');
                    $isNumeric = is_int($val) || is_float($val);

                    if ($isNumeric && $align !== 'center') {
                        $style = 5;
                    } elseif ($align === 'center') {
                        $style = 6;
                    } else {
                        $style = 4;
                    }

                    $cells[] = [
                        'value' => $val,
                        'style' => $style,
                        'numeric' => $isNumeric,
                    ];

                    $strVal = (string) $val;
                    $lineLen = max(array_map('mb_strlen', explode("\n", $strVal)));
                    $colWidths[$colIdx] = min(52, max($colWidths[$colIdx] ?? 10, $lineLen + 4));

                    $estimatedLines = max(1, (int) ceil(max(1, mb_strlen($strVal)) / 42));
                    $maxLines = max($maxLines, $estimatedLines);
                }

                $rowHeight = max(20, min(72, $maxLines * 16));
                $rowsXml[] = $this->buildRowXml($rowNum++, $cells, $rowHeight);
            }
        }

        // Susun definisi lebar kolom otomatis
        $colsXml = '';
        if (! empty($colWidths)) {
            ksort($colWidths);
            $colTags = [];
            foreach ($colWidths as $idx => $width) {
                $colNumber = $idx + 1;
                $colTags[] = '<col min="' . $colNumber . '" max="' . $colNumber . '" width="' . round($width, 2) . '" customWidth="1"/>';
            }
            $colsXml = '<cols>' . implode('', $colTags) . '</cols>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="20"/>'
            . $colsXml
            . '<sheetData>' . implode('', $rowsXml) . '</sheetData>'
            . '</worksheet>';
    }

    private function buildRowXml(int $rowNumber, array $cells, float $height = 20): string
    {
        $cellXmls = [];
        foreach ($cells as $colIndex => $cell) {
            $colLetter = $this->columnIndexToLetter($colIndex);
            $ref = $colLetter . $rowNumber;
            $style = (int) ($cell['style'] ?? 0);
            $val = $cell['value'] ?? '';
            $isNumeric = ! empty($cell['numeric']) && (is_int($val) || is_float($val));

            if ($isNumeric) {
                $cellXmls[] = '<c r="' . $ref . '" s="' . $style . '"><v>' . $val . '</v></c>';
            } else {
                $escaped = $this->escapeXml((string) $val);
                $cellXmls[] = '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . $escaped . '</t></is></c>';
            }
        }

        return '<row r="' . $rowNumber . '" ht="' . $height . '" customHeight="1">' . implode('', $cellXmls) . '</row>';
    }

    private function columnIndexToLetter(int $index): string
    {
        $letter = '';
        $num = $index + 1;
        while ($num > 0) {
            $mod = ($num - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $num = intdiv($num - 1, 26);
        }

        return $letter;
    }

    private function escapeXml(string $text): string
    {
        // Hapus karakter kontrol XML yang tidak valid agar file terbuka mulus di Excel
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text) ?? '';

        return htmlspecialchars($clean, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Paketkan seluruh XML menjadi arsip ZIP (.xlsx) standar PKZIP tanpa bergantung pada ekstensi php_zip.
     *
     * @param array<string, string> $files
     */
    private function createZipBinary(array $files): string
    {
        $localData = '';
        $centralDir = '';
        $offset = 0;
        $count = 0;

        foreach ($files as $name => $content) {
            $nameBytes = $name;
            $nameLen = strlen($nameBytes);
            $uncompressedLen = strlen($content);
            $crc = crc32($content);
            $compressed = gzdeflate($content, 6);
            $compressedLen = strlen($compressed);

            // Local file header (30 bytes + filename)
            $localHeader = pack(
                'VvvvvvVVVvv',
                0x04034b50,
                20,
                0x0800,
                8,
                0,
                0,
                $crc,
                $compressedLen,
                $uncompressedLen,
                $nameLen,
                0
            );

            $localData .= $localHeader . $nameBytes . $compressed;

            // Central directory file header (46 bytes + filename)
            $centralHeader = pack(
                'VvvvvvvVVVvvvvvVV',
                0x02014b50,
                20,
                20,
                0x0800,
                8,
                0,
                0,
                $crc,
                $compressedLen,
                $uncompressedLen,
                $nameLen,
                0,
                0,
                0,
                0,
                0,
                $offset
            );

            $centralDir .= $centralHeader . $nameBytes;
            $offset = strlen($localData);
            $count++;
        }

        // End of central directory record
        $eocd = pack(
            'VvvvvVVv',
            0x06054b50,
            0,
            0,
            $count,
            $count,
            strlen($centralDir),
            strlen($localData),
            0
        );

        return $localData . $centralDir . $eocd;
    }
}

