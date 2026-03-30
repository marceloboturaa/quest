<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_browser_pdf.php';
require_once __DIR__ . '/includes/exam_document_renderer.php';
require_once __DIR__ . '/includes/xerox_repository.php';

use Dompdf\Dompdf;
use Dompdf\Options;

require_login();

$user = current_user();
$examId = (int) ($_GET['id'] ?? 0);
$exam = $examId > 0 ? exam_find_accessible($examId, $user) : null;

if (!$exam) {
    flash('error', 'Prova não encontrada.');
    redirect(can_view_xerox_queue() ? 'xerox.php' : 'exams.php');
}

[$questions, $questionOptions] = exam_questions_for_view($examId);

if ($questions === []) {
    flash('error', 'Esta prova ainda não possui questões.');
    redirect(can_view_xerox_queue() ? 'xerox.php' : 'exams.php');
}

$document = exam_document_view_data($exam, $questions, $questionOptions);
$previewStyle = trim((string) ($_GET['preview_style'] ?? $document['style']));
$previewPaperSize = exam_document_resolve_paper_size((string) ($_GET['preview_paper_size'] ?? $document['paper_size']));

if (array_key_exists($previewStyle, exam_style_options())) {
    $document['style'] = $previewStyle;
    $document['metadata']['exam_style'] = $previewStyle;
}

$document['paper_size'] = $previewPaperSize;
$html = exam_document_render_html($document);
$browserPdf = null;

try {
    $browserPdf = exam_pdf_render_with_browser($html);
} catch (Throwable $exception) {
    error_log('Quest PDF browser render failed: ' . $exception->getMessage());
    $browserPdf = null;
}

if ($browserPdf !== null) {
    $filename = 'prova-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) $exam['title'])) . '.pdf';

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('X-Quest-Pdf-Engine: chrome');
    echo $browserPdf;
    exit;
}

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'DejaVu Sans');

try {
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper(exam_document_paper_dimensions($document['paper_size'])['dompdf'], 'portrait');
    $dompdf->render();
} catch (Throwable $exception) {
    error_log('Quest PDF dompdf render failed for exam ' . $examId . ': ' . $exception->getMessage());
    flash('error', 'Não foi possível gerar o PDF desta prova agora. Tente novamente.');
    redirect('exam-preview.php?id=' . $examId);
}

$filename = 'prova-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) $exam['title'])) . '.pdf';

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $filename . '"');
header('X-Quest-Pdf-Engine: dompdf');
echo $dompdf->output();
exit;
