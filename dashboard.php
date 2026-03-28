<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/dashboard_repository.php';

require_login();

$user = current_user();
$metrics = dashboard_metrics($user);
$recentQuestions = dashboard_recent_questions((int) $user['id'], can_manage_all_questions(), 5);
$recentExams = dashboard_recent_exams((int) $user['id'], can_manage_all_questions(), 5);
$publicQuestionsTotal = dashboard_public_questions_total();
$myQuestionsTotal = dashboard_user_questions_total((int) $user['id']);

render_header(
    'Início',
    'Escolha uma ação e acompanhe suas provas e questões sem excesso de informação.'
);
?>

<section class="simple-stack">
    <article class="simple-card">
        <div class="simple-card-head">
            <h2>Ações principais</h2>
        </div>
        <div class="simple-action-row">
            <a class="button" href="question-editor.php?new=1">Nova questão</a>
            <a class="button-secondary" href="exam-library.php">Provas</a>
            <a class="ghost-button" href="question-bank.php">Banco de questões</a>
            <a class="ghost-button" href="xerox.php">Xerox</a>
        </div>
    </article>

    <section class="simple-metric-grid">
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
        <article class="simple-metric-card">
            <small>Provas criadas</small>
            <strong><?= h((string) $metrics['exams']) ?></strong>
        </article>
    </section>

    <article class="simple-card">
        <div class="simple-card-head">
            <h2>Seu acesso</h2>
        </div>
        <div class="simple-inline-list">
            <span class="badge"><?= h(role_label($user['role'])) ?></span>
            <span class="badge"><?= can_view_xerox_queue() ? 'Xerox habilitado' : 'Acompanhamento do Xerox' ?></span>
            <?php if (can_manage_backups()): ?>
                <span class="badge">Backup disponível</span>
            <?php endif; ?>
            <?php if (can_manage_users()): ?>
                <span class="badge">Usuários</span>
            <?php endif; ?>
        </div>
    </article>

    <section class="simple-panel-grid">
        <article class="simple-card">
            <div class="simple-card-head">
                <h2>Provas recentes</h2>
                <a class="ghost-button" href="exam-library.php">Central de provas</a>
            </div>
            <?php if ($recentExams === []): ?>
                <div class="empty-state">
                    <h2>Nenhuma prova recente</h2>
                    <p>Crie a primeira prova para começar.</p>
                </div>
            <?php else: ?>
                <div class="simple-list">
                    <?php foreach ($recentExams as $exam): ?>
                        <article class="simple-list-item">
                            <div>
                                <strong><?= h((string) $exam['title']) ?></strong>
                                <p><?= h((string) $exam['total_questions']) ?> questões · <?= h(date('d/m/Y H:i', strtotime((string) $exam['created_at']))) ?></p>
                            </div>
                            <div class="simple-list-actions">
                                <span class="badge <?= h(xerox_status_badge_class((string) ($exam['xerox_status'] ?? 'not_sent'))) ?>">
                                    <?= h(xerox_status_label((string) ($exam['xerox_status'] ?? 'not_sent'))) ?>
                                </span>
                                <a class="ghost-button" href="exam-preview.php?id=<?= h((string) $exam['id']) ?>">Abrir</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <article class="simple-card">
            <div class="simple-card-head">
                <h2>Questões recentes</h2>
                <a class="ghost-button" href="question-bank.php">Abrir banco</a>
            </div>
            <?php if ($recentQuestions === []): ?>
                <div class="empty-state">
                    <h2>Nenhuma questão recente</h2>
                    <p>Crie ou importe uma questão para iniciar.</p>
                </div>
            <?php else: ?>
                <div class="simple-list">
                    <?php foreach ($recentQuestions as $question): ?>
                        <article class="simple-list-item">
                            <div>
                                <strong><?= h((string) $question['title']) ?></strong>
                                <p><?= h(question_type_label((string) $question['question_type'])) ?> · <?= h($question['discipline_name'] ?? 'Sem disciplina') ?></p>
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
</section>

<?php render_footer(); ?>
