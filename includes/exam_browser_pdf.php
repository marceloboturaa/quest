<?php
declare(strict_types=1);

function exam_pdf_browser_candidates(): array
{
    $configured = trim((string) getenv('QUEST_PDF_BROWSER'));
    $candidates = [
        $configured,
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
    ];

    return array_values(array_unique(array_filter($candidates, static fn(string $path): bool => $path !== '')));
}

function exam_pdf_find_browser(): ?string
{
    foreach (exam_pdf_browser_candidates() as $path) {
        if (is_file($path)) {
            return $path;
        }
    }

    return null;
}

function exam_pdf_supports_browser_rendering(): bool
{
    return exam_pdf_find_browser() !== null;
}

function exam_pdf_path_to_file_url(string $path): string
{
    $normalized = str_replace('\\', '/', $path);
    return 'file:///' . str_replace(' ', '%20', ltrim($normalized, '/'));
}

function exam_pdf_render_with_browser(string $html): ?string
{
    $browserPath = exam_pdf_find_browser();

    if ($browserPath === null) {
        return null;
    }

    $tempDir = rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'quest_pdf_' . bin2hex(random_bytes(6));
    $htmlPath = $tempDir . DIRECTORY_SEPARATOR . 'exam.html';
    $pdfPath = $tempDir . DIRECTORY_SEPARATOR . 'exam.pdf';

    if (!mkdir($tempDir, 0777, true) && !is_dir($tempDir)) {
        return null;
    }

    file_put_contents($htmlPath, $html);

    $command = '"' . $browserPath . '"'
        . ' --headless=new'
        . ' --disable-gpu'
        . ' --no-sandbox'
        . ' --allow-file-access-from-files'
        . ' --run-all-compositor-stages-before-draw'
        . ' --virtual-time-budget=2000'
        . ' --no-pdf-header-footer'
        . ' --print-to-pdf="' . $pdfPath . '"'
        . ' "' . exam_pdf_path_to_file_url($htmlPath) . '"'
        . ' 2>&1';

    exec($command, $output, $exitCode);

    $pdfBinary = null;

    if ($exitCode === 0 && is_file($pdfPath) && filesize($pdfPath) > 0) {
        $pdfBinary = file_get_contents($pdfPath) ?: null;
    }

    @unlink($htmlPath);
    @unlink($pdfPath);
    @rmdir($tempDir);

    return $pdfBinary;
}
