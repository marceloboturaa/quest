<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/dashboard_repository.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$questionMetrics = [
    'private' => 0,
    'public' => 0,
    'mine' => 0,
];
$examCount = 0;
$accessibleQuestionCount = 0;
$xeroxPendingCount = 0;
$recentActivities = [];

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

try {
    $statement = db()->prepare('SELECT COUNT(*) FROM exams WHERE user_id = :user_id');
    $statement->execute(['user_id' => $userId]);
    $examCount = (int) $statement->fetchColumn();
} catch (Throwable) {
    $examCount = 0;
}

try {
    $statement = db()->prepare('SELECT COUNT(*) FROM questions WHERE visibility = "public" OR author_id = :author_id');
    $statement->execute(['author_id' => $userId]);
    $accessibleQuestionCount = (int) $statement->fetchColumn();
} catch (Throwable) {
    $accessibleQuestionCount = $questionMetrics['public'] + $questionMetrics['mine'];
}

try {
    $statement = db()->prepare(
        'SELECT COUNT(*) FROM exams
         WHERE user_id = :user_id
           AND xerox_status IN ("sent", "in_progress")'
    );
    $statement->execute(['user_id' => $userId]);
    $xeroxPendingCount = (int) $statement->fetchColumn();
} catch (Throwable) {
    $xeroxPendingCount = 0;
}

foreach (dashboard_recent_questions($userId, false, 3) as $item) {
    $recentActivities[] = [
        'type' => 'question',
        'title' => (string) ($item['title'] ?? 'Questao sem titulo'),
        'meta' => trim((string) (($item['discipline_name'] ?? '') !== '' ? $item['discipline_name'] . ' · ' : '') . question_type_label((string) ($item['question_type'] ?? 'multiple_choice'))),
        'date' => datetime_label($item['created_at'] ?? null),
        'timestamp' => strtotime((string) ($item['created_at'] ?? '')) ?: 0,
        'link' => (int) ($item['id'] ?? 0) > 0 ? 'question-editor.php?edit=' . (int) $item['id'] : 'question-bank.php',
    ];
}

foreach (dashboard_recent_exams($userId, false, 3) as $item) {
    $recentActivities[] = [
        'type' => 'exam',
        'title' => (string) ($item['title'] ?? 'Prova sem titulo'),
        'meta' => ((int) ($item['total_questions'] ?? 0)) . ' questoes',
        'date' => datetime_label($item['created_at'] ?? null),
        'timestamp' => strtotime((string) ($item['created_at'] ?? '')) ?: 0,
        'link' => (int) ($item['id'] ?? 0) > 0 ? 'exam-preview.php?id=' . (int) $item['id'] : 'exam-create.php',
    ];
}

usort(
    $recentActivities,
    static fn(array $left, array $right): int => ($right['timestamp'] <=> $left['timestamp'])
);
$recentActivities = array_slice($recentActivities, 0, 5);

render_header(
    'Questões',
    'Escolha o que deseja fazer: criar, buscar no banco ou montar uma prova.'
);
?>

<section class="simple-stack">
    <article class="simple-card">
        <div class="simple-card-head">
            <div>
                <h2>O que deseja fazer?</h2>
                <p class="helper-text">Escolha uma opção para continuar.</p>
            </div>
        </div>

        <div class="simple-decision-grid">
            <a class="simple-action-card" href="question-editor.php?new=1">
                <span class="simple-action-icon"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i></span>
                <span>
                    <strong>Criar questão</strong>
                    <small>Escrever uma nova questão</small>
                </span>
            </a>
            <a class="simple-action-card" href="question-bank.php">
                <span class="simple-action-icon"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></span>
                <span>
                    <strong>Abrir banco</strong>
                    <small>Buscar e usar questões já prontas</small>
                </span>
            </a>
            <a class="simple-action-card" href="exam-library.php">
                <span class="simple-action-icon"><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span>
                <span>
                    <strong>Montar prova</strong>
                    <small>Criar uma prova a partir do banco</small>
                </span>
            </a>
            <a class="simple-action-card" href="enem.php">
                <span class="simple-action-icon"><i class="fa-solid fa-download" aria-hidden="true"></i></span>
                <span>
                    <strong>Importar ENEM</strong>
                    <small>Trazer questões oficiais para adaptação</small>
                </span>
            </a>
        </div>
    </article>

    <section class="simple-metric-grid">
        <article class="simple-metric-card">
            <small>Minhas questões</small>
            <strong><?= h((string) $questionMetrics['mine']) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Banco disponível</small>
            <strong><?= h((string) $accessibleQuestionCount) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Provas criadas</small>
            <strong><?= h((string) $examCount) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Xerox</small>
            <strong><?= $xeroxPendingCount > 0 ? h((string) $xeroxPendingCount) : 'Livre' ?></strong>
        </article>
    </section>

    <article class="simple-card">
        <div class="simple-card-head">
            <h2>Atividades recentes</h2>
        </div>
        <?php if ($recentActivities === []): ?>
            <div class="empty-state">
                <h2>Nenhuma atividade recente</h2>
                <p>Comece criando uma questão ou montando uma prova.</p>
            </div>
        <?php else: ?>
            <div class="simple-list">
                <?php foreach ($recentActivities as $activity): ?>
                    <a class="simple-list-item simple-list-item-link" href="<?= h($activity['link']) ?>">
                        <div>
                            <strong><?= h($activity['title']) ?></strong>
                            <p><?= h($activity['meta']) ?></p>
                        </div>
                        <div class="simple-list-actions">
                            <span class="badge"><?= h($activity['type'] === 'exam' ? 'Prova' : 'Questão') ?></span>
                            <span class="badge"><?= h($activity['date']) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>

<?php render_footer(); ?>
