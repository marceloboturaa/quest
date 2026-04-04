<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/dashboard_repository.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$isStudent = ($user['role'] ?? '') === 'aluno';
$questionMetrics = [
    'private' => 0,
    'public' => 0,
    'mine' => 0,
];
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
    $statement = db()->prepare('SELECT COUNT(*) FROM questions WHERE visibility = "public" OR author_id = :author_id');
    $statement->execute(['author_id' => $userId]);
    $accessibleQuestionCount = (int) $statement->fetchColumn();
} catch (Throwable) {
    $accessibleQuestionCount = $questionMetrics['public'] + $questionMetrics['mine'];
}

foreach (dashboard_recent_questions($userId, false, 3) as $item) {
    $recentActivities[] = [
        'title' => (string) ($item['title'] ?? 'Questao sem titulo'),
        'meta' => trim((string) (($item['discipline_name'] ?? '') !== '' ? $item['discipline_name'] . ' · ' : '') . question_type_label((string) ($item['question_type'] ?? 'multiple_choice')) . (($item['author_name'] ?? '') !== '' ? ' · ' . (string) $item['author_name'] : '')),
        'date' => datetime_label($item['created_at'] ?? null),
        'timestamp' => strtotime((string) ($item['created_at'] ?? '')) ?: 0,
        'link' => (int) ($item['id'] ?? 0) > 0 ? 'question-editor.php?edit=' . (int) $item['id'] : 'question-bank.php',
        'visibility' => (string) ($item['visibility'] ?? 'private'),
    ];
}

usort(
    $recentActivities,
    static fn(array $left, array $right): int => ($right['timestamp'] <=> $left['timestamp'])
);
$recentActivities = array_slice($recentActivities, 0, 5);

render_header(
    'Questões',
    $isStudent
        ? 'Área organizada para estudo, com acesso rápido ao banco e ao treino.'
        : 'Área organizada para montar questões, importar conteúdo e preparar provas.'
);
?>

<section class="questions-workspace">
    <article class="simple-card questions-hero-card">
        <div class="simple-card-head">
            <div>
                <span class="exam-library-kicker">Painel de questões</span>
                <h2><?= $isStudent ? 'Seu fluxo é de estudo' : 'Seu fluxo é de produção' ?></h2>
                <p class="helper-text">
                    <?= $isStudent
                        ? 'Use o banco para resolver questões e seguir para o Modo Estudo.'
                        : 'Crie, organize e depois transforme o material em prova.' ?>
                </p>
            </div>
            <div class="questions-role-badge">
                <small>Perfil atual</small>
                <strong><?= h(role_label((string) $user['role'])) ?></strong>
            </div>
        </div>
    </article>

    <div class="questions-panel-grid">
    <article class="simple-card questions-role-card">
        <div class="simple-card-head">
            <div>
                <h2>Estudo rápido</h2>
                <p class="helper-text">Atalhos pensados para quem vai resolver questões.</p>
            </div>
            </div>

            <div class="simple-decision-grid">
                <a class="simple-action-card" href="study.php">
                    <span class="simple-action-icon"><i class="fa-solid fa-book-open-reader" aria-hidden="true"></i></span>
                    <span>
                        <strong>Modo estudo</strong>
                        <small>Começar um treino com correção automática</small>
                    </span>
                </a>
                <a class="simple-action-card" href="question-bank.php">
                    <span class="simple-action-icon"><i class="fa-solid fa-folder-open" aria-hidden="true"></i></span>
                    <span>
                        <strong>Banco de questões</strong>
                        <small>Buscar, filtrar e usar questões prontas</small>
                    </span>
                </a>
            </div>
        </article>

        <?php if (!$isStudent): ?>
            <article class="simple-card questions-role-card">
                <div class="simple-card-head">
                    <div>
                        <h2>Área docente</h2>
                        <p class="helper-text">Ferramentas para criar e organizar provas.</p>
                    </div>
                </div>

                <div class="simple-decision-grid">
                    <a class="simple-action-card" href="question-editor.php?new=1">
                        <span class="simple-action-icon"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i></span>
                        <span>
                            <strong>Criar questão</strong>
                            <small>Escrever uma nova questão para o banco</small>
                        </span>
                    </a>
                    <a class="simple-action-card" href="enem.php">
                        <span class="simple-action-icon"><i class="fa-solid fa-download" aria-hidden="true"></i></span>
                        <span>
                            <strong>Importar ENEM</strong>
                            <small>Adicionar questões oficiais ao seu banco</small>
                        </span>
                    </a>
                    <button
                        class="simple-action-card simple-action-card-button simple-action-card--maintenance"
                        type="button"
                        title="Em manutenção"
                        aria-disabled="true"
                    >
                        <span class="simple-action-icon"><i class="fa-solid fa-file-circle-plus" aria-hidden="true"></i></span>
                        <span>
                            <strong>Criar prova</strong>
                            <small>Em manutenção</small>
                        </span>
                    </button>
                </div>
            </article>
        <?php endif; ?>
    </div>

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
            <small>Xerox</small>
            <strong><?= $xeroxPendingCount > 0 ? h((string) $xeroxPendingCount) : 'Livre' ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Perfil</small>
            <strong><?= h(role_label((string) $user['role'])) ?></strong>
        </article>
    </section>

    <article class="simple-card">
        <div class="simple-card-head">
            <h2>Questões recentes</h2>
        </div>
        <?php if ($recentActivities === []): ?>
            <div class="empty-state">
                <h2>Nenhuma questão recente</h2>
                <p>Comece criando uma questão ou abrindo o banco.</p>
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
                            <span class="badge"><?= h($activity['visibility'] === 'public' ? 'Pública' : 'Privada') ?></span>
                            <span class="badge"><?= h($activity['date']) ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>

<?php render_footer(); ?>
