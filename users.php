<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_role('master_admin');

if (is_post()) {
    abort_if_invalid_csrf();

    if ((string) ($_POST['action'] ?? '') === 'set_role') {
        $userId = (int) ($_POST['user_id'] ?? 0);
        $newRole = (string) ($_POST['role'] ?? 'user');
        $allowedRoles = ['user', 'local_admin'];

        if (!in_array($newRole, $allowedRoles, true)) {
            flash('error', 'Perfil invalido.');
            redirect('users.php');
        }

        $current = current_user();

        if ($userId === (int) $current['id']) {
            flash('error', 'O master admin principal nao pode alterar o proprio perfil por aqui.');
            redirect('users.php');
        }

        $statement = db()->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $userId]);
        $target = $statement->fetch();

        if (!$target) {
            flash('error', 'Usuario nao encontrado.');
            redirect('users.php');
        }

        if ($target['role'] === 'master_admin') {
            flash('error', 'Nao altere um master admin por esta tela.');
            redirect('users.php');
        }

        $update = db()->prepare('UPDATE users SET role = :role, updated_at = NOW() WHERE id = :id');
        $update->execute([
            'role' => $newRole,
            'id' => $userId,
        ]);

        flash('success', 'Perfil do usuario atualizado.');
        redirect('users.php');
    }
}

$users = db()->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
$counts = [
    'total' => count($users),
    'local_admins' => count(array_filter($users, static fn(array $user): bool => $user['role'] === 'local_admin')),
    'users' => count(array_filter($users, static fn(array $user): bool => $user['role'] === 'user')),
];

render_header(
    'Usuarios',
    'Area master para promover usuarios comuns para admin local e acompanhar toda a estrutura de acesso.'
);
?>
<section class="stats-grid">
    <article>
        <span class="metric-copy">Total de contas</span>
        <strong class="metric-number"><?= h((string) $counts['total']) ?></strong>
        <p>Usuarios cadastrados na plataforma.</p>
    </article>
    <article>
        <span class="metric-copy">Admins locais</span>
        <strong class="metric-number"><?= h((string) $counts['local_admins']) ?></strong>
        <p>Contas com poder operacional ampliado.</p>
    </article>
    <article>
        <span class="metric-copy">Usuarios comuns</span>
        <strong class="metric-number"><?= h((string) $counts['users']) ?></strong>
        <p>Perfis basicos voltados para autoria.</p>
    </article>
</section>

<section class="panel">
    <h2>Gerenciar perfis</h2>
    <p>Somente o master admin pode alterar o nivel de acesso entre usuario e admin local.</p>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Perfil atual</th>
                <th>Criado em</th>
                <th>Acao</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $managedUser): ?>
                <tr>
                    <td><?= h($managedUser['name']) ?></td>
                    <td><?= h($managedUser['email']) ?></td>
                    <td>
                        <span class="badge <?= $managedUser['role'] === 'local_admin' ? 'badge-accent' : '' ?>">
                            <?= h(role_label($managedUser['role'])) ?>
                        </span>
                    </td>
                    <td><?= h(date('d/m/Y H:i', strtotime((string) $managedUser['created_at']))) ?></td>
                    <td>
                        <?php if ($managedUser['role'] === 'master_admin'): ?>
                            <span class="badge">Master bloqueado</span>
                        <?php else: ?>
                            <form method="post" class="inline-actions">
                                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="set_role">
                                <input type="hidden" name="user_id" value="<?= h((string) $managedUser['id']) ?>">
                                <select name="role">
                                    <option value="user" <?= $managedUser['role'] === 'user' ? 'selected' : '' ?>>Usuario</option>
                                    <option value="local_admin" <?= $managedUser['role'] === 'local_admin' ? 'selected' : '' ?>>Admin local</option>
                                </select>
                                <button class="button-secondary" type="submit">Salvar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="info-grid">
    <article class="panel">
        <h2>Leitura dos perfis</h2>
        <ul class="mini-list">
            <li><strong>Master admin:</strong> visao total e gestao de usuarios.</li>
            <li><strong>Admin local:</strong> apoio operacional ao banco de questoes.</li>
            <li><strong>Usuario:</strong> autoria, favoritos e montagem de provas.</li>
        </ul>
    </article>

    <article class="panel">
        <h2>Boa pratica</h2>
        <p>Promova para admin local apenas contas que realmente precisem atuar na organizacao do conteudo e da operacao.</p>
    </article>
</section>
<?php render_footer(); ?>
