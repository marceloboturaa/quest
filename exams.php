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
$selectedPreviewQuestions = array_values(array_filter(
    $availableQuestions,
    static fn(array $question): bool => in_array((int) $question['id'], $preselectedQuestionIds, true)
));

$questionExcerpt = static function (?string $text, int $limit = 170): string {
    $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

    if ($plain === '') {
        return '';
    }

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($plain, 0, $limit, '...');
    }

    return strlen($plain) > $limit ? substr($plain, 0, $limit - 3) . '...' : $plain;
};

render_header(
    $editingExam ? 'Editar prova' : 'Montagem da prova',
    'Passo 2. Escolha as questões e salve a prova.'
);
?>

<section class="simple-stack">
    <article class="simple-card">
        <div class="simple-card-head">
            <div>
                <h2><?= h($draftTitle !== '' ? $draftTitle : 'Nova prova') ?></h2>
                <p class="helper-text">Confira os dados e marque apenas as questões que deseja usar.</p>
            </div>
            <div class="simple-action-row">
                <a class="ghost-button" href="exam-create.php?<?= h(http_build_query(array_filter([
                    'edit' => $editingExam ? $examId : null,
                    'draft_title' => $draftTitle,
                    'draft_instructions' => $draftInstructions,
                    ...$draftMetadata,
                    'question_ids' => $preselectedQuestionIds,
                ], static fn(mixed $value): bool => $value !== null && $value !== ''))) ?>">Editar dados</a>
            </div>
        </div>

        <div class="simple-inline-list">
            <span class="badge"><?= h((string) count($preselectedQuestionIds)) ?> selecionadas</span>
            <?php foreach (array_slice($metadataSummary, 0, 4) as $item): ?>
                <span class="badge"><?= h($item['label'] . ': ' . $item['value']) ?></span>
            <?php endforeach; ?>
        </div>

        <?php if ($draftInstructions !== ''): ?>
            <div class="simple-note">
                <strong>Instruções</strong>
                <p><?= nl2br(h($draftInstructions)) ?></p>
            </div>
        <?php endif; ?>
    </article>

    <article class="simple-card">
        <div class="simple-card-head">
            <h2>Filtrar banco</h2>
        </div>

        <form method="get" class="simple-filter-grid">
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

            <label>Buscar
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
            <div class="simple-action-row">
                <button class="button" type="submit">Filtrar</button>
                <a class="ghost-button" href="exams.php?<?= h(http_build_query(array_filter(array_merge([
                    'exam_id' => $editingExam ? $examId : null,
                    'draft_title' => $draftTitle,
                    'draft_instructions' => $draftInstructions,
                ], $draftMetadata), static fn(mixed $value): bool => $value !== ''))) ?>">Limpar</a>
            </div>
        </form>
    </article>

    <form method="post" class="simple-stack" data-exam-builder-form>
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

        <article class="simple-card">
            <div class="simple-card-head">
                <h2>Questões selecionadas</h2>
                <span class="badge" data-selected-count><?= h((string) count($preselectedQuestionIds)) ?> selecionadas</span>
            </div>

            <div class="simple-list" data-selected-list>
                <?php if ($selectedPreviewQuestions === []): ?>
                    <div class="empty-state" data-selected-empty>
                        <h2>Nenhuma questão selecionada</h2>
                        <p>Marque as questões abaixo para montar a prova.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($selectedPreviewQuestions as $index => $question): ?>
                        <article class="simple-list-item">
                            <div>
                                <strong><?= h((string) ($index + 1)) ?>. <?= h((string) $question['title']) ?></strong>
                                <p>Pronta para entrar na prova.</p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="simple-card">
            <div class="simple-card-head">
                <h2>Banco de questões</h2>
                <span class="badge"><?= h((string) count($availableQuestions)) ?> disponíveis</span>
            </div>

            <?php if ($availableQuestions === []): ?>
                <div class="empty-state">
                    <h2>Nenhuma questão disponível</h2>
                    <p>Crie questões, torne itens públicos ou limpe os filtros.</p>
                </div>
            <?php else: ?>
                <div class="simple-list">
                    <?php foreach ($availableQuestions as $question): ?>
                        <?php $isPreselected = in_array((int) $question['id'], $preselectedQuestionIds, true); ?>
                        <label class="simple-question-picker">
                            <input
                                type="checkbox"
                                name="question_ids[]"
                                value="<?= h((string) $question['id']) ?>"
                                data-exam-question
                                data-question-title="<?= h($question['title']) ?>"
                                <?= $isPreselected ? 'checked' : '' ?>
                            >
                            <span class="simple-question-picker-body">
                                <span class="simple-question-picker-top">
                                    <span class="simple-question-picker-state"><?= $isPreselected ? 'Selecionada' : 'Selecionar' ?></span>
                                    <span class="simple-inline-list">
                                        <span class="badge"><?= h(question_type_label((string) $question['question_type'])) ?></span>
                                        <span class="badge"><?= h($question['discipline_name'] ?? 'Sem disciplina') ?></span>
                                        <span class="badge"><?= h(visibility_label((string) $question['visibility'])) ?></span>
                                    </span>
                                </span>
                                <strong><?= h($question['title']) ?></strong>
                                <?php if ($questionExcerpt((string) ($question['prompt'] ?? '')) !== ''): ?>
                                    <small><?= h($questionExcerpt((string) ($question['prompt'] ?? ''))) ?></small>
                                <?php endif; ?>
                                <span class="simple-question-picker-meta">
                                    <span><?= !empty($question['source_name']) ? 'Fonte: ' . h((string) $question['source_name']) : 'Autor: ' . h((string) $question['author_name']) ?></span>
                                    <span>Uso em prova: <?= h((string) $question['usage_count']) ?></span>
                                </span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <div class="simple-action-row">
            <button class="button" type="submit" data-submit-exam-builder><?= $editingExam ? 'Atualizar prova' : 'Salvar prova' ?></button>
            <a class="button-secondary" href="exam-create.php<?= $editingExam ? '?edit=' . h((string) $examId) : '' ?>">Voltar</a>
        </div>
    </form>
</section>

<?php render_footer(); ?>
