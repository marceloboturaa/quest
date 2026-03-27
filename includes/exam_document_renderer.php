<?php
declare(strict_types=1);

require_once __DIR__ . '/exam_metadata.php';

function exam_document_escape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function exam_document_question_weight(array $question, array $options): int
{
    $prompt = trim((string) ($question['prompt'] ?? ''));
    $title = trim((string) ($question['title'] ?? ''));
    $weight = 8;
    $weight += (int) ceil(strlen($title) / 90);
    $weight += (int) ceil(strlen($prompt) / 150);
    $weight += count($options) * 2;

    if (($question['question_type'] ?? '') === 'discursive') {
        $weight += max(4, (int) ($question['response_lines'] ?? 5));
    }

    if (($question['question_type'] ?? '') === 'drawing') {
        $weight += 10;
    }

    return $weight;
}

function exam_document_split_questions(array $questions, array $questionOptions): array
{
    $columns = [[], []];
    $weights = [0, 0];

    foreach ($questions as $question) {
        $questionId = (int) ($question['id'] ?? 0);
        $options = $questionOptions[$questionId] ?? [];
        $target = $weights[0] <= $weights[1] ? 0 : 1;
        $columns[$target][] = $question;
        $weights[$target] += exam_document_question_weight($question, $options);
    }

    return $columns;
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

function exam_document_answer_sheet_rows(array $questions, array $questionOptions): array
{
    $rows = [];

    foreach ($questions as $question) {
        $labels = exam_document_answer_labels($question, $questionOptions[(int) ($question['id'] ?? 0)] ?? []);

        if ($labels === []) {
            continue;
        }

        $rows[] = [
            'number' => count($rows) + 1,
            'labels' => $labels,
        ];
    }

    return $rows;
}

function exam_document_view_data(array $exam, array $questions, array $questionOptions): array
{
    $parsed = exam_parse_stored_instructions((string) ($exam['instructions'] ?? ''));
    $metadata = array_replace(exam_default_metadata(), $parsed['metadata']);
    $defaultHeaderTitle = function_exists('mb_strtoupper') ? mb_strtoupper((string) $exam['title']) : strtoupper((string) $exam['title']);
    $schoolName = $metadata['school_name'] !== '' ? (string) $metadata['school_name'] : 'COLEGIO / ESCOLA';
    $disciplineLabel = $metadata['discipline'] !== '' ? (string) $metadata['discipline'] : 'ENSINO FUNDAMENTAL, MEDIO E PROFISSIONALIZANTE';
    $headerTitle = $metadata['exam_label'] !== '' ? (string) $metadata['exam_label'] : $defaultHeaderTitle;
    $questionColumns = exam_document_split_questions($questions, $questionOptions);
    $answerSheetRows = exam_document_answer_sheet_rows($questions, $questionOptions);

    return [
        'exam' => $exam,
        'questions' => $questions,
        'question_options' => $questionOptions,
        'question_columns' => $questionColumns,
        'answer_sheet_rows' => $answerSheetRows,
        'parsed' => $parsed,
        'metadata' => $metadata,
        'metadata_summary' => exam_metadata_summary($parsed['metadata']),
        'template' => (string) $metadata['exam_template'],
        'style' => (string) $metadata['exam_style'],
        'school_name' => $schoolName,
        'discipline_label' => $disciplineLabel,
        'header_title' => $headerTitle,
        'page_count' => $answerSheetRows === [] ? 1 : 2,
    ];
}

function exam_document_styles(bool $forPdf = false): string
{
    $sheetWidth = $forPdf ? '100%' : 'min(100%, 1120px)';
    $bodyBackground = $forPdf ? '#ffffff' : '#efe8df';
    $fontFamily = $forPdf ? "'DejaVu Sans', Arial, sans-serif" : "'Poppins', Arial, sans-serif";

    return <<<CSS
body {
    margin: 0;
    color: #1f1f1f;
    font-family: {$fontFamily};
    background: {$bodyBackground};
}

.exam-document-stack {
    display: grid;
    gap: 28px;
    width: {$sheetWidth};
    margin: 0 auto;
}

.exam-page {
    background: #ffffff;
    border: 1px solid rgba(36, 52, 71, 0.12);
    border-radius: 12px;
    box-shadow: 0 12px 32px rgba(36, 52, 71, 0.08);
    overflow: hidden;
}

.exam-page-inner {
    min-height: 1122px;
    padding: 18px 18px 22px;
    box-sizing: border-box;
}

.exam-page-number {
    display: flex;
    justify-content: flex-end;
    gap: 18px;
    margin-bottom: 10px;
    font-size: 12px;
    color: #66768a;
}

.exam-header {
    border-bottom: 1px solid rgba(36, 52, 71, 0.12);
    padding-bottom: 12px;
}

.exam-header-school {
    display: grid;
    grid-template-columns: 84px minmax(0, 1fr) 72px;
    align-items: center;
    gap: 10px;
}

.exam-school-badge {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    border: 2px solid rgba(47, 93, 124, 0.2);
    color: #2f5d7c;
    font-weight: 800;
    font-size: 14px;
    background: #f7fafc;
}

.exam-school-copy {
    text-align: center;
}

.exam-school-copy strong,
.exam-school-copy small {
    display: block;
    text-transform: uppercase;
}

.exam-school-copy strong {
    font-size: 16px;
    line-height: 1.25;
    color: #243447;
    letter-spacing: 0.02em;
}

.exam-school-copy small {
    margin-top: 2px;
    font-size: 12px;
    line-height: 1.25;
    color: #66768a;
    font-weight: 700;
}

.exam-header-grade {
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    color: #243447;
}

.exam-header-grid {
    width: 100%;
    margin-top: 10px;
    border-collapse: collapse;
}

.exam-header-grid td {
    padding: 4px 6px;
    border: 1px solid rgba(36, 52, 71, 0.18);
    font-size: 12px;
    color: #243447;
}

.exam-header-grid .exam-header-title {
    width: 26%;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    background: #f8fafc;
}

.exam-instructions {
    margin-top: 10px;
    font-size: 12px;
    line-height: 1.45;
    color: #425466;
}

.exam-instructions p {
    margin: 0;
}

.exam-columns {
    width: 100%;
    margin-top: 18px;
    border-collapse: separate;
    border-spacing: 16px 0;
    table-layout: fixed;
}

.exam-column {
    width: 50%;
    vertical-align: top;
}

.exam-question {
    margin-bottom: 16px;
    break-inside: avoid;
}

.exam-question:last-child {
    margin-bottom: 0;
}

.exam-question h4 {
    margin: 0 0 6px;
    font-size: 15px;
    line-height: 1.35;
    color: #243447;
}

.exam-question p {
    margin: 0 0 6px;
    font-size: 13px;
    line-height: 1.45;
    text-align: justify;
    color: #243447;
}

.exam-option-list {
    margin: 0;
    padding: 0;
    list-style: none;
}

.exam-option-list li {
    margin: 0 0 4px;
    font-size: 13px;
    line-height: 1.35;
}

.exam-answer-bullet {
    display: inline-block;
    min-width: 36px;
}

.exam-discursive-line {
    height: 22px;
    border-bottom: 1px solid rgba(36, 52, 71, 0.42);
}

.exam-drawing-box {
    min-height: 180px;
    border: 1px solid rgba(36, 52, 71, 0.24);
    border-radius: 10px;
    background: #fbfcfd;
}

.exam-footer {
    margin-top: 18px;
    text-align: center;
    font-size: 11px;
    color: #66768a;
}

.exam-answer-sheet-title {
    margin: 22px 0 18px;
    text-align: center;
    font-size: 17px;
    line-height: 1.4;
    font-weight: 700;
}

.exam-answer-sheet-grid {
    width: auto;
    margin: 0 auto;
    border-collapse: separate;
    border-spacing: 14px 8px;
}

.exam-answer-sheet-grid td {
    font-size: 14px;
    vertical-align: middle;
    white-space: nowrap;
}

.exam-answer-sheet-number {
    width: 54px;
    font-weight: 700;
}

.exam-answer-sheet-option {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border: 1px solid rgba(36, 52, 71, 0.44);
    border-radius: 50%;
    margin-left: 6px;
    font-size: 12px;
    text-transform: lowercase;
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

@media print {
    body {
        background: #ffffff;
    }

    .exam-page {
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }
}
CSS;
}

function exam_document_render_question(array $question, array $options, int $displayNumber): string
{
    ob_start();
    ?>
<article class="exam-question">
    <h4>Q.<?= exam_document_escape((string) $displayNumber) ?> (1.00) - <?= exam_document_escape((string) $question['title']) ?></h4>
    <p><?= nl2br(exam_document_escape((string) $question['prompt'])) ?></p>

    <?php if (($question['question_type'] ?? '') === 'multiple_choice'): ?>
        <ul class="exam-option-list">
            <?php foreach ($options as $index => $option): ?>
                <li>
                    <span class="exam-answer-bullet"><?= exam_document_escape(option_label((int) $index)) ?>) ( )</span>
                    <?= exam_document_escape((string) $option['option_text']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php elseif (($question['question_type'] ?? '') === 'true_false'): ?>
        <ul class="exam-option-list">
            <li><span class="exam-answer-bullet">a) ( )</span> Verdadeiro</li>
            <li><span class="exam-answer-bullet">b) ( )</span> Falso</li>
        </ul>
    <?php elseif (($question['question_type'] ?? '') === 'discursive'): ?>
        <?php for ($i = 0; $i < max(3, min(12, (int) ($question['response_lines'] ?? 5))); $i++): ?>
            <div class="exam-discursive-line"></div>
        <?php endfor; ?>
    <?php else: ?>
        <div class="exam-drawing-box"></div>
    <?php endif; ?>
</article>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_header(array $document, int $pageNumber): string
{
    $metadata = $document['metadata'];
    $instructions = trim((string) $document['parsed']['instructions']);
    $course = trim((string) ($metadata['discipline'] !== '' ? $metadata['discipline'] : $metadata['component_name']));
    $code = trim((string) (($document['exam']['id'] ?? '') !== '' ? $document['exam']['id'] : $document['exam']['title']));

    ob_start();
    ?>
<div class="exam-page-number">
    <span>Pagina <?= exam_document_escape((string) $pageNumber) ?> de <?= exam_document_escape((string) $document['page_count']) ?></span>
    <span><?= exam_document_escape($course !== '' ? $course : (string) $document['exam']['title']) ?></span>
</div>

<header class="exam-header">
    <div class="exam-header-school">
        <div class="exam-school-badge">ESC</div>
        <div class="exam-school-copy">
            <strong><?= exam_document_escape((string) $document['school_name']) ?></strong>
            <small><?= exam_document_escape((string) $document['discipline_label']) ?></small>
        </div>
        <div class="exam-header-grade">Nota</div>
    </div>

    <table class="exam-header-grid" role="presentation">
        <tr>
            <td class="exam-header-title"><?= exam_document_escape((string) $document['header_title']) ?></td>
            <td>Prof.: <?= exam_document_escape((string) $metadata['teacher_name']) ?></td>
            <td>Comp. Curricular: <?= exam_document_escape((string) $metadata['component_name']) ?></td>
        </tr>
        <tr>
            <td colspan="2">Aluno(a): </td>
            <td>Turma: <?= exam_document_escape((string) $metadata['class_name']) ?></td>
        </tr>
        <tr>
            <td>Data: <?= exam_document_escape(exam_format_date((string) $metadata['application_date'])) ?></td>
            <td>Curso: <?= exam_document_escape($course) ?></td>
            <td>Codigo: <?= exam_document_escape($code) ?></td>
        </tr>
    </table>

    <div class="exam-instructions">
        <p>
            <?= exam_document_escape($instructions !== '' ? $instructions : 'Use caneta azul ou preta. Preencha nome completo, numero e turma. Questoes com mais de uma alternativa serao anuladas.') ?>
        </p>
    </div>
</header>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_question_page(array $document): string
{
    $columns = $document['question_columns'];
    $questionOptions = $document['question_options'];
    $questionNumbers = [];
    $number = 1;

    foreach ($columns as $columnIndex => $columnQuestions) {
        foreach ($columnQuestions as $question) {
            $questionNumbers[$columnIndex . '-' . (int) $question['id']] = $number;
            $number++;
        }
    }

    ob_start();
    ?>
<section class="exam-page">
    <div class="exam-page-inner">
        <?= exam_document_render_header($document, 1) ?>

        <table class="exam-columns" role="presentation">
            <tr>
                <?php foreach ($columns as $columnIndex => $columnQuestions): ?>
                    <td class="exam-column">
                        <?php foreach ($columnQuestions as $question): ?>
                            <?= exam_document_render_question(
                                $question,
                                $questionOptions[(int) $question['id']] ?? [],
                                $questionNumbers[$columnIndex . '-' . (int) $question['id']]
                            ) ?>
                        <?php endforeach; ?>
                    </td>
                <?php endforeach; ?>
            </tr>
        </table>

        <div class="exam-footer">
            <?= exam_document_escape((string) $document['school_name']) ?> | <?= exam_document_escape((string) $document['exam']['title']) ?>
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

    $rows = $document['answer_sheet_rows'];
    $leftRows = array_slice($rows, 0, (int) ceil(count($rows) / 2));
    $rightRows = array_slice($rows, count($leftRows));

    ob_start();
    ?>
<section class="exam-page">
    <div class="exam-page-inner">
        <?= exam_document_render_header($document, 2) ?>

        <div class="exam-answer-sheet-title">
            Marque o gabarito preenchendo completamente a regiao de cada alternativa.
        </div>

        <table class="exam-columns" role="presentation">
            <tr>
                <?php foreach ([$leftRows, $rightRows] as $columnRows): ?>
                    <td class="exam-column">
                        <table class="exam-answer-sheet-grid" role="presentation">
                            <?php foreach ($columnRows as $row): ?>
                                <tr>
                                    <td class="exam-answer-sheet-number">Q.<?= exam_document_escape(str_pad((string) $row['number'], 1, '0', STR_PAD_LEFT)) ?>:</td>
                                    <td>
                                        <?php foreach ($row['labels'] as $label): ?>
                                            <span class="exam-answer-sheet-option"><?= exam_document_escape(mb_strtolower($label)) ?></span>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                <?php endforeach; ?>
            </tr>
        </table>

        <div class="exam-footer">
            <?= exam_document_escape((string) $document['school_name']) ?> | Folha de gabarito
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
        ['label' => 'Colegio', 'value' => (string) $document['school_name']],
        ['label' => 'Professor', 'value' => (string) $metadata['teacher_name']],
        ['label' => 'Turma', 'value' => (string) $metadata['class_name']],
        ['label' => 'Questoes', 'value' => (string) count($document['questions'])],
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
        <h3>Checklist de revisao</h3>
        <ul>
            <li>Confirme o nome do colegio e do professor antes de exportar.</li>
            <li>Revise se a prova permaneceu em duas colunas na pagina principal.</li>
            <li>Valide a folha de gabarito quando houver questoes objetivas.</li>
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
    <title>{$title}</title>
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        {$styles}
    </style>
</head>
<body>
    {$sheet}
</body>
</html>
HTML;
}
