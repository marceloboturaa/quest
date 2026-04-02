<?php
declare(strict_types=1);

return [
    'default_environment' => getenv('QUEST_DB_ENV') ?: 'local',
    'connections' => [
        'local' => [
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'u488847015_quest_basedado',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
        ],
        'hostinger' => [
            'host' => '193.203.175.150',
            'port' => 3306,
            'database' => 'u488847015_quest_baseDado',
            'username' => 'u488847015_quest_userName',
            'password' => getenv('QUEST_DB_HOSTINGER_PASS') ?: getenv('QUEST_DB_PASS') ?: '',
            'charset' => 'utf8mb4',
        ],
    ],
];
