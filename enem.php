<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/enem_api.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];

if (is_post()) {
    abort_if_invalid_csrf();

    if ((string) ($_POST['action'] ?? '') === 'import_enem_question') {
        $year = (int) ($_POST['year'] ?? 0);
        $index = (int) ($_POST['index'] ?? 0);
        $language = trim((string) ($_POST['language'] ?? ''));

        try {
            $import = enem_import_question($userId, $year, $index, $language !== '' ? $language : null);
            flash($import['created'] ? 'success' : 'info', $import['created'] ? 'Questao importada da API ENEM.' : 'Essa questao ja estava no seu banco.');
            redirect('question-editor.php?edit=' . (int) $import['id']);
        } catch (Throwable $throwable) {
            flash('error', 'Falha ao importar a questao do ENEM: ' . $throwable->getMessage());
            redirect('enem.php?' . http_build_query(array_filter([
                'year' => $year > 0 ? $year : null,
                'language' => $language !== '' ? $language : null,
            ])));
        }
    }
}

$apiError = null;
$exams = [];
$questionsPayload = [
    'metadata' => [
        'limit' => 10,
        'offset' => 0,
        'total' => 0,
        'hasMore' => false,
    ],
    'questions' => [],
];

try {
    $exams = enem_fetch_exams();
} catch (Throwable $throwable) {
    $apiError = $throwable->getMessage();
}

$selectedYear = isset($_GET['year']) ? (int) $_GET['year'] : 0;
$currentExam = null;

if ($exams !== []) {
    if ($selectedYear <= 0) {
        $selectedYear = (int) ($exams[0]['year'] ?? 0);
    }

    foreach ($exams as $exam) {
        if ((int) ($exam['year'] ?? 0) === $selectedYear) {
            $currentExam = $exam;
            break;
        }
    }

    if ($currentExam === null) {
        $currentExam = $exams[0];
        $selectedYear = (int) ($currentExam['year'] ?? 0);
    }
}

$languageOptions = is_array($currentExam['languages'] ?? null) ? $currentExam['languages'] : [];
$selectedLanguage = trim((string) ($_GET['language'] ?? ''));
$validLanguageValues = array_map(
    static fn(array $language): string => isset($language['value']) ? (string) $language['value'] : '',
    $languageOptions
);

if ($selectedLanguage !== '' && !in_array($selectedLanguage, $validLanguageValues, true)) {
    $selectedLanguage = '';
}

$limit = (int) ($_GET['limit'] ?? 10);

if (!in_array($limit, [5, 10, 20], true)) {
    $limit = 10;
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

if ($currentExam !== null && $apiError === null) {
    try {
        $questionsPayload = enem_fetch_questions(
            $selectedYear,
            $limit,
            $offset,
            $selectedLanguage !== '' ? $selectedLanguage : null
        );
    } catch (Throwable $throwable) {
        $apiError = $throwable->getMessage();
    }
}

$questions = is_array($questionsPayload['questions'] ?? null) ? $questionsPayload['questions'] : [];
$metadata = is_array($questionsPayload['metadata'] ?? null) ? $questionsPayload['metadata'] : [];
$totalQuestions = (int) ($metadata['total'] ?? 0);
$totalPages = max(1, (int) ceil($totalQuestions / max(1, $limit)));
$previousPage = max(1, $page - 1);
$nextPage = min($totalPages, $page + 1);
$disciplineLabels = [];

foreach ((array) ($currentExam['disciplines'] ?? []) as $discipline) {
    if (!is_array($discipline) || empty($discipline['label'])) {
        continue;
    }

    $disciplineLabels[] = enem_normalize_spaces((string) $discipline['label']);
}

function enem_page_query(array $overrides = []): string
{
    $params = [
        'year' => isset($_GET['year']) ? (int) $_GET['year'] : null,
        'language' => trim((string) ($_GET['language'] ?? '')),
        'limit' => isset($_GET['limit']) ? (int) $_GET['limit'] : 10,
        'page' => isset($_GET['page']) ? (int) $_GET['page'] : 1,
    ];

    foreach ($overrides as $key => $value) {
        $params[$key] = $value;
    }

    return http_build_query(array_filter(
        $params,
        static fn(mixed $value): bool => $value !== null && $value !== '' && $value !== 0
    ));
}

render_header(
    'API ENEM',
    'Navegue pelas provas oficiais do ENEM e importe questoes diretamente para o banco interno.'
);
?>

<section class="split-card">
    <section>
        <h2>Catalogo ENEM</h2>
        <p class="helper-text">Integracao server-side com cache local para respeitar o limite da API e acelerar a navegacao.</p>

        <form method="get" class="form-grid two-columns">
            <label>
                Ano da prova
                <select name="year" <?= $exams === [] ? 'disabled' : '' ?>>
                    <?php foreach ($exams as $exam): ?>
                        <option value="<?= h((string) $exam['year']) ?>" <?= $selectedYear === (int) $exam['year'] ? 'selected' : '' ?>>
                            <?= h((string) $exam['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Idioma
                <select name="language" <?= $languageOptions === [] ? 'disabled' : '' ?>>
                    <option value="">Todos</option>
                    <?php foreach ($languageOptions as $language): ?>
                        <option value="<?= h((string) $language['value']) ?>" <?= $selectedLanguage === (string) $language['value'] ? 'selected' : '' ?>>
                            <?= h(enem_normalize_spaces((string) $language['label'])) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Questoes por pagina
                <select name="limit">
                    <?php foreach ([5, 10, 20] as $limitOption): ?>
                        <option value="<?= h((string) $limitOption) ?>" <?= $limit === $limitOption ? 'selected' : '' ?>>
                            <?= h((string) $limitOption) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label>
                Pagina
                <input type="number" name="page" min="1" max="<?= h((string) $totalPages) ?>" value="<?= h((string) $page) ?>">
            </label>

            <div class="form-actions">
                <button class="button" type="submit">Atualizar lista</button>
                <a class="ghost-button" href="enem.php">Limpar filtros</a>
            </div>
        </form>
    </section>

    <section>
        <h2>Leitura rapida</h2>
        <div class="kicker-grid">
            <div class="kicker-card">
                <strong>Provas encontradas</strong>
                <p><?= h((string) count($exams)) ?> anos disponiveis na API.</p>
            </div>

            <div class="kicker-card">
                <strong>Volume atual</strong>
                <p><?= h((string) $totalQuestions) ?> questoes no recorte selecionado.</p>
            </div>

            <div class="kicker-card">
                <strong>Importacao</strong>
                <p>Cada questao entra no seu banco como multipla escolha privada e pode ser editada em seguida.</p>
            </div>
        </div>
    </section>
</section>

<?php if ($currentExam !== null): ?>
    <section class="info-grid">
        <article class="panel">
            <h2>Disciplinas da prova</h2>
            <div class="form-actions">
                <?php foreach ($disciplineLabels as $label): ?>
                    <span class="badge"><?= h($label) ?></span>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="panel">
            <h2>Fonte</h2>
            <p>Dados oficiais comunitarios servidos pela API ENEM. Requisicoes sem cache da API possuem limite de 1 por segundo.</p>
            <div class="form-actions">
                <a class="button-secondary" href="https://enem.dev/" target="_blank" rel="noreferrer">Abrir site</a>
                <a class="ghost-button" href="https://docs.enem.dev/api-reference/provas/listar-provas" target="_blank" rel="noreferrer">Ver docs</a>
            </div>
        </article>
    </section>
<?php endif; ?>

<?php if ($apiError !== null): ?>
    <div class="empty-state">
        <h2>Falha ao carregar a API ENEM</h2>
        <p><?= h($apiError) ?></p>
    </div>
<?php elseif ($questions === []): ?>
    <div class="empty-state">
        <h2>Nenhuma questao encontrada</h2>
        <p>Altere o ano, o idioma ou a paginacao para consultar outro recorte.</p>
    </div>
<?php else: ?>
    <section class="question-list">
        <?php foreach ($questions as $question): ?>
            <?php
            $questionYear = (int) ($question['year'] ?? $selectedYear);
            $questionIndex = (int) ($question['index'] ?? 0);
            $questionLanguage = isset($question['language']) ? (string) $question['language'] : '';
            $questionDiscipline = isset($question['discipline']) ? (string) $question['discipline'] : '';
            $disciplineLabel = enem_resolve_discipline_name($questionDiscipline, $questionYear);
            $languageLabel = enem_resolve_language_label($questionLanguage !== '' ? $questionLanguage : null, $questionYear);
            $files = enem_string_list($question['files'] ?? []);
            $preview = enem_compose_prompt(is_array($question) ? $question : []);
            ?>
            <article class="question-card">
                <div class="question-meta">
                    <span class="badge">ENEM <?= h((string) $questionYear) ?></span>
                    <span class="badge">Q<?= h((string) $questionIndex) ?></span>
                    <span class="badge"><?= h($disciplineLabel) ?></span>
                    <?php if ($languageLabel !== null): ?>
                        <span class="badge"><?= h($languageLabel) ?></span>
                    <?php endif; ?>
                    <span>Pagina <?= h((string) $page) ?> de <?= h((string) $totalPages) ?></span>
                </div>

                <h3><?= h((string) ($question['title'] ?? 'Questao ENEM')) ?></h3>
                <p><?= nl2br(h($preview)) ?></p>

                <?php if ($files !== []): ?>
                    <ul class="mini-list">
                        <?php foreach ($files as $fileUrl): ?>
                            <li><a href="<?= h($fileUrl) ?>" target="_blank" rel="noreferrer">Abrir imagem da questao</a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (!empty($question['alternatives'])): ?>
                    <ul class="option-list">
                        <?php foreach ($question['alternatives'] as $alternative): ?>
                            <li class="<?= !empty($alternative['isCorrect']) ? 'correct' : '' ?>">
                                <?= h(enem_compose_alternative_text(is_array($alternative) ? $alternative : [])) ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <div class="question-actions">
                    <form method="post">
                        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="action" value="import_enem_question">
                        <input type="hidden" name="year" value="<?= h((string) $questionYear) ?>">
                        <input type="hidden" name="index" value="<?= h((string) $questionIndex) ?>">
                        <input type="hidden" name="language" value="<?= h($questionLanguage) ?>">
                        <button class="button" type="submit">Importar para questoes</button>
                    </form>

                    <a class="ghost-button" href="<?= h(enem_question_api_url($questionYear, $questionIndex, $questionLanguage !== '' ? $questionLanguage : null)) ?>" target="_blank" rel="noreferrer">
                        Abrir JSON
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="panel">
        <h2>Paginacao</h2>
        <div class="form-actions">
            <?php if ($page > 1): ?>
                <a class="ghost-button" href="enem.php?<?= h(enem_page_query(['year' => $selectedYear, 'page' => $previousPage])) ?>">Pagina anterior</a>
            <?php endif; ?>

            <span class="badge">Pagina <?= h((string) $page) ?> de <?= h((string) $totalPages) ?></span>

            <?php if ($page < $totalPages): ?>
                <a class="button-secondary" href="enem.php?<?= h(enem_page_query(['year' => $selectedYear, 'page' => $nextPage])) ?>">Proxima pagina</a>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php render_footer(); ?>
