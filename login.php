<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_guest();

if (is_post()) {
    abort_if_invalid_csrf();

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    remember_input(['email' => $email]);

    if ($email === '' || $password === '') {
        flash('error', 'Preencha e-mail e senha.');
        redirect('login.php');
    }

    $statement = db()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        flash('error', 'Credenciais inválidas.');
        redirect('login.php');
    }

    login_user($user);
    forget_old_input();
    flash('success', 'Login realizado com sucesso.');
    redirect('dashboard.php');
}

render_header('Entrar', 'Use sua conta para acessar o painel do Quest.', false, false);
?>
<div class="auth-wrap">
    <section class="auth-card">
        <h2>Acesse sua conta</h2>
        <p>Entre com seu e-mail e senha para acessar o banco de questões e o painel do sistema.</p>

        <form method="post" class="form-grid">
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">

            <label>
                E-mail
                <input type="email" name="email" value="<?= h(old('email')) ?>" required>
            </label>

            <label>
                Senha
                <span class="password-input-wrap">
                    <input type="password" name="password" required data-password-input>
                    <button class="password-toggle-button" type="button" data-password-toggle aria-label="Mostrar senha" aria-pressed="false">
                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        <span>Mostrar</span>
                    </button>
                </span>
            </label>

            <div class="form-actions">
                <button class="button" type="submit">Entrar</button>
                <a class="ghost-button" href="forgot-password.php">Esqueci minha senha</a>
                <a class="ghost-button" href="register.php">Criar conta</a>
            </div>
        </form>

        <div class="auth-note">
            <strong>Acesso</strong><br>
            Se você ainda não tem conta, crie um cadastro. O perfil inicial entra como usuário comum.
        </div>

        <div class="auth-back-link-wrap">
            <a class="auth-back-link" href="index.php">Voltar ao início</a>
        </div>
    </section>
</div>
<?php forget_old_input(); ?>
<?php render_footer(false); ?>
