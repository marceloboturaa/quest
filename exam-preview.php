<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_document_renderer.php';

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
$document = exam_document_view_data($exam, $questions, $questionOptions);
$metadataSummary = $document['metadata_summary'];

render_header(
    'Visualizacao da prova',
    'Confira o cabecalho, a ordem das questoes e o formato de impressao antes de exportar.'
);
?>

<style>
<?= exam_document_styles(false) ?>
</style>

<section class="split-card">
    <section class="panel">
        <div class="workspace-panel-head">
            <div>
                <p class="workspace-kicker">Preview</p>
                <h2><?= h((string) $exam['title']) ?></h2>
            </div>
            <div class="form-actions">
                <a class="ghost-button" href="exams.php">Voltar</a>
                <a class="button-secondary" href="exam-pdf.php?id=<?= h((string) $exam['id']) ?>">Abrir PDF</a>
            </div>
        </div>

        <?= exam_document_render_sheet($document) ?>
    </section>

    <aside class="panel">
        <h2>Checklist</h2>
        <ul class="mini-list">
            <li>Confira se o cabecalho esta completo.</li>
            <li>Revise a ordem das questoes antes do PDF.</li>
            <li>Use o PDF para impressao e distribuicao.</li>
        </ul>
    </aside>
</section>

<?php render_footer(); ?>
