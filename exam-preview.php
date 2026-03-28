<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_document_renderer.php';
require_once __DIR__ . '/includes/xerox_repository.php';

require_login();

$user = current_user();
$examId = (int) ($_GET['id'] ?? 0);

if (is_post()) {
    abort_if_invalid_csrf();

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'send_to_xerox') {
        if (!xerox_is_available()) {
            flash('error', 'Nenhum usuario Xerox esta autorizado no momento.');
            redirect('exam-preview.php?id=' . $examId);
        }

        if (!xerox_submit_exam($examId, (int) $user['id'])) {
            flash('error', 'Nao foi possivel encaminhar essa prova para o setor Xerox.');
            redirect('exam-preview.php?id=' . $examId);
        }

        flash('success', 'Prova encaminhada para o setor Xerox.');
        redirect('exam-preview.php?id=' . $examId);
    }

    if ($action === 'cancel_xerox') {
        if (!xerox_cancel_exam($examId, (int) $user['id'])) {
            flash('error', 'Nao foi possivel cancelar o envio. A prova pode ja estar em andamento.');
            redirect('exam-preview.php?id=' . $examId);
        }

        flash('success', 'Envio para o setor Xerox cancelado.');
        redirect('exam-preview.php?id=' . $examId);
    }

    if ($action === 'resend_to_xerox') {
        if (!xerox_is_available()) {
            flash('error', 'Nenhum usuario Xerox esta autorizado no momento.');
            redirect('exam-preview.php?id=' . $examId);
        }

        if (!xerox_resend_exam($examId, (int) $user['id'])) {
            flash('error', 'Nao foi possivel reenviar essa prova para o setor Xerox.');
            redirect('exam-preview.php?id=' . $examId);
        }

        flash('success', 'Prova reenviada para o setor Xerox.');
        redirect('exam-preview.php?id=' . $examId);
    }

    if ($action === 'delete_exam') {
        if (!exam_delete($examId, (int) $user['id'])) {
            flash('error', 'Nao foi possivel excluir essa prova.');
            redirect('exam-preview.php?id=' . $examId);
        }

        flash('success', 'Prova excluida com sucesso.');
        redirect('dashboard.php');
    }
}

$exam = $examId > 0 ? exam_find_accessible($examId, $user) : null;

if (!$exam) {
    flash('error', 'Prova nao encontrada.');
    redirect(can_view_xerox_queue() ? 'xerox.php' : 'exams.php');
}

[$questions, $questionOptions] = exam_questions_for_view($examId);
$document = exam_document_view_data($exam, $questions, $questionOptions);
$metadataSummary = array_values(array_filter(
    $document['metadata_summary'],
    static fn(array $item): bool => in_array((string) ($item['label'] ?? ''), ['Tipo', 'Professor', 'Turma', 'Data', 'Versão'], true)
));
$xeroxStatus = (string) ($exam['xerox_status'] ?? 'not_sent');
$isExamOwner = (int) $exam['user_id'] === (int) $user['id'];
$xeroxButtonLabel = $xeroxStatus === 'sent'
    ? 'Encaminhado'
    : ($xeroxStatus === 'in_progress' ? 'Em andamento' : ($xeroxStatus === 'finished' ? 'Finalizado' : 'Xerox'));

render_header(
    'Visualizacao da prova',
    'Confira cabecalho, corpo, rodape e a ordem das questoes antes de exportar.'
);
?>

<style>
<?= exam_document_styles(false) ?>
</style>

<section class="panel">
    <div class="workspace-panel-head">
        <div>
            <p class="workspace-kicker">Preview</p>
            <h2><?= h((string) $exam['title']) ?></h2>
        </div>
        <div class="form-actions">
            <?php if ($isExamOwner): ?>
                <a class="ghost-button" href="exam-create.php?edit=<?= h((string) $exam['id']) ?>#dados-basicos">Editar dados</a>
                <a class="ghost-button" href="exam-create.php?edit=<?= h((string) $exam['id']) ?>#header-section">Cabeçalho</a>
                <a class="ghost-button" href="exam-create.php?edit=<?= h((string) $exam['id']) ?>#body-section">Corpo</a>
                <a class="ghost-button" href="exam-create.php?edit=<?= h((string) $exam['id']) ?>#footer-section">Rodapé</a>
                <a class="ghost-button" href="exams.php?exam_id=<?= h((string) $exam['id']) ?>">Questões</a>
            <?php endif; ?>
            <a class="ghost-button" href="<?= $isExamOwner ? 'exams.php' : 'xerox.php' ?>">Voltar</a>
            <a class="button-secondary" href="exam-pdf.php?id=<?= h((string) $exam['id']) ?>">Abrir PDF</a>
            <?php if ($isExamOwner): ?>
                <?php if ($xeroxStatus === 'not_sent'): ?>
                    <form method="post" class="inline-actions">
                        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="action" value="send_to_xerox">
                        <button class="button" type="submit" <?= xerox_is_available() ? '' : 'disabled' ?>><?= h($xeroxButtonLabel) ?></button>
                    </form>
                <?php elseif ($xeroxStatus === 'sent'): ?>
                    <span class="badge badge-accent">Encaminhado</span>
                    <form method="post" class="inline-actions">
                        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="action" value="cancel_xerox">
                        <button class="button-danger" type="submit">Cancelar envio</button>
                    </form>
                <?php elseif ($xeroxStatus === 'finished'): ?>
                    <span class="badge badge-success">Finalizado</span>
                    <form method="post" class="inline-actions">
                        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="action" value="resend_to_xerox">
                        <button class="button" type="submit" <?= xerox_is_available() ? '' : 'disabled' ?>>Reenviar Xerox</button>
                    </form>
                <?php else: ?>
                    <span class="badge <?= h(xerox_status_badge_class($xeroxStatus)) ?>"><?= h($xeroxButtonLabel) ?></span>
                <?php endif; ?>

                <?php if ($xeroxStatus !== 'in_progress'): ?>
                    <form method="post" class="inline-actions">
                        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="action" value="delete_exam">
                        <button class="button-danger" type="submit" onclick="return confirm('Excluir esta prova?');">Excluir</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="workspace-builder-note">
        <strong>Status Xerox</strong>
        <p>
            <?= h(xerox_status_label($xeroxStatus)) ?>
            <?php if (!empty($exam['xerox_owner_name'])): ?>
                | Responsavel: <?= h((string) $exam['xerox_owner_name']) ?>
            <?php endif; ?>
            <?php if (!$isExamOwner): ?>
                | Professor: <?= h((string) ($exam['owner_name'] ?? 'Nao informado')) ?>
            <?php endif; ?>
        </p>
        <?php if ($isExamOwner && !xerox_is_available()): ?>
            <p class="helper-text">O envio so fica liberado quando existir pelo menos um usuario autorizado no setor Xerox.</p>
        <?php elseif ($isExamOwner && $xeroxStatus === 'sent'): ?>
            <p class="helper-text">Enquanto a prova estiver apenas encaminhada, voce pode cancelar o envio.</p>
        <?php elseif ($isExamOwner && $xeroxStatus === 'finished'): ?>
            <p class="helper-text">Se precisar de nova impressao, use o botao Reenviar Xerox.</p>
        <?php endif; ?>
    </div>

    <?php if ($metadataSummary !== []): ?>
        <div class="simple-inline-list exam-preview-meta-badges">
            <?php foreach (array_slice($metadataSummary, 0, 8) as $item): ?>
                <span class="badge"><?= h($item['label'] . ': ' . $item['value']) ?></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?= exam_document_render_sheet($document) ?>
</section>

<?= exam_document_render_preview_notes($document) ?>

<?php render_footer(); ?>
