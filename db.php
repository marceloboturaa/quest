<?php
declare(strict_types=1);

if (!function_exists('db_connection_catalog')) {
    function db_connection_catalog(): array
    {
        $connections = config('db_connections', null);

        if (is_array($connections) && $connections !== []) {
            return $connections;
        }

        $environmentConfig = require __DIR__ . '/env.php';

        return is_array($environmentConfig['connections'] ?? null)
            ? $environmentConfig['connections']
            : [];
    }
}

if (!function_exists('db_current_environment')) {
    function db_current_environment(): string
    {
        return (string) config('db_environment', 'local');
    }
}

if (!function_exists('set_db_environment')) {
    function set_db_environment(string $environment): void
    {
        $environment = strtolower(trim($environment));
        $connections = db_connection_catalog();

        if (!isset($connections[$environment])) {
            throw new InvalidArgumentException(
                'Ambiente de banco invalido: ' . $environment . '. Use local ou hostinger.'
            );
        }

        global $config;
        $config['db_environment'] = $environment;
        $config['db'] = $connections[$environment];
    }
}

if (!function_exists('db_config')) {
    function db_config(?string $environment = null): array
    {
        $connections = db_connection_catalog();
        $environment = strtolower(trim($environment ?? db_current_environment()));

        if (!isset($connections[$environment])) {
            throw new InvalidArgumentException(
                'Ambiente de banco invalido: ' . $environment . '. Use local ou hostinger.'
            );
        }

        return $connections[$environment];
    }
}

if (!function_exists('db_dsn')) {
    function db_dsn(array $dbConfig): string
    {
        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $dbConfig['host'],
            (int) $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['charset']
        );
    }
}

if (!function_exists('db')) {
    function db(?string $environment = null): PDO
    {
        static $connections = [];

        $environment = strtolower(trim($environment ?? db_current_environment()));

        if (isset($connections[$environment]) && $connections[$environment] instanceof PDO) {
            return $connections[$environment];
        }

        $dbConfig = db_config($environment);
        $dsn = db_dsn($dbConfig);

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
            throw new RuntimeException(
                'Nao foi possivel conectar ao banco de dados [' . $environment . ']: ' . $exception->getMessage(),
                0,
                $exception
            );
        }

        if ($environment === 'local') {
            ensure_runtime_schema($pdo);
        }
        $connections[$environment] = $pdo;

        return $pdo;
    }
}

if (!function_exists('ensure_runtime_schema')) {
    function ensure_runtime_schema(PDO $pdo): void
    {
        static $ensured = [];
        $schemaKey = spl_object_id($pdo);

        if (isset($ensured[$schemaKey])) {
            return;
        }

        $ensured[$schemaKey] = true;

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

        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS `system_settings` (
                `setting_key` VARCHAR(100) NOT NULL,
                `setting_value` VARCHAR(255) NOT NULL,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`setting_key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $pdo->exec(
            "INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`)
             VALUES ('registration_enabled', '1')"
        );
    }
}

if (!function_exists('db_example_select_users')) {
    function db_example_select_users(?string $environment = null): array
    {
        $statement = db($environment)->query('SELECT * FROM users');

        return $statement->fetchAll();
    }
}

if (!function_exists('system_setting')) {
    function system_setting(string $key, mixed $default = null): mixed
    {
        try {
            $statement = db()->prepare('SELECT setting_value FROM system_settings WHERE setting_key = :setting_key LIMIT 1');
            $statement->execute(['setting_key' => $key]);
            $value = $statement->fetchColumn();
        } catch (Throwable) {
            return $default;
        }

        if ($value === false) {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('system_setting_bool')) {
    function system_setting_bool(string $key, bool $default = false): bool
    {
        $value = system_setting($key, $default ? '1' : '0');

        if (is_bool($value)) {
            return $value;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }
}

if (!function_exists('system_set_setting')) {
    function system_set_setting(string $key, string $value): void
    {
        try {
            db()->exec(
                "CREATE TABLE IF NOT EXISTS `system_settings` (
                    `setting_key` VARCHAR(100) NOT NULL,
                    `setting_value` VARCHAR(255) NOT NULL,
                    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`setting_key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );

            $statement = db()->prepare(
                'INSERT INTO system_settings (setting_key, setting_value, updated_at)
                 VALUES (:setting_key, :setting_value, NOW())
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()'
            );
            $statement->execute([
                'setting_key' => $key,
                'setting_value' => $value,
            ]);
        } catch (Throwable $throwable) {
            throw new RuntimeException('Nao foi possivel salvar a configuracao do sistema: ' . $throwable->getMessage(), 0, $throwable);
        }
    }
}

if (!function_exists('system_registration_enabled')) {
    function system_registration_enabled(): bool
    {
        return system_setting_bool('registration_enabled', true);
    }
}

if (!function_exists('system_set_registration_enabled')) {
    function system_set_registration_enabled(bool $enabled): void
    {
        system_set_setting('registration_enabled', $enabled ? '1' : '0');
    }
}
