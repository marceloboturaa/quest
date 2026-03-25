<?php
declare(strict_types=1);

function pdf_normalize_text(string $text): string
{
    $normalized = preg_replace("/\r\n?/", "\n", $text) ?? $text;
    $converted = @iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $normalized);

    return $converted === false ? $normalized : $converted;
}

function pdf_escape_text(string $text): string
{
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace('(', '\\(', $text);

    return str_replace(')', '\\)', $text);
}

function pdf_wrap_text(string $text, int $maxLength = 92): array
{
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

    if ($text === '') {
        return [''];
    }

    $words = preg_split('/\s+/', $text) ?: [$text];
    $lines = [];
    $current = '';

    foreach ($words as $word) {
        $candidate = $current === '' ? $word : $current . ' ' . $word;

        if (mb_strlen($candidate) <= $maxLength) {
            $current = $candidate;
            continue;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        $current = $word;
    }

    if ($current !== '') {
        $lines[] = $current;
    }

    return $lines;
}

function pdf_paginate_lines(array $lines, int $maxLinesPerPage = 45): array
{
    $pages = [];
    $currentPage = [];

    foreach ($lines as $line) {
        if (count($currentPage) >= $maxLinesPerPage) {
            $pages[] = $currentPage;
            $currentPage = [];
        }

        $currentPage[] = $line;
    }

    if ($currentPage !== []) {
        $pages[] = $currentPage;
    }

    return $pages === [] ? [['Sem conteudo.']] : $pages;
}

function pdf_build_document(array $pages, string $title = 'Documento'): string
{
    $objects = [];
    $pageObjectNumbers = [];
    $fontObjectNumber = 3 + (count($pages) * 2);

    $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
    $objects[] = '';

    $pageIndex = 0;
    foreach ($pages as $pageLines) {
        $pageObjectNumber = 3 + ($pageIndex * 2);
        $contentObjectNumber = $pageObjectNumber + 1;
        $pageObjectNumbers[] = $pageObjectNumber;

        $content = "BT\n/F1 12 Tf\n50 790 Td\n";
        $first = true;

        foreach ($pageLines as $line) {
            $escaped = pdf_escape_text(pdf_normalize_text($line));
            if ($first) {
                $content .= '(' . $escaped . ") Tj\n";
                $first = false;
            } else {
                $content .= "0 -16 Td\n(" . $escaped . ") Tj\n";
            }
        }

        $content .= "ET\n";

        $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 ' . $fontObjectNumber . ' 0 R >> >> /Contents ' . $contentObjectNumber . ' 0 R >>';
        $objects[] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "endstream";
        $pageIndex++;
    }

    $kids = implode(' ', array_map(static fn(int $number): string => $number . ' 0 R', $pageObjectNumbers));
    $objects[1] = '<< /Type /Pages /Kids [' . $kids . '] /Count ' . count($pageObjectNumbers) . ' >>';
    $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';

    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    foreach ($objects as $index => $object) {
        $offsets[] = strlen($pdf);
        $pdf .= ($index + 1) . " 0 obj\n" . $object . "\nendobj\n";
    }

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";

    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= sprintf('%010d 00000 n ', $offsets[$i]) . "\n";
    }

    $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R /Info << /Title (" . pdf_escape_text(pdf_normalize_text($title)) . ") >> >>\n";
    $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

    return $pdf;
}
