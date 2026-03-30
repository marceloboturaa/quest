<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_role('master_admin');

if (is_post()) {
    abort_if_invalid_csrf();

    if ((string) ($_POST['action'] ?? '') === 'set_role') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $newRole = (string) ($_POST['role'] ?? 'user');
        $allowedRoles = ['user', 'local_admin', 'xerox'];

        if (!in_array($newRole, $allowedRoles, true)) {
            flash('error', 'Perfil inválido.');
            redirect('users.php');
        }

        $current = current_user();

        if ($userId === (int) $current['id']) {
            flash('error', 'O administrador master principal não pode alterar o próprio perfil por aqui.');
            redirect('users.php');
        }

        $statement = db()->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $userId]);
        $target = $statement->fetch();

        if (!$target) {
            flash('error', 'Usuário não encontrado.');
            redirect('users.php');
        }

        if ($target['role'] === 'master_admin') {
            flash('error', 'Não altere um administrador master por esta tela.');
            redirect('users.php');
        }

        $update = db()->prepare('UPDATE users SET role = :role, updated_at = NOW() WHERE id = :id');
        $update->execute([
            'role' => $newRole,
            'id' => $userId,
        ]);

        flash('success', 'Perfil do usuário atualizado.');
        redirect('users.php');
    }
}

$users = db()->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
$counts = [
    'total' => count($users),
    'local_admins' => count(array_filter($users, static fn(array $user): bool => $user['role'] === 'local_admin')),
    'xerox' => count(array_filter($users, static fn(array $user): bool => $user['role'] === 'xerox')),
    'users' => count(array_filter($users, static fn(array $user): bool => $user['role'] === 'user')),
];

render_header(
    'Usuários',
    'Altere o perfil de acesso das contas de forma simples.'
);
?>

<section class="simple-stack">
    <section class="simple-metric-grid">
        <article class="simple-metric-card">
            <small>Total de contas</small>
            <strong><?= h((string) $counts['total']) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Admins locais</small>
            <strong><?= h((string) $counts['local_admins']) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Usuários</small>
            <strong><?= h((string) $counts['users']) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Xerox</small>
            <strong><?= h((string) $counts['xerox']) ?></strong>
        </article>
    </section>

    <article class="simple-card">
        <div class="simple-card-head">
            <div>
                <h2>Gerenciar perfis</h2>
                <p class="helper-text">Escolha o perfil de cada pessoa e clique em salvar.</p>
            </div>
        </div>

        <div class="simple-user-list">
            <?php foreach ($users as $managedUser): ?>
                <article class="simple-user-card">
                    <div class="simple-user-copy">
                        <strong><?= h($managedUser['name']) ?></strong>
                        <p><?= h($managedUser['email']) ?></p>
                        <div class="simple-inline-list">
                            <span class="badge <?= $managedUser['role'] === 'local_admin' ? 'badge-accent' : '' ?>">
                                <?= h(role_label($managedUser['role'])) ?>
                            </span>
                            <span class="badge">Criado em <?= h(date('d/m/Y', strtotime((string) $managedUser['created_at']))) ?></span>
                        </div>
                    </div>

                    <div class="simple-user-actions">
                        <?php if ($managedUser['role'] === 'master_admin'): ?>
                            <span class="badge">Master bloqueado</span>
                        <?php else: ?>
                            <form method="post" class="simple-user-form">
                                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="set_role">
                                <input type="hidden" name="user_id" value="<?= h((string) $managedUser['id']) ?>">
                                <label>Perfil
                                    <select name="role">
                                        <option value="user" <?= $managedUser['role'] === 'user' ? 'selected' : '' ?>>Usuário</option>
                                        <option value="local_admin" <?= $managedUser['role'] === 'local_admin' ? 'selected' : '' ?>>Admin local</option>
                                        <option value="xerox" <?= $managedUser['role'] === 'xerox' ? 'selected' : '' ?>>Xerox</option>
                                    </select>
                                </label>
                                <button class="button-secondary" type="submit">Salvar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </article>

    <section class="simple-panel-grid">
        <article class="simple-card">
            <div class="simple-card-head">
                <h2>Perfis</h2>
            </div>
            <div class="simple-list">
                <div class="simple-list-item">
                    <div>
                        <strong>Master admin</strong>
                        <p>Controle total do sistema e dos usuários.</p>
                    </div>
                </div>
                <div class="simple-list-item">
                    <div>
                        <strong>Admin local</strong>
                        <p>Apoio operacional ao banco de questões.</p>
                    </div>
                </div>
                <div class="simple-list-item">
                    <div>
                        <strong>Xerox</strong>
                        <p>Recebe provas e controla a impressão.</p>
                    </div>
                </div>
                <div class="simple-list-item">
                    <div>
                        <strong>Usuário</strong>
                        <p>Cria questões e monta provas.</p>
                    </div>
                </div>
            </div>
        </article>

        <article class="simple-card">
            <div class="simple-card-head">
                <h2>Orientação</h2>
            </div>
            <div class="simple-list">
                <div class="simple-list-item">
                    <div>
                        <strong>Use com cuidado</strong>
                        <p>Promova só quem realmente precisa de acesso ampliado.</p>
                    </div>
                </div>
                <div class="simple-list-item">
                    <div>
                        <strong>Master protegido</strong>
                        <p>O perfil master principal não pode ser alterado por esta tela.</p>
                    </div>
                </div>
            </div>
        </article>
    </section>
</section>

<?php render_footer(); ?>
