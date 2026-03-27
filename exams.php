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
$draftTitle = trim((string) ($_GET['draft_title'] ?? ''));
$draftInstructions = trim((string) ($_GET['draft_instructions'] ?? ''));
$draftMetadata = exam_collect_metadata($_GET);
$preselectedQuestionIds = array_values(array_unique(array_map('intval', array_merge(
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
$exams = exam_list($userId);
$metadataSummary = exam_metadata_summary($draftMetadata);
$styleLabel = exam_style_label((string) ($draftMetadata['exam_style'] ?? 'double_column'));
$selectedPreviewQuestions = array_values(array_filter(
    $availableQuestions,
    static fn(array $question): bool => in_array((int) $question['id'], $preselectedQuestionIds, true)
));

render_header(
    'Montagem de provas',
    'Escolha os dados da avaliacao, filtre o banco de questoes e monte a prova em um workspace unico.'
);
?>

<section class="assessment-workspace">
    <section class="assessment-search-column">
        <article class="workspace-panel">
            <div class="workspace-panel-head">
                <div>
                    <p class="workspace-kicker">Banco de questoes</p>
                    <h2>Filtre e selecione os itens da prova</h2>
                </div>
                <a class="ghost-button" href="exam-create.php">Editar dados da prova</a>
            </div>

            <form method="get" class="workspace-filter-grid">
                <input type="hidden" name="draft_title" value="<?= h($draftTitle) ?>">
                <input type="hidden" name="draft_instructions" value="<?= h($draftInstructions) ?>">
                <?php foreach ($draftMetadata as $metaKey => $metaValue): ?>
                    <input type="hidden" name="<?= h($metaKey) ?>" value="<?= h($metaValue) ?>">
                <?php endforeach; ?>
                <?php foreach ($preselectedQuestionIds as $selectedId): ?>
                    <input type="hidden" name="question_ids[]" value="<?= h((string) $selectedId) ?>">
                <?php endforeach; ?>

                <label>Buscar questoes
                    <input type="text" name="term" value="<?= h($builderFilters['term']) ?>" placeholder="Titulo ou enunciado">
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
                        <option value="multiple_choice" <?= $builderFilters['question_type'] === 'multiple_choice' ? 'selected' : '' ?>>Multipla escolha</option>
                        <option value="discursive" <?= $builderFilters['question_type'] === 'discursive' ? 'selected' : '' ?>>Discursiva</option>
                        <option value="drawing" <?= $builderFilters['question_type'] === 'drawing' ? 'selected' : '' ?>>Desenho</option>
                        <option value="true_false" <?= $builderFilters['question_type'] === 'true_false' ? 'selected' : '' ?>>Verdadeiro ou falso</option>
                    </select>
                </label>
                <label>Visibilidade
                    <select name="visibility">
                        <option value="">Todas</option>
                        <option value="public" <?= $builderFilters['visibility'] === 'public' ? 'selected' : '' ?>>Publicas</option>
                        <option value="private" <?= $builderFilters['visibility'] === 'private' ? 'selected' : '' ?>>Privadas</option>
                    </select>
                </label>
                <div class="form-actions">
                    <button class="button" type="submit">Filtrar banco</button>
                    <a class="ghost-button" href="exams.php?<?= h(http_build_query(array_filter(array_merge([
                        'draft_title' => $draftTitle,
                        'draft_instructions' => $draftInstructions,
                    ], $draftMetadata), static fn(mixed $value): bool => $value !== ''))) ?>">Limpar filtros</a>
                </div>
            </form>

            <form id="exam-builder-form" method="post" class="workspace-question-grid" data-exam-builder-form>
                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="create_exam">
                <input type="hidden" name="title" value="<?= h($draftTitle !== '' ? $draftTitle : 'Nova prova') ?>">
                <input type="hidden" name="instructions" value="<?= h($draftInstructions) ?>">
                <?php foreach ($draftMetadata as $metaKey => $metaValue): ?>
                    <input type="hidden" name="<?= h($metaKey) ?>" value="<?= h($metaValue) ?>">
                <?php endforeach; ?>

                <?php if ($availableQuestions === []): ?>
                    <div class="empty-state">
                        <h2>Nenhuma questao disponivel</h2>
                        <p>Crie questoes, torne itens publicos ou limpe os filtros para ampliar o banco.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($availableQuestions as $question): ?>
                        <?php $isPreselected = in_array((int) $question['id'], $preselectedQuestionIds, true); ?>
                        <label class="question-card question-card-workspace question-pick-card">
                            <input
                                type="checkbox"
                                name="question_ids[]"
                                value="<?= h((string) $question['id']) ?>"
                                data-exam-question
                                data-question-title="<?= h($question['title']) ?>"
                                <?= $isPreselected ? 'checked' : '' ?>
                            >
                            <div class="question-meta">
                                <span class="badge"><?= h(question_type_label((string) $question['question_type'])) ?></span>
                                <span class="badge"><?= h($question['discipline_name'] ?? 'Sem disciplina') ?></span>
                                <span class="badge"><?= h($question['subject_name'] ?? 'Sem assunto') ?></span>
                            </div>
                            <h3><?= h($question['title']) ?></h3>
                            <p class="helper-text"><?= !empty($question['source_name']) ? 'Fonte: ' . h($question['source_name']) : 'Autor: ' . h($question['author_name']) ?></p>
                            <div class="question-card-footer">
                                <span>Uso: <?= h((string) $question['usage_count']) ?></span>
                                <span><?= h(visibility_label((string) $question['visibility'])) ?></span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </form>
        </article>
    </section>

    <aside class="assessment-builder-column">
        <article class="workspace-panel workspace-builder-panel">
            <div class="workspace-panel-head">
                <div>
                    <p class="workspace-kicker">Prova em construcao</p>
                    <h2><?= h($draftTitle !== '' ? $draftTitle : 'Nova prova') ?></h2>
                </div>
                <span class="badge" data-selected-count><?= h((string) count($preselectedQuestionIds)) ?> questoes selecionadas</span>
            </div>

            <?php if ($metadataSummary !== []): ?>
                <div class="workspace-inline-meta">
                    <?php foreach ($metadataSummary as $item): ?>
                        <span class="badge"><?= h($item['label'] . ': ' . $item['value']) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="workspace-builder-note">
                <strong>Formato selecionado</strong>
                <p><?= h($styleLabel) ?></p>
            </div>

            <?php if ($draftInstructions !== ''): ?>
                <div class="workspace-builder-note">
                    <strong>Instrucoes</strong>
                    <p><?= nl2br(h($draftInstructions)) ?></p>
                </div>
            <?php endif; ?>

            <div class="workspace-quick-list" data-selected-list>
                <?php if ($selectedPreviewQuestions === []): ?>
                    <div class="workspace-quick-item" data-selected-empty>
                        <strong>Nenhuma questao selecionada</strong>
                        <p>Marque itens no banco ao lado para montar a prova.</p>
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

            <div class="workspace-builder-actions">
                <button class="button" type="submit" form="exam-builder-form" data-submit-exam-builder>Salvar prova</button>
                <a class="button-secondary" href="exam-create.php">Voltar aos dados</a>
            </div>
        </article>

        <article class="workspace-panel">
            <div class="workspace-panel-head">
                <div>
                    <p class="workspace-kicker">Provas salvas</p>
                    <h2>Historico recente</h2>
                </div>
            </div>

            <?php if ($exams === []): ?>
                <div class="empty-state">
                    <h2>Nenhuma prova criada</h2>
                    <p>Selecione questoes e salve a primeira avaliacao.</p>
                </div>
            <?php else: ?>
                <div class="workspace-quick-list">
                    <?php foreach ($exams as $exam): ?>
                        <?php $parsed = exam_parse_stored_instructions((string) ($exam['instructions'] ?? '')); ?>
                        <article class="workspace-quick-item">
                            <strong><?= h((string) $exam['title']) ?></strong>
                            <p><?= h((string) $exam['total_questions']) ?> questoes | criada em <?= h(date('d/m/Y H:i', strtotime((string) $exam['created_at']))) ?></p>
                            <?php foreach (array_slice(exam_metadata_summary($parsed['metadata']), 0, 3) as $item): ?>
                                <span class="badge"><?= h($item['label'] . ': ' . $item['value']) ?></span>
                            <?php endforeach; ?>
                            <div class="form-actions">
                                <a class="ghost-button" href="exam-preview.php?id=<?= h((string) $exam['id']) ?>">Preview</a>
                                <a class="button-secondary" href="exam-pdf.php?id=<?= h((string) $exam['id']) ?>">PDF</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    </aside>
</section>

<?php render_footer(); ?>
