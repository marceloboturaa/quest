<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

$dbEnvironmentConfig = require __DIR__ . '/env.php';
$dbConnections = $dbEnvironmentConfig['connections'] ?? [];
$defaultDbEnvironment = (string) ($dbEnvironmentConfig['default_environment'] ?? 'local');
$activeDbEnvironment = array_key_exists($defaultDbEnvironment, $dbConnections) ? $defaultDbEnvironment : 'local';
$activeDbConfig = $dbConnections[$activeDbEnvironment] ?? [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => '',
    'username' => '',
    'password' => '',
    'charset' => 'utf8mb4',
];

$config = [
    'app_name' => 'Quest',
    'app_url' => getenv('QUEST_APP_URL') ?: 'https://quest.cidadenovainforma.com.br',
    'storage_path' => getenv('QUEST_STORAGE_PATH') ?: dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'quest-storage',
    'db_environment' => $activeDbEnvironment,
    'db_connections' => $dbConnections,
    'db' => $activeDbConfig,
    'mail' => [
        'from_name' => 'Quest',
        'from_email' => getenv('QUEST_MAIL_FROM') ?: 'nao-responda@quest.cidadenovainforma.com.br',
    ],
    'backup' => [
        'enabled' => getenv('QUEST_BACKUP_ENABLED') ?: '1',
        'path' => getenv('QUEST_BACKUP_PATH') ?: dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'quest-storage' . DIRECTORY_SEPARATOR . 'backups',
        'mysqldump_path' => getenv('QUEST_MYSQLDUMP_PATH') ?: 'C:\\xampp\\mysql\\bin\\mysqldump.exe',
        'schedule_time' => getenv('QUEST_BACKUP_SCHEDULE_TIME') ?: '02:00',
        'keep_local_files' => (int) (getenv('QUEST_BACKUP_KEEP_LOCAL') ?: 14),
        'keep_drive_files' => (int) (getenv('QUEST_BACKUP_KEEP_DRIVE') ?: 30),
        'include_storage' => getenv('QUEST_BACKUP_INCLUDE_STORAGE') ?: '1',
        'google_drive' => [
            'credentials_path' => getenv('QUEST_GOOGLE_DRIVE_CREDENTIALS') ?: '',
            'folder_id' => getenv('QUEST_GOOGLE_DRIVE_FOLDER_ID') ?: '',
            'shared_drive_id' => getenv('QUEST_GOOGLE_DRIVE_SHARED_DRIVE_ID') ?: '',
        ],
    ],
    'password_reset_expires_minutes' => 60,
];

$localConfigPath = __DIR__ . DIRECTORY_SEPARATOR . 'config.local.php';

if (is_file($localConfigPath)) {
    $overrides = require $localConfigPath;

    if (is_array($overrides)) {
        $config = array_replace_recursive($config, $overrides);
    }
}

return $config;
