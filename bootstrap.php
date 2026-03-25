<?php
declare(strict_types=1);

$config = require __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/includes/layout.php';

function config(?string $key = null, mixed $default = null): mixed
{
    global $config;

    if ($key === null) {
        return $config;
    }

    $value = $config;

    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }

        $value = $value[$segment];
    }

    return $value;
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dbConfig = config('db');
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $dbConfig['host'],
        $dbConfig['port'],
        $dbConfig['database'],
        $dbConfig['charset']
    );

    try {
        $pdo = new PDO(
            $dsn,
            $dbConfig['username'],
            $dbConfig['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $exception) {
        throw new RuntimeException('Nao foi possivel conectar ao banco de dados: ' . $exception->getMessage());
    }

    return $pdo;
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
    $base = rtrim((string) config('app_url', ''), '/');
    $path = ltrim($path, '/');

    return $path === '' ? $base : $base . '/' . $path;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function flash(string $type, string $message): void
{
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message,
    ];
}

function pull_flashes(): array
{
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);

    return $messages;
}

function remember_input(array $input): void
{
    $_SESSION['old_input'] = $input;
}

function old(string $key, string $default = ''): string
{
    $value = $_SESSION['old_input'][$key] ?? $default;

    return is_scalar($value) ? (string) $value : $default;
}

function forget_old_input(): void
{
    unset($_SESSION['old_input']);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): bool
{
    $token = $_POST['_token'] ?? '';

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function abort_if_invalid_csrf(): void
{
    if (!verify_csrf()) {
        flash('error', 'A sessao expirou. Tente novamente.');
        redirect($_SERVER['HTTP_REFERER'] ?? 'login.php');
    }
}

function current_user(): ?array
{
    static $user = false;

    if ($user !== false) {
        return $user;
    }

    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        $user = null;
        return $user;
    }

    $statement = db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $userId]);
    $user = $statement->fetch() ?: null;

    if ($user === null) {
        unset($_SESSION['user_id']);
    }

    return $user;
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
}

function logout_user(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

function require_guest(): void
{
    if (current_user() !== null) {
        redirect('dashboard.php');
    }
}

function require_login(): void
{
    if (current_user() === null) {
        flash('error', 'Faca login para continuar.');
        redirect('login.php');
    }
}

function has_role(string|array $roles): bool
{
    $user = current_user();

    if ($user === null) {
        return false;
    }

    return in_array($user['role'], (array) $roles, true);
}

function require_role(string|array $roles): void
{
    require_login();

    if (!has_role($roles)) {
        flash('error', 'Voce nao tem permissao para acessar essa area.');
        redirect('dashboard.php');
    }
}

function role_label(string $role): string
{
    return match ($role) {
        'master_admin' => 'Master Admin',
        'local_admin' => 'Admin Local',
        default => 'Usuario',
    };
}

function question_type_label(string $type): string
{
    return match ($type) {
        'multiple_choice' => 'Multipla escolha',
        'discursive' => 'Discursiva',
        'drawing' => 'Desenho / espaco livre',
        'true_false' => 'Verdadeiro ou falso',
        default => 'Nao definido',
    };
}

function visibility_label(string $visibility): string
{
    return match ($visibility) {
        'public' => 'Publica',
        default => 'Privada',
    };
}

function education_level_label(string $level): string
{
    return match ($level) {
        'fundamental' => 'Ensino Fundamental',
        'medio' => 'Ensino Medio',
        'tecnico' => 'Tecnico',
        'superior' => 'Superior',
        default => 'Nao definido',
    };
}

function drawing_size_label(?string $size): string
{
    return match ($size) {
        'small' => 'Pequeno',
        'large' => 'Grande',
        default => 'Medio',
    };
}

function status_label(string $status): string
{
    return match ($status) {
        'published' => 'Publicada',
        'review' => 'Em revisao',
        default => 'Rascunho',
    };
}

function can_manage_users(): bool
{
    return has_role('master_admin');
}

function can_manage_all_questions(): bool
{
    return has_role(['master_admin', 'local_admin']);
}

function can_manage_catalogs(): bool
{
    return has_role(['master_admin', 'local_admin']);
}

function option_label(int $index): string
{
    $label = '';
    $number = $index + 1;

    while ($number > 0) {
        $remainder = ($number - 1) % 26;
        $label = chr(65 + $remainder) . $label;
        $number = intdiv($number - 1, 26);
    }

    return $label;
}

function send_password_reset_email(array $user, string $token): bool
{
    $resetLink = app_url('reset-password.php?token=' . urlencode($token));
    $subject = 'Quest - Redefinicao de senha';
    $message = "Ola {$user['name']},\n\n";
    $message .= "Recebemos um pedido para redefinir a sua senha.\n";
    $message .= "Use o link abaixo para criar uma nova senha:\n\n";
    $message .= $resetLink . "\n\n";
    $message .= 'Se voce nao fez essa solicitacao, ignore este e-mail.';

    $headers = [
        'From: ' . config('mail.from_name') . ' <' . config('mail.from_email') . '>',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $logLine = '[' . date('Y-m-d H:i:s') . '] TO ' . $user['email'] . PHP_EOL . $message . PHP_EOL . PHP_EOL;
    file_put_contents(__DIR__ . '/storage/mail.log', $logLine, FILE_APPEND);

    return @mail($user['email'], $subject, $message, implode("\r\n", $headers));
}

function find_question_options(array $questionIds): array
{
    if ($questionIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
    $statement = db()->prepare(
        "SELECT * FROM question_options WHERE question_id IN ($placeholders) ORDER BY display_order ASC"
    );
    $statement->execute($questionIds);

    $grouped = [];

    foreach ($statement->fetchAll() as $option) {
        $grouped[(int) $option['question_id']][] = $option;
    }

    return $grouped;
}

function dashboard_metrics(array $user): array
{
    if ($user['role'] === 'master_admin') {
        return [
            'users' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'questions' => (int) db()->query('SELECT COUNT(*) FROM questions')->fetchColumn(),
            'exams' => (int) db()->query('SELECT COUNT(*) FROM exams')->fetchColumn(),
            'local_admins' => (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'local_admin'")->fetchColumn(),
        ];
    }

    if ($user['role'] === 'local_admin') {
        return [
            'users' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'questions' => (int) db()->query('SELECT COUNT(*) FROM questions')->fetchColumn(),
            'exams' => (int) db()->query('SELECT COUNT(*) FROM exams')->fetchColumn(),
            'local_admins' => (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'local_admin'")->fetchColumn(),
        ];
    }

    $statement = db()->prepare('SELECT COUNT(*) FROM questions WHERE author_id = :author_id');
    $statement->execute(['author_id' => $user['id']]);
    $examStatement = db()->prepare('SELECT COUNT(*) FROM exams WHERE user_id = :user_id');
    $examStatement->execute(['user_id' => $user['id']]);

    return [
        'users' => 1,
        'questions' => (int) $statement->fetchColumn(),
        'exams' => (int) $examStatement->fetchColumn(),
        'local_admins' => 0,
    ];
}
