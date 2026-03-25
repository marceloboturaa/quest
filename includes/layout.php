<?php
declare(strict_types=1);

function render_header(string $title, string $subtitle = '', bool $showHero = true): void
{
    $user = function_exists('current_user') ? current_user() : null;
    $flashes = function_exists('pull_flashes') ? pull_flashes() : [];
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title . ' | Quest') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= h(asset_url('assets/css/app.css')) ?>">
</head>
<body>
    <div class="page-shell">
        <header class="topbar">
            <a class="brand" href="<?= $user ? 'dashboard.php' : 'index.php' ?>">
                <span class="brand-mark">Q</span>
                <span>
                    <strong>Quest</strong>
                    <small>Banco inteligente de questoes</small>
                </span>
            </a>

            <nav class="topbar-nav">
                <?php if ($user): ?>
                    <a href="dashboard.php">Dashboard</a>
                    <a href="questions.php">Questoes</a>
                    <a href="exams.php">Provas</a>
                    <?php if (can_manage_users()): ?>
                        <a href="users.php">Usuarios</a>
                    <?php endif; ?>
                    <a class="ghost-button" href="logout.php">Sair</a>
                <?php else: ?>
                    <a href="login.php">Entrar</a>
                    <a class="ghost-button" href="register.php">Criar conta</a>
                <?php endif; ?>
            </nav>
        </header>

        <main class="page-content">
            <?php if ($showHero): ?>
                <section class="page-hero">
                    <div>
                        <p class="eyebrow">CNI apoiando a ideia</p>
                        <h1><?= h($title) ?></h1>
                        <?php if ($subtitle !== ''): ?>
                            <p class="page-subtitle"><?= h($subtitle) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($user): ?>
                        <div class="user-badge">
                            <span><?= h($user['name']) ?></span>
                            <small><?= h(role_label($user['role'])) ?></small>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php foreach ($flashes as $flash): ?>
                <div class="flash flash-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
            <?php endforeach; ?>
<?php
}

function render_footer(): void
{
    ?>
        </main>
        <footer class="site-footer">
            <p>Quest. Projeto pessoal de Marcelo Botura com apoio do CNI.</p>
            <small>Banco colaborativo de questoes, gestao de usuarios e montagem inicial de provas.</small>
        </footer>
    </div>
    <script src="<?= h(asset_url('assets/js/app.js')) ?>"></script>
</body>
</html>
<?php
}
