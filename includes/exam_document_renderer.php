<?php
declare(strict_types=1);

require_once __DIR__ . '/exam_metadata.php';
require_once __DIR__ . '/question_helpers.php';

function exam_document_escape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function exam_document_question_number_map(array $questions): array
{
    $map = [];

    foreach ($questions as $index => $question) {
        $map[(int) ($question['id'] ?? 0)] = $index + 1;
    }

    return $map;
}

function exam_document_answer_labels(array $question, array $options): array
{
    if (($question['question_type'] ?? '') === 'true_false') {
        return ['V', 'F'];
    }

    if (($question['question_type'] ?? '') !== 'multiple_choice' || $options === []) {
        return [];
    }

    $labels = [];

    foreach ($options as $index => $option) {
        $labels[] = option_label((int) $index);
    }

    return $labels;
}

function exam_document_answer_sheet_rows(array $questions, array $questionOptions, array $numberMap): array
{
    $rows = [];

    foreach ($questions as $question) {
        $questionId = (int) ($question['id'] ?? 0);
        $labels = exam_document_answer_labels($question, $questionOptions[$questionId] ?? []);

        if ($labels === []) {
            continue;
        }

        $rows[] = [
            'number' => $numberMap[$questionId] ?? (count($rows) + 1),
            'labels' => $labels,
        ];
    }

    return $rows;
}

function exam_document_logo_data_uri(): ?string
{
    $baseDir = dirname(__DIR__);
    $candidates = [
        $baseDir . '/assets/images/cecmtancredoneves.svg',
        $baseDir . '/assets/images/exam-logo.svg',
        $baseDir . '/assets/images/exam-logo.png',
        $baseDir . '/assets/images/exam-logo.jpg',
        $baseDir . '/assets/images/exam-logo.jpeg',
    ];

    foreach ($candidates as $path) {
        if (!is_file($path) || !is_readable($path)) {
            continue;
        }

        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => '',
        };

        if ($mimeType === '') {
            continue;
        }

        $contents = @file_get_contents($path);

        if ($contents === false || $contents === '') {
            continue;
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }

    return null;
}

function exam_document_font_data_uri(string $relativePath, string $mimeType): ?string
{
    static $cache = [];

    if (array_key_exists($relativePath, $cache)) {
        return $cache[$relativePath];
    }

    $path = dirname(__DIR__) . '/' . ltrim($relativePath, '/');

    if (!is_file($path) || !is_readable($path)) {
        $cache[$relativePath] = null;
        return null;
    }

    $contents = @file_get_contents($path);

    if ($contents === false || $contents === '') {
        $cache[$relativePath] = null;
        return null;
    }

    $cache[$relativePath] = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    return $cache[$relativePath];
}

function exam_document_font_face_css(): string
{
    $fontData = exam_document_font_data_uri('assets/fonts/Poppins-Black.ttf', 'font/ttf');

    if ($fontData === null) {
        return '';
    }

    return "@font-face {\n"
        . "    font-family: 'Quest Poppins Black';\n"
        . "    src: url('" . $fontData . "') format('truetype');\n"
        . "    font-style: normal;\n"
        . "    font-weight: 900;\n"
        . "}\n";
}

function exam_document_strip_formatting(string $text): string
{
    $text = preg_replace('/!\[[^\]]*\]\(([^)]+)\)/', ' ', $text) ?? $text;
    $text = preg_replace('/!\s*\(([^)]+)\)/', ' ', $text) ?? $text;
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = strip_tags($text);
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    return trim($text);
}

function exam_document_question_weight(array $question, array $options, string $style): int
{
    $charsPerLine = match ($style) {
        'single_column' => 96,
        'accessibility' => 78,
        'economic' => 54,
        default => 50,
    };

    $promptText = exam_document_strip_formatting((string) ($question['prompt'] ?? ''));
    $titleText = exam_document_strip_formatting((string) ($question['title'] ?? ''));
    $weight = 8;
    $weight += max(1, (int) ceil(mb_strlen($titleText, 'UTF-8') / $charsPerLine));
    $weight += max(2, (int) ceil(mb_strlen($promptText, 'UTF-8') / $charsPerLine));
    $weight += max(0, count(preg_split("/\n{2,}/", trim((string) ($question['prompt'] ?? ''))) ?: []) - 1);

    foreach ($options as $option) {
        $optionText = exam_document_strip_formatting((string) ($option['option_text'] ?? ''));
        $weight += max(1, (int) ceil(mb_strlen($optionText, 'UTF-8') / max(28, $charsPerLine - 8))) + 1;
    }

    if (($question['question_type'] ?? '') === 'discursive') {
        $weight += max(6, (int) ($question['response_lines'] ?? 6));
    }

    if (($question['question_type'] ?? '') === 'drawing') {
        $weight += 16;
    }

    if (trim((string) ($question['prompt_image_url'] ?? '')) !== '') {
        $weight += 12;
    }

    if (trim((string) ($question['source_name'] ?? '')) !== '') {
        $weight += 2;
    }

    return $weight;
}

function exam_document_page_capacity(array $document): int
{
    $style = (string) ($document['style'] ?? 'double_column');
    $template = (string) ($document['template'] ?? 'version_1');

    $capacity = match ($style) {
        'single_column' => 74,
        'accessibility' => 58,
        'economic' => 130,
        default => 112,
    };

    if ($template === 'version_3_1') {
        $capacity -= 10;
    }

    return max(42, $capacity);
}

function exam_document_paginate_questions(array $document): array
{
    $pages = [];
    $currentPage = [];
    $currentWeight = 0;
    $capacity = exam_document_page_capacity($document);
    $style = (string) ($document['style'] ?? 'double_column');

    foreach ($document['questions'] as $question) {
        $questionId = (int) ($question['id'] ?? 0);
        $options = $document['question_options'][$questionId] ?? [];
        $weight = exam_document_question_weight($question, $options, $style);

        if ($currentPage !== [] && ($currentWeight + $weight) > $capacity) {
            $pages[] = $currentPage;
            $currentPage = [];
            $currentWeight = 0;
        }

        $currentPage[] = $question;
        $currentWeight += $weight;
    }

    if ($currentPage !== []) {
        $pages[] = $currentPage;
    }

    return $pages === [] ? [[]] : $pages;
}

function exam_document_view_data(array $exam, array $questions, array $questionOptions): array
{
    $parsed = exam_parse_stored_instructions((string) ($exam['instructions'] ?? ''));
    $metadata = array_replace(exam_default_metadata(), $parsed['metadata']);
    $numberMap = exam_document_question_number_map($questions);
    $answerRows = exam_document_answer_sheet_rows($questions, $questionOptions, $numberMap);

    $schoolName = trim((string) ($metadata['school_name'] !== '' ? $metadata['school_name'] : EXAM_DEFAULT_SCHOOL_NAME));
    $courseLabel = EXAM_DEFAULT_SCHOOL_SUBTITLE;
    $headerTitle = trim((string) ($metadata['exam_label'] !== '' ? $metadata['exam_label'] : 'AVALIAÇÃO TRIMESTRAL'));
    $template = (string) ($metadata['exam_template'] !== '' ? $metadata['exam_template'] : 'version_1');
    $style = (string) ($metadata['exam_style'] !== '' ? $metadata['exam_style'] : 'double_column');

    return [
        'exam' => $exam,
        'questions' => $questions,
        'question_options' => $questionOptions,
        'question_number_map' => $numberMap,
        'answer_sheet_rows' => $answerRows,
        'parsed' => $parsed,
        'metadata' => $metadata,
        'metadata_summary' => exam_metadata_summary($metadata),
        'template' => $template,
        'style' => $style,
        'school_name' => $schoolName,
        'course_label' => $courseLabel,
        'header_title' => $headerTitle,
        'logo_data_uri' => exam_document_logo_data_uri(),
    ];
}

function exam_document_is_usable_image_url(?string $url): bool
{
    $url = trim((string) $url);

    if ($url === '' || stripos($url, 'broken-image.svg') !== false) {
        return false;
    }

    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function exam_document_prepare_prompt(string $prompt): string
{
    $prompt = str_replace(["\r\n", "\r"], "\n", trim($prompt));
    $prompt = preg_replace('/^\s*!\s*\((https?:\/\/[^)\s]+)\)\s*$/mi', '', $prompt) ?? $prompt;
    $prompt = preg_replace('/^\s*!\[\]\((https?:\/\/[^)\s]+)\)\s*$/mi', '', $prompt) ?? $prompt;
    $prompt = preg_replace('/^\s*!\[[^\]]*\]\((https?:\/\/[^)\s]+)\)\s*$/mi', '', $prompt) ?? $prompt;
    $prompt = preg_replace("/\n{3,}/", "\n\n", $prompt) ?? $prompt;

    return trim($prompt);
}

function exam_document_prompt_block_class(string $block): string
{
    $normalized = trim($block);

    if ($normalized === '') {
        return 'exam-question-paragraph';
    }

    if (preg_match('/^(texto|texto\s+[ivxlcdm]+|leia|observe|considere|quest[aã]o|figura|charge|tirinha)/iu', $normalized) === 1) {
        return 'exam-question-heading-block';
    }

    if (preg_match('/^dispon[ií]vel em:/iu', $normalized) === 1) {
        return 'exam-question-source-block';
    }

    return 'exam-question-paragraph';
}

function exam_document_render_prompt_html(array $question): string
{
    $prompt = exam_document_prepare_prompt((string) ($question['prompt'] ?? ''));
    $imageUrl = (string) ($question['prompt_image_url'] ?? '');
    $htmlParts = [];

    if ($prompt !== '') {
        $blocks = preg_split("/\n{2,}/", $prompt) ?: [];

        foreach ($blocks as $block) {
            $lines = array_values(array_filter(array_map(
                static fn(string $line): string => trim($line),
                preg_split("/\n/", trim($block)) ?: []
            ), static fn(string $line): bool => $line !== ''));

            if ($lines === []) {
                continue;
            }

            $className = exam_document_prompt_block_class(implode(' ', $lines));
            $content = question_render_formatted_text_html(implode("\n", $lines));
            $htmlParts[] = '<p class="' . $className . '">' . $content . '</p>';
        }
    }

    if (exam_document_is_usable_image_url($imageUrl)) {
        $htmlParts[] = '<div class="exam-question-image-wrap"><img class="exam-question-image" src="'
            . exam_document_escape($imageUrl)
            . '" alt="Imagem complementar da questão"></div>';
    }

    return implode('', $htmlParts);
}

function exam_document_render_inline_multiline_text(string $text): string
{
    return question_render_formatted_text_html($text);
}

function exam_document_styles(bool $forPdf = false): string
{
    $bodyBackground = $forPdf ? '#ffffff' : '#eef2f6';
    $shadow = $forPdf ? 'none' : '0 18px 42px rgba(20, 39, 62, 0.12)';
    $fontFaceCss = exam_document_font_face_css();

    return <<<CSS
{$fontFaceCss}body {
    margin: 0;
    background: {$bodyBackground};
    color: #222222;
    font-family: Arial, Helvetica, sans-serif;
}

@page {
    size: A4 portrait;
    margin: 5mm;
}

* {
    box-sizing: border-box;
}

.math-fraction {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    vertical-align: middle;
    margin: 0 0.08em;
    line-height: 1;
    font-size: 0.92em;
}

.math-fraction-top,
.math-fraction-bottom {
    display: block;
    padding: 0 0.14em;
}

.math-fraction-top {
    border-bottom: 1.2px solid currentColor;
    padding-bottom: 0.06em;
}

.math-fraction-bottom {
    padding-top: 0.06em;
}

.math-expression-block {
    display: block;
    margin: 0.4rem 0;
    text-align: center;
    font-family: "Times New Roman", Georgia, serif;
    font-size: 1.12em;
    font-style: italic;
}

.exam-document-stack {
    display: grid;
    gap: 24px;
    width: min(100%, 210mm);
    margin: 0 auto;
}

.exam-page {
    width: 210mm;
    max-width: 100%;
    background: #ffffff;
    border: 1px solid rgba(36, 52, 71, 0.12);
    border-radius: 8px;
    box-shadow: {$shadow};
}

.exam-page-inner {
    min-height: 287mm;
    padding: 4.5mm;
}

.exam-sheet {
    min-height: 100%;
    border: 1.2px solid #8f8f8f;
    border-radius: 8px;
    padding: 6px;
}

.exam-header {
    display: grid;
    gap: 8px;
    padding-bottom: 6px;
}

.exam-brand {
    display: grid;
    grid-template-columns: 96px minmax(0, 1fr);
    gap: 14px;
    align-items: center;
}

.exam-brand-mark {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    overflow: hidden;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.exam-brand-mark img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    border-radius: 50%;
    display: block;
}

.exam-brand-copy {
    text-align: center;
}

.exam-school-name,
.exam-school-subtitle {
    display: block;
    text-transform: uppercase;
}

.exam-school-name {
    font-family: 'Quest Poppins Black', Arial, Helvetica, sans-serif;
    font-size: 21px;
    line-height: 1.05;
    letter-spacing: 0.01em;
    color: #7f7f7f;
}

.exam-school-subtitle {
    margin-top: 2px;
    font-family: 'Quest Poppins Black', Arial, Helvetica, sans-serif;
    font-size: 16px;
    line-height: 1.06;
    color: #7f7f7f;
}

.exam-header-rows {
    display: grid;
    gap: 5px;
}

.exam-header-row {
    display: grid;
    gap: 5px;
}

.exam-header-row-primary {
    grid-template-columns: 28% 30% 42%;
}

.exam-header-row-student {
    grid-template-columns: 70% 10% 20%;
}

.exam-header-row-secondary {
    grid-template-columns: 23% 47% 30%;
}

.exam-header-cell {
    min-width: 0;
    min-height: 42px;
    padding: 6px 10px;
    border: 1px solid #8f8f8f;
    border-radius: 8px;
    background: #ffffff;
    display: flex;
    align-items: center;
    gap: 6px;
    overflow: hidden;
}

.exam-header-cell-fill {
    align-items: flex-end;
    padding-bottom: 10px;
}

.exam-header-inline {
    display: inline-flex;
    align-items: baseline;
    gap: 6px;
    flex-wrap: wrap;
    min-width: 0;
}

.exam-header-label {
    color: #8d8d8d;
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
}

.exam-header-value {
    color: #111111;
    font-size: 12px;
    font-weight: 700;
    min-width: 0;
    overflow-wrap: anywhere;
}

.exam-header-title .exam-header-value {
    font-family: 'Quest Poppins Black', Arial, Helvetica, sans-serif;
    color: #7f7f7f;
    font-size: 13px;
    line-height: 1.05;
}

.exam-header-cell-fill .exam-header-label {
    font-size: 11px;
}

.exam-instructions {
    padding: 6px 8px;
    border: 1px solid #cfd6df;
    border-radius: 8px;
    background: #f8fafc;
    font-size: 10.5px;
    line-height: 1.35;
}

.exam-instructions p {
    margin: 0;
}

.exam-questions {
    margin-top: 8px;
}

.exam-questions.is-double-column,
.exam-questions.is-economic {
    column-count: 2;
    column-gap: 16px;
    column-fill: auto;
}

.exam-questions.is-single-column,
.exam-questions.is-accessibility {
    column-count: 1;
}

.exam-question {
    display: block;
    margin: 0 0 10px;
    font-size: 11px;
    line-height: 1.32;
    overflow-wrap: anywhere;
    break-inside: auto;
    page-break-inside: auto;
}

.exam-question:last-child {
    margin-bottom: 0;
}

.exam-question-head {
    margin-bottom: 4px;
    font-size: 11px;
    line-height: 1.28;
    font-weight: 700;
    break-after: avoid;
}

.exam-question-body {
    break-inside: auto;
}

.exam-question-paragraph,
.exam-question-heading-block,
.exam-question-source-block {
    margin: 0 0 5px;
    line-height: 1.34;
    overflow-wrap: anywhere;
}

.exam-question-paragraph {
    text-align: left;
}

.exam-question-heading-block {
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.01em;
}

.exam-question-source-block,
.exam-question-source {
    font-size: 9px;
    color: #6f7b88;
}

.exam-question-image-wrap {
    margin: 6px 0 8px;
}

.exam-question-image {
    display: block;
    max-width: 100%;
    max-height: 155px;
    width: auto;
    height: auto;
    margin: 0 auto;
}

.exam-question-source {
    margin-top: 4px;
}

.exam-option-list {
    list-style: none;
    margin: 5px 0 0;
    padding: 0;
}

.exam-option-list li {
    margin-bottom: 3px;
    line-height: 1.28;
    overflow-wrap: anywhere;
}

.exam-option-prefix {
    display: inline-block;
    min-width: 24px;
    font-weight: 700;
}

.exam-discursive-lines {
    display: grid;
    gap: 8px;
    margin-top: 8px;
}

.exam-discursive-line {
    border-bottom: 1px solid #9ca7b3;
    height: 14px;
}

.exam-drawing-box {
    margin-top: 8px;
    min-height: 120px;
    border: 1px dashed #95a2b0;
    border-radius: 8px;
}

.exam-footer {
    margin-top: 10px;
    text-align: center;
    font-size: 9px;
    color: #788391;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.exam-sheet.template-version_2 .exam-sheet,
.exam-sheet.template-version_2 {
    border-color: #8999ac;
}

.exam-sheet.template-version_3_1 .exam-questions {
    column-count: 1;
}

.exam-sheet.template-version_3_1 .exam-question {
    font-size: 12px;
    line-height: 1.42;
}

.exam-sheet.style-accessibility .exam-questions {
    column-count: 1;
}

.exam-sheet.style-accessibility .exam-question,
.exam-sheet.style-accessibility .exam-question-head,
.exam-sheet.style-accessibility .exam-option-list li,
.exam-sheet.style-accessibility .exam-question-paragraph,
.exam-sheet.style-accessibility .exam-question-heading-block {
    font-size: 15px;
    line-height: 1.55;
}

.exam-sheet.style-accessibility .exam-school-name {
    font-size: 21px;
}

.exam-sheet.style-economic .exam-question,
.exam-sheet.style-economic .exam-question-head,
.exam-sheet.style-economic .exam-option-list li,
.exam-sheet.style-economic .exam-question-paragraph,
.exam-sheet.style-economic .exam-question-heading-block {
    font-size: 10px;
    line-height: 1.2;
}

.exam-answer-page .exam-page-inner {
    min-height: auto;
}

.exam-answer-title {
    margin: 12px 0 14px;
    text-align: center;
    font-size: 16px;
    font-weight: 700;
}

.exam-answer-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px 18px;
}

.exam-answer-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    font-size: 12px;
}

.exam-answer-number {
    width: 38px;
    font-weight: 700;
}

.exam-answer-option {
    width: 20px;
    height: 20px;
    border: 1px solid #758292;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

.exam-preview-notes {
    display: grid;
    gap: 14px;
}

.exam-preview-note-panel {
    padding: 18px 20px;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid rgba(36, 52, 71, 0.12);
    box-shadow: 0 10px 24px rgba(36, 52, 71, 0.06);
}

.exam-preview-note-panel h3 {
    margin: 0 0 10px;
    font-size: 16px;
    color: #243447;
}

.exam-preview-note-panel p,
.exam-preview-note-panel li {
    font-size: 14px;
    line-height: 1.45;
}

.exam-preview-note-panel ul {
    margin: 0;
    padding-left: 18px;
}

.exam-preview-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.exam-preview-summary-grid div {
    padding: 12px 14px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid rgba(36, 52, 71, 0.08);
}

.exam-preview-summary-grid strong,
.exam-preview-summary-grid span {
    display: block;
}

.exam-preview-summary-grid strong {
    margin-bottom: 4px;
    font-size: 12px;
    text-transform: uppercase;
    color: #66768a;
}

.exam-preview-summary-grid span {
    font-size: 14px;
    color: #243447;
}

@media screen and (max-width: 920px) {
    .exam-page {
        width: 100%;
    }

    .exam-brand {
        grid-template-columns: 1fr;
        justify-items: center;
    }

    .exam-header-row,
    .exam-header-row-primary,
    .exam-header-row-student,
    .exam-header-row-secondary,
    .exam-answer-grid,
    .exam-preview-summary-grid {
        grid-template-columns: 1fr;
    }

    .exam-questions,
    .exam-questions.is-double-column,
    .exam-questions.is-economic {
        column-count: 1;
    }
}

@media print {
    body {
        background: #ffffff;
    }

    .exam-document-stack {
        gap: 0;
        width: 100%;
    }

    .exam-page {
        width: 100%;
        border: 0;
        border-radius: 0;
        box-shadow: none;
        page-break-after: always;
        break-after: page;
    }

    .exam-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }
}
CSS;
}

function exam_document_render_header(array $document): string
{
    $metadata = $document['metadata'];
    $instructions = trim((string) $document['parsed']['instructions']);
    $dateLabel = exam_format_date((string) $metadata['application_date']);
    $componentLabel = trim((string) ($metadata['component_name'] !== '' ? $metadata['component_name'] : $metadata['discipline']));
    $teacherLabel = trim((string) $metadata['teacher_name']);
    $classLabel = trim((string) $metadata['class_name']);
    $componentLabel = $componentLabel !== '' ? $componentLabel : 'Não informado';
    $teacherLabel = $teacherLabel !== '' ? $teacherLabel : 'Não informado';

    ob_start();
    ?>
<header class="exam-header">
    <div class="exam-brand">
        <div class="exam-brand-mark">
            <?php if (!empty($document['logo_data_uri'])): ?>
                <img src="<?= exam_document_escape((string) $document['logo_data_uri']) ?>" alt="Logo da escola">
            <?php else: ?>
                ESC
            <?php endif; ?>
        </div>
        <div class="exam-brand-copy">
            <strong class="exam-school-name"><?= exam_document_escape((string) $document['school_name']) ?></strong>
            <span class="exam-school-subtitle"><?= exam_document_escape((string) $document['course_label']) ?></span>
        </div>
    </div>

    <div class="exam-header-rows">
        <div class="exam-header-row exam-header-row-primary">
            <div class="exam-header-cell exam-header-title">
                <div class="exam-header-inline">
                    <span class="exam-header-value"><?= exam_document_escape((string) $document['header_title']) ?></span>
                </div>
            </div>
            <div class="exam-header-cell">
                <div class="exam-header-inline">
                    <span class="exam-header-label">Prof.:</span>
                    <span class="exam-header-value"><?= exam_document_escape($teacherLabel) ?></span>
                </div>
            </div>
            <div class="exam-header-cell">
                <div class="exam-header-inline">
                    <span class="exam-header-label">Comp. Curricular:</span>
                    <span class="exam-header-value"><?= exam_document_escape($componentLabel) ?></span>
                </div>
            </div>
        </div>

        <div class="exam-header-row exam-header-row-student">
            <div class="exam-header-cell exam-header-cell-fill">
                <span class="exam-header-label">Aluno(a):</span>
            </div>
            <div class="exam-header-cell">
                <span class="exam-header-label">Nº</span>
            </div>
            <div class="exam-header-cell">
                <span class="exam-header-label">Turma:</span>
                <?php if ($classLabel !== ''): ?>
                    <span class="exam-header-value"><?= exam_document_escape($classLabel) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="exam-header-row exam-header-row-secondary">
            <div class="exam-header-cell">
                <div class="exam-header-inline">
                    <span class="exam-header-label">Data:</span>
                    <span class="exam-header-value"><?= exam_document_escape($dateLabel !== '' ? $dateLabel : '____/____/________') ?></span>
                </div>
            </div>
            <div class="exam-header-cell exam-header-cell-fill">
                <span class="exam-header-label">Assin. Resp. (a):</span>
            </div>
            <div class="exam-header-cell exam-header-cell-fill">
                <span class="exam-header-label">Valor obtido:</span>
            </div>
        </div>
    </div>

    <?php if ($instructions !== ''): ?>
        <div class="exam-instructions">
            <p><?= nl2br(exam_document_escape($instructions)) ?></p>
        </div>
    <?php endif; ?>
</header>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_question(array $question, array $options, int $displayNumber): string
{
    $title = trim((string) ($question['title'] ?? ''));
    $promptHtml = exam_document_render_prompt_html($question);

    ob_start();
    ?>
<article class="exam-question">
    <div class="exam-question-head">
        <?= exam_document_escape((string) $displayNumber) ?>.
        <?php if ($title !== ''): ?>
            (<?= exam_document_escape($title) ?>)
        <?php endif; ?>
    </div>

    <?php if ($promptHtml !== ''): ?>
        <div class="exam-question-body"><?= $promptHtml ?></div>
    <?php endif; ?>

    <?php if (($question['question_type'] ?? '') === 'multiple_choice'): ?>
        <ul class="exam-option-list">
            <?php foreach ($options as $index => $option): ?>
                <li>
                    <span class="exam-option-prefix"><?= exam_document_escape(option_label((int) $index)) ?>)</span>
                    <?= exam_document_render_inline_multiline_text((string) $option['option_text']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif (($question['question_type'] ?? '') === 'true_false'): ?>
        <ul class="exam-option-list">
            <li><span class="exam-option-prefix">V)</span> Verdadeiro</li>
            <li><span class="exam-option-prefix">F)</span> Falso</li>
        </ul>
    <?php elseif (($question['question_type'] ?? '') === 'discursive'): ?>
        <div class="exam-discursive-lines">
            <?php for ($i = 0; $i < max(4, min(14, (int) ($question['response_lines'] ?? 6))); $i++): ?>
                <div class="exam-discursive-line"></div>
            <?php endfor; ?>
        </div>
    <?php elseif (($question['question_type'] ?? '') === 'drawing'): ?>
        <div class="exam-drawing-box"></div>
    <?php endif; ?>

    <?php if (!empty($question['source_name'])): ?>
        <div class="exam-question-source">Fonte: <?= exam_document_escape((string) $question['source_name']) ?></div>
    <?php endif; ?>
</article>
    <?php

    return (string) ob_get_clean();
}

function exam_document_question_container_class(array $document): string
{
    return match ($document['style']) {
        'single_column' => 'exam-questions is-single-column',
        'economic' => 'exam-questions is-economic',
        'accessibility' => 'exam-questions is-accessibility',
        default => 'exam-questions is-double-column',
    };
}

function exam_document_render_question_page(array $document, array $questionsOnPage, int $pageNumber, int $totalPages): string
{
    $questionClass = exam_document_question_container_class($document);
    $sheetClass = 'exam-sheet template-' . exam_document_escape($document['template']) . ' style-' . exam_document_escape($document['style']);
    $pageSuffix = $totalPages > 1 ? ' | Página ' . $pageNumber . ' de ' . $totalPages : '';

    ob_start();
    ?>
<section class="exam-page">
    <div class="exam-page-inner">
        <div class="<?= $sheetClass ?>">
            <?= exam_document_render_header($document) ?>

            <div class="<?= $questionClass ?>">
                <?php foreach ($questionsOnPage as $question): ?>
                    <?= exam_document_render_question(
                        $question,
                        $document['question_options'][(int) $question['id']] ?? [],
                        $document['question_number_map'][(int) $question['id']] ?? 0
                    ) ?>
                <?php endforeach; ?>
            </div>

            <div class="exam-footer">
                <?= exam_document_escape((string) $document['school_name']) ?> | <?= exam_document_escape((string) $document['exam']['title']) ?><?= exam_document_escape($pageSuffix) ?>
            </div>
        </div>
    </div>
</section>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_answer_sheet_page(array $document): string
{
    if ($document['answer_sheet_rows'] === []) {
        return '';
    }

    ob_start();
    ?>
<section class="exam-page exam-answer-page">
    <div class="exam-page-inner">
        <div class="exam-sheet template-<?= exam_document_escape($document['template']) ?> style-<?= exam_document_escape($document['style']) ?>">
            <?= exam_document_render_header($document) ?>

            <div class="exam-answer-title">Folha de gabarito</div>

            <div class="exam-answer-grid">
                <?php foreach ($document['answer_sheet_rows'] as $row): ?>
                    <div class="exam-answer-row">
                        <span class="exam-answer-number"><?= exam_document_escape((string) $row['number']) ?>.</span>
                        <?php foreach ($row['labels'] as $label): ?>
                            <span class="exam-answer-option"><?= exam_document_escape($label) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="exam-footer">
                <?= exam_document_escape((string) $document['school_name']) ?> | Folha de gabarito
            </div>
        </div>
    </div>
</section>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_sheet(array $document): string
{
    $questionPages = exam_document_paginate_questions($document);
    $html = '<div class="exam-document-stack">';
    $totalPages = count($questionPages);

    foreach ($questionPages as $index => $questionsOnPage) {
        $html .= exam_document_render_question_page($document, $questionsOnPage, $index + 1, $totalPages);
    }

    $html .= exam_document_render_answer_sheet_page($document);
    $html .= '</div>';

    return $html;
}

function exam_document_render_preview_notes(array $document): string
{
    $metadata = $document['metadata'];
    $summaryItems = [
        ['label' => 'Colégio', 'value' => (string) $document['school_name']],
        ['label' => 'Professor', 'value' => (string) $metadata['teacher_name']],
        ['label' => 'Turma', 'value' => (string) $metadata['class_name']],
        ['label' => 'Questões', 'value' => (string) count($document['questions'])],
    ];

    ob_start();
    ?>
<section class="exam-preview-notes">
    <article class="exam-preview-note-panel">
        <h3>Resumo da prova</h3>
        <div class="exam-preview-summary-grid">
            <?php foreach ($summaryItems as $item): ?>
                <div>
                    <strong><?= exam_document_escape($item['label']) ?></strong>
                    <span><?= exam_document_escape($item['value']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="exam-preview-note-panel">
        <h3>Checklist de revisão</h3>
        <ul>
            <li>Confira cabeçalho, data, turma e professor antes de exportar.</li>
            <li>Valide a ordem das questões e o espaçamento do modelo escolhido.</li>
            <li>Se houver questões objetivas, revise a folha de gabarito.</li>
        </ul>
    </article>
</section>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_html(array $document): string
{
    $title = exam_document_escape((string) ($document['exam']['title'] ?? 'Prova'));
    $styles = exam_document_styles(true);
    $sheet = exam_document_render_sheet($document);

    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        @page { size: A4 portrait; margin: 5mm; }
        {$styles}
    </style>
</head>
<body>
    {$sheet}
</body>
</html>
HTML;
}
