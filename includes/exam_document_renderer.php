<?php
declare(strict_types=1);

require_once __DIR__ . '/exam_metadata.php';

function exam_document_view_data(array $exam, array $questions, array $questionOptions): array
{
    $parsed = exam_parse_stored_instructions((string) ($exam['instructions'] ?? ''));
    $metadata = array_replace(exam_default_metadata(), $parsed['metadata']);
    $defaultHeaderTitle = function_exists('mb_strtoupper') ? mb_strtoupper((string) $exam['title']) : strtoupper((string) $exam['title']);
    $schoolName = $metadata['school_name'] !== '' ? (string) $metadata['school_name'] : 'QUEST - PLATAFORMA DE AVALIACOES';
    $disciplineLabel = $metadata['discipline'] !== '' ? (string) $metadata['discipline'] : 'ENSINO FUNDAMENTAL, MEDIO E PROFISSIONALIZANTE';
    $headerTitle = $metadata['exam_label'] !== '' ? (string) $metadata['exam_label'] : $defaultHeaderTitle;
    $badgeSeed = preg_replace('/[^A-Z]/', '', function_exists('mb_strtoupper') ? mb_strtoupper($schoolName) : strtoupper($schoolName));
    $badgeText = substr($badgeSeed !== '' ? $badgeSeed : 'QP', 0, 4);

    return [
        'exam' => $exam,
        'questions' => $questions,
        'question_options' => $questionOptions,
        'parsed' => $parsed,
        'metadata' => $metadata,
        'metadata_summary' => exam_metadata_summary($parsed['metadata']),
        'template' => (string) $metadata['exam_template'],
        'style' => (string) $metadata['exam_style'],
        'school_name' => $schoolName,
        'discipline_label' => $disciplineLabel,
        'header_title' => $headerTitle,
        'badge_text' => substr($badgeText !== '' ? $badgeText : 'QP', 0, 4),
        'response_box_total' => max(10, min(20, count($questions) > 0 ? count($questions) : 15)),
        'objective_strip_total' => max(10, min(20, count($questions) > 0 ? count($questions) : 10)),
    ];
}

function exam_document_escape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function exam_document_styles(bool $forPdf = false): string
{
    $sheetWidth = $forPdf ? '100%' : 'min(100%, 940px)';
    $fontFamily = $forPdf ? "'DejaVu Sans', Arial, sans-serif" : "'Segoe UI', Arial, sans-serif";

    return <<<CSS
body {
    margin: 0;
    color: #2a2a2a;
    font-family: {$fontFamily};
    background: #f2ede6;
}

.exam-preview-sheet {
    display: grid;
    gap: 22px;
    width: {$sheetWidth};
    padding: 20px;
    border-radius: 24px;
    background: #f2ede6;
    border: 1px solid rgba(87, 64, 45, 0.12);
    box-sizing: border-box;
}

.exam-preview-frame {
    min-height: 1122px;
    padding: 18px;
    border-radius: 18px;
    background: #fff;
    border: 1px solid rgba(111, 107, 103, 0.35);
    box-shadow: inset 0 0 0 1px rgba(111, 107, 103, 0.12);
    box-sizing: border-box;
}

.exam-paper-header {
    display: block;
    padding-bottom: 12px;
}

.exam-paper-brand {
    display: table;
    width: 100%;
    margin-bottom: 14px;
}

.exam-paper-brand-badge,
.exam-paper-brand-copy {
    display: table-cell;
    vertical-align: middle;
}

.exam-paper-brand-badge {
    width: 88px;
}

.exam-paper-logo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 72px;
    height: 72px;
    border-radius: 50%;
    border: 2px solid rgba(52, 78, 117, 0.22);
    font-size: 14px;
    font-weight: 700;
    color: #344e75;
    background: radial-gradient(circle at top left, #ffffff, #edf1f6);
}

.exam-paper-brand-copy {
    text-align: center;
}

.exam-paper-brand-copy strong,
.exam-paper-brand-copy small {
    display: block;
    color: #676767;
}

.exam-paper-brand-copy strong {
    font-size: 20px;
    line-height: 1.2;
    font-weight: 800;
    text-transform: uppercase;
    color: #707070;
}

.exam-paper-brand-copy small {
    margin-top: 2px;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.exam-paper-grid {
    width: 100%;
    border-collapse: separate;
    border-spacing: 4px;
}

.exam-paper-grid td {
    min-height: 38px;
    padding: 4px 8px;
    border: 1px solid rgba(118, 118, 118, 0.5);
    border-radius: 4px;
    background: #fff;
    font-size: 12px;
    color: #767676;
}

.exam-paper-grid .exam-paper-cell-title {
    font-weight: 800;
    letter-spacing: 0.04em;
    background: #fafafa;
    text-transform: uppercase;
    color: #808080;
}

.exam-paper-grid strong {
    color: #4d4d4d;
    font-weight: 600;
}

.exam-response-strip {
    margin-top: 14px;
    padding: 12px 14px;
    border-radius: 8px;
    border: 1px solid rgba(118, 118, 118, 0.4);
    background: #fbfbfb;
}

.exam-response-strip p,
.exam-response-strip small {
    margin: 0;
    color: #272727;
}

.exam-response-strip-version-2 p {
    font-size: 13px;
}

.exam-response-strip-version-2 small {
    display: block;
    margin-top: 8px;
    font-size: 13px;
    font-weight: 700;
}

.exam-response-boxes {
    width: 100%;
    margin-top: 10px;
    border-collapse: separate;
    border-spacing: 8px 0;
}

.exam-response-boxes td {
    width: 52px;
    vertical-align: top;
}

.exam-response-box-label {
    display: block;
    height: 28px;
    line-height: 28px;
    text-align: center;
    border-radius: 4px 4px 0 0;
    background: #ececec;
    color: #1d1d1d;
    font-weight: 800;
    font-size: 13px;
}

.exam-response-box-slot {
    display: block;
    min-height: 38px;
    border: 2px solid rgba(36, 36, 36, 0.7);
    border-radius: 4px;
    background: #fff;
}

.exam-response-strip-version-3 {
    background: transparent;
    border: 0;
    padding: 0;
}

.exam-bubble-grid {
    width: 100%;
    border-collapse: separate;
    border-spacing: 12px 10px;
}

.exam-bubble-grid td {
    white-space: nowrap;
}

.exam-bubble-number,
.exam-bubble-option {
    display: inline-block;
    width: 28px;
    height: 28px;
    line-height: 28px;
    text-align: center;
    border-radius: 50%;
    font-weight: 800;
    font-size: 12px;
}

.exam-bubble-number {
    width: 36px;
    border-radius: 12px;
    background: #324f79;
    color: #fff;
    border: 2px solid #6c8235;
}

.exam-bubble-option {
    margin-left: 6px;
    background: #fff;
    border: 2px solid rgba(29, 29, 29, 0.8);
    color: #1d1d1d;
}

.exam-preview-instructions {
    margin-top: 14px;
    padding: 14px 16px;
    border-radius: 10px;
    background: rgba(234, 232, 228, 0.65);
    border: 1px solid rgba(118, 118, 118, 0.26);
}

.exam-preview-instructions p {
    margin: 10px 0 0;
    white-space: pre-line;
}

.exam-preview-questions {
    margin-top: 18px;
}

.exam-preview-questions-double_column {
    column-count: 2;
    column-gap: 24px;
}

.exam-preview-questions-single_column,
.exam-preview-questions-accessibility {
    column-count: 1;
}

.exam-preview-sheet-accessibility {
    font-size: 15px;
}

.exam-preview-sheet-accessibility .exam-preview-question h4 {
    font-size: 18px;
}

.exam-preview-sheet-economic .exam-preview-question {
    padding-bottom: 12px;
}

.exam-preview-question {
    break-inside: avoid;
    margin: 0 0 18px;
    padding-bottom: 18px;
    border-bottom: 1px dashed rgba(87, 64, 45, 0.18);
}

.exam-preview-question h4 {
    margin: 0 0 10px;
    color: #4f331d;
    font-size: 16px;
}

.exam-preview-question p {
    margin: 0 0 10px;
}

.option-list {
    margin: 0;
    padding-left: 18px;
}

.option-list li {
    margin-bottom: 6px;
}

.exam-preview-line {
    height: 24px;
    border-bottom: 1px solid rgba(87, 64, 45, 0.32);
}

.exam-preview-drawing-space {
    min-height: 180px;
    border: 1px dashed rgba(87, 64, 45, 0.32);
    border-radius: 18px;
    background: linear-gradient(180deg, rgba(158, 124, 94, 0.05), rgba(158, 124, 94, 0.02));
}
CSS;
}

function exam_document_render_sheet(array $document): string
{
    $metadata = $document['metadata'];
    $questions = $document['questions'];
    $questionOptions = $document['question_options'];
    $template = $document['template'];
    $style = $document['style'];

    ob_start();
    ?>
<article class="exam-preview-sheet exam-preview-sheet-<?= exam_document_escape($style) ?> exam-preview-template-<?= exam_document_escape($template) ?>">
    <div class="exam-preview-frame">
        <header class="exam-paper-header">
            <div class="exam-paper-brand">
                <div class="exam-paper-brand-badge">
                    <div class="exam-paper-logo"><?= exam_document_escape((string) $document['badge_text']) ?></div>
                </div>
                <div class="exam-paper-brand-copy">
                    <strong><?= exam_document_escape((string) $document['school_name']) ?></strong>
                    <small><?= exam_document_escape((string) $document['discipline_label']) ?></small>
                </div>
            </div>

            <table class="exam-paper-grid" role="presentation">
                <tr>
                    <td class="exam-paper-cell-title"><?= exam_document_escape((string) $document['header_title']) ?></td>
                    <td><span>Prof.:</span> <strong><?= exam_document_escape((string) $metadata['teacher_name']) ?></strong></td>
                    <td colspan="2"><span>Comp. Curricular:</span> <strong><?= exam_document_escape((string) $metadata['component_name']) ?></strong></td>
                </tr>
                <tr>
                    <td colspan="2"><span>Aluno(a):</span> <strong>&nbsp;</strong></td>
                    <td><span>Nº</span> <strong>&nbsp;</strong></td>
                    <td><span>Turma:</span> <strong><?= exam_document_escape((string) $metadata['class_name']) ?></strong></td>
                </tr>
                <tr>
                    <td><span>Data:</span> <strong><?= exam_document_escape(exam_format_date((string) $metadata['application_date'])) ?></strong></td>
                    <td colspan="2"><span>Assin. Resp. (a):</span> <strong>&nbsp;</strong></td>
                    <td><span>Valor obtido:</span> <strong>&nbsp;</strong></td>
                </tr>
            </table>
        </header>

        <?php if ($template === 'version_2'): ?>
            <section class="exam-response-strip exam-response-strip-version-2">
                <p><strong>Orientacoes:</strong> Somente o seu gabarito sera corrigido. Escreva com letra legivel e sem rasuras.</p>
                <table class="exam-response-boxes" role="presentation">
                    <tr>
                        <?php for ($i = 1; $i <= (int) $document['response_box_total']; $i++): ?>
                            <td>
                                <span class="exam-response-box-label"><?= exam_document_escape(str_pad((string) $i, 2, '0', STR_PAD_LEFT)) ?></span>
                                <span class="exam-response-box-slot"></span>
                            </td>
                        <?php endfor; ?>
                    </tr>
                </table>
                <small>Use caneta azul ou preta.</small>
            </section>
        <?php elseif ($template === 'version_3_1'): ?>
            <section class="exam-response-strip exam-response-strip-version-3">
                <table class="exam-bubble-grid" role="presentation">
                    <tr>
                        <?php for ($i = 1; $i <= (int) $document['objective_strip_total']; $i++): ?>
                            <td>
                                <span class="exam-bubble-number"><?= exam_document_escape(str_pad((string) $i, 2, '0', STR_PAD_LEFT)) ?></span>
                                <?php foreach (['A', 'B', 'C', 'D', 'E'] as $letter): ?>
                                    <span class="exam-bubble-option"><?= exam_document_escape($letter) ?></span>
                                <?php endforeach; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                </table>
            </section>
        <?php endif; ?>

        <?php if ((string) $document['parsed']['instructions'] !== ''): ?>
            <section class="exam-preview-instructions">
                <strong>Instrucoes</strong>
                <p><?= exam_document_escape((string) $document['parsed']['instructions']) ?></p>
            </section>
        <?php endif; ?>

        <section class="exam-preview-questions exam-preview-questions-<?= exam_document_escape($style) ?>">
            <?php foreach ($questions as $index => $question): ?>
                <article class="exam-preview-question">
                    <h4><?= exam_document_escape((string) ($index + 1)) ?>. <?= exam_document_escape((string) $question['title']) ?></h4>
                    <p><?= nl2br(exam_document_escape((string) $question['prompt'])) ?></p>

                    <?php if ($question['question_type'] === 'multiple_choice'): ?>
                        <ul class="option-list">
                            <?php foreach ($questionOptions[(int) $question['id']] ?? [] as $optionIndex => $option): ?>
                                <li><?= exam_document_escape(option_label($optionIndex) . ') ' . (string) $option['option_text']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php elseif ($question['question_type'] === 'true_false'): ?>
                        <p>( ) Verdadeiro &nbsp;&nbsp;&nbsp; ( ) Falso</p>
                    <?php elseif ($question['question_type'] === 'discursive'): ?>
                        <?php for ($i = 0; $i < max(3, min(12, (int) ($question['response_lines'] ?? 5))); $i++): ?>
                            <div class="exam-preview-line"></div>
                        <?php endfor; ?>
                    <?php else: ?>
                        <div class="exam-preview-drawing-space"></div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </div>
</article>
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
        @page { margin: 10mm 8mm 12mm; }
        {$styles}
    </style>
</head>
<body>
    {$sheet}
</body>
</html>
HTML;
}
