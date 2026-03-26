<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_guest();

if (is_post()) {
    abort_if_invalid_csrf();

    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

    remember_input([
        'name' => $name,
        'email' => $email,
    ]);

    if ($name === '' || $email === '' || $password === '' || $passwordConfirmation === '') {
        flash('error', 'Preencha todos os campos obrigatorios.');
        redirect('register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Informe um e-mail valido.');
        redirect('register.php');
    }

    if (mb_strlen($password) < 8) {
        flash('error', 'A senha precisa ter pelo menos 8 caracteres.');
        redirect('register.php');
    }

    if ($password !== $passwordConfirmation) {
        flash('error', 'As senhas nao conferem.');
        redirect('register.php');
    }

    $statement = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);

    if ($statement->fetch()) {
        flash('error', 'Ja existe uma conta com esse e-mail.');
        redirect('register.php');
    }

    $insert = db()->prepare(
        'INSERT INTO users (name, email, password_hash, role, created_at, updated_at)
         VALUES (:name, :email, :password_hash, :role, NOW(), NOW())'
    );
    $insert->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'user',
    ]);

    $userId = (int) db()->lastInsertId();
    $statement = db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $userId]);
    $user = $statement->fetch();

    login_user($user);
    forget_old_input();
    flash('success', 'Conta criada com sucesso.');
    redirect('dashboard.php');
}

render_header('Criar conta', 'Cadastre novos usuarios no Quest com perfil inicial de usuario.', false, false);
?>
<div class="auth-wrap">
    <section class="auth-card">
        <h2>Novo cadastro</h2>
        <p>Depois do cadastro, a conta entra como usuario comum. O master admin pode promover para admin local quando necessario.</p>

        <form method="post" class="form-grid">
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">

            <label>
                Nome completo
                <input type="text" name="name" value="<?= h(old('name')) ?>" required>
            </label>

            <label>
                E-mail
                <input type="email" name="email" value="<?= h(old('email')) ?>" required>
            </label>

            <div class="form-grid two-columns">
                <label>
                    Senha
                    <input type="password" name="password" required>
                </label>

                <label>
                    Confirmar senha
                    <input type="password" name="password_confirmation" required>
                </label>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">Criar conta</button>
                <a class="ghost-button" href="login.php">Ja tenho login</a>
            </div>
        </form>

        <div class="auth-note">
            <strong>Perfil inicial</strong><br>
            Usuarios comuns podem criar questoes, editar as proprias e montar provas com questoes visiveis.
        </div>
    </section>
</div>
<?php forget_old_input(); ?>
<?php render_footer(false); ?>
