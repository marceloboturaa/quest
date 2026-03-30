<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$tokenHash = $token !== '' ? hash('sha256', $token) : '';

$statement = db()->prepare(
    'SELECT password_resets.id, password_resets.user_id, password_resets.expires_at, password_resets.used_at, users.email
     FROM password_resets
     INNER JOIN users ON users.id = password_resets.user_id
     WHERE token_hash = :token_hash
     LIMIT 1'
);
$statement->execute(['token_hash' => $tokenHash]);
$resetRequest = $statement->fetch();

$tokenIsValid = $resetRequest
    && $resetRequest['used_at'] === null
    && strtotime((string) $resetRequest['expires_at']) > time();

if (is_post()) {
    abort_if_invalid_csrf();

    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (!$tokenIsValid) {
        flash('error', 'Esse link de redefinição não é mais válido.');
        redirect('forgot-password.php');
    }

    if (mb_strlen($password) < 8) {
        flash('error', 'A nova senha precisa ter pelo menos 8 caracteres.');
        redirect('reset-password.php?token=' . urlencode($token));
    }

    if ($password !== $passwordConfirmation) {
        flash('error', 'As senhas não conferem.');
        redirect('reset-password.php?token=' . urlencode($token));
    }

    $updateUser = db()->prepare('UPDATE users SET password_hash = :password_hash, updated_at = NOW() WHERE id = :id');
    $updateUser->execute([
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'id' => $resetRequest['user_id'],
    ]);

    $updateReset = db()->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = :id');
    $updateReset->execute(['id' => $resetRequest['id']]);

    flash('success', 'Senha redefinida com sucesso. Faça login.');
    redirect('login.php');
}

render_header('Redefinir senha', 'Crie uma nova senha para continuar usando o Quest.');
?>
<div class="auth-wrap">
    <section class="auth-card">
        <h2>Nova senha</h2>

        <?php if (!$tokenIsValid): ?>
            <p>Esse link expirou ou já foi utilizado.</p>
            <div class="form-actions">
                <a class="button" href="forgot-password.php">Gerar novo link</a>
                <a class="ghost-button" href="login.php">Voltar ao login</a>
            </div>
        <?php else: ?>
            <p>Defina a nova senha para a conta <strong><?= h($resetRequest['email']) ?></strong>.</p>

            <form method="post" class="form-grid">
                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="token" value="<?= h($token) ?>">

                <label>
                    Nova senha
                    <input type="password" name="password" required>
                </label>

                <label>
                    Confirmar nova senha
                    <input type="password" name="password_confirmation" required>
                </label>

                <div class="form-actions">
                    <button class="button" type="submit">Salvar nova senha</button>
                    <a class="ghost-button" href="login.php">Cancelar</a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</div>
<?php render_footer(); ?>
