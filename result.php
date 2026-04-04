<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();

$user = current_user();
$answerId = (int) ($_GET['id'] ?? ($_SESSION['study_last_answer_id'] ?? 0));

if ($answerId <= 0) {
    flash('error', 'Nenhum resultado disponível para exibir.');
    redirect('study.php');
}

$result = study_answer_details($answerId, (int) $user['id']);

if ($result === null) {
    flash('error', 'Não foi possível localizar esse resultado.');
    redirect('study.php');
}

$answer = $result['answer'];
$explanation = trim((string) ($answer['explanation'] ?? ''));
$progress = study_progress();
$hasStudyState = study_state() !== null;

render_header(
    $result['is_correct'] ? 'Resposta correta' : 'Resposta incorreta',
    'Veja a correção automática, a alternativa certa e a explicação da questão.'
);
?>

<section class="simple-stack">
    <article class="simple-card">
        <div class="simple-card-head">
            <h2><?= $result['is_correct'] ? 'Você acertou' : 'Você errou' ?></h2>
            <span class="badge <?= $result['is_correct'] ? 'badge-success' : 'badge-danger' ?>">
                <?= $result['is_correct'] ? 'Acerto' : 'Erro' ?>
            </span>
        </div>

        <p class="metric-copy">
            Questão: <strong><?= h((string) $answer['title']) ?></strong>
        </p>

        <div class="simple-inline-list">
            <span class="badge"><?= h((string) ($answer['discipline_name'] ?? 'Sem disciplina')) ?></span>
            <?php if (!empty($answer['subject_name'])): ?>
                <span class="badge"><?= h((string) $answer['subject_name']) ?></span>
            <?php endif; ?>
            <span class="badge">Dificuldade <?= h((string) $answer['difficulty']) ?></span>
            <span class="badge">Respondida em <?= h(datetime_label((string) $answer['data'])) ?></span>
        </div>

        <div class="result-summary-grid">
            <div class="result-summary-card">
                <small>Sua resposta</small>
                <strong><?= h((string) $result['selected_letter']) ?></strong>
                <p><?= h((string) ($result['selected_text'] ?? '')) ?></p>
            </div>
            <div class="result-summary-card">
                <small>Resposta certa</small>
                <strong><?= h((string) ($result['correct_letter'] ?? '-')) ?></strong>
                <p><?= h((string) ($result['correct_text'] ?? '')) ?></p>
            </div>
        </div>
    </article>

    <article class="simple-card">
        <div class="simple-card-head">
            <h2>Explicação</h2>
        </div>

        <?php if ($explanation !== ''): ?>
            <div class="helper-box">
                <p><?= nl2br(h($explanation)) ?></p>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <h2>Sem explicação cadastrada</h2>
                <p>Você ainda pode continuar treinando com a correção automática.</p>
            </div>
        <?php endif; ?>
    </article>

    <article class="simple-card">
        <div class="simple-card-head">
            <h2>Próximo passo</h2>
        </div>

        <div class="simple-action-row">
            <?php if ($hasStudyState): ?>
                <a class="button" href="question.php?next=1">
                    <?= $progress['current'] < $progress['total'] ? 'Próxima questão' : 'Finalizar treino' ?>
                </a>
            <?php else: ?>
                <a class="button" href="study.php">Novo estudo</a>
            <?php endif; ?>
            <a class="ghost-button" href="dashboard.php">Voltar ao dashboard</a>
        </div>
    </article>
</section>

<?php render_footer(); ?>
