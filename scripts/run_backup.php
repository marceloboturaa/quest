<?php
declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/includes/backup_repository.php';

try {
    $result = backup_execute_run(null, 'scheduled');
    echo 'Backup concluido: ' . $result['artifact']['file_name'] . PHP_EOL;
    exit(0);
} catch (Throwable $throwable) {
    fwrite(STDERR, 'Falha no backup: ' . $throwable->getMessage() . PHP_EOL);
    exit(1);
}
