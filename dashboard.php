<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/dashboard_repository.php';

require_login();

$user = current_user();
$role = (string) ($user['role'] ?? '');

if ($role === 'aluno') {
    $summary = study_dashboard_summary((int) $user['id']);
    $history = study_dashboard_history((int) $user['id'], 8);

    render_header(
        'Dashboard do aluno',
        'Painel enxuto para continuar estudando e revisar o desempenho.'
    );
    ?>
    <section class="dashboard-minimal">
        <article class="dashboard-minimal-hero simple-card">
            <div class="dashboard-minimal-hero-copy">
                <p class="workspace-kicker">Modo de estudos</p>
                <h2>Continue no ritmo certo</h2>
                <p class="helper-text">Abra uma nova sessão, responda e acompanhe sua evolução sem distrações.</p>
            </div>
            <div class="dashboard-minimal-hero-actions">
                <a class="button" href="study.php">Iniciar estudo</a>
                <a class="ghost-button" href="profile.php">Meu painel</a>
            </div>
        </article>

        <section class="simple-metric-grid dashboard-minimal-metrics">
            <article class="simple-metric-card">
                <small>Total respondidas</small>
                <strong><?= h((string) $summary['total_answers']) ?></strong>
            </article>
            <article class="simple-metric-card">
                <small>Acertos</small>
                <strong><?= h((string) $summary['total_correct']) ?></strong>
            </article>
            <article class="simple-metric-card">
                <small>Taxa de acerto</small>
                <strong><?= h((string) $summary['accuracy']) ?>%</strong>
            </article>
        </section>

        <article class="simple-card dashboard-minimal-card">
            <div class="simple-card-head">
                <h2>Histórico recente</h2>
                <a class="ghost-button" href="study.php">Novo treino</a>
            </div>

            <?php if ($history === []): ?>
                <div class="empty-state">
                    <h2>Ainda sem respostas</h2>
                    <p>Inicie um treino para registrar seu histórico automaticamente.</p>
                </div>
            <?php else: ?>
                <div class="simple-list dashboard-minimal-list">
                    <?php foreach ($history as $row): ?>
                        <article class="simple-list-item">
                            <div>
                                <strong><?= h((string) $row['title']) ?></strong>
                                <p>
                                    <?= h((string) ($row['discipline_name'] ?? 'Sem disciplina')) ?>
                                    <?php if (!empty($row['subject_name'])): ?>
                                        · <?= h((string) $row['subject_name']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="simple-list-actions">
                                <span class="badge <?= (int) $row['correta'] === 1 ? 'badge-success' : 'badge-danger' ?>">
                                    <?= (int) $row['correta'] === 1 ? 'Acertou' : 'Errou' ?>
                                </span>
                                <small class="metric-copy"><?= h(datetime_label((string) $row['data'])) ?></small>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>
    </section>
    <?php
    render_footer();
    return;
}

$metrics = dashboard_metrics($user);
$recentQuestions = dashboard_recent_questions((int) $user['id'], can_manage_all_questions(), 5);
$publicQuestionsTotal = dashboard_public_questions_total();
$myQuestionsTotal = dashboard_user_questions_total((int) $user['id']);
$badgeList = [
    role_label($role),
];

if (can_view_xerox_queue()) {
    $badgeList[] = 'Xerox habilitado';
}

if (can_manage_backups()) {
    $badgeList[] = 'Backup';
}

if (can_manage_users()) {
    $badgeList[] = 'Usuários';
}

render_header(
    'Início',
    'Painel enxuto para acompanhar questões, estudar e produzir.'
);
?>

<section class="dashboard-minimal">
    <article class="dashboard-minimal-hero simple-card">
        <div class="dashboard-minimal-hero-copy">
            <p class="workspace-kicker">Painel principal</p>
            <h2>Menos ruído, mais ação</h2>
            <p class="helper-text">Use o atalho certo para criar, estudar ou abrir o banco sem perder tempo.</p>
        </div>
        <div class="dashboard-minimal-hero-actions">
            <a class="button" href="question-editor.php?new=1">Nova questão</a>
            <a class="ghost-button" href="question-bank.php">Banco</a>
            <a class="ghost-button" href="study.php">Modo Estudo</a>
        </div>
    </article>

    <section class="simple-metric-grid dashboard-minimal-metrics">
        <article class="simple-metric-card">
            <small>Total de questões</small>
            <strong><?= h((string) $metrics['questions']) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Suas questões</small>
            <strong><?= h((string) $myQuestionsTotal) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Questões públicas</small>
            <strong><?= h((string) $publicQuestionsTotal) ?></strong>
        </article>
    </section>

    <article class="simple-card dashboard-minimal-card">
        <div class="simple-card-head">
            <h2>Seu acesso</h2>
        </div>
        <div class="simple-inline-list dashboard-minimal-badges">
            <?php foreach ($badgeList as $badge): ?>
                <span class="badge"><?= h($badge) ?></span>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="simple-card dashboard-minimal-card">
        <div class="simple-card-head">
            <h2>Questões recentes</h2>
            <a class="ghost-button" href="question-bank.php">Abrir banco</a>
        </div>
        <?php if ($recentQuestions === []): ?>
            <div class="empty-state">
                <h2>Nenhuma questão recente</h2>
                <p>Crie ou importe uma questão para começar.</p>
            </div>
        <?php else: ?>
            <div class="simple-list dashboard-minimal-list">
                <?php foreach ($recentQuestions as $question): ?>
                    <article class="simple-list-item">
                        <div>
                            <strong><?= h((string) $question['title']) ?></strong>
                            <p><?= h(question_type_label((string) $question['question_type'])) ?> · <?= h((string) ($question['discipline_name'] ?? 'Sem disciplina')) ?></p>
                        </div>
                        <div class="simple-list-actions">
                            <span class="badge"><?= h(visibility_label((string) $question['visibility'])) ?></span>
                            <a class="ghost-button" href="question-editor.php?edit=<?= h((string) $question['id']) ?>">Abrir</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>

<?php render_footer(); ?>
