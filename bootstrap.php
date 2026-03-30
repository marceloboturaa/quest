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
        throw new RuntimeException('Não foi possível conectar ao banco de dados: ' . $exception->getMessage());
    }

    ensure_runtime_schema($pdo);

    return $pdo;
}

function ensure_runtime_schema(PDO $pdo): void
{
    static $ensured = false;

    if ($ensured) {
        return;
    }

    $ensured = true;

    $userRoleColumn = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'role'")->fetch();

    if ($userRoleColumn && !str_contains((string) $userRoleColumn['Type'], "'xerox'")) {
        $pdo->exec(
            "ALTER TABLE `users`
             MODIFY `role` ENUM('master_admin', 'local_admin', 'xerox', 'user') NOT NULL DEFAULT 'user'"
        );
    }

    $userColumnStatements = [
        'preferred_teacher_name' => "ALTER TABLE `users`
            ADD COLUMN `preferred_teacher_name` VARCHAR(120) NULL DEFAULT NULL AFTER `role`",
        'preferred_discipline' => "ALTER TABLE `users`
            ADD COLUMN `preferred_discipline` VARCHAR(120) NULL DEFAULT NULL AFTER `preferred_teacher_name`",
        'preferred_component_name' => "ALTER TABLE `users`
            ADD COLUMN `preferred_component_name` VARCHAR(140) NULL DEFAULT NULL AFTER `preferred_discipline`",
        'preferred_class_name' => "ALTER TABLE `users`
            ADD COLUMN `preferred_class_name` VARCHAR(80) NULL DEFAULT NULL AFTER `preferred_component_name`",
        'preferred_year_reference' => "ALTER TABLE `users`
            ADD COLUMN `preferred_year_reference` VARCHAR(80) NULL DEFAULT NULL AFTER `preferred_class_name`",
        'preferred_exam_label' => "ALTER TABLE `users`
            ADD COLUMN `preferred_exam_label` VARCHAR(140) NULL DEFAULT NULL AFTER `preferred_year_reference`",
        'preferred_school_name' => "ALTER TABLE `users`
            ADD COLUMN `preferred_school_name` VARCHAR(180) NULL DEFAULT NULL AFTER `preferred_exam_label`",
        'preferred_school_subtitle' => "ALTER TABLE `users`
            ADD COLUMN `preferred_school_subtitle` VARCHAR(180) NULL DEFAULT NULL AFTER `preferred_school_name`",
        'preferred_header_logo_left' => "ALTER TABLE `users`
            ADD COLUMN `preferred_header_logo_left` VARCHAR(500) NULL DEFAULT NULL AFTER `preferred_school_subtitle`",
        'preferred_header_logo_right' => "ALTER TABLE `users`
            ADD COLUMN `preferred_header_logo_right` VARCHAR(500) NULL DEFAULT NULL AFTER `preferred_header_logo_left`",
    ];

    foreach ($userColumnStatements as $column => $statement) {
        $columnExists = $pdo->query("SHOW COLUMNS FROM `users` LIKE " . $pdo->quote($column))->fetch();

        if (!$columnExists) {
            $pdo->exec($statement);
        }
    }

    $examColumnStatements = [
        'xerox_status' => "ALTER TABLE `exams`
            ADD COLUMN `xerox_status` ENUM('not_sent', 'sent', 'in_progress', 'finished') NOT NULL DEFAULT 'not_sent' AFTER `instructions`",
        'xerox_target_user_id' => "ALTER TABLE `exams`
            ADD COLUMN `xerox_target_user_id` INT UNSIGNED NULL DEFAULT NULL AFTER `xerox_status`",
        'xerox_requested_at' => "ALTER TABLE `exams`
            ADD COLUMN `xerox_requested_at` TIMESTAMP NULL DEFAULT NULL AFTER `xerox_target_user_id`",
        'xerox_started_at' => "ALTER TABLE `exams`
            ADD COLUMN `xerox_started_at` TIMESTAMP NULL DEFAULT NULL AFTER `xerox_requested_at`",
        'xerox_finished_at' => "ALTER TABLE `exams`
            ADD COLUMN `xerox_finished_at` TIMESTAMP NULL DEFAULT NULL AFTER `xerox_started_at`",
    ];

    foreach ($examColumnStatements as $column => $statement) {
        $columnExists = $pdo->query("SHOW COLUMNS FROM `exams` LIKE " . $pdo->quote($column))->fetch();

        if (!$columnExists) {
            $pdo->exec($statement);
        }
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `backup_runs` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `trigger_type` ENUM('manual', 'scheduled') NOT NULL DEFAULT 'manual',
            `status` ENUM('running', 'success', 'failed') NOT NULL DEFAULT 'running',
            `triggered_by_user_id` INT UNSIGNED NULL DEFAULT NULL,
            `file_name` VARCHAR(255) NULL DEFAULT NULL,
            `local_path` VARCHAR(500) NULL DEFAULT NULL,
            `drive_file_id` VARCHAR(255) NULL DEFAULT NULL,
            `drive_file_link` VARCHAR(500) NULL DEFAULT NULL,
            `size_bytes` BIGINT UNSIGNED NULL DEFAULT NULL,
            `error_message` TEXT NULL,
            `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `finished_at` TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `backup_runs_status_index` (`status`),
            KEY `backup_runs_started_at_index` (`started_at`),
            KEY `backup_runs_triggered_by_user_id_index` (`triggered_by_user_id`),
            CONSTRAINT `backup_runs_triggered_by_user_id_foreign`
                FOREIGN KEY (`triggered_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS `user_messages` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `sender_user_id` INT UNSIGNED NULL DEFAULT NULL,
            `recipient_user_id` INT UNSIGNED NOT NULL,
            `kind` ENUM('suggestion', 'reply', 'broadcast', 'direct') NOT NULL DEFAULT 'direct',
            `subject` VARCHAR(180) NOT NULL DEFAULT '',
            `body` TEXT NOT NULL,
            `status` ENUM('unread', 'read', 'replied', 'archived') NOT NULL DEFAULT 'unread',
            `reply_to_id` INT UNSIGNED NULL DEFAULT NULL,
            `delivery_group` VARCHAR(40) NULL DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `user_messages_recipient_status_index` (`recipient_user_id`, `status`),
            KEY `user_messages_sender_index` (`sender_user_id`),
            KEY `user_messages_kind_index` (`kind`),
            KEY `user_messages_delivery_group_index` (`delivery_group`),
            CONSTRAINT `user_messages_sender_user_id_foreign`
                FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
            CONSTRAINT `user_messages_recipient_user_id_foreign`
                FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
            CONSTRAINT `user_messages_reply_to_id_foreign`
                FOREIGN KEY (`reply_to_id`) REFERENCES `user_messages` (`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    );
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
