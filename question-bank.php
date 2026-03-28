<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/question_helpers.php';
require_once __DIR__ . '/includes/question_repository.php';
require_once __DIR__ . '/includes/question_actions.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];

if (is_post()) {
    abort_if_invalid_csrf();
    handle_question_request($userId);
}

$disciplines = question_disciplines();
$subjects = question_subjects();
$authors = question_authors();
$filters = question_filters($_GET);
[$questions, $questionOptions] = question_list($filters, $userId);
$questionMetrics = [
    'private' => 0,
    'public' => 0,
    'mine' => 0,
];

try {
    $statement = db()->prepare(
        'SELECT
            SUM(CASE WHEN author_id = :author_id AND visibility = "private" THEN 1 ELSE 0 END) AS private_total,
            SUM(CASE WHEN visibility = "public" THEN 1 ELSE 0 END) AS public_total,
            SUM(CASE WHEN author_id = :author_id THEN 1 ELSE 0 END) AS mine_total
         FROM questions'
    );
    $statement->execute(['author_id' => $userId]);
    $metricsRow = $statement->fetch() ?: [];
    $questionMetrics = [
        'private' => (int) ($metricsRow['private_total'] ?? 0),
        'public' => (int) ($metricsRow['public_total'] ?? 0),
        'mine' => (int) ($metricsRow['mine_total'] ?? 0),
    ];
} catch (Throwable) {
    $questionMetrics = [
        'private' => 0,
        'public' => 0,
        'mine' => 0,
    ];
}

$questionPreview = static function (?string $text, int $limit = 220): string {
    $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

    if ($plain === '') {
        return 'Sem resumo disponível.';
    }

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($plain, 0, $limit, '...');
    }

    return strlen($plain) > $limit ? substr($plain, 0, $limit - 3) . '...' : $plain;
};

render_header(
    'Banco de questões',
    'Filtre, abra e use questões em prova de forma simples.'
);
?>

<section class="simple-stack">
    <article class="simple-card">
        <div class="simple-card-head">
            <h2>Ações</h2>
            <div class="simple-action-row">
                <a class="button" href="question-editor.php?new=1">Nova questão</a>
                <a class="ghost-button" href="enem.php">Importar ENEM</a>
            </div>
        </div>

        <form method="get" class="simple-filter-grid">
            <label>Pesquisar
                <input type="text" name="term" value="<?= h($filters['term']) ?>" placeholder="Título, autor ou trecho">
            </label>
            <label>Disciplina
                <select name="discipline_id" data-discipline-select data-target="filter-subject-select">
                    <option value="0">Todas</option>
                    <?php foreach ($disciplines as $discipline): ?>
                        <option value="<?= h((string) $discipline['id']) ?>" <?= $filters['discipline_id'] === (int) $discipline['id'] ? 'selected' : '' ?>><?= h($discipline['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Assunto
                <select name="subject_id" id="filter-subject-select" data-subject-select>
                    <option value="0">Todos</option>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?= h((string) $subject['id']) ?>" data-discipline-id="<?= h((string) $subject['discipline_id']) ?>" <?= $filters['subject_id'] === (int) $subject['id'] ? 'selected' : '' ?>><?= h($subject['discipline_name'] . ' - ' . $subject['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Tipo
                <select name="question_type">
                    <option value="">Todos</option>
                    <option value="multiple_choice" <?= $filters['question_type'] === 'multiple_choice' ? 'selected' : '' ?>>Múltipla escolha</option>
                    <option value="discursive" <?= $filters['question_type'] === 'discursive' ? 'selected' : '' ?>>Discursiva</option>
                    <option value="drawing" <?= $filters['question_type'] === 'drawing' ? 'selected' : '' ?>>Desenho</option>
                    <option value="true_false" <?= $filters['question_type'] === 'true_false' ? 'selected' : '' ?>>Verdadeiro ou falso</option>
                </select>
            </label>
            <label>Nível
                <select name="education_level">
                    <option value="">Todos</option>
                    <option value="fundamental" <?= $filters['education_level'] === 'fundamental' ? 'selected' : '' ?>>Fundamental</option>
                    <option value="medio" <?= $filters['education_level'] === 'medio' ? 'selected' : '' ?>>Médio</option>
                    <option value="tecnico" <?= $filters['education_level'] === 'tecnico' ? 'selected' : '' ?>>Técnico</option>
                    <option value="superior" <?= $filters['education_level'] === 'superior' ? 'selected' : '' ?>>Superior</option>
                </select>
            </label>
            <label>Visibilidade
                <select name="visibility">
                    <option value="">Todas</option>
                    <option value="public" <?= $filters['visibility'] === 'public' ? 'selected' : '' ?>>Públicas</option>
                    <option value="private" <?= $filters['visibility'] === 'private' ? 'selected' : '' ?>>Privadas</option>
                </select>
            </label>
            <label>Autor
                <select name="author_id">
                    <option value="0">Todos</option>
                    <?php foreach ($authors as $author): ?>
                        <option value="<?= h((string) $author['id']) ?>" <?= $filters['author_id'] === (int) $author['id'] ? 'selected' : '' ?>><?= h($author['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <div class="simple-action-row">
                <button class="button" type="submit">Filtrar</button>
                <a class="ghost-button" href="question-bank.php">Limpar</a>
            </div>
        </form>
    </article>

    <section class="simple-metric-grid">
        <article class="simple-metric-card">
            <small>Resultados</small>
            <strong><?= h((string) count($questions)) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Minhas</small>
            <strong><?= h((string) $questionMetrics['mine']) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Públicas</small>
            <strong><?= h((string) $questionMetrics['public']) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Privadas</small>
            <strong><?= h((string) $questionMetrics['private']) ?></strong>
        </article>
    </section>

    <article class="simple-card">
        <div class="simple-card-head">
            <h2>Montagem de prova</h2>
            <form id="question-bulk-exam-form" method="get" action="exam-create.php" class="simple-action-row">
                <button class="button-secondary" type="submit">Usar selecionadas</button>
                <a class="ghost-button" href="exam-create.php">Nova prova</a>
            </form>
        </div>
    </article>

    <?php if ($questions === []): ?>
        <div class="empty-state">
            <h2>Nenhuma questão encontrada</h2>
            <p>Ajuste os filtros ou crie uma nova questão.</p>
        </div>
    <?php else: ?>
        <section class="simple-list">
            <?php foreach ($questions as $question): ?>
                <?php
                $visibility = (string) $question['visibility'];
                $visibilityBadgeClass = $visibility === 'public' ? 'badge badge-public' : 'badge badge-private';
                $visibilityIconClass = $visibility === 'public' ? 'fa-solid fa-earth-americas' : 'fa-solid fa-lock';
                ?>
                <article class="simple-list-card">
                    <div class="simple-list-card-top">
                        <label class="question-select-toggle">
                            <input form="question-bulk-exam-form" type="checkbox" name="question_ids[]" value="<?= h((string) $question['id']) ?>">
                            <span>Selecionar</span>
                        </label>
                        <div class="simple-inline-list">
                            <span class="<?= h($visibilityBadgeClass) ?>">
                                <i class="<?= h($visibilityIconClass) ?>" aria-hidden="true"></i>
                                <?= h(visibility_label($visibility)) ?>
                            </span>
                            <span class="badge"><?= h(question_type_label((string) $question['question_type'])) ?></span>
                            <?php if (!empty($question['discipline_name'])): ?>
                                <span class="badge"><?= h((string) $question['discipline_name']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h3><?= h((string) $question['title']) ?></h3>
                    <p class="simple-list-copy"><?= h($questionPreview((string) $question['prompt'])) ?></p>

                    <?php if ((string) $question['question_type'] === 'multiple_choice' && !empty($questionOptions[(int) $question['id']])): ?>
                        <div class="simple-inline-list">
                            <?php foreach (array_slice($questionOptions[(int) $question['id']], 0, 3) as $option): ?>
                                <span class="badge"><?= h($questionPreview((string) $option['option_text'], 70)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="simple-list-footer">
                        <div class="simple-inline-list">
                            <span class="badge">Uso em provas: <?= h((string) $question['usage_count']) ?></span>
                            <?php if (!empty($question['author_name'])): ?>
                                <span class="badge">Autor: <?= h((string) $question['author_name']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="simple-list-actions">
                            <form method="post">
                                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="toggle_favorite">
                                <input type="hidden" name="question_id" value="<?= h((string) $question['id']) ?>">
                                <button class="<?= (int) $question['is_favorite'] === 1 ? 'button-secondary' : 'ghost-button' ?>" type="submit"><?= (int) $question['is_favorite'] === 1 ? 'Favoritada' : 'Favoritar' ?></button>
                            </form>
                            <a class="ghost-button" href="exam-create.php?question_ids%5B%5D=<?= h((string) $question['id']) ?>">Usar em prova</a>
                            <?php if ((string) $question['visibility'] === 'public' || (int) $question['author_id'] === $userId): ?>
                                <form method="post">
                                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="clone_question">
                                    <input type="hidden" name="question_id" value="<?= h((string) $question['id']) ?>">
                                    <button class="ghost-button" type="submit">Clonar</button>
                                </form>
                            <?php endif; ?>
                            <?php if ((int) $question['author_id'] === $userId): ?>
                                <a class="button-secondary" href="question-editor.php?edit=<?= h((string) $question['id']) ?>">Editar</a>
                                <form method="post">
                                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete_question">
                                    <input type="hidden" name="question_id" value="<?= h((string) $question['id']) ?>">
                                    <button class="button-danger" type="submit" onclick="return confirm('Excluir esta questao?')">Excluir</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <?php if (can_manage_catalogs()): ?>
        <section class="simple-panel-grid">
            <article class="simple-card">
                <div class="simple-card-head">
                    <h2>Nova disciplina</h2>
                </div>
                <form method="post" class="simple-stack">
                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="create_discipline">
                    <label>Nome da disciplina
                        <input type="text" name="discipline_name" required>
                    </label>
                    <button class="ghost-button" type="submit">Cadastrar</button>
                </form>
            </article>

            <article class="simple-card">
                <div class="simple-card-head">
                    <h2>Novo assunto</h2>
                </div>
                <form method="post" class="simple-stack">
                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="create_subject">
                    <label>Disciplina
                        <select name="discipline_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($disciplines as $discipline): ?>
                                <option value="<?= h((string) $discipline['id']) ?>"><?= h($discipline['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Nome do assunto
                        <input type="text" name="subject_name" required>
                    </label>
                    <button class="ghost-button" type="submit">Cadastrar</button>
                </form>
            </article>
        </section>
    <?php endif; ?>
</section>

<?php render_footer(); ?>
