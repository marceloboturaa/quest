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
            flash('error', 'Nenhum usuário do Xerox está autorizado no momento.');
            redirect('exam-preview.php?id=' . $examId);
        }

        if (!xerox_submit_exam($examId, (int) $user['id'])) {
            flash('error', 'Não foi possível encaminhar esta prova para o setor Xerox.');
            redirect('exam-preview.php?id=' . $examId);
        }

        flash('success', 'Prova encaminhada para o setor Xerox.');
        redirect('exam-preview.php?id=' . $examId);
    }

    if ($action === 'cancel_xerox') {
        if (!xerox_cancel_exam($examId, (int) $user['id'])) {
            flash('error', 'Não foi possível cancelar o envio. A prova pode já estar em andamento.');
            redirect('exam-preview.php?id=' . $examId);
        }

        flash('success', 'Envio para o setor Xerox cancelado.');
        redirect('exam-preview.php?id=' . $examId);
    }

    if ($action === 'resend_to_xerox') {
        if (!xerox_is_available()) {
            flash('error', 'Nenhum usuário do Xerox está autorizado no momento.');
            redirect('exam-preview.php?id=' . $examId);
        }

        if (!xerox_resend_exam($examId, (int) $user['id'])) {
            flash('error', 'Não foi possível reenviar esta prova para o setor Xerox.');
            redirect('exam-preview.php?id=' . $examId);
        }

        flash('success', 'Prova reenviada para o setor Xerox.');
        redirect('exam-preview.php?id=' . $examId);
    }

    if ($action === 'delete_exam') {
        if (!exam_delete($examId, (int) $user['id'])) {
            flash('error', 'Não foi possível excluir esta prova.');
            redirect('exam-preview.php?id=' . $examId);
        }

        flash('success', 'Prova excluída com sucesso.');
        redirect('dashboard.php');
    }
}

$exam = $examId > 0 ? exam_find_accessible($examId, $user) : null;

if (!$exam) {
    flash('error', 'Prova não encontrada.');
    redirect(can_view_xerox_queue() ? 'xerox.php' : 'exams.php');
}

[$questions, $questionOptions] = exam_questions_for_view($examId);
$document = exam_document_view_data($exam, $questions, $questionOptions);
$previewStyle = trim((string) ($_GET['preview_style'] ?? $document['style']));
$previewPaperSize = exam_document_resolve_paper_size((string) ($_GET['preview_paper_size'] ?? $document['paper_size']));

if (array_key_exists($previewStyle, exam_style_options())) {
    $document['style'] = $previewStyle;
    $document['metadata']['exam_style'] = $previewStyle;
}

$document['paper_size'] = $previewPaperSize;
$metadata = $document['metadata'];
$metadataSummary = array_values(array_filter(
    $document['metadata_summary'],
    static fn(array $item): bool => in_array((string) ($item['label'] ?? ''), ['Tipo', 'Professor', 'Turma', 'Data', 'Versão'], true)
));
$answerPreviewOrientation = (($metadata['answer_preview_orientation'] ?? 'vertical') === 'horizontal')
    ? 'Horizontal deitado'
    : 'Vertical em pé';
$answerPreviewWidth = (($metadata['answer_preview_width_mode'] ?? 'full') === 'full')
    ? 'Largura da prova'
    : trim((string) ($metadata['answer_preview_size_cm'] ?? '18')) . ' cm';
$previewSettings = [
    ['label' => 'Gabarito', 'value' => exam_response_mode_label((string) ($metadata['response_mode'] ?? 'separate_answer_sheet'))],
    ['label' => 'Questões na prévia', 'value' => trim((string) ($metadata['answer_preview_quantity'] ?? count($questions)))],
    ['label' => 'Orientação', 'value' => $answerPreviewOrientation],
    ['label' => 'Largura do gabarito', 'value' => $answerPreviewWidth],
    ['label' => 'Altura do gabarito', 'value' => trim((string) ($metadata['answer_preview_height_cm'] ?? '7')) . ' cm'],
    ['label' => 'Letra do gabarito', 'value' => trim((string) ($metadata['answer_preview_font_size'] ?? '13')) . ' px'],
    ['label' => 'Logo', 'value' => trim((string) ($metadata['header_logo_size'] ?? '2.2')) . ' cm'],
    ['label' => 'Altura do cabeçalho', 'value' => trim((string) ($metadata['header_min_height'] ?? '3.2')) . ' cm'],
];
$xeroxStatus = (string) ($exam['xerox_status'] ?? 'not_sent');
$isExamOwner = (int) $exam['user_id'] === (int) $user['id'];
$xeroxButtonLabel = $xeroxStatus === 'sent'
    ? 'Encaminhado'
    : ($xeroxStatus === 'in_progress' ? 'Em andamento' : ($xeroxStatus === 'finished' ? 'Finalizado' : 'Xerox'));

render_header(
    'Visualização da prova',
    'Confira o cabeçalho, o corpo, o rodapé e a ordem das questões antes de exportar.'
);
?>

<style>
<?= exam_document_styles(false, $document) ?>
</style>

<section class="exam-preview-layout">
    <section class="panel">
        <div class="workspace-panel-head">
            <div>
                <p class="workspace-kicker">Preview</p>
                <h2><?= h((string) $exam['title']) ?></h2>
            </div>
            <div class="form-actions">
                <?php if ($isExamOwner): ?>
                    <a class="ghost-button" href="exam-create.php?edit=<?= h((string) $exam['id']) ?>#dados-principais">Editar dados</a>
                    <a class="ghost-button" href="exam-create.php?edit=<?= h((string) $exam['id']) ?>#cabecalho-visual">Cabeçalho</a>
                    <a class="ghost-button" href="exam-create.php?edit=<?= h((string) $exam['id']) ?>#textos-prova">Textos</a>
                    <a class="ghost-button" href="exams.php?exam_id=<?= h((string) $exam['id']) ?>">Questões</a>
                <?php endif; ?>
                <a class="ghost-button" href="<?= $isExamOwner ? 'exams.php' : 'xerox.php' ?>">Voltar</a>
                <a class="button-secondary" href="exam-pdf.php?id=<?= h((string) $exam['id']) ?>&preview_style=<?= h($document['style']) ?>&preview_paper_size=<?= h($document['paper_size']) ?>">Abrir PDF</a>
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
                    | Responsável: <?= h((string) $exam['xerox_owner_name']) ?>
                <?php endif; ?>
                <?php if (!$isExamOwner): ?>
                    | Professor: <?= h((string) ($exam['owner_name'] ?? 'Não informado')) ?>
                <?php endif; ?>
            </p>
            <?php if ($isExamOwner && !xerox_is_available()): ?>
                <p class="helper-text">O envio só fica liberado quando existir pelo menos um usuário autorizado no setor Xerox.</p>
            <?php elseif ($isExamOwner && $xeroxStatus === 'sent'): ?>
                <p class="helper-text">Enquanto a prova estiver apenas encaminhada, você pode cancelar o envio.</p>
            <?php elseif ($isExamOwner && $xeroxStatus === 'finished'): ?>
                <p class="helper-text">Se precisar de nova impressão, use o botão Reenviar Xerox.</p>
            <?php endif; ?>
        </div>

        <?php if ($metadataSummary !== []): ?>
            <div class="simple-inline-list exam-preview-meta-badges">
                <?php foreach (array_slice($metadataSummary, 0, 8) as $item): ?>
                    <span class="badge"><?= h($item['label'] . ': ' . $item['value']) ?></span>
                <?php endforeach; ?>
                <span class="badge">Layout: <?= h(exam_style_label($document['style'])) ?></span>
                <span class="badge">Papel: <?= h($document['paper_size']) ?></span>
            </div>
        <?php endif; ?>

        <?= exam_document_render_sheet($document) ?>
    </section>

    <aside class="simple-card exam-preview-floating-panel">
        <div class="simple-card-head">
            <div>
                <h2>Ajuste rápido</h2>
                <p class="helper-text">Mude a visualização do corpo e do papel direto no preview.</p>
            </div>
        </div>

        <form method="get" class="simple-stack">
            <input type="hidden" name="id" value="<?= h((string) $exam['id']) ?>">
            <label>Corpo / colunas
                <select name="preview_style">
                    <?php foreach (exam_style_options() as $styleValue => $styleLabel): ?>
                        <option value="<?= h($styleValue) ?>" <?= $document['style'] === $styleValue ? 'selected' : '' ?>><?= h($styleLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Tamanho do papel
                <select name="preview_paper_size">
                    <?php foreach (exam_paper_size_options() as $paperValue => $paperLabel): ?>
                        <option value="<?= h($paperValue) ?>" <?= $document['paper_size'] === $paperValue ? 'selected' : '' ?>><?= h($paperLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="button" type="submit">Atualizar preview</button>
        </form>

        <div class="exam-preview-settings">
            <strong>Configuração salva</strong>
            <div class="exam-preview-settings-grid">
                <?php foreach ($previewSettings as $item): ?>
                    <div class="exam-preview-settings-item">
                        <small><?= h($item['label']) ?></small>
                        <span><?= h($item['value']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</section>

<?= exam_document_render_preview_notes($document) ?>

<?php render_footer(); ?>
