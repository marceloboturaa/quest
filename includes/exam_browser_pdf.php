<?php
declare(strict_types=1);

function exam_pdf_resolve_candidate(string $candidate): ?string
{
    $candidate = trim($candidate);

    if ($candidate === '') {
        return null;
    }

    if (
        str_contains($candidate, '\\')
        || str_contains($candidate, '/')
        || preg_match('/^[A-Za-z]:\\\\/', $candidate) === 1
    ) {
        return is_file($candidate) ? $candidate : null;
    }

    $lookupCommand = PHP_OS_FAMILY === 'Windows'
        ? 'where ' . escapeshellarg($candidate) . ' 2>NUL'
        : 'command -v ' . escapeshellarg($candidate) . ' 2>/dev/null';
    $output = shell_exec($lookupCommand);

    if (!is_string($output) || trim($output) === '') {
        return null;
    }

    $normalized = str_replace(["\r\n", "\r"], "\n", trim($output));
    $resolved = trim((string) strtok($normalized, "\n"));

    return $resolved !== '' ? $resolved : null;
}

function exam_pdf_browser_candidates(): array
{
    $configured = trim((string) getenv('QUEST_PDF_BROWSER'));
    $candidates = [
        $configured,
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
        'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        '/usr/bin/google-chrome',
        '/usr/bin/google-chrome-stable',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
        '/snap/bin/chromium',
        '/usr/bin/microsoft-edge',
        '/usr/bin/microsoft-edge-stable',
        'google-chrome',
        'google-chrome-stable',
        'chromium',
        'chromium-browser',
        'microsoft-edge',
        'microsoft-edge-stable',
        'chrome',
        'msedge',
    ];

    return array_values(array_unique(array_filter($candidates, static fn(string $path): bool => $path !== '')));
}

function exam_pdf_find_browser(): ?string
{
    foreach (exam_pdf_browser_candidates() as $path) {
        $resolved = exam_pdf_resolve_candidate($path);

        if ($resolved !== null) {
            return $resolved;
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
