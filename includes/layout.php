<?php
declare(strict_types=1);

const QUEST_ADMIN_UI_VERSION = '2026.03.29.1';

function render_header(string $title, string $subtitle = '', bool $showHero = true, bool $showTopbar = true): void
{
    $user = function_exists('current_user') ? current_user() : null;
    $flashes = function_exists('pull_flashes') ? pull_flashes() : [];
    $unreadMessages = $user && function_exists('messages_unread_count') ? messages_unread_count((int) $user['id']) : 0;
    $toastMessage = $user && function_exists('messages_latest_toast_for_user') ? messages_latest_toast_for_user((int) $user['id']) : null;
    $currentPath = basename((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
    $currentRole = (string) ($user['role'] ?? '');
    $isStudent = $currentRole === 'aluno';
    $isProfessor = in_array($currentRole, ['professor', 'local_admin', 'master_admin'], true);
    $isMaster = $currentRole === 'master_admin';
    $isActive = static function (array $paths) use ($currentPath): bool {
        return in_array($currentPath, $paths, true);
    };
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/favicon/android-chrome-512x512.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <meta name="theme-color" content="#4f2ec9">
    <title><?= h($title . ' | Quest') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700;800&family=Inter:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" referrerpolicy="no-referrer">
    <script>
        window.MathJax = {
            tex: {
                inlineMath: [['\\(', '\\)'], ['$', '$']],
                displayMath: [['\\[', '\\]'], ['$$', '$$']]
            },
            options: {
                skipHtmlTags: ['script', 'noscript', 'style', 'textarea', 'pre', 'code']
            }
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <link rel="stylesheet" href="<?= h(asset_url('assets/css/app.css')) ?>">
</head>
<body data-app-version="<?= h(QUEST_ADMIN_UI_VERSION) ?>" data-authenticated="<?= $user ? 'true' : 'false' ?>" data-csrf-token="<?= h(csrf_token()) ?>">
    <?php if ($showTopbar): ?>
        <header class="topbar">
            <div class="topbar-inner <?= $user ? '' : 'topbar-inner-public' ?>">
                <div class="topbar-branding">
                    <a class="brand" href="<?= $user ? 'dashboard.php' : 'index.php' ?>">
                        <span class="brand-mark">Q</span>
                        <span>
                            <strong>Quest</strong>
                        </span>
                    </a>

                    <?php if ($user): ?>
                        <button
                            class="topbar-menu-toggle"
                            type="button"
                            aria-expanded="false"
                            aria-controls="topbar-nav"
                            data-menu-toggle
                        >
                            Menu
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($user): ?>
                    <nav id="topbar-nav" class="topbar-nav" data-menu-panel>
                        <div class="topbar-nav-group">
                            <?php if ($isStudent): ?>
                                <a class="<?= $isActive(['study.php', 'question.php', 'result.php']) ? 'is-active' : '' ?>" href="study.php"><i class="fa-solid fa-book-open-reader nav-link-icon" aria-hidden="true"></i><span>Modo de estudos</span></a>
                                <a class="<?= $isActive(['question-bank.php']) ? 'is-active' : '' ?>" href="question-bank.php"><i class="fa-solid fa-lightbulb nav-link-icon" aria-hidden="true"></i><span>Questões</span></a>
                            <?php else: ?>
                                <a class="<?= $isActive(['dashboard.php']) ? 'is-active' : '' ?>" href="dashboard.php"><i class="fa-solid fa-house nav-link-icon" aria-hidden="true"></i><span>Início</span></a>
                                <a class="<?= $isActive(['study.php', 'question.php', 'result.php']) ? 'is-active' : '' ?>" href="study.php"><i class="fa-solid fa-book-open-reader nav-link-icon" aria-hidden="true"></i><span>Modo de estudos</span></a>
                                <a class="<?= $isActive(['question-bank.php']) ? 'is-active' : '' ?>" href="question-bank.php"><i class="fa-solid fa-lightbulb nav-link-icon" aria-hidden="true"></i><span>Questões</span></a>
                                <?php if ($isProfessor): ?>
                                    <a class="topbar-nav-prova <?= $isActive(['exam-create.php']) ? 'is-active' : '' ?>" href="exam-create.php" title="Em manutenção"><i class="fa-solid fa-circle-plus nav-link-icon" aria-hidden="true"></i><span>Prova</span></a>
                                <?php endif; ?>
                                <?php if ($isMaster || $currentRole === 'xerox'): ?>
                                    <a class="<?= $isActive(['xerox.php']) ? 'is-active' : '' ?>" href="xerox.php"><i class="fa-solid fa-print nav-link-icon" aria-hidden="true"></i><span>Xerox</span></a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (!$isStudent): ?>
                                <a class="<?= $isActive(['messages.php']) ? 'is-active' : '' ?>" href="messages.php"><i class="fa-solid fa-envelope nav-link-icon" aria-hidden="true"></i><span>Mensagens</span><?php if ($unreadMessages > 0): ?><small class="nav-pill"><?= h((string) $unreadMessages) ?></small><?php endif; ?></a>
                                <?php if ($isMaster || has_role('local_admin')): ?>
                                    <a class="<?= $isActive(['backup.php']) ? 'is-active' : '' ?>" href="backup.php"><i class="fa-solid fa-cloud-arrow-up nav-link-icon" aria-hidden="true"></i><span>Backup</span></a>
                                <?php endif; ?>
                                <?php if ($isMaster): ?>
                                    <a class="<?= $isActive(['users.php']) ? 'is-active' : '' ?>" href="users.php"><i class="fa-solid fa-users-gear nav-link-icon" aria-hidden="true"></i><span>Usuários</span></a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <div class="topbar-nav-group topbar-nav-group-end">
                            <a class="ghost-button <?= $isActive(['profile.php']) ? 'is-active' : '' ?>" href="profile.php"><i class="fa-solid fa-id-card nav-link-icon" aria-hidden="true"></i><span>Meu painel</span></a>
                            <a class="ghost-button" href="logout.php"><i class="fa-solid fa-arrow-right-from-bracket nav-link-icon" aria-hidden="true"></i><span>Sair</span></a>
                        </div>
                    </nav>
                <?php endif; ?>
            </div>
        </header>
    <?php endif; ?>

    <div class="page-shell">

        <main class="page-content">
            <?php if ($showHero): ?>
                <section class="page-titlebar">
                    <div class="page-titlebar-copy">
                        <h1><?= h($title) ?></h1>
                        <?php if ($subtitle !== ''): ?>
                            <p><?= h($subtitle) ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($user): ?>
                        <div class="page-titlebar-user">
                            <strong><?= h($user['name']) ?></strong>
                            <small><?= h(role_label($user['role'])) ?></small>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php foreach ($flashes as $flash): ?>
                <div class="flash flash-<?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
            <?php endforeach; ?>

            <?php if ($toastMessage): ?>
                <button
                    class="app-update-toast"
                    type="button"
                    data-message-toast
                    data-message-id="<?= h((string) $toastMessage['id']) ?>"
                    aria-live="polite"
                >
                    <span><i class="fa-solid fa-bell" aria-hidden="true"></i> <?= h((string) ($toastMessage['subject'] !== '' ? $toastMessage['subject'] : message_kind_label((string) $toastMessage['kind']))) ?></span>
                    <strong><?= h((string) ($toastMessage['kind'] === 'broadcast' ? 'Toque para fechar' : 'Nova mensagem')) ?></strong>
                </button>
            <?php endif; ?>
<?php
}

function render_footer(bool $showFooter = true): void
{
    ?>
        </main>
        <?php if ($showFooter): ?>
            <footer class="site-footer">
                <small>Quest</small>
            </footer>
        <?php endif; ?>
    </div>
    <script src="<?= h(asset_url('assets/js/app.js')) ?>"></script>
</body>
</html>
<?php
}
