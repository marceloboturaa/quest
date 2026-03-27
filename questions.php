<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/enem_api.php';
require_once __DIR__ . '/includes/question_helpers.php';
require_once __DIR__ . '/includes/question_repository.php';
require_once __DIR__ . '/includes/question_actions.php';
require_once __DIR__ . '/includes/public_sources.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];

if (is_post()) {
    abort_if_invalid_csrf();
    handle_question_request($userId);
}

$disciplines = question_disciplines();
$subjects = question_subjects();
[$edit, $editOptions] = question_edit_payload($userId, isset($_GET['edit']) ? (int) $_GET['edit'] : null);
$filters = question_filters($_GET);
[$questions, $questionOptions] = question_list($filters, $userId);
$authors = question_authors();
$officialSources = official_question_sources();
$enemFilters = [
    'year' => (int) ($_GET['enem_year'] ?? 0),
    'language' => trim((string) ($_GET['enem_language'] ?? '')),
    'limit' => max(1, min(20, (int) ($_GET['enem_limit'] ?? 5))),
    'offset' => max(0, (int) ($_GET['enem_offset'] ?? 0)),
];
$enemSearchActive = $enemFilters['year'] > 0;
$enemResults = null;
$enemError = null;
$enemRedirectQuery = http_build_query(array_filter([
    'enem_year' => $enemFilters['year'] > 0 ? $enemFilters['year'] : null,
    'enem_language' => $enemFilters['language'] !== '' ? $enemFilters['language'] : null,
    'enem_limit' => $enemSearchActive ? $enemFilters['limit'] : null,
    'enem_offset' => $enemSearchActive ? $enemFilters['offset'] : null,
], static fn(mixed $value): bool => $value !== null && $value !== ''));

if ($enemSearchActive) {
    try {
        $enemResults = enem_api_list_questions(
            $enemFilters['year'],
            $enemFilters['limit'],
            $enemFilters['offset'],
            $enemFilters['language'] !== '' ? $enemFilters['language'] : null
        );
    } catch (Throwable $exception) {
        $enemError = $exception->getMessage();
    }
}

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

$questionModalOpen = $edit !== null;
$draftExamTitle = trim((string) ($_GET['draft_exam_title'] ?? ''));
$draftExamInstructions = trim((string) ($_GET['draft_exam_instructions'] ?? ''));
$quickQuestions = array_slice($questions, 0, 4);
$selectedSourceKey = '';

foreach ($officialSources as $sourceKey => $source) {
    if (($edit['source_name'] ?? null) === $source['name'] && ($edit['source_url'] ?? null) === $source['url']) {
        $selectedSourceKey = $sourceKey;
        break;
    }
}

render_header(
    'Avaliacoes e questoes',
    'Pesquise, importe, cadastre e transforme questoes do seu banco em provas com um fluxo mais direto.'
);
?>

<section class="assessment-workspace">
    <div class="assessment-search-column">
        <article class="workspace-panel workspace-search-panel">
            <div class="workspace-panel-head">
                <div>
                    <p class="workspace-kicker">Ambiente de busca</p>
                    <h2>Pesquise ou cadastre questoes</h2>
                </div>
                <button class="button" type="button" data-open-question-modal>Criar nova questao</button>
            </div>

            <form method="get" class="workspace-filter-grid">
                <label>Pesquisar questoes
                    <input type="text" name="term" value="<?= h($filters['term']) ?>" placeholder="Titulo, enunciado, autor ou fonte">
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
                        <option value="multiple_choice" <?= $filters['question_type'] === 'multiple_choice' ? 'selected' : '' ?>>Multipla escolha</option>
                        <option value="discursive" <?= $filters['question_type'] === 'discursive' ? 'selected' : '' ?>>Discursiva</option>
                        <option value="drawing" <?= $filters['question_type'] === 'drawing' ? 'selected' : '' ?>>Desenho</option>
                        <option value="true_false" <?= $filters['question_type'] === 'true_false' ? 'selected' : '' ?>>Verdadeiro ou falso</option>
                    </select>
                </label>
                <label>Nivel
                    <select name="education_level">
                        <option value="">Todos</option>
                        <option value="fundamental" <?= $filters['education_level'] === 'fundamental' ? 'selected' : '' ?>>Fundamental</option>
                        <option value="medio" <?= $filters['education_level'] === 'medio' ? 'selected' : '' ?>>Medio</option>
                        <option value="tecnico" <?= $filters['education_level'] === 'tecnico' ? 'selected' : '' ?>>Tecnico</option>
                        <option value="superior" <?= $filters['education_level'] === 'superior' ? 'selected' : '' ?>>Superior</option>
                    </select>
                </label>
                <label>Visibilidade
                    <select name="visibility">
                        <option value="">Todas</option>
                        <option value="public" <?= $filters['visibility'] === 'public' ? 'selected' : '' ?>>Publicas</option>
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
                <div class="form-actions">
                    <button class="button" type="submit">Filtrar banco</button>
                    <a class="ghost-button" href="questions.php">Limpar</a>
                </div>
            </form>

            <div class="workspace-metric-row">
                <article class="workspace-metric-card"><span>Questoes privadas</span><strong><?= h((string) $questionMetrics['private']) ?></strong></article>
                <article class="workspace-metric-card"><span>Questoes publicas</span><strong><?= h((string) $questionMetrics['public']) ?></strong></article>
                <article class="workspace-metric-card"><span>Minhas questoes</span><strong><?= h((string) $questionMetrics['mine']) ?></strong></article>
                <article class="workspace-metric-card"><span>Resultados atuais</span><strong><?= h((string) count($questions)) ?></strong></article>
            </div>
        </article>

        <article class="workspace-panel workspace-enem-panel">
            <div class="workspace-panel-head">
                <div>
                    <p class="workspace-kicker">Fonte externa</p>
                    <h2>Importar da API ENEM</h2>
                </div>
                <a class="ghost-button" href="enem.php">Abrir catalogo completo</a>
            </div>

            <form method="get" class="workspace-filter-grid workspace-filter-grid-tight">
                <label>Ano da prova
                    <input type="number" name="enem_year" min="2009" max="2100" value="<?= $enemFilters['year'] > 0 ? h((string) $enemFilters['year']) : '' ?>" placeholder="Ex.: 2023">
                </label>
                <label>Idioma
                    <select name="enem_language">
                        <option value="">Todos</option>
                        <?php foreach (enem_api_supported_languages() as $languageValue => $languageLabel): ?>
                            <option value="<?= h($languageValue) ?>" <?= $enemFilters['language'] === $languageValue ? 'selected' : '' ?>><?= h($languageLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Quantidade
                    <select name="enem_limit">
                        <?php foreach ([5, 10, 15, 20] as $limitOption): ?>
                            <option value="<?= h((string) $limitOption) ?>" <?= $enemFilters['limit'] === $limitOption ? 'selected' : '' ?>><?= h((string) $limitOption) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Offset
                    <input type="number" name="enem_offset" min="0" value="<?= h((string) $enemFilters['offset']) ?>">
                </label>
                <div class="form-actions">
                    <button class="button-secondary" type="submit">Buscar no ENEM</button>
                    <a class="ghost-button" href="questions.php">Limpar busca externa</a>
                </div>
            </form>

            <?php if ($enemError !== null): ?>
                <div class="flash flash-error"><?= h($enemError) ?></div>
            <?php elseif ($enemResults !== null): ?>
                <?php
                    $enemMetadata = $enemResults['metadata'] ?? [];
                    $enemQuestions = is_array($enemResults['questions'] ?? null) ? $enemResults['questions'] : [];
                    $prevOffset = max(0, $enemFilters['offset'] - $enemFilters['limit']);
                    $nextOffset = $enemFilters['offset'] + $enemFilters['limit'];
                    $prevQuery = http_build_query(array_filter([
                        'enem_year' => $enemFilters['year'],
                        'enem_language' => $enemFilters['language'] !== '' ? $enemFilters['language'] : null,
                        'enem_limit' => $enemFilters['limit'],
                        'enem_offset' => $prevOffset,
                    ], static fn(mixed $value): bool => $value !== null && $value !== ''));
                    $nextQuery = http_build_query(array_filter([
                        'enem_year' => $enemFilters['year'],
                        'enem_language' => $enemFilters['language'] !== '' ? $enemFilters['language'] : null,
                        'enem_limit' => $enemFilters['limit'],
                        'enem_offset' => $nextOffset,
                    ], static fn(mixed $value): bool => $value !== null && $value !== ''));
                ?>
                <div class="workspace-inline-meta">
                    <span class="badge">Total remoto: <?= h((string) ($enemMetadata['total'] ?? 0)) ?></span>
                    <span class="badge">Limite: <?= h((string) ($enemMetadata['limit'] ?? $enemFilters['limit'])) ?></span>
                    <span class="badge">Offset: <?= h((string) ($enemMetadata['offset'] ?? $enemFilters['offset'])) ?></span>
                </div>

                <?php if ($enemQuestions === []): ?>
                    <div class="empty-state">
                        <h2>Nenhuma questao encontrada</h2>
                        <p>Ajuste o ano, o idioma ou a paginacao para tentar outro recorte.</p>
                    </div>
                <?php else: ?>
                    <div class="workspace-quick-list">
                        <?php foreach ($enemQuestions as $enemQuestion): ?>
                            <?php
                                $previewText = enem_api_join_prompt($enemQuestion);
                                $previewLength = function_exists('mb_strlen') ? mb_strlen($previewText) : strlen($previewText);
                                $previewText = (function_exists('mb_substr') ? mb_substr($previewText, 0, 240) : substr($previewText, 0, 240))
                                    . ($previewLength > 240 ? '...' : '');
                            ?>
                            <article class="workspace-quick-item">
                                <div class="workspace-inline-meta">
                                    <span class="badge"><?= h((string) ($enemQuestion['year'] ?? $enemFilters['year'])) ?></span>
                                    <span class="badge"><?= h(enem_api_discipline_name($enemQuestion['discipline'] ?? null)) ?></span>
                                    <?php if (!empty($enemQuestion['language'])): ?>
                                        <span class="badge"><?= h((string) $enemQuestion['language']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <strong><?= h((string) ($enemQuestion['title'] ?? 'Questao ENEM')) ?></strong>
                                <p><?= nl2br(h($previewText)) ?></p>
                                <div class="form-actions">
                                    <form method="post">
                                        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="import_enem_question">
                                        <input type="hidden" name="enem_year" value="<?= h((string) ($enemQuestion['year'] ?? $enemFilters['year'])) ?>">
                                        <input type="hidden" name="enem_index" value="<?= h((string) ($enemQuestion['index'] ?? 0)) ?>">
                                        <input type="hidden" name="enem_language" value="<?= h((string) ($enemQuestion['language'] ?? '')) ?>">
                                        <input type="hidden" name="redirect_query" value="<?= h($enemRedirectQuery) ?>">
                                        <button class="button-secondary" type="submit">Importar</button>
                                    </form>
                                    <a class="ghost-button" href="<?= h(enem_api_source_url((int) ($enemQuestion['year'] ?? $enemFilters['year']), (int) ($enemQuestion['index'] ?? 0), !empty($enemQuestion['language']) ? (string) $enemQuestion['language'] : null)) ?>" target="_blank" rel="noreferrer">Abrir fonte</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-actions">
                        <?php if ($enemFilters['offset'] > 0): ?>
                            <a class="ghost-button" href="questions.php?<?= h($prevQuery) ?>">Pagina anterior</a>
                        <?php endif; ?>
                        <?php if (!empty($enemMetadata['hasMore'])): ?>
                            <a class="button-secondary" href="questions.php?<?= h($nextQuery) ?>">Proxima pagina</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </article>
    </div>

    <aside class="assessment-builder-column">
        <article class="workspace-panel workspace-builder-panel">
            <div class="workspace-panel-head">
                <div>
                    <p class="workspace-kicker">Montagem</p>
                    <h2>Monte a sua avaliacao</h2>
                </div>
                <a class="ghost-button" href="exams.php">Abrir provas</a>
            </div>

            <form method="get" action="exams.php" class="form-grid">
                <label>Nome da sua prova
                    <input type="text" name="draft_title" value="<?= h($draftExamTitle) ?>" placeholder="Ex.: Simulado de Matematica - 9o ano">
                </label>
                <label>Instrucoes iniciais
                    <textarea name="draft_instructions" placeholder="Observacoes para iniciar a montagem da avaliacao"><?= h($draftExamInstructions) ?></textarea>
                </label>
                <div class="workspace-builder-actions">
                    <button class="button" type="submit">Montar avaliacao</button>
                    <a class="button-secondary" href="exams.php">Pesquisar banco</a>
                </div>
            </form>

            <div class="workspace-builder-note">
                <strong>Como isso encaixa no Quest</strong>
                <p>Use esta pagina para filtrar ou importar questoes. Depois avance para a montagem da prova com o titulo ja preenchido e selecione os itens finais.</p>
            </div>

            <?php if ($quickQuestions !== []): ?>
                <div class="workspace-quick-links">
                    <h3>Atalhos rapidos</h3>
                    <?php foreach ($quickQuestions as $quickQuestion): ?>
                        <a class="workspace-question-link" href="exams.php?question_id=<?= h((string) $quickQuestion['id']) ?>">
                            <strong><?= h($quickQuestion['title']) ?></strong>
                            <small><?= h($quickQuestion['discipline_name'] ?? 'Sem disciplina') ?> | <?= h(question_type_label($quickQuestion['question_type'])) ?></small>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <article class="workspace-panel workspace-guidance-panel">
            <p class="workspace-kicker">Regras do ambiente</p>
            <ul class="mini-list">
                <li>Questoes privadas ficam restritas ao autor.</li>
                <li>Questoes publicas podem ser visualizadas, clonadas e usadas por outros usuarios.</li>
                <li>Itens importados do ENEM entram como questoes privadas prontas para ajuste.</li>
                <li>A montagem da prova continua centralizada em <code>exams.php</code>.</li>
            </ul>
        </article>
    </aside>
</section>

<section class="workspace-results-shell">
    <div class="workspace-results-head">
        <div>
            <p class="workspace-kicker">Banco interno</p>
            <h2>Questoes cadastradas</h2>
        </div>
        <div class="form-actions">
            <button class="button" type="button" data-open-question-modal>Criar nova questao</button>
            <a class="ghost-button" href="exams.php">Ir para provas</a>
        </div>
    </div>

    <?php if ($questions === []): ?>
        <div class="empty-state">
            <h2>Nenhuma questao encontrada</h2>
            <p>Crie a primeira questao, ajuste os filtros ou importe itens do ENEM.</p>
        </div>
    <?php else: ?>
        <section class="workspace-question-grid">
            <?php foreach ($questions as $question): ?>
                <article class="question-card question-card-workspace">
                    <span class="question-card-ribbon question-card-ribbon-<?= h($question['visibility']) ?>"><?= h(visibility_label($question['visibility'])) ?></span>
                    <div class="question-meta">
                        <span class="badge"><?= h(question_type_label($question['question_type'])) ?></span>
                        <span class="badge"><?= h(education_level_label($question['education_level'])) ?></span>
                        <span class="badge"><?= h($question['discipline_name'] ?? 'Sem disciplina') ?></span>
                        <span class="badge"><?= h($question['subject_name'] ?? 'Sem assunto') ?></span>
                    </div>
                    <h3><?= h($question['title']) ?></h3>
                    <p><?= nl2br(h($question['prompt'])) ?></p>
                    <?php if (!empty($question['prompt_image_url'])): ?>
                        <p><a href="<?= h($question['prompt_image_url']) ?>" target="_blank" rel="noreferrer">Abrir imagem do enunciado</a></p>
                    <?php endif; ?>
                    <?php if (!empty($question['source_url'])): ?>
                        <p class="helper-text">Origem oficial: <a href="<?= h($question['source_url']) ?>" target="_blank" rel="noreferrer"><?= h($question['source_name']) ?></a><?php if (!empty($question['source_reference'])): ?> | <?= h($question['source_reference']) ?><?php endif; ?></p>
                    <?php elseif (!empty($question['author_name'])): ?>
                        <p class="helper-text">Autor: <?= h($question['author_name']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($question['based_on_author_name'])): ?>
                        <p class="helper-text">Baseada na questao de <?= h($question['based_on_author_name']) ?>.</p>
                    <?php endif; ?>
                    <?php if ($question['question_type'] === 'multiple_choice'): ?>
                        <ul class="option-list">
                            <?php foreach ($questionOptions[(int) $question['id']] ?? [] as $option): ?>
                                <li class="<?= (int) $option['is_correct'] === 1 ? 'correct' : '' ?>"><?= h($option['option_text']) ?><?php if ((int) $option['is_correct'] === 1): ?> <strong>- correta</strong><?php endif; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php elseif ($question['question_type'] === 'discursive'): ?>
                        <p><strong>Linhas:</strong> <?= h((string) ($question['response_lines'] ?? 5)) ?></p>
                        <?php if (!empty($question['discursive_answer'])): ?><p><strong>Resposta de referencia:</strong> <?= nl2br(h($question['discursive_answer'])) ?></p><?php endif; ?>
                    <?php elseif ($question['question_type'] === 'true_false'): ?>
                        <p><strong>Resposta correta:</strong> <?= (int) $question['true_false_answer'] === 1 ? 'Verdadeiro' : 'Falso' ?></p>
                    <?php else: ?>
                        <p><strong>Espaco:</strong> <?= h(drawing_size_label($question['drawing_size'], isset($question['drawing_height_px']) ? (int) $question['drawing_height_px'] : null)) ?></p>
                    <?php endif; ?>
                    <div class="question-card-footer">
                        <span>Uso em provas: <?= h((string) $question['usage_count']) ?></span>
                        <div class="question-actions">
                            <form method="post">
                                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="toggle_favorite">
                                <input type="hidden" name="question_id" value="<?= h((string) $question['id']) ?>">
                                <button class="<?= (int) $question['is_favorite'] === 1 ? 'button-secondary' : 'ghost-button' ?>" type="submit"><?= (int) $question['is_favorite'] === 1 ? 'Favoritada' : 'Favoritar' ?></button>
                            </form>
                            <a class="ghost-button" href="exams.php?question_id=<?= h((string) $question['id']) ?>">Usar em prova</a>
                            <?php if ($question['visibility'] === 'public' || (int) $question['author_id'] === $userId): ?>
                                <form method="post">
                                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="clone_question">
                                    <input type="hidden" name="question_id" value="<?= h((string) $question['id']) ?>">
                                    <button class="ghost-button" type="submit">Clonar</button>
                                </form>
                            <?php endif; ?>
                            <?php if ((int) $question['author_id'] === $userId): ?>
                                <a class="button-secondary" href="questions.php?edit=<?= h((string) $question['id']) ?>">Editar</a>
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
</section>

<?php if (can_manage_catalogs()): ?>
    <section class="catalog-grid">
        <article class="panel">
            <h2>Nova disciplina</h2>
            <form method="post" class="form-grid">
                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="create_discipline">
                <label>Nome da disciplina<input type="text" name="discipline_name" required></label>
                <button class="button-secondary" type="submit">Cadastrar disciplina</button>
            </form>
        </article>

        <article class="panel">
            <h2>Novo assunto</h2>
            <form method="post" class="form-grid">
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
                <label>Nome do assunto<input type="text" name="subject_name" required></label>
                <button class="button-secondary" type="submit">Cadastrar assunto</button>
            </form>
        </article>

        <?php if ($disciplines !== []): ?>
            <article class="panel">
                <h2>Disciplinas cadastradas</h2>
                <div class="workspace-quick-list">
                    <?php foreach ($disciplines as $discipline): ?>
                        <article class="workspace-quick-item">
                            <form method="post" class="form-grid">
                                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="update_discipline">
                                <input type="hidden" name="discipline_id" value="<?= h((string) $discipline['id']) ?>">
                                <label>Nome da disciplina<input type="text" name="discipline_name" value="<?= h($discipline['name']) ?>" required></label>
                                <div class="form-actions">
                                    <button class="button-secondary" type="submit">Salvar</button>
                                </div>
                            </form>
                            <form method="post" class="inline-actions">
                                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_discipline">
                                <input type="hidden" name="discipline_id" value="<?= h((string) $discipline['id']) ?>">
                                <button class="button-danger" type="submit" onclick="return confirm('Excluir esta disciplina?')">Excluir</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>

        <?php if ($subjects !== []): ?>
            <article class="panel">
                <h2>Assuntos cadastrados</h2>
                <div class="workspace-quick-list">
                    <?php foreach ($subjects as $subject): ?>
                        <article class="workspace-quick-item">
                            <form method="post" class="form-grid">
                                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="update_subject">
                                <input type="hidden" name="subject_id" value="<?= h((string) $subject['id']) ?>">
                                <label>Disciplina
                                    <select name="discipline_id" required>
                                        <?php foreach ($disciplines as $discipline): ?>
                                            <option value="<?= h((string) $discipline['id']) ?>" <?= (int) $subject['discipline_id'] === (int) $discipline['id'] ? 'selected' : '' ?>><?= h($discipline['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </label>
                                <label>Nome do assunto<input type="text" name="subject_name" value="<?= h($subject['name']) ?>" required></label>
                                <div class="form-actions">
                                    <button class="button-secondary" type="submit">Salvar</button>
                                </div>
                            </form>
                            <form method="post" class="inline-actions">
                                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_subject">
                                <input type="hidden" name="subject_id" value="<?= h((string) $subject['id']) ?>">
                                <button class="button-danger" type="submit" onclick="return confirm('Excluir este assunto?')">Excluir</button>
                            </form>
                        </article>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endif; ?>
    </section>
<?php endif; ?>

<div class="question-modal<?= $questionModalOpen ? ' is-open' : '' ?>" data-question-modal>
    <div class="question-modal-backdrop" data-close-question-modal></div>
    <div class="question-modal-dialog">
        <div class="question-modal-header">
            <div>
                <p class="workspace-kicker"><?= $edit ? 'Edicao guiada' : 'Novo cadastro' ?></p>
                <h2><?= $edit ? 'Editar questao' : 'Nova questao' ?></h2>
            </div>
            <?php if ($edit): ?>
                <a class="question-modal-close" href="questions.php" aria-label="Fechar formulario">×</a>
            <?php else: ?>
                <button class="question-modal-close" type="button" data-close-question-modal aria-label="Fechar formulario">×</button>
            <?php endif; ?>
        </div>

        <div class="question-modal-layout">
            <section class="question-modal-main">
                <form method="post" class="form-grid" data-question-form data-next-option-index="<?= h((string) count($editOptions)) ?>">
                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="<?= $edit ? 'update_question' : 'create_question' ?>">
                    <?php if ($edit): ?><input type="hidden" name="question_id" value="<?= h((string) $edit['id']) ?>"><?php endif; ?>
                    <label>Titulo<input type="text" name="title" required value="<?= h($edit['title'] ?? '') ?>"></label>
                    <label>Enunciado<textarea name="prompt" required><?= h($edit['prompt'] ?? '') ?></textarea></label>
                    <label>Imagem do enunciado (URL)<input type="url" name="prompt_image_url" value="<?= h($edit['prompt_image_url'] ?? '') ?>" placeholder="https://..."></label>
                    <div class="form-grid two-columns">
                        <label>Tipo
                            <?php $selectedType = $edit['question_type'] ?? 'multiple_choice'; ?>
                            <select name="question_type" required>
                                <option value="multiple_choice" <?= $selectedType === 'multiple_choice' ? 'selected' : '' ?>>Multipla escolha</option>
                                <option value="discursive" <?= $selectedType === 'discursive' ? 'selected' : '' ?>>Discursiva</option>
                                <option value="drawing" <?= $selectedType === 'drawing' ? 'selected' : '' ?>>Desenho / espaco livre</option>
                                <option value="true_false" <?= $selectedType === 'true_false' ? 'selected' : '' ?>>Verdadeiro ou falso</option>
                            </select>
                        </label>
                        <label>Visibilidade
                            <?php $selectedVisibility = $edit['visibility'] ?? 'private'; ?>
                            <select name="visibility" required>
                                <option value="private" <?= $selectedVisibility === 'private' ? 'selected' : '' ?>>Privada</option>
                                <option value="public" <?= $selectedVisibility === 'public' ? 'selected' : '' ?>>Publica</option>
                            </select>
                        </label>
                    </div>
                    <div class="form-grid two-columns">
                        <label>Disciplina
                            <?php $selectedDiscipline = (int) ($edit['discipline_id'] ?? 0); ?>
                            <select name="discipline_id" required data-discipline-select data-target="question-subject-select">
                                <option value="">Selecione</option>
                                <?php foreach ($disciplines as $discipline): ?>
                                    <option value="<?= h((string) $discipline['id']) ?>" <?= $selectedDiscipline === (int) $discipline['id'] ? 'selected' : '' ?>><?= h($discipline['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Assunto
                            <?php $selectedSubject = (int) ($edit['subject_id'] ?? 0); ?>
                            <select name="subject_id" id="question-subject-select" required data-subject-select>
                                <option value="">Selecione</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= h((string) $subject['id']) ?>" data-discipline-id="<?= h((string) $subject['discipline_id']) ?>" <?= $selectedSubject === (int) $subject['id'] ? 'selected' : '' ?>><?= h($subject['discipline_name'] . ' - ' . $subject['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    </div>
                    <div class="form-grid two-columns">
                        <label>Nivel de ensino
                            <?php $selectedLevel = $edit['education_level'] ?? 'medio'; ?>
                            <select name="education_level" required>
                                <option value="fundamental" <?= $selectedLevel === 'fundamental' ? 'selected' : '' ?>>Ensino Fundamental</option>
                                <option value="medio" <?= $selectedLevel === 'medio' ? 'selected' : '' ?>>Ensino Medio</option>
                                <option value="tecnico" <?= $selectedLevel === 'tecnico' ? 'selected' : '' ?>>Tecnico</option>
                                <option value="superior" <?= $selectedLevel === 'superior' ? 'selected' : '' ?>>Superior</option>
                            </select>
                        </label>
                        <label>Dificuldade
                            <?php $selectedDifficulty = $edit['difficulty'] ?? 'medio'; ?>
                            <select name="difficulty" required>
                                <option value="facil" <?= $selectedDifficulty === 'facil' ? 'selected' : '' ?>>Facil</option>
                                <option value="medio" <?= $selectedDifficulty === 'medio' ? 'selected' : '' ?>>Medio</option>
                                <option value="dificil" <?= $selectedDifficulty === 'dificil' ? 'selected' : '' ?>>Dificil</option>
                            </select>
                        </label>
                    </div>
                    <div class="panel panel-nested">
                        <h3>Fonte publica oficial (opcional)</h3>
                        <div class="form-grid two-columns">
                            <label>Origem oficial
                                <select name="official_source_key">
                                    <option value="">Sem origem externa</option>
                                    <?php foreach ($officialSources as $sourceKey => $source): ?>
                                        <option value="<?= h($sourceKey) ?>" data-source-name="<?= h($source['name']) ?>" data-source-url="<?= h($source['url']) ?>" <?= $selectedSourceKey === $sourceKey ? 'selected' : '' ?>>
                                            <?= h($source['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>Referencia da fonte<input type="text" name="source_reference" value="<?= h($edit['source_reference'] ?? '') ?>" placeholder="Ex.: Caderno azul, questao 12"></label>
                        </div>
                    </div>
                    <div data-question-section="multiple_choice">
                        <label class="checkbox-row"><input type="checkbox" name="allow_multiple_correct" value="1" <?= !empty($edit['allow_multiple_correct']) ? 'checked' : '' ?>> Permitir multiplas corretas</label>
                        <div class="option-list-editor" data-options-container>
                            <?php foreach ($editOptions as $index => $option): ?>
                                <div class="option-editor-row">
                                    <strong><?= h(option_label($index)) ?></strong>
                                    <input type="text" name="options[<?= h((string) $index) ?>][text]" value="<?= h($option['text']) ?>" placeholder="Texto da alternativa">
                                    <label class="checkbox-row compact"><input type="checkbox" name="options[<?= h((string) $index) ?>][is_correct]" value="1" <?= !empty($option['is_correct']) ? 'checked' : '' ?>> Correta</label>
                                    <button class="ghost-button option-remove-button" type="button" data-remove-option>&minus;</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="form-actions"><button class="button-secondary" type="button" data-add-option>Adicionar alternativa</button></div>
                    </div>
                    <div class="hidden" data-question-section="discursive">
                        <div class="form-grid two-columns">
                            <label>Numero de linhas<input type="number" min="1" max="30" name="response_lines" value="<?= h((string) ($edit['response_lines'] ?? 5)) ?>"></label>
                            <label>Resposta de referencia (opcional)<textarea name="discursive_answer"><?= h($edit['discursive_answer'] ?? '') ?></textarea></label>
                        </div>
                    </div>
                    <div class="hidden" data-question-section="drawing">
                        <?php $selectedDrawing = $edit['drawing_size'] ?? 'medium'; ?>
                        <label>Altura do espaco
                            <select name="drawing_size" data-drawing-size-select>
                                <option value="small" <?= $selectedDrawing === 'small' ? 'selected' : '' ?>>Pequeno</option>
                                <option value="medium" <?= $selectedDrawing === 'medium' ? 'selected' : '' ?>>Medio</option>
                                <option value="large" <?= $selectedDrawing === 'large' ? 'selected' : '' ?>>Grande</option>
                                <option value="custom" <?= $selectedDrawing === 'custom' ? 'selected' : '' ?>>Customizado</option>
                            </select>
                        </label>
                        <label class="<?= $selectedDrawing === 'custom' ? '' : 'hidden' ?>" data-drawing-custom-field>
                            Altura customizada (px)
                            <input type="number" name="drawing_height_px" min="120" max="1200" step="10" value="<?= h((string) ($edit['drawing_height_px'] ?? 320)) ?>">
                        </label>
                    </div>
                    <div class="hidden" data-question-section="true_false">
                        <?php $selectedTrueFalseAnswer = array_key_exists('true_false_answer', (array) $edit) && $edit['true_false_answer'] !== null ? (int) $edit['true_false_answer'] : 1; ?>
                        <label>Resposta correta
                            <select name="true_false_answer">
                                <option value="1" <?= $selectedTrueFalseAnswer === 1 ? 'selected' : '' ?>>Verdadeiro</option>
                                <option value="0" <?= $selectedTrueFalseAnswer === 0 ? 'selected' : '' ?>>Falso</option>
                            </select>
                        </label>
                    </div>
                    <div class="form-actions">
                        <button class="button" type="submit"><?= $edit ? 'Salvar alteracoes' : 'Salvar questao' ?></button>
                        <?php if ($edit): ?><a class="ghost-button" href="questions.php">Cancelar edicao</a><?php endif; ?>
                    </div>
                </form>
            </section>

            <aside class="question-modal-side">
                <article class="panel panel-nested">
                    <h3>Orientacao rapida</h3>
                    <ul class="mini-list">
                        <li>Cadastre questoes privadas quando ainda estiver validando o item.</li>
                        <li>Publique apenas quando a redacao, o assunto e as alternativas estiverem revisados.</li>
                        <li>Itens importados do ENEM podem ser ajustados antes de entrar em provas.</li>
                    </ul>
                </article>
            </aside>
        </div>
    </div>
</div>

<template id="question-option-template">
    <div class="option-editor-row">
        <strong data-option-label>__LABEL__</strong>
        <input type="text" name="options[__INDEX__][text]" placeholder="Texto da alternativa">
        <label class="checkbox-row compact"><input type="checkbox" name="options[__INDEX__][is_correct]" value="1"> Correta</label>
        <button class="ghost-button option-remove-button" type="button" data-remove-option>&minus;</button>
    </div>
</template>

<?php render_footer(); ?>
