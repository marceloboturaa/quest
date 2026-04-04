<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/question_helpers.php';

require_login();

$user = current_user();

if (is_post()) {
    abort_if_invalid_csrf();

    $state = study_state();
    $questionId = study_current_question_id();
    $selectedLetter = strtoupper(trim((string) ($_POST['answer'] ?? '')));

    if ($state === null || $questionId === null) {
        flash('error', 'Inicie um estudo antes de responder.');
        redirect('study.php');
    }

    $options = study_load_options($questionId);
    $letters = study_option_letters($options);

    if ($selectedLetter === '' || !in_array($selectedLetter, $letters, true)) {
        flash('error', 'Escolha uma alternativa válida.');
        redirect('question.php');
    }

    try {
        $result = study_record_answer((int) $user['id'], $questionId, $selectedLetter);
        $_SESSION['study_last_answer_id'] = $result['answer_id'];
        flash($result['is_correct'] ? 'success' : 'error', $result['is_correct'] ? 'Resposta correta.' : 'Resposta incorreta.');
        redirect('result.php?id=' . $result['answer_id']);
    } catch (Throwable $exception) {
        flash('error', 'Não foi possível salvar sua resposta.');
        redirect('question.php');
    }
}

if (isset($_GET['next'])) {
    $state = study_state();

    if ($state === null) {
        flash('error', 'Inicie um estudo antes de avançar.');
        redirect('study.php');
    }

    study_advance_question();

    if (study_current_question_id() === null) {
        study_clear_state();
        flash('success', 'Treino concluído. Escolha novos filtros para continuar.');
        redirect('study.php');
    }

    redirect('question.php');
}

if (isset($_GET['goto'])) {
    $state = study_state();

    if ($state === null) {
        flash('error', 'Inicie um estudo antes de navegar pelas questões.');
        redirect('study.php');
    }

    $targetPage = (int) $_GET['goto'];
    $queue = is_array($state['queue'] ?? null) ? array_values($state['queue']) : [];
    $totalQueued = count($queue);

    if ($targetPage < 1 || $targetPage > $totalQueued) {
        flash('error', 'Questão inválida.');
        redirect('question.php');
    }

    $_SESSION['study_mode']['index'] = $targetPage - 1;
    redirect('question.php');
}

$state = study_state();

if ($state === null) {
    flash('error', 'Inicie um estudo para acessar a próxima questão.');
    redirect('study.php');
}

$questionId = study_current_question_id();

if ($questionId === null) {
    study_clear_state();
    flash('error', 'Não há mais questões na fila deste treino.');
    redirect('study.php');
}

$question = study_load_question($questionId);

    if ($question === null) {
        study_advance_question();
        if (study_current_question_id() === null) {
            study_clear_state();
            flash('error', 'Não há mais questões na fila deste treino.');
            redirect('study.php');
        }

        flash('error', 'Uma questão deste treino não está mais disponível. A próxima será carregada.');
        redirect('question.php');
    }

$options = study_load_options($questionId);

    if ($options === []) {
        study_advance_question();
        if (study_current_question_id() === null) {
            study_clear_state();
            flash('error', 'Não há mais questões na fila deste treino.');
            redirect('study.php');
        }

        flash('error', 'Esta questão não possui alternativas cadastradas. A próxima será carregada.');
        redirect('question.php');
    }

$progress = study_progress();
$state = study_state();
$queue = is_array($state['queue'] ?? null) ? array_values($state['queue']) : [];
$currentPage = (int) ($state['index'] ?? 0) + 1;
$totalPages = count($queue);
$paginationPages = [];

if ($totalPages > 0) {
    if ($totalPages <= 5) {
        $paginationPages = range(1, $totalPages);
    } elseif ($currentPage <= 3) {
        $paginationPages = [1, 2, 3, 4, 5];
    } elseif ($currentPage >= $totalPages - 2) {
        $paginationPages = range(max(1, $totalPages - 4), $totalPages);
    } else {
        $paginationPages = range($currentPage - 2, $currentPage + 2);
    }
}
$questionCode = trim((string) ($question['question_code'] ?? ''));
$lastAnswerId = (int) ($_SESSION['study_last_answer_id'] ?? 0);
$authorName = trim((string) ($question['author_name'] ?? ''));
$disciplineName = trim((string) ($question['discipline_name'] ?? ''));
$subjectName = trim((string) ($question['subject_name'] ?? ''));

$metaYear = '';
$metaProof = 'Pública';

render_header(
    'Questão ' . $progress['current'] . ' de ' . $progress['total'],
    'Responda a questão e veja a correção automática no resultado.',
    false,
    true
);
?>
<style>
    body {
        background: #ffffff;
    }

    body::before {
        display: none;
    }
</style>

<section class="question-reference-shell" data-question-answer-shell>
    <header class="question-reference-header">
        <div class="question-reference-topline">
            <span class="question-reference-index"><?= h((string) $progress['current']) ?></span>
            <div class="question-reference-breadcrumbs" aria-label="Disciplina e assunto">
                <span><?= h($disciplineName !== '' ? $disciplineName : 'Sem disciplina') ?></span>
                <span class="question-reference-breadcrumb-separator" aria-hidden="true">›</span>
                <span><?= h($subjectName !== '' ? $subjectName : 'Sem assunto') ?></span>
            </div>
        </div>

        <div class="question-reference-submeta">
            <span><strong>Ano:</strong> <?= h($metaYear !== '' ? $metaYear : '—') ?></span>
            <span><strong>Banca:</strong> —</span>
            <span><strong>Órgão:</strong> —</span>
            <span><strong>Autor:</strong> <?= h($authorName !== '' ? $authorName : '—') ?></span>
            <span><strong>Prova:</strong> <?= h($metaProof) ?></span>
            <span><strong>Código:</strong> <?= h($questionCode !== '' ? $questionCode : '—') ?></span>
        </div>
    </header>

    <div class="question-reference-divider" aria-hidden="true"></div>

    <article class="question-reference-body">
        <div class="question-reference-stem">
            <?= question_render_rich_content_html((string) $question['prompt']) ?>
        </div>

        <?php if (!empty($question['prompt_image_url'])): ?>
            <figure class="question-reference-image">
                <img src="<?= h((string) $question['prompt_image_url']) ?>" alt="Imagem do enunciado">
            </figure>
        <?php endif; ?>

        <form method="post" class="question-reference-form">
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">

            <div class="question-reference-options">
                <?php foreach ($options as $index => $option): ?>
                    <?php $letter = option_label((int) $index); ?>
                    <label class="question-reference-option">
                        <input type="radio" name="answer" value="<?= h($letter) ?>" required>
                        <span class="question-reference-option-letter"><?= h($letter) ?></span>
                        <span class="question-reference-option-text"><?= h((string) $option['option_text']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="question-reference-actions">
                <button class="question-reference-button" type="submit">Responder</button>
            </div>
        </form>

        <?php if ($paginationPages !== []): ?>
            <nav class="question-reference-pagination" aria-label="Navegação das questões">
                <?php foreach ($paginationPages as $pageNumber): ?>
                    <a
                        class="question-reference-pagination-item<?= $pageNumber === $currentPage ? ' is-active' : '' ?>"
                        href="question.php?goto=<?= h((string) $pageNumber) ?>"
                        aria-current="<?= $pageNumber === $currentPage ? 'page' : 'false' ?>"
                    >
                        <?= h((string) $pageNumber) ?>
                    </a>
                <?php endforeach; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <span class="question-reference-pagination-ellipsis" aria-hidden="true">...</span>
                    <a class="question-reference-pagination-item question-reference-pagination-next" href="question.php?goto=<?= h((string) ($currentPage + 1)) ?>" aria-label="Próxima questão">
                        <i class="fa-solid fa-caret-right" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </article>

    <nav class="question-reference-rail" aria-label="Atalhos da questão">
        <?php if ($lastAnswerId > 0): ?>
            <a class="question-reference-rail-item" href="result.php?id=<?= h((string) $lastAnswerId) ?>">
                <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
                <span>Gabarito Comentado</span>
            </a>
        <?php else: ?>
            <span class="question-reference-rail-item is-disabled" title="Disponível após responder">
                <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
                <span>Gabarito Comentado</span>
            </span>
        <?php endif; ?>
        <span class="question-reference-rail-item is-disabled" title="Em breve">
            <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
            <span>Aulas</span>
        </span>
        <span class="question-reference-rail-item is-disabled" title="Em breve">
            <i class="fa-regular fa-comments" aria-hidden="true"></i>
            <span>Comentários</span>
        </span>
        <span class="question-reference-rail-item is-disabled" title="Em breve">
            <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
            <span>Estatísticas</span>
        </span>
        <span class="question-reference-rail-item is-disabled" title="Em breve">
            <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
            <span>Cadernos</span>
        </span>
        <span class="question-reference-rail-item is-disabled" title="Em breve">
            <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
            <span>Criar anotações</span>
        </span>
        <span class="question-reference-rail-item is-disabled" title="Em manutenção">
            <i class="fa-regular fa-flag" aria-hidden="true"></i>
            <span>Notificar Erro</span>
        </span>
    </nav>
</section>

<?php render_footer(false); ?>
