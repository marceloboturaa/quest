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

require_once __DIR__ . '/db.php';

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

function asset_url(string $path): string
{
    $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
    $absolutePath = __DIR__ . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath);
    $version = is_file($absolutePath) ? (string) filemtime($absolutePath) : (string) time();

    return $normalizedPath . '?v=' . rawurlencode($version);
}

function storage_path(string $path = ''): string
{
    $base = rtrim((string) config('storage_path', sys_get_temp_dir()), '\\/');
    $normalized = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

    return $normalized === '' ? $base : $base . DIRECTORY_SEPARATOR . ltrim($normalized, DIRECTORY_SEPARATOR);
}

function bool_config(string $key, bool $default = false): bool
{
    $value = config($key, $default ? '1' : '0');

    if (is_bool($value)) {
        return $value;
    }

    return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
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
        flash('error', 'A sessão expirou. Tente novamente.');
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
        flash('error', 'Faça login para continuar.');
        redirect('login.php');
    }
}

/**
 * @param string|array $roles
 */
function has_role($roles): bool
{
    $user = current_user();

    if ($user === null) {
        return false;
    }

    return in_array($user['role'], (array) $roles, true);
}

/**
 * @param string|array $roles
 */
function require_role($roles): void
{
    require_login();

    if (!has_role($roles)) {
        flash('error', 'Você não tem permissão para acessar esta área.');
        redirect('dashboard.php');
    }
}

function role_label(string $role): string
{
    switch ($role) {
        case 'master_admin':
            return 'Master Admin';
        case 'local_admin':
            return 'Admin Local';
        case 'xerox':
            return 'Xerox';
        default:
            return 'Usuário';
    }
}

function question_type_label(string $type): string
{
    switch ($type) {
        case 'multiple_choice':
            return 'Múltipla escolha';
        case 'discursive':
            return 'Discursiva';
        case 'drawing':
            return 'Desenho / espaço livre';
        case 'true_false':
            return 'Verdadeiro ou falso';
        default:
            return 'Não definido';
    }
}

function visibility_label(string $visibility): string
{
    switch ($visibility) {
        case 'public':
            return 'Pública';
        default:
            return 'Privada';
    }
}

function education_level_label(string $level): string
{
    switch ($level) {
        case 'fundamental':
            return 'Ensino Fundamental';
        case 'medio':
            return 'Ensino Médio';
        case 'tecnico':
            return 'Técnico';
        case 'superior':
            return 'Superior';
        default:
            return 'Não definido';
    }
}

function drawing_size_label(?string $size, ?int $height = null): string
{
    switch ($size) {
        case 'small':
            return 'Pequeno';
        case 'custom':
            return $height !== null ? 'Customizado (' . $height . ' px)' : 'Customizado';
        case 'large':
            return 'Grande';
        default:
            return 'Médio';
    }
}

function status_label(string $status): string
{
    switch ($status) {
        case 'published':
            return 'Publicada';
        case 'review':
            return 'Em revisão';
        default:
            return 'Rascunho';
    }
}

function can_manage_users(): bool
{
    return has_role('master_admin');
}

function can_authorize_xerox_users(): bool
{
    return has_role(['master_admin', 'local_admin']);
}

function can_view_xerox_queue(): bool
{
    return has_role(['master_admin', 'local_admin', 'xerox']);
}

function is_xerox_user(): bool
{
    return has_role('xerox');
}

function can_manage_all_questions(): bool
{
    return has_role(['master_admin', 'local_admin']);
}

function can_manage_question_bulk_deletion(): bool
{
    return has_role('master_admin');
}

function can_manage_catalogs(): bool
{
    return has_role(['master_admin', 'local_admin']);
}

function can_manage_backups(): bool
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
    $subject = 'Quest - Redefinição de senha';
    $message = "Olá {$user['name']},\n\n";
    $message .= "Recebemos um pedido para redefinir a sua senha.\n";
    $message .= "Use o link abaixo para criar uma nova senha:\n\n";
    $message .= $resetLink . "\n\n";
    $message .= 'Se você não fez esta solicitação, ignore este e-mail.';

    $headers = [
        'From: ' . config('mail.from_name') . ' <' . config('mail.from_email') . '>',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $logDirectory = storage_path();

    if (!is_dir($logDirectory)) {
        mkdir($logDirectory, 0775, true);
    }

    $logLine = '[' . date('Y-m-d H:i:s') . '] TO ' . $user['email'] . PHP_EOL . $message . PHP_EOL . PHP_EOL;
    file_put_contents(storage_path('mail.log'), $logLine, FILE_APPEND);

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

function datetime_label(?string $value, string $fallback = 'Não informado'): string
{
    if ($value === null || trim($value) === '') {
        return $fallback;
    }

    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return $fallback;
    }

    return date('d/m/Y H:i', $timestamp);
}

function xerox_status_label(string $status): string
{
    switch ($status) {
        case 'sent':
            return 'Encaminhado';
        case 'in_progress':
            return 'Em andamento';
        case 'finished':
            return 'Finalizado';
        default:
            return 'Não encaminhado';
    }
}

function xerox_status_badge_class(string $status): string
{
    switch ($status) {
        case 'finished':
            return 'badge-success';
        case 'sent':
        case 'in_progress':
            return 'badge-accent';
        default:
            return '';
    }
}

require_once __DIR__ . '/includes/messages_repository.php';
