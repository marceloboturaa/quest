<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (is_post()) {
    abort_if_invalid_csrf();

    $email = trim((string) ($_POST['email'] ?? ''));

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $statement = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);
        $user = $statement->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = date(
                'Y-m-d H:i:s',
                time() + ((int) config('password_reset_expires_minutes') * 60)
            );

            $delete = db()->prepare('DELETE FROM password_resets WHERE user_id = :user_id');
            $delete->execute(['user_id' => $user['id']]);

            $insert = db()->prepare(
                'INSERT INTO password_resets (user_id, token_hash, expires_at, created_at)
                 VALUES (:user_id, :token_hash, :expires_at, NOW())'
            );
            $insert->bindValue('user_id', (int) $user['id'], PDO::PARAM_INT);
            $insert->bindValue('token_hash', $tokenHash);
            $insert->bindValue('expires_at', $expiresAt);
            $insert->execute();

            send_password_reset_email($user, $token);
        }
    }

    flash('info', 'Se o e-mail existir no sistema, voce recebera instrucoes para redefinir a senha.');
    redirect('login.php');
}

render_header('Recuperar senha', 'Informe seu e-mail para gerar um link de redefinicao.');
?>
<div class="auth-wrap">
    <section class="auth-card">
        <h2>Esqueci minha senha</h2>
        <p>O sistema tenta enviar o link por e-mail e registra o conteudo em armazenamento privado para teste local.</p>

        <form method="post" class="form-grid">
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">

            <label>
                E-mail
                <input type="email" name="email" required>
            </label>

            <div class="form-actions">
                <button class="button" type="submit">Enviar link</button>
                <a class="ghost-button" href="login.php">Voltar ao login</a>
            </div>
        </form>
    </section>
</div>
<?php render_footer(); ?>
