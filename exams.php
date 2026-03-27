<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_actions.php';
require_once __DIR__ . '/includes/exam_metadata.php';
require_once __DIR__ . '/includes/question_repository.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$examId = (int) ($_GET['exam_id'] ?? 0);
$editingExam = $examId > 0 ? exam_find($examId, $userId) : null;
$parsedExamData = $editingExam ? exam_parse_stored_instructions($editingExam['instructions'] ?? null) : [
    'metadata' => exam_default_metadata(),
    'instructions' => '',
];
$draftTitle = trim((string) ($_GET['draft_title'] ?? ($editingExam['title'] ?? '')));
$draftInstructions = trim((string) ($_GET['draft_instructions'] ?? $parsedExamData['instructions']));
$draftMetadata = array_replace($parsedExamData['metadata'], exam_collect_metadata($_GET));
$preselectedQuestionIds = array_values(array_unique(array_map('intval', array_merge(
    $editingExam ? exam_question_ids($examId, $userId) : [],
    (array) ($_GET['question_ids'] ?? []),
    isset($_GET['question_id']) ? [(int) $_GET['question_id']] : []
))));
$builderFilters = [
    'term' => trim((string) ($_GET['term'] ?? '')),
    'discipline_id' => (int) ($_GET['discipline_id'] ?? 0),
    'question_type' => trim((string) ($_GET['question_type'] ?? '')),
    'visibility' => trim((string) ($_GET['visibility'] ?? '')),
];

if (is_post()) {
    abort_if_invalid_csrf();
    handle_exam_request($userId);
}

$disciplines = question_disciplines();
$availableQuestions = exam_available_questions($userId, $builderFilters);
$metadataSummary = exam_metadata_summary($draftMetadata);
$styleLabel = exam_style_label((string) ($draftMetadata['exam_style'] ?? 'double_column'));
$selectedPreviewQuestions = array_values(array_filter(
    $availableQuestions,
    static fn(array $question): bool => in_array((int) $question['id'], $preselectedQuestionIds, true)
));

render_header(
    $editingExam ? 'Editar prova' : 'Montagem de provas',
    $editingExam
        ? 'Revise os dados e ajuste as questões da prova já criada.'
        : 'Selecione as questões e salve a prova sem etapas extras.'
);
?>

<section class="panel">
    <div class="workspace-panel-head">
        <div>
            <p class="workspace-kicker">Prova atual</p>
            <h2><?= h($draftTitle !== '' ? $draftTitle : 'Nova prova') ?></h2>
        </div>
        <div class="form-actions">
            <a class="ghost-button" href="exam-create.php?<?= h(http_build_query(array_filter([
                'edit' => $editingExam ? $examId : null,
                'draft_title' => $draftTitle,
                'draft_instructions' => $draftInstructions,
                ...$draftMetadata,
                'question_ids' => $preselectedQuestionIds,
            ], static fn(mixed $value): bool => $value !== null && $value !== ''))) ?>">Editar dados</a>
            <a class="ghost-button" href="dashboard.php">Histórico de provas</a>
        </div>
    </div>

    <div class="exam-builder-simple-summary">
        <article class="workspace-builder-note">
            <strong>Selecionadas</strong>
            <p><span data-selected-count><?= h((string) count($preselectedQuestionIds)) ?> questões selecionadas</span></p>
        </article>
        <article class="workspace-builder-note">
            <strong>Formato</strong>
            <p><?= h($styleLabel) ?></p>
        </article>
        <?php if ($metadataSummary !== []): ?>
            <article class="workspace-builder-note exam-builder-simple-meta">
                <strong>Resumo</strong>
                <div class="workspace-inline-meta">
                    <?php foreach ($metadataSummary as $item): ?>
                        <span class="badge"><?= h($item['label'] . ': ' . $item['value']) ?></span>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>
    </div>

    <?php if ($draftInstructions !== ''): ?>
        <div class="workspace-builder-note">
            <strong>Instruções</strong>
            <p><?= nl2br(h($draftInstructions)) ?></p>
        </div>
    <?php endif; ?>
</section>

<section class="panel">
    <div class="workspace-panel-head">
        <div>
            <p class="workspace-kicker">Filtro</p>
            <h2>Encontre questões no banco</h2>
        </div>
    </div>

    <form method="get" class="workspace-filter-grid">
        <?php if ($editingExam): ?>
            <input type="hidden" name="exam_id" value="<?= h((string) $examId) ?>">
        <?php endif; ?>
        <input type="hidden" name="draft_title" value="<?= h($draftTitle) ?>">
        <input type="hidden" name="draft_instructions" value="<?= h($draftInstructions) ?>">
        <?php foreach ($draftMetadata as $metaKey => $metaValue): ?>
            <input type="hidden" name="<?= h($metaKey) ?>" value="<?= h($metaValue) ?>">
        <?php endforeach; ?>
        <?php foreach ($preselectedQuestionIds as $selectedId): ?>
            <input type="hidden" name="question_ids[]" value="<?= h((string) $selectedId) ?>">
        <?php endforeach; ?>

        <label>Buscar questões
            <input type="text" name="term" value="<?= h($builderFilters['term']) ?>" placeholder="Título ou enunciado">
        </label>
        <label>Disciplina
            <select name="discipline_id">
                <option value="0">Todas</option>
                <?php foreach ($disciplines as $discipline): ?>
                    <option value="<?= h((string) $discipline['id']) ?>" <?= $builderFilters['discipline_id'] === (int) $discipline['id'] ? 'selected' : '' ?>><?= h($discipline['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Tipo
            <select name="question_type">
                <option value="">Todos</option>
                <option value="multiple_choice" <?= $builderFilters['question_type'] === 'multiple_choice' ? 'selected' : '' ?>>Múltipla escolha</option>
                <option value="discursive" <?= $builderFilters['question_type'] === 'discursive' ? 'selected' : '' ?>>Discursiva</option>
                <option value="drawing" <?= $builderFilters['question_type'] === 'drawing' ? 'selected' : '' ?>>Desenho</option>
                <option value="true_false" <?= $builderFilters['question_type'] === 'true_false' ? 'selected' : '' ?>>Verdadeiro ou falso</option>
            </select>
        </label>
        <label>Visibilidade
            <select name="visibility">
                <option value="">Todas</option>
                <option value="public" <?= $builderFilters['visibility'] === 'public' ? 'selected' : '' ?>>Públicas</option>
                <option value="private" <?= $builderFilters['visibility'] === 'private' ? 'selected' : '' ?>>Privadas</option>
            </select>
        </label>
        <div class="form-actions">
            <button class="button" type="submit">Filtrar banco</button>
            <a class="ghost-button" href="exams.php?<?= h(http_build_query(array_filter(array_merge([
                'exam_id' => $editingExam ? $examId : null,
                'draft_title' => $draftTitle,
                'draft_instructions' => $draftInstructions,
            ], $draftMetadata), static fn(mixed $value): bool => $value !== ''))) ?>">Limpar filtros</a>
        </div>
    </form>
</section>

<section class="panel">
    <div class="workspace-panel-head">
        <div>
            <p class="workspace-kicker">Montagem</p>
            <h2>Selecione as questões da prova</h2>
        </div>
        <span class="badge"><?= h((string) count($availableQuestions)) ?> itens no banco</span>
    </div>

    <form id="exam-builder-form" method="post" data-exam-builder-form>
        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="<?= $editingExam ? 'update_exam' : 'create_exam' ?>">
        <?php if ($editingExam): ?>
            <input type="hidden" name="exam_id" value="<?= h((string) $examId) ?>">
        <?php endif; ?>
        <input type="hidden" name="title" value="<?= h($draftTitle !== '' ? $draftTitle : 'Nova prova') ?>">
        <input type="hidden" name="instructions" value="<?= h($draftInstructions) ?>">
        <?php foreach ($draftMetadata as $metaKey => $metaValue): ?>
            <input type="hidden" name="<?= h($metaKey) ?>" value="<?= h($metaValue) ?>">
        <?php endforeach; ?>

        <div class="exam-builder-selected-shell" data-selected-list>
            <?php if ($selectedPreviewQuestions === []): ?>
                <div class="workspace-quick-item" data-selected-empty>
                    <strong>Nenhuma questao selecionada</strong>
                    <p>Marque os itens abaixo para montar a prova.</p>
                </div>
            <?php else: ?>
                <?php foreach ($selectedPreviewQuestions as $index => $question): ?>
                    <div class="workspace-quick-item">
                        <strong><?= h((string) ($index + 1)) ?>. <?= h((string) $question['title']) ?></strong>
                        <p>Item pronto para entrar na prova atual.</p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if ($availableQuestions === []): ?>
            <div class="empty-state">
                <h2>Nenhuma questão disponível</h2>
                <p>Crie questões, torne itens públicos ou limpe os filtros para ampliar o banco.</p>
            </div>
        <?php else: ?>
            <div class="workspace-question-grid exam-question-picker-grid">
                <?php foreach ($availableQuestions as $question): ?>
                    <?php
                    $isPreselected = in_array((int) $question['id'], $preselectedQuestionIds, true);
                    $promptPreview = trim((string) ($question['prompt'] ?? ''));
                    $promptPreview = preg_replace('/\s+/', ' ', $promptPreview) ?? $promptPreview;
                    if (function_exists('mb_strlen') && function_exists('mb_substr') && mb_strlen($promptPreview) > 180) {
                        $promptPreview = rtrim(mb_substr($promptPreview, 0, 180)) . '...';
                    } elseif (strlen($promptPreview) > 180) {
                        $promptPreview = rtrim(substr($promptPreview, 0, 180)) . '...';
                    }
                    ?>
                    <label class="exam-question-picker">
                        <input
                            type="checkbox"
                            name="question_ids[]"
                            value="<?= h((string) $question['id']) ?>"
                            data-exam-question
                            data-question-title="<?= h($question['title']) ?>"
                            <?= $isPreselected ? 'checked' : '' ?>
                        >
                        <div class="exam-question-picker-body">
                            <div class="exam-question-picker-top">
                                <span class="exam-question-picker-check"><?= $isPreselected ? 'Selecionada' : 'Selecionar' ?></span>
                                <div class="question-meta">
                                    <span class="badge"><?= h(question_type_label((string) $question['question_type'])) ?></span>
                                    <span class="badge"><?= h($question['discipline_name'] ?? 'Sem disciplina') ?></span>
                                    <span class="badge"><?= h($question['subject_name'] ?? 'Sem assunto') ?></span>
                                </div>
                            </div>
                            <h3><?= h($question['title']) ?></h3>
                            <?php if ($promptPreview !== ''): ?>
                                <p class="helper-text"><?= h($promptPreview) ?></p>
                            <?php endif; ?>
                            <div class="question-card-footer">
                                <span><?= !empty($question['source_name']) ? 'Fonte: ' . h($question['source_name']) : 'Autor: ' . h($question['author_name']) ?></span>
                                <span><?= h(visibility_label((string) $question['visibility'])) ?> • Uso <?= h((string) $question['usage_count']) ?></span>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="workspace-builder-actions exam-builder-simple-actions">
            <button class="button" type="submit" data-submit-exam-builder><?= $editingExam ? 'Atualizar prova' : 'Salvar prova' ?></button>
            <a class="button-secondary" href="exam-create.php<?= $editingExam ? '?edit=' . h((string) $examId) : '' ?>">Voltar aos dados</a>
        </div>
    </form>
</section>

<?php render_footer(); ?>
