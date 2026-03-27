<?php
declare(strict_types=1);

require_once __DIR__ . '/exam_metadata.php';

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

function exam_document_view_data(array $exam, array $questions, array $questionOptions): array
{
    $parsed = exam_parse_stored_instructions((string) ($exam['instructions'] ?? ''));
    $metadata = array_replace(exam_default_metadata(), $parsed['metadata']);
    $numberMap = exam_document_question_number_map($questions);
    $answerRows = exam_document_answer_sheet_rows($questions, $questionOptions, $numberMap);

    $schoolName = trim((string) ($metadata['school_name'] !== '' ? $metadata['school_name'] : 'COLÉGIO / ESCOLA'));
    $courseLabel = trim((string) ($metadata['discipline'] !== '' ? $metadata['discipline'] : $metadata['component_name']));
    $courseLabel = $courseLabel !== '' ? $courseLabel : 'ENSINO FUNDAMENTAL, MÉDIO E PROFISSIONALIZANTE';
    $headerTitle = trim((string) ($metadata['exam_label'] !== '' ? $metadata['exam_label'] : 'AVALIAÇÃO'));
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
            $content = implode('<br>', array_map('exam_document_escape', $lines));
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

function exam_document_styles(bool $forPdf = false): string
{
    $bodyBackground = $forPdf ? '#ffffff' : '#f3efe9';
    $shadow = $forPdf ? 'none' : '0 18px 48px rgba(36, 52, 71, 0.10)';

    return <<<CSS
body {
    margin: 0;
    background: {$bodyBackground};
    color: #222222;
    font-family: Arial, Helvetica, sans-serif;
}

.exam-document-stack {
    display: grid;
    gap: 28px;
    width: min(100%, 1120px);
    margin: 0 auto;
}

.exam-page {
    background: #ffffff;
    border: 1px solid rgba(36, 52, 71, 0.12);
    box-shadow: {$shadow};
    overflow: hidden;
}

.exam-page-inner {
    min-height: 1122px;
    padding: 5mm;
    box-sizing: border-box;
}

.exam-sheet {
    min-height: 100%;
    border: 1.8px solid #cfd5dc;
    border-radius: 8px;
    padding: 7px;
    box-sizing: border-box;
}

.exam-sheet.template-version_2 {
    border-color: #b8c5d4;
}

.exam-sheet.template-version_3_1 {
    border-color: #c3ccd8;
}

.exam-header {
    border-bottom: 1px solid #d3d8df;
    padding-bottom: 6px;
}

.exam-brand {
    display: grid;
    grid-template-columns: 60px minmax(0, 1fr);
    gap: 10px;
    align-items: center;
}

.exam-brand-mark {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    border: 1px solid #c9d2dd;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #456b8f;
    background: linear-gradient(180deg, #fdfefe, #eef4f9);
}

.exam-brand-copy {
    text-align: center;
}

.exam-brand-copy strong,
.exam-brand-copy span {
    display: block;
    text-transform: uppercase;
}

.exam-brand-copy strong {
    font-size: 15px;
    line-height: 1.2;
    letter-spacing: 0.01em;
    color: #646464;
}

.exam-brand-copy span {
    margin-top: 2px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.02em;
    color: #7a7a7a;
}

.exam-meta-grid {
    width: 100%;
    margin-top: 6px;
    border-collapse: collapse;
}

.exam-meta-grid td {
    padding: 3px 5px;
    border: 1px solid #bfc7d2;
    font-size: 10.5px;
    vertical-align: middle;
}

.exam-meta-title {
    width: 28%;
    font-weight: 700;
    text-transform: uppercase;
    background: #f5f7fa;
    color: #666666;
}

.exam-meta-fill {
    min-height: 18px;
}

.exam-instructions {
    margin-top: 6px;
    padding: 5px 7px;
    border-radius: 4px;
    background: #f9fbfc;
    border: 1px solid #d5dbe3;
    font-size: 10.5px;
    line-height: 1.35;
}

.exam-instructions p {
    margin: 0;
}

.exam-questions {
    margin-top: 7px;
}

.exam-questions.is-double-column {
    column-count: 2;
    column-gap: 18px;
    position: relative;
    padding: 0 8px;
    background-image: linear-gradient(
        to right,
        transparent calc(50% - 0.5px),
        #d8dee6 calc(50% - 0.5px),
        #d8dee6 calc(50% + 0.5px),
        transparent calc(50% + 0.5px)
    );
    background-repeat: no-repeat;
    background-size: 100% calc(100% - 8px);
    background-position: center top;
}

.exam-questions.is-single-column,
.exam-questions.is-accessibility {
    column-count: 1;
}

.exam-questions.is-economic {
    column-count: 2;
    column-gap: 16px;
    position: relative;
    padding: 0 8px;
    background-image: linear-gradient(
        to right,
        transparent calc(50% - 0.5px),
        #d8dee6 calc(50% - 0.5px),
        #d8dee6 calc(50% + 0.5px),
        transparent calc(50% + 0.5px)
    );
    background-repeat: no-repeat;
    background-size: 100% calc(100% - 8px);
    background-position: center top;
}

.exam-question {
    break-inside: avoid;
    margin: 0 0 10px;
    font-size: 11px;
    line-height: 1.3;
}

.exam-question:last-child {
    margin-bottom: 0;
}

.exam-question-head {
    margin-bottom: 3px;
    font-size: 11px;
    line-height: 1.3;
    font-weight: 700;
}

.exam-question-paragraph,
.exam-question-heading-block,
.exam-question-source-block {
    margin: 0 0 5px;
    line-height: 1.34;
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
    font-size: 9.5px;
    color: #6f7b88;
}

.exam-question-image-wrap {
    margin: 6px 0 8px;
}

.exam-question-image {
    display: block;
    max-width: 82%;
    max-height: 170px;
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
    margin-bottom: 2px;
    line-height: 1.28;
}

.exam-option-prefix {
    display: inline-block;
    min-width: 26px;
    font-weight: 700;
}

.exam-discursive-lines {
    display: grid;
    gap: 8px;
    margin-top: 6px;
}

.exam-discursive-line {
    border-bottom: 1px solid #9ca7b3;
    height: 14px;
}

.exam-drawing-box {
    margin-top: 6px;
    min-height: 130px;
    border: 1px dashed #95a2b0;
    border-radius: 8px;
}

.exam-footer {
    margin-top: 8px;
    text-align: center;
    font-size: 10px;
    color: #788391;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.exam-sheet.template-version_2 .exam-header {
    border-bottom-width: 2px;
}

.exam-sheet.template-version_2 .exam-instructions {
    background: #eef3f8;
}

.exam-sheet.template-version_3_1 .exam-questions {
    column-count: 1;
}

.exam-sheet.template-version_3_1 .exam-question {
    font-size: 12px;
    line-height: 1.45;
    margin-bottom: 12px;
}

.exam-sheet.template-version_3_1 .exam-question-head {
    font-size: 12px;
}

.exam-sheet.style-accessibility .exam-question,
.exam-sheet.style-accessibility .exam-question-head,
.exam-sheet.style-accessibility .exam-option-list li,
.exam-sheet.style-accessibility .exam-question-paragraph,
.exam-sheet.style-accessibility .exam-question-heading-block {
    font-size: 16px;
    line-height: 1.6;
}

.exam-sheet.style-accessibility .exam-brand-copy strong {
    font-size: 18px;
}

.exam-sheet.style-economic .exam-question,
.exam-sheet.style-economic .exam-question-head,
.exam-sheet.style-economic .exam-option-list li,
.exam-sheet.style-economic .exam-question-paragraph,
.exam-sheet.style-economic .exam-question-heading-block {
    font-size: 10px;
    line-height: 1.22;
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
    .exam-brand {
        grid-template-columns: 1fr;
        justify-items: center;
    }

    .exam-questions,
    .exam-questions.is-double-column,
    .exam-questions.is-economic {
        column-count: 1;
        background-image: none;
        padding: 0;
    }

    .exam-answer-grid,
    .exam-preview-summary-grid {
        grid-template-columns: 1fr;
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
        border: 0;
        box-shadow: none;
        page-break-after: always;
    }

    .exam-page:last-child {
        page-break-after: auto;
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
    $yearLabel = trim((string) $metadata['year_reference']);

    ob_start();
    ?>
<header class="exam-header">
    <div class="exam-brand">
        <div class="exam-brand-mark">ESC</div>
        <div class="exam-brand-copy">
            <strong><?= exam_document_escape((string) $document['school_name']) ?></strong>
            <span><?= exam_document_escape((string) $document['course_label']) ?></span>
        </div>
    </div>

    <table class="exam-meta-grid" role="presentation">
        <tr>
            <td class="exam-meta-title"><?= exam_document_escape((string) $document['header_title']) ?></td>
            <td>Prof.: <?= exam_document_escape((string) $metadata['teacher_name']) ?></td>
            <td>Comp. Curricular: <?= exam_document_escape($componentLabel) ?></td>
        </tr>
        <tr>
            <td class="exam-meta-fill">Aluno(a):</td>
            <td>Ano / Série: <?= exam_document_escape($yearLabel) ?></td>
            <td>Nº / Turma: <?= exam_document_escape((string) $metadata['class_name']) ?></td>
        </tr>
        <tr>
            <td>Data: <?= exam_document_escape($dateLabel) ?></td>
            <td>Assin. Resp. (a):</td>
            <td>Valor obtido:</td>
        </tr>
    </table>

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
                    <?= exam_document_escape((string) $option['option_text']) ?>
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

function exam_document_render_question_page(array $document): string
{
    $questionClass = exam_document_question_container_class($document);
    $sheetClass = 'exam-sheet template-' . exam_document_escape($document['template']) . ' style-' . exam_document_escape($document['style']);

    ob_start();
    ?>
<section class="exam-page">
    <div class="exam-page-inner">
        <div class="<?= $sheetClass ?>">
            <?= exam_document_render_header($document) ?>

            <div class="<?= $questionClass ?>">
                <?php foreach ($document['questions'] as $question): ?>
                    <?= exam_document_render_question(
                        $question,
                        $document['question_options'][(int) $question['id']] ?? [],
                        $document['question_number_map'][(int) $question['id']] ?? 0
                    ) ?>
                <?php endforeach; ?>
            </div>

            <div class="exam-footer">
                <?= exam_document_escape((string) $document['school_name']) ?> | <?= exam_document_escape((string) $document['exam']['title']) ?>
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
    return '<div class="exam-document-stack">'
        . exam_document_render_question_page($document)
        . exam_document_render_answer_sheet_page($document)
        . '</div>';
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
