<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_metadata.php';
require_once __DIR__ . '/includes/pdf_builder.php';
require_login();

$user = current_user();
$userId = (int) $user['id'];
$examId = (int) ($_GET['id'] ?? 0);
$exam = $examId > 0 ? exam_find($examId, $userId) : null;

if (!$exam) {
    flash('error', 'Prova nao encontrada.');
    redirect('exams.php');
}

[$questions, $questionOptions] = exam_questions($examId, $userId);
$parsed = exam_parse_stored_instructions((string) ($exam['instructions'] ?? ''));
$metadata = array_replace(exam_default_metadata(), $parsed['metadata']);
$styleLabel = exam_style_label((string) $metadata['exam_style']);

if ($questions === []) {
    flash('error', 'Essa prova ainda nao possui questoes.');
    redirect('exams.php');
}

$lines = [];
$lines = array_merge($lines, pdf_wrap_text('Quest - ' . $exam['title'], 80), ['']);
$lines = array_merge($lines, pdf_wrap_text('Formato: ' . $styleLabel, 90));
$lines = array_merge($lines, pdf_wrap_text('Escola: ' . ($metadata['school_name'] !== '' ? $metadata['school_name'] : 'Quest'), 90));
$lines = array_merge($lines, pdf_wrap_text('Professor: ' . ($metadata['teacher_name'] !== '' ? $metadata['teacher_name'] : '________________'), 90));
$lines = array_merge($lines, pdf_wrap_text('Comp. Curricular: ' . ($metadata['component_name'] !== '' ? $metadata['component_name'] : '________________'), 90));
$lines = array_merge($lines, pdf_wrap_text('Aluno(a): ____________________  Ano: ' . ($metadata['year_reference'] !== '' ? $metadata['year_reference'] : '________') . '  Turma: ' . ($metadata['class_name'] !== '' ? $metadata['class_name'] : '________'), 90));
$lines = array_merge($lines, pdf_wrap_text('Data: ' . ($metadata['application_date'] !== '' ? exam_format_date((string) $metadata['application_date']) : '____/____/______') . '  Assin. Resp.: ____________________  Valor obtido: ______', 90), ['']);

if ($parsed['instructions'] !== '') {
    $lines = array_merge($lines, pdf_wrap_text('Instrucoes: ' . $parsed['instructions'], 90), ['']);
}

foreach ($questions as $index => $question) {
    $number = $index + 1;
    $header = sprintf(
        '%d. %s [%s | %s]',
        $number,
        (string) $question['title'],
        (string) ($question['discipline_name'] ?? 'Sem disciplina'),
        education_level_label((string) $question['education_level'])
    );
    $lines = array_merge($lines, pdf_wrap_text($header, 92));
    $lines = array_merge($lines, pdf_wrap_text((string) $question['prompt'], 92));

    if ($question['question_type'] === 'multiple_choice') {
        foreach ($questionOptions[(int) $question['id']] ?? [] as $optionIndex => $option) {
            $lines = array_merge(
                $lines,
                pdf_wrap_text(option_label($optionIndex) . ') ' . (string) $option['option_text'], 88)
            );
        }
    } elseif ($question['question_type'] === 'true_false') {
        $lines[] = '( ) Verdadeiro    ( ) Falso';
    } elseif ($question['question_type'] === 'discursive') {
        $totalLines = max(3, min(15, (int) ($question['response_lines'] ?? 5)));
        for ($i = 0; $i < $totalLines; $i++) {
            $lines[] = '_______________________________________________________________';
        }
    } else {
        $drawingSize = (string) ($question['drawing_size'] ?? 'medium');
        $drawingHeight = (int) ($question['drawing_height_px'] ?? 0);
        $blankLines = match ($drawingSize) {
            'small' => 5,
            'large' => 12,
            'custom' => max(5, min(18, (int) ceil($drawingHeight / 60))),
            default => 8,
        };

        $lines[] = '[Espaco para desenho / resolucao]';
        for ($i = 0; $i < $blankLines; $i++) {
            $lines[] = '';
        }
    }

    $lines[] = '';
}

$pages = pdf_paginate_lines($lines);
$pdf = pdf_build_document($pages, (string) $exam['title']);
$filename = 'prova-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) $exam['title'])) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
exit;
