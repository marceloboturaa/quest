<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

$requestedEnvironment = $_GET['env'] ?? null;

if (is_string($requestedEnvironment) && $requestedEnvironment !== '') {
    set_db_environment($requestedEnvironment);
}

$users = db_example_select_users();
$currentEnvironment = db_current_environment();

header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exemplo PDO - Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #1f2937;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background: #f9fafb;
        }
    </style>
</head>
<body>
    <h1>Exemplo de SELECT * FROM users</h1>
    <p>Ambiente atual: <code><?= h($currentEnvironment) ?></code></p>
    <p>Troque o ambiente usando <code>?env=local</code> ou <code>?env=hostinger</code>.</p>

    <p>Total de registros: <strong><?= count($users) ?></strong></p>

    <table>
        <thead>
            <tr>
                <?php if ($users !== []): ?>
                    <?php foreach (array_keys($users[0]) as $column): ?>
                        <th><?= h((string) $column) ?></th>
                    <?php endforeach; ?>
                <?php else: ?>
                    <th>Sem registros</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <?php foreach ($user as $value): ?>
                        <td><?= h(is_scalar($value) || $value === null ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
