<?php
declare(strict_types=1);

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

function backup_is_enabled(): bool
{
    return bool_config('backup.enabled', true);
}

function backup_local_root(): string
{
    return rtrim((string) config('backup.path', storage_path('backups')), '\\/');
}

function backup_schedule_time(): string
{
    return (string) config('backup.schedule_time', '02:00');
}

function backup_mysqldump_path(): string
{
    return (string) config('backup.mysqldump_path', 'mysqldump');
}

function backup_keep_local_files(): int
{
    return max(1, (int) config('backup.keep_local_files', 14));
}

function backup_keep_drive_files(): int
{
    return max(1, (int) config('backup.keep_drive_files', 30));
}

function backup_google_drive_credentials_path(): string
{
    return trim((string) config('backup.google_drive.credentials_path', ''));
}

function backup_google_drive_folder_id(): string
{
    return trim((string) config('backup.google_drive.folder_id', ''));
}

function backup_google_drive_shared_drive_id(): string
{
    return trim((string) config('backup.google_drive.shared_drive_id', ''));
}

function backup_google_drive_ready(): bool
{
    $credentialsPath = backup_google_drive_credentials_path();

    return $credentialsPath !== ''
        && is_file($credentialsPath)
        && backup_google_drive_folder_id() !== '';
}

function backup_status_summary(): array
{
    $credentialsPath = backup_google_drive_credentials_path();

    return [
        'enabled' => backup_is_enabled(),
        'schedule_time' => backup_schedule_time(),
        'local_root' => backup_local_root(),
        'mysqldump_path' => backup_mysqldump_path(),
        'google_drive_ready' => backup_google_drive_ready(),
        'google_drive_folder_id' => backup_google_drive_folder_id(),
        'google_drive_credentials_path' => $credentialsPath,
        'google_drive_credentials_exists' => $credentialsPath !== '' && is_file($credentialsPath),
        'keep_local_files' => backup_keep_local_files(),
        'keep_drive_files' => backup_keep_drive_files(),
    ];
}

function backup_history(int $limit = 20): array
{
    $limit = max(1, $limit);

    $statement = db()->prepare(
        'SELECT backup_runs.*, users.name AS triggered_by_name
         FROM backup_runs
         LEFT JOIN users ON users.id = backup_runs.triggered_by_user_id
         ORDER BY backup_runs.started_at DESC
         LIMIT ' . $limit
    );
    $statement->execute();

    return $statement->fetchAll();
}

function backup_latest_run(): ?array
{
    $runs = backup_history(1);

    return $runs[0] ?? null;
}

function backup_scheduler_command(): string
{
    return '"' . PHP_BINARY . '" "' . __DIR__ . '\\..\\scripts\\run_backup.php"';
}

function backup_register_task_command(): string
{
    return 'powershell -ExecutionPolicy Bypass -File "' . __DIR__ . '\\..\\scripts\\register_backup_task.ps1"';
}

function backup_create_run(string $triggerType, ?int $triggeredByUserId): int
{
    $statement = db()->prepare(
        'INSERT INTO backup_runs (trigger_type, status, triggered_by_user_id, started_at)
         VALUES (:trigger_type, :status, :triggered_by_user_id, NOW())'
    );
    $statement->execute([
        'trigger_type' => $triggerType,
        'status' => 'running',
        'triggered_by_user_id' => $triggeredByUserId,
    ]);

    return (int) db()->lastInsertId();
}

function backup_finish_run_success(int $runId, array $artifact, array $driveFile): void
{
    $statement = db()->prepare(
        'UPDATE backup_runs
         SET status = :status,
             file_name = :file_name,
             local_path = :local_path,
             drive_file_id = :drive_file_id,
             drive_file_link = :drive_file_link,
             size_bytes = :size_bytes,
             finished_at = NOW(),
             error_message = NULL
         WHERE id = :id'
    );
    $statement->execute([
        'id' => $runId,
        'status' => 'success',
        'file_name' => $artifact['file_name'],
        'local_path' => $artifact['zip_path'],
        'drive_file_id' => $driveFile['id'] ?? null,
        'drive_file_link' => $driveFile['webViewLink'] ?? null,
        'size_bytes' => $artifact['size_bytes'],
    ]);
}

function backup_finish_run_failure(int $runId, string $message): void
{
    $statement = db()->prepare(
        'UPDATE backup_runs
         SET status = :status,
             error_message = :error_message,
             finished_at = NOW()
         WHERE id = :id'
    );
    $statement->execute([
        'id' => $runId,
        'status' => 'failed',
        'error_message' => $message,
    ]);
}

function backup_execute_run(?int $triggeredByUserId, string $triggerType = 'manual'): array
{
    if (!backup_is_enabled()) {
        throw new RuntimeException('O backup automatico esta desativado na configuracao.');
    }

    if (!backup_google_drive_ready()) {
        throw new RuntimeException('Configure as credenciais e a pasta do Google Drive antes de executar o backup.');
    }

    set_time_limit(0);
    $runId = backup_create_run($triggerType, $triggeredByUserId);

    try {
        $artifact = backup_build_artifact($runId);
        $driveFile = backup_upload_to_google_drive($artifact['zip_path'], $artifact['file_name']);
        backup_apply_local_retention();
        backup_apply_drive_retention();
        backup_finish_run_success($runId, $artifact, $driveFile);

        return [
            'run_id' => $runId,
            'artifact' => $artifact,
            'drive_file' => $driveFile,
        ];
    } catch (Throwable $throwable) {
        backup_finish_run_failure($runId, $throwable->getMessage());
        throw $throwable;
    }
}

function backup_build_artifact(int $runId): array
{
    $localRoot = backup_local_root();
    $timestamp = date('Ymd-His');
    $yearMonthPath = date('Y') . DIRECTORY_SEPARATOR . date('m');
    $backupDirectory = $localRoot . DIRECTORY_SEPARATOR . $yearMonthPath;
    $tempDirectory = $localRoot . DIRECTORY_SEPARATOR . '.tmp' . DIRECTORY_SEPARATOR . 'run-' . $runId . '-' . $timestamp;
    $fileName = 'quest-backup-' . $timestamp . '.zip';
    $zipPath = $backupDirectory . DIRECTORY_SEPARATOR . $fileName;

    backup_ensure_directory($backupDirectory);
    backup_ensure_directory($tempDirectory);

    try {
        $dumpPath = $tempDirectory . DIRECTORY_SEPARATOR . 'database.sql';
        $manifestPath = $tempDirectory . DIRECTORY_SEPARATOR . 'manifest.json';

        backup_create_database_dump($dumpPath);
        file_put_contents($manifestPath, json_encode(backup_manifest_data($runId, $fileName), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Nao foi possivel criar o arquivo ZIP de backup.');
        }

        $zip->addFile($dumpPath, 'database/database.sql');
        $zip->addFile($manifestPath, 'manifest.json');

        foreach (backup_project_directories() as $directory) {
            backup_add_path_to_zip($zip, dirname(__DIR__) . DIRECTORY_SEPARATOR . $directory, 'app/' . $directory, [$localRoot]);
        }

        foreach (backup_project_files() as $file) {
            $absolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $file;

            if (is_file($absolutePath)) {
                $zip->addFile($absolutePath, 'app/' . str_replace('\\', '/', $file));
            }
        }

        if (bool_config('backup.include_storage', true)) {
            backup_add_path_to_zip($zip, storage_path(), 'storage', [$localRoot]);
        }

        $zip->close();

        return [
            'file_name' => $fileName,
            'zip_path' => $zipPath,
            'size_bytes' => is_file($zipPath) ? filesize($zipPath) : 0,
        ];
    } finally {
        backup_delete_directory($tempDirectory);
    }
}

function backup_manifest_data(int $runId, string $fileName): array
{
    return [
        'app' => (string) config('app_name', 'Quest'),
        'run_id' => $runId,
        'file_name' => $fileName,
        'generated_at' => date('c'),
        'app_url' => (string) config('app_url', ''),
        'db_name' => (string) config('db.database', ''),
        'storage_path' => storage_path(),
    ];
}

function backup_project_directories(): array
{
    return [
        'assets',
        'includes',
        'scripts',
    ];
}

function backup_project_files(): array
{
    return [
        'bootstrap.php',
        'composer.json',
        'composer.lock',
        'config.php',
        'dashboard.php',
        'database_geral_atualizado.sql',
        'enem.php',
        'exam-create.php',
        'exam-pdf.php',
        'exam-preview.php',
        'exams.php',
        'forgot-password.php',
        'index.php',
        'login.php',
        'logout.php',
        'questions.php',
        'README.md',
        'register.php',
        'reset-password.php',
        'users.php',
        'xerox.php',
    ];
}

function backup_create_database_dump(string $dumpPath): void
{
    try {
        backup_create_database_dump_with_mysqldump($dumpPath);
    } catch (Throwable $throwable) {
        backup_create_database_dump_with_pdo($dumpPath);
    }
}

function backup_create_database_dump_with_mysqldump(string $dumpPath): void
{
    $mysqldumpPath = backup_mysqldump_path();
    $dbConfig = config('db');

    if ($mysqldumpPath === '' || (!is_file($mysqldumpPath) && !str_contains($mysqldumpPath, 'mysqldump'))) {
        throw new RuntimeException('Nao foi possivel localizar o executavel do mysqldump.');
    }

    $command = [
        $mysqldumpPath,
        '--host=' . (string) $dbConfig['host'],
        '--port=' . (string) ((int) $dbConfig['port']),
        '--user=' . (string) $dbConfig['username'],
        '--single-transaction',
        '--quick',
        '--routines',
        '--events',
        '--default-character-set=' . (string) $dbConfig['charset'],
        (string) $dbConfig['database'],
    ];

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];

    $environment = $_ENV;
    $environment['MYSQL_PWD'] = (string) $dbConfig['password'];

    $process = proc_open($command, $descriptors, $pipes, dirname(__DIR__), $environment);

    if (!is_resource($process)) {
        throw new RuntimeException('Nao foi possivel iniciar o mysqldump.');
    }

    fclose($pipes[0]);
    $outputHandle = fopen($dumpPath, 'wb');

    if ($outputHandle === false) {
        throw new RuntimeException('Nao foi possivel criar o arquivo temporario do dump.');
    }

    try {
        stream_copy_to_stream($pipes[1], $outputHandle);
        fclose($pipes[1]);
        $errorOutput = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new RuntimeException('Falha ao gerar dump do banco: ' . trim((string) $errorOutput));
        }
    } finally {
        fclose($outputHandle);
    }
}

function backup_create_database_dump_with_pdo(string $dumpPath): void
{
    $pdo = db();
    $handle = fopen($dumpPath, 'wb');

    if ($handle === false) {
        throw new RuntimeException('Nao foi possivel criar o arquivo temporario do dump.');
    }

    try {
        fwrite($handle, "-- Quest backup fallback generated via PDO\n");
        fwrite($handle, '-- Generated at ' . date('c') . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

        $tables = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_NUM);
        $views = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'")->fetchAll(PDO::FETCH_NUM);

        foreach ($tables as $tableRow) {
            $tableName = (string) $tableRow[0];
            $escapedName = '`' . str_replace('`', '``', $tableName) . '`';
            $createRow = $pdo->query('SHOW CREATE TABLE ' . $escapedName)->fetch(PDO::FETCH_ASSOC);
            $createSql = (string) ($createRow['Create Table'] ?? '');

            fwrite($handle, "DROP TABLE IF EXISTS $escapedName;\n");
            fwrite($handle, $createSql . ";\n\n");

            $rows = $pdo->query('SELECT * FROM ' . $escapedName);

            while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                $columns = array_map(
                    static fn(string $column): string => '`' . str_replace('`', '``', $column) . '`',
                    array_keys($row)
                );
                $values = array_map(
                    static function ($value) use ($pdo): string {
                        if ($value === null) {
                            return 'NULL';
                        }

                        if (is_bool($value)) {
                            return $value ? '1' : '0';
                        }

                        if (is_int($value) || is_float($value)) {
                            return (string) $value;
                        }

                        return $pdo->quote((string) $value);
                    },
                    array_values($row)
                );

                fwrite(
                    $handle,
                    'INSERT INTO ' . $escapedName . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $values) . ");\n"
                );
            }

            fwrite($handle, "\n");
        }

        foreach ($views as $viewRow) {
            $viewName = (string) $viewRow[0];
            $escapedName = '`' . str_replace('`', '``', $viewName) . '`';
            $createRow = $pdo->query('SHOW CREATE VIEW ' . $escapedName)->fetch(PDO::FETCH_ASSOC);
            $createSql = (string) ($createRow['Create View'] ?? '');

            fwrite($handle, "DROP VIEW IF EXISTS $escapedName;\n");
            fwrite($handle, $createSql . ";\n\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
    } finally {
        fclose($handle);
    }
}

function backup_upload_to_google_drive(string $zipPath, string $fileName): array
{
    $service = backup_google_drive_service();
    $driveFile = new DriveFile([
        'name' => $fileName,
        'parents' => [backup_google_drive_folder_id()],
    ]);

    $created = $service->files->create(
        $driveFile,
        [
            'data' => file_get_contents($zipPath),
            'mimeType' => 'application/zip',
            'uploadType' => 'multipart',
            'fields' => 'id,name,webViewLink,size',
            'supportsAllDrives' => true,
        ]
    );

    return [
        'id' => (string) $created->id,
        'name' => (string) $created->name,
        'webViewLink' => (string) ($created->webViewLink ?? ''),
        'size' => (string) ($created->size ?? ''),
    ];
}

function backup_apply_local_retention(): void
{
    $root = backup_local_root();

    if (!is_dir($root)) {
        return;
    }

    $files = glob($root . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . 'quest-backup-*.zip') ?: [];

    usort($files, static fn(string $left, string $right): int => filemtime($right) <=> filemtime($left));

    foreach (array_slice($files, backup_keep_local_files()) as $file) {
        @unlink($file);
    }
}

function backup_apply_drive_retention(): void
{
    $service = backup_google_drive_service();
    $query = sprintf("'%s' in parents and trashed = false and name contains 'quest-backup-'", backup_google_drive_folder_id());
    $response = $service->files->listFiles([
        'q' => $query,
        'pageSize' => 100,
        'orderBy' => 'createdTime desc',
        'fields' => 'files(id,name,createdTime)',
        'supportsAllDrives' => true,
        'includeItemsFromAllDrives' => true,
    ]);

    $files = $response->getFiles();

    foreach (array_slice($files, backup_keep_drive_files()) as $file) {
        $service->files->delete($file->id, ['supportsAllDrives' => true]);
    }
}

function backup_google_drive_service(): Drive
{
    static $service = null;

    if ($service instanceof Drive) {
        return $service;
    }

    if (!backup_google_drive_ready()) {
        throw new RuntimeException('Google Drive nao configurado para backup.');
    }

    $client = new Client();
    $client->setApplicationName((string) config('app_name', 'Quest') . ' Backup');
    $client->setAuthConfig(backup_google_drive_credentials_path());
    $client->setScopes([Drive::DRIVE]);
    $client->setAccessType('offline');

    $service = new Drive($client);

    return $service;
}

function backup_add_path_to_zip(ZipArchive $zip, string $absolutePath, string $zipPrefix, array $excludedRoots = []): void
{
    if (!file_exists($absolutePath)) {
        return;
    }

    $absolutePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);
    $zipPrefix = trim(str_replace('\\', '/', $zipPrefix), '/');

    if (is_file($absolutePath)) {
        $zip->addFile($absolutePath, $zipPrefix);
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($absolutePath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $itemPath = (string) $item->getPathname();

        if (backup_path_is_excluded($itemPath, $excludedRoots)) {
            continue;
        }

        $relative = trim(str_replace('\\', '/', substr($itemPath, strlen($absolutePath))), '/');
        $zipTarget = $zipPrefix . ($relative !== '' ? '/' . $relative : '');

        if ($item->isDir()) {
            $zip->addEmptyDir($zipTarget);
            continue;
        }

        $zip->addFile($itemPath, $zipTarget);
    }
}

function backup_path_is_excluded(string $itemPath, array $excludedRoots): bool
{
    foreach ($excludedRoots as $excludedRoot) {
        $excludedRoot = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $excludedRoot), DIRECTORY_SEPARATOR);

        if ($excludedRoot !== '' && str_starts_with($itemPath, $excludedRoot)) {
            return true;
        }
    }

    return str_contains($itemPath, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR);
}

function backup_ensure_directory(string $path): void
{
    if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
        throw new RuntimeException('Nao foi possivel criar a pasta de backup: ' . $path);
    }
}

function backup_delete_directory(string $path): void
{
    if (!is_dir($path)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($iterator as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }

    @rmdir($path);
}
