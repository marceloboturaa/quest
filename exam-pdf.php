<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_document_renderer.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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

if ($questions === []) {
    flash('error', 'Essa prova ainda nao possui questoes.');
    redirect('exams.php');
}

$document = exam_document_view_data($exam, $questions, $questionOptions);
$html = exam_document_render_html($document);

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = 'prova-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) $exam['title'])) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
echo $dompdf->output();
exit;
