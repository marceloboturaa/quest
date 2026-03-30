<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/xerox_repository.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$xeroxFilters = [
    'term' => trim((string) ($_GET['term'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
    'owner' => trim((string) ($_GET['owner'] ?? '')),
];

if (is_post()) {
    abort_if_invalid_csrf();

    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'set_xerox_role') {
        if (!can_authorize_xerox_users()) {
            flash('error', 'Você não pode autorizar usuários para o setor Xerox.');
            redirect('xerox.php');
        }

        $targetUserId = (int) ($_POST['user_id'] ?? 0);
        $newRole = (string) ($_POST['role'] ?? 'user');

        if (!xerox_set_user_role($targetUserId, $newRole)) {
            flash('error', 'Não foi possível atualizar o acesso do setor Xerox.');
            redirect('xerox.php');
        }

        flash('success', $newRole === 'xerox' ? 'Usuário autorizado para o setor Xerox.' : 'Usuário removido do setor Xerox.');
        redirect('xerox.php');
    }

    if ($action === 'cancel_xerox') {
        $examId = (int) ($_POST['exam_id'] ?? 0);

        if (!xerox_cancel_exam($examId, $userId)) {
            flash('error', 'Não foi possível cancelar o envio desta prova.');
            redirect('xerox.php');
        }

        flash('success', 'Envio para o setor Xerox cancelado.');
        redirect('xerox.php');
    }

    if ($action === 'resend_to_xerox') {
        $examId = (int) ($_POST['exam_id'] ?? 0);

        if (!xerox_is_available()) {
            flash('error', 'Nenhum usuário Xerox está autorizado no momento.');
            redirect('xerox.php');
        }

        if (!xerox_resend_exam($examId, $userId)) {
            flash('error', 'Não foi possível reenviar esta prova.');
            redirect('xerox.php');
        }

        flash('success', 'Prova reenviada para o setor Xerox.');
        redirect('xerox.php');
    }

    if ($action === 'xerox_accept_exam') {
        if (!is_xerox_user()) {
            flash('error', 'Somente usuários do Xerox podem aceitar provas para impressão.');
            redirect('xerox.php');
        }

        $examId = (int) ($_POST['exam_id'] ?? 0);

        if (!xerox_accept_exam($examId, $userId)) {
            flash('error', 'Não foi possível colocar a prova em andamento.');
            redirect('xerox.php');
        }

            flash('success', 'Prova aceita e movida para a fila de impressão.');
        redirect('xerox.php');
    }

    if ($action === 'xerox_finish_exam') {
        if (!is_xerox_user()) {
            flash('error', 'Somente usuários do Xerox podem finalizar impressões.');
            redirect('xerox.php');
        }

        $examId = (int) ($_POST['exam_id'] ?? 0);

        if (!xerox_finish_exam($examId, $userId)) {
            flash('error', 'Não foi possível finalizar esta prova.');
            redirect('xerox.php');
        }

        flash('success', 'Prova marcada como finalizada.');
        redirect('xerox.php');
    }
}

$operators = xerox_operator_list();
$manageableUsers = can_authorize_xerox_users() ? xerox_manageable_users() : [];
$ownerExams = xerox_owner_exam_list($userId);
$matchesXeroxFilters = static function (array $exam) use ($xeroxFilters): bool {
    if ($xeroxFilters['term'] !== '' && stripos((string) ($exam['title'] ?? ''), $xeroxFilters['term']) === false) {
        return false;
    }

    if ($xeroxFilters['status'] !== '' && (string) ($exam['xerox_status'] ?? '') !== $xeroxFilters['status']) {
        return false;
    }

    if ($xeroxFilters['owner'] !== '') {
        $ownerHaystack = trim((string) (($exam['owner_name'] ?? '') . ' ' . ($exam['xerox_owner_name'] ?? '')));

        if ($ownerHaystack === '' || stripos($ownerHaystack, $xeroxFilters['owner']) === false) {
            return false;
        }
    }

    return true;
};

$ownerForwardedExams = array_values(array_filter(
    $ownerExams,
    static fn(array $exam): bool => (string) $exam['xerox_status'] !== 'not_sent'
));
$ownerForwardedExams = array_values(array_filter($ownerForwardedExams, $matchesXeroxFilters));
$queueTotals = can_view_xerox_queue() ? xerox_queue_totals() : [
    'not_sent' => 0,
    'sent' => 0,
    'in_progress' => 0,
    'finished' => 0,
];
$queue = can_view_xerox_queue() ? xerox_queue_list() : [];
$queue = array_values(array_filter($queue, $matchesXeroxFilters));
$sentExams = array_values(array_filter($queue, static fn(array $exam): bool => (string) $exam['xerox_status'] === 'sent'));
$inProgressExams = array_values(array_filter($queue, static fn(array $exam): bool => (string) $exam['xerox_status'] === 'in_progress'));
$finishedExams = array_values(array_filter($queue, static fn(array $exam): bool => (string) $exam['xerox_status'] === 'finished'));
$panelCount = 1 + (can_view_xerox_queue() ? 3 : 0) + (can_authorize_xerox_users() ? 1 : 0);
$showXeroxSwitcher = $panelCount > 1;
$xeroxHeadline = is_xerox_user()
    ? 'Controle da fila de impressão'
    : (can_authorize_xerox_users() ? 'Painel do setor Xerox' : 'Acompanhe suas provas no Xerox');
$xeroxQuickFacts = can_view_xerox_queue()
    ? [
        ['label' => 'Operadores', 'value' => (string) count($operators)],
        ['label' => 'Na fila', 'value' => (string) count($sentExams)],
        ['label' => 'Em andamento', 'value' => (string) count($inProgressExams)],
        ['label' => 'Finalizadas', 'value' => (string) count($finishedExams)],
    ]
    : [
        ['label' => 'Setor', 'value' => xerox_is_available() ? 'Ativo' : 'Parado'],
        ['label' => 'Operadores', 'value' => (string) count($operators)],
        ['label' => 'Minhas provas', 'value' => (string) count($ownerForwardedExams)],
    ];

$defaultPanel = 'mine';
if (is_xerox_user()) {
    $defaultPanel = 'sent';
} elseif (can_authorize_xerox_users()) {
    $defaultPanel = 'mine';
}

render_header(
    'Xerox',
    can_view_xerox_queue()
        ? 'Receba, acompanhe e finalize impressões em um fluxo simples.'
        : 'Acompanhe o andamento das provas enviadas para impressão.',
    false
);
?>

<section class="simple-stack">
    <article class="simple-card xerox-overview-card">
        <div class="xerox-overview-headline">
            <div class="xerox-overview-copy">
                <span class="exam-library-kicker">Setor de impressão</span>
                <h1><?= h($xeroxHeadline) ?></h1>
                <p class="helper-text">
                    <?= is_xerox_user()
                        ? 'Veja a fila, aceite provas e finalize a impressão.'
                        : (can_authorize_xerox_users()
                            ? 'Autorize operadores e acompanhe a fila do setor.'
                            : 'Veja somente as suas provas enviadas ao setor.') ?>
                </p>
            </div>
            <aside class="xerox-status-panel">
                <?php if (!xerox_is_available()): ?>
                    <span class="badge">Sem operador Xerox</span>
                <?php endif; ?>
                <div class="xerox-status-list">
                    <?php foreach ($xeroxQuickFacts as $fact): ?>
                        <div class="xerox-status-item">
                            <small><?= h($fact['label']) ?></small>
                            <strong><?= h($fact['value']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </aside>
        </div>

        <?php if ($showXeroxSwitcher): ?>
            <div class="xerox-switcher" data-xerox-switcher data-default-panel="<?= h($defaultPanel) ?>">
                <button class="xerox-switch-chip" type="button" data-xerox-switch="mine">Minhas provas <span><?= h((string) count($ownerForwardedExams)) ?></span></button>
                <?php if (can_view_xerox_queue()): ?>
                    <button class="xerox-switch-chip" type="button" data-xerox-switch="sent">Fila <span><?= h((string) count($sentExams)) ?></span></button>
                    <button class="xerox-switch-chip" type="button" data-xerox-switch="progress">Em andamento <span><?= h((string) count($inProgressExams)) ?></span></button>
                    <button class="xerox-switch-chip" type="button" data-xerox-switch="finished">Finalizadas <span><?= h((string) count($finishedExams)) ?></span></button>
                <?php endif; ?>
                <?php if (can_authorize_xerox_users()): ?>
                    <button class="xerox-switch-chip" type="button" data-xerox-switch="team">Equipe <span><?= h((string) count($operators)) ?></span></button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="get" class="simple-filter-grid xerox-filter-grid">
            <div class="xerox-filter-heading">
                <strong>Filtrar a lista</strong>
                    <span>Busque uma prova ou refine a lista por status e responsável.</span>
            </div>
            <label>Buscar prova
                <input type="text" name="term" value="<?= h($xeroxFilters['term']) ?>" placeholder="Título da prova">
            </label>
            <label>Status
                <select name="status">
                    <option value="">Todos</option>
                    <option value="sent" <?= $xeroxFilters['status'] === 'sent' ? 'selected' : '' ?>>Encaminhado</option>
                    <option value="in_progress" <?= $xeroxFilters['status'] === 'in_progress' ? 'selected' : '' ?>>Em andamento</option>
                    <option value="finished" <?= $xeroxFilters['status'] === 'finished' ? 'selected' : '' ?>>Finalizado</option>
                </select>
            </label>
            <label>Professor ou operador
                <input type="text" name="owner" value="<?= h($xeroxFilters['owner']) ?>" placeholder="Nome">
            </label>
            <div class="simple-action-row">
                <button class="button" type="submit">Filtrar</button>
                <a class="ghost-button" href="xerox.php">Limpar</a>
            </div>
        </form>
    </article>

    <section class="simple-card" data-xerox-panel="mine">
        <div class="simple-card-head">
            <h2>Minhas provas</h2>
            <span class="badge"><?= h((string) count($ownerForwardedExams)) ?> em fluxo</span>
        </div>

        <?php if ($ownerForwardedExams === []): ?>
            <div class="empty-state">
                <h2>Nenhuma prova encaminhada</h2>
                <p>Abra o preview da prova e use o botão Xerox para enviar a impressão.</p>
            </div>
        <?php else: ?>
            <div class="simple-list">
                <?php foreach ($ownerForwardedExams as $exam): ?>
                    <article class="simple-list-card">
                        <div class="simple-card-head">
                            <div>
                                <h3><?= h((string) $exam['title']) ?></h3>
                                <p class="simple-list-copy">
                                    <?= h((string) $exam['total_questions']) ?> questões
                                    <?php if (!empty($exam['xerox_owner_name'])): ?>
                                        · Xerox: <?= h((string) $exam['xerox_owner_name']) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <span class="badge <?= h(xerox_status_badge_class((string) $exam['xerox_status'])) ?>">
                                <?= h(xerox_status_label((string) $exam['xerox_status'])) ?>
                            </span>
                        </div>
                        <div class="simple-inline-list">
                            <span class="badge">Enviado: <?= h(datetime_label((string) ($exam['xerox_requested_at'] ?? null), '-')) ?></span>
                        </div>
                        <div class="simple-list-actions">
                            <a class="ghost-button" href="exam-preview.php?id=<?= h((string) $exam['id']) ?>">Preview</a>
                            <a class="button-secondary" href="exam-pdf.php?id=<?= h((string) $exam['id']) ?>">PDF</a>
                            <?php if ((string) $exam['xerox_status'] === 'sent'): ?>
                                <form method="post">
                                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="cancel_xerox">
                                    <input type="hidden" name="exam_id" value="<?= h((string) $exam['id']) ?>">
                                    <button class="button-danger" type="submit">Cancelar envio</button>
                                </form>
                            <?php elseif ((string) $exam['xerox_status'] === 'finished'): ?>
                                <form method="post">
                                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="resend_to_xerox">
                                    <input type="hidden" name="exam_id" value="<?= h((string) $exam['id']) ?>">
                                    <button class="button" type="submit" <?= xerox_is_available() ? '' : 'disabled' ?>>Reenviar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <?php if (can_view_xerox_queue()): ?>
        <section class="simple-card" data-xerox-panel="sent">
            <div class="simple-card-head">
                <h2>Fila</h2>
                <span class="badge"><?= h((string) count($sentExams)) ?> aguardando</span>
            </div>

            <?php if ($sentExams === []): ?>
                <div class="empty-state">
                    <h2>Sem provas aguardando</h2>
                    <p>Nenhuma prova foi encaminhada neste momento.</p>
                </div>
            <?php else: ?>
                <div class="simple-list">
                    <?php foreach ($sentExams as $exam): ?>
                        <article class="simple-list-card">
                            <div class="simple-card-head">
                                <div>
                                    <h3><?= h((string) $exam['title']) ?></h3>
                                    <p class="simple-list-copy"><?= h((string) $exam['owner_name']) ?> · <?= h((string) $exam['total_questions']) ?> questões</p>
                                </div>
                                <span class="badge">Encaminhado</span>
                            </div>
                            <div class="simple-inline-list">
                                <span class="badge">Enviado: <?= h(datetime_label((string) ($exam['xerox_requested_at'] ?? null), '-')) ?></span>
                            </div>
                            <div class="simple-list-actions">
                                <a class="ghost-button" href="exam-preview.php?id=<?= h((string) $exam['id']) ?>">Preview</a>
                                <a class="ghost-button" href="exam-pdf.php?id=<?= h((string) $exam['id']) ?>">PDF</a>
                                <?php if (is_xerox_user()): ?>
                                    <form method="post">
                                        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="xerox_accept_exam">
                                        <input type="hidden" name="exam_id" value="<?= h((string) $exam['id']) ?>">
                                        <button class="button-secondary" type="submit">Aceitar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="simple-card" data-xerox-panel="progress">
            <div class="simple-card-head">
                <h2>Em andamento</h2>
                <span class="badge"><?= h((string) count($inProgressExams)) ?> ativas</span>
            </div>

            <?php if ($inProgressExams === []): ?>
                <div class="empty-state">
                    <h2>Nenhuma prova em andamento</h2>
                    <p>A fila ativa do setor aparece aqui.</p>
                </div>
            <?php else: ?>
                <div class="simple-list">
                    <?php foreach ($inProgressExams as $exam): ?>
                        <?php $canFinish = is_xerox_user() && (int) ($exam['xerox_target_user_id'] ?? 0) === $userId; ?>
                        <article class="simple-list-card">
                            <div class="simple-card-head">
                                <div>
                                    <h3><?= h((string) $exam['title']) ?></h3>
                                    <p class="simple-list-copy">
                                        <?= h((string) $exam['owner_name']) ?>
                                        <?php if (!empty($exam['xerox_owner_name'])): ?>
                                            · Responsável: <?= h((string) $exam['xerox_owner_name']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <span class="badge">Em andamento</span>
                            </div>
                            <div class="simple-inline-list">
                                <span class="badge">Enviado: <?= h(datetime_label((string) ($exam['xerox_requested_at'] ?? null), '-')) ?></span>
                                <span class="badge">Aceito: <?= h(datetime_label((string) ($exam['xerox_started_at'] ?? null), '-')) ?></span>
                            </div>
                            <div class="simple-list-actions">
                                <a class="ghost-button" href="exam-preview.php?id=<?= h((string) $exam['id']) ?>">Preview</a>
                                <a class="ghost-button" href="exam-pdf.php?id=<?= h((string) $exam['id']) ?>">PDF</a>
                                <?php if ($canFinish): ?>
                                    <form method="post">
                                        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="xerox_finish_exam">
                                        <input type="hidden" name="exam_id" value="<?= h((string) $exam['id']) ?>">
                                        <button class="button-secondary" type="submit">Finalizar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="simple-card" data-xerox-panel="finished">
            <div class="simple-card-head">
                <h2>Finalizadas</h2>
                <span class="badge"><?= h((string) count($finishedExams)) ?> concluídas</span>
            </div>

            <?php if ($finishedExams === []): ?>
                <div class="empty-state">
                    <h2>Nenhuma prova finalizada</h2>
                    <p>As impressões concluídas aparecem aqui.</p>
                </div>
            <?php else: ?>
                <div class="simple-list">
                    <?php foreach ($finishedExams as $exam): ?>
                        <article class="simple-list-card">
                            <div class="simple-card-head">
                                <div>
                                    <h3><?= h((string) $exam['title']) ?></h3>
                                    <p class="simple-list-copy">
                                        <?= h((string) $exam['owner_name']) ?>
                                        <?php if (!empty($exam['xerox_owner_name'])): ?>
                                            · Concluída por: <?= h((string) $exam['xerox_owner_name']) ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <span class="badge badge-success">Finalizado</span>
                            </div>
                            <div class="simple-inline-list">
                                <span class="badge">Enviado: <?= h(datetime_label((string) ($exam['xerox_requested_at'] ?? null), '-')) ?></span>
                                <span class="badge">Aceito: <?= h(datetime_label((string) ($exam['xerox_started_at'] ?? null), '-')) ?></span>
                                <span class="badge">Finalizado: <?= h(datetime_label((string) ($exam['xerox_finished_at'] ?? null), '-')) ?></span>
                            </div>
                            <div class="simple-list-actions">
                                <a class="ghost-button" href="exam-preview.php?id=<?= h((string) $exam['id']) ?>">Preview</a>
                                <a class="button-secondary" href="exam-pdf.php?id=<?= h((string) $exam['id']) ?>">PDF</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if (can_authorize_xerox_users()): ?>
        <section class="simple-card" data-xerox-panel="team">
            <div class="simple-card-head">
                <div>
                    <h2>Equipe Xerox</h2>
                    <p class="helper-text">Escolha quem pode operar o setor Xerox.</p>
                </div>
                <span class="badge"><?= h((string) count($operators)) ?> autorizados</span>
            </div>

            <div class="simple-user-list">
                <?php foreach ($manageableUsers as $managedUser): ?>
                    <article class="simple-user-card">
                        <div class="simple-user-copy">
                            <strong><?= h($managedUser['name']) ?></strong>
                            <p><?= h($managedUser['email']) ?></p>
                            <div class="simple-inline-list">
                                <span class="badge <?= $managedUser['role'] === 'xerox' ? 'badge-accent' : '' ?>">
                                    <?= h(role_label((string) $managedUser['role'])) ?>
                                </span>
                                <span class="badge">Criado em <?= h(date('d/m/Y', strtotime((string) $managedUser['created_at']))) ?></span>
                            </div>
                        </div>

                        <div class="simple-user-actions">
                            <form method="post" class="simple-user-form">
                                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="set_xerox_role">
                                <input type="hidden" name="user_id" value="<?= h((string) $managedUser['id']) ?>">
                                <label>Perfil
                                    <select name="role">
                                        <option value="user" <?= $managedUser['role'] === 'user' ? 'selected' : '' ?>>Usuário</option>
                                        <option value="xerox" <?= $managedUser['role'] === 'xerox' ? 'selected' : '' ?>>Xerox</option>
                                    </select>
                                </label>
                                <button class="button-secondary" type="submit">Salvar</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>

<?php render_footer(); ?>
