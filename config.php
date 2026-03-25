<?php
declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

return [
    'app_name' => 'Quest',
    'app_url' => getenv('QUEST_APP_URL') ?: 'https://quest.cidadenovainforma.com.br',
    'db' => [
        'host' => getenv('QUEST_DB_HOST') ?: 'localhost',
        'port' => (int) (getenv('QUEST_DB_PORT') ?: 3306),
        'database' => getenv('QUEST_DB_NAME') ?: 'u488847015_quest_baseDado',
        'username' => getenv('QUEST_DB_USER') ?: 'u488847015_quest_userName',
        'password' => getenv('QUEST_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'from_name' => 'Quest',
        'from_email' => getenv('QUEST_MAIL_FROM') ?: 'nao-responda@quest.cidadenovainforma.com.br',
    ],
    'password_reset_expires_minutes' => 60,
];
