<?php

namespace App\Services;

use DOMDocument;
use ZipArchive;

class ReportExportService
{
    public function build(string $format, array $columns, array $rows): string
    {
        return match (strtolower($format)) {
            'json' => $this->buildJson($columns, $rows),
            'xml' => $this->buildXml($columns, $rows),
            'xlsx' => $this->buildXlsx($columns, $rows),
            'pdf' => $this->buildPdf($columns, $rows),
            default => $this->buildCsv($columns, $rows),
        };
    }

    public function contentType(string $format): string
    {
        return match (strtolower($format)) {
            'json' => 'application/json',
            'xml' => 'application/xml',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pdf' => 'application/pdf',
            default => 'text/csv',
        };
    }

    public function extension(string $format): string
    {
        return match (strtolower($format)) {
            'json' => 'json',
            'xml' => 'xml',
            'xlsx' => 'xlsx',
            'pdf' => 'pdf',
            default => 'csv',
        };
    }

    private function buildCsv(array $columns, array $rows): string
    {
        $handle = fopen('php://temp', 'w+');

        if ($columns !== []) {
            fputcsv($handle, $columns);
        }

        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $column) {
                $line[] = $row[$column] ?? null;
            }
            fputcsv($handle, $line);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        return $contents ?: '';
    }

    private function buildJson(array $columns, array $rows): string
    {
        return json_encode([
            'columns' => $columns,
            'rows' => $rows,
        ], JSON_PRETTY_PRINT) ?: '';
    }

    private function buildXml(array $columns, array $rows): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $root = $doc->createElement('report');
        $doc->appendChild($root);

        $columnsNode = $doc->createElement('columns');
        foreach ($columns as $column) {
            $colNode = $doc->createElement('column');
            $colNode->appendChild($doc->createTextNode((string) $column));
            $columnsNode->appendChild($colNode);
        }
        $root->appendChild($columnsNode);

        $rowsNode = $doc->createElement('rows');
        foreach ($rows as $row) {
            $rowNode = $doc->createElement('row');
            foreach ($columns as $column) {
                $cellNode = $doc->createElement('cell');
                $cellNode->setAttribute('name', (string) $column);
                $cellNode->appendChild($doc->createTextNode((string) ($row[$column] ?? '')));
                $rowNode->appendChild($cellNode);
            }
            $rowsNode->appendChild($rowNode);
        }
        $root->appendChild($rowsNode);

        return $doc->saveXML() ?: '';
    }

    private function buildXlsx(array $columns, array $rows): string
    {
        if (! class_exists(ZipArchive::class)) {
            return $this->buildCsv($columns, $rows);
        }

        $sheetXml = $this->buildWorksheetXml($columns, $rows);

        $contentTypes = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '</Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $workbook = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>'
            . '<sheet name="Report" sheetId="1" r:id="rId1"/>'
            . '</sheets>'
            . '</workbook>';

        $workbookRels = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';

        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $contents = file_get_contents($tmpFile) ?: '';
        unlink($tmpFile);

        return $contents;
    }

    private function buildWorksheetXml(array $columns, array $rows): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = false;

        $worksheet = $doc->createElement('worksheet');
        $worksheet->setAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $doc->appendChild($worksheet);

        $sheetData = $doc->createElement('sheetData');
        $worksheet->appendChild($sheetData);

        $rowIndex = 1;
        if ($columns !== []) {
            $rowNode = $doc->createElement('row');
            $rowNode->setAttribute('r', (string) $rowIndex);
            foreach ($columns as $index => $column) {
                $cellRef = $this->columnLetter($index + 1) . $rowIndex;
                $cell = $doc->createElement('c');
                $cell->setAttribute('r', $cellRef);
                $cell->setAttribute('t', 'inlineStr');
                $inline = $doc->createElement('is');
                $text = $doc->createElement('t');
                $text->appendChild($doc->createTextNode((string) $column));
                $inline->appendChild($text);
                $cell->appendChild($inline);
                $rowNode->appendChild($cell);
            }
            $sheetData->appendChild($rowNode);
            $rowIndex++;
        }

        foreach ($rows as $row) {
            $rowNode = $doc->createElement('row');
            $rowNode->setAttribute('r', (string) $rowIndex);
            foreach ($columns as $index => $column) {
                $cellRef = $this->columnLetter($index + 1) . $rowIndex;
                $cell = $doc->createElement('c');
                $cell->setAttribute('r', $cellRef);
                $cell->setAttribute('t', 'inlineStr');
                $inline = $doc->createElement('is');
                $text = $doc->createElement('t');
                $text->appendChild($doc->createTextNode((string) ($row[$column] ?? '')));
                $inline->appendChild($text);
                $cell->appendChild($inline);
                $rowNode->appendChild($cell);
            }
            $sheetData->appendChild($rowNode);
            $rowIndex++;
        }

        return $doc->saveXML($worksheet) ?: '';
    }

    private function columnLetter(int $index): string
    {
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = intdiv($index - 1, 26);
        }

        return $letters;
    }

    private function buildPdf(array $columns, array $rows): string
    {
        $lines = [];
        if ($columns !== []) {
            $lines[] = implode(' | ', $columns);
            $lines[] = str_repeat('-', 80);
        }

        foreach ($rows as $row) {
            $line = [];
            foreach ($columns as $column) {
                $line[] = (string) ($row[$column] ?? '');
            }
            $lines[] = implode(' | ', $line);
        }

        $text = implode("\n", $lines);
        return $this->simplePdf($text);
    }

    private function simplePdf(string $text): string
    {
        $lines = explode("\n", $text);
        $y = 750;
        $content = "BT\n/F1 10 Tf\n";

        foreach ($lines as $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $content .= sprintf("1 0 0 1 72 %d Tm (%s) Tj\n", $y, $escaped);
            $y -= 12;
            if ($y < 60) {
                break;
            }
        }
        $content .= "ET";

        $objects = [];
        $objects[] = "1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj";
        $objects[] = "2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj";
        $objects[] = "3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>endobj";
        $objects[] = sprintf("4 0 obj<< /Length %d >>stream\n%s\nendstream\nendobj", strlen($content), $content);
        $objects[] = "5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj";

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 " . count($offsets) . "\n";
        $pdf .= sprintf("%010d 65535 f \n", 0);
        for ($i = 1; $i < count($offsets); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer<< /Size " . count($offsets) . " /Root 1 0 R >>\nstartxref\n";
        $pdf .= $xrefPosition . "\n%%EOF";

        return $pdf;
    }
}
