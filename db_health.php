<?php
declare(strict_types=1);

$env = require __DIR__ . '/env.php';
$connections = $env['connections'] ?? [];

function db_health_try_connect(array $config): array
{
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['host'] ?? '',
        (int) ($config['port'] ?? 3306),
        $config['database'] ?? '',
        $config['charset'] ?? 'utf8mb4'
    );

    try {
        $pdo = new PDO(
            $dsn,
            $config['username'] ?? '',
            $config['password'] ?? '',
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        $pdo->query('SELECT 1');

        return [
            'ok' => true,
            'message' => 'Conexao OK',
        ];
    } catch (Throwable $throwable) {
        return [
            'ok' => false,
            'message' => $throwable->getMessage(),
        ];
    }
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostico DB</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 24px;
            color: #111827;
            background: #f8fafc;
        }
        .card {
            max-width: 920px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 16px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        }
        .ok { color: #166534; }
        .fail { color: #b91c1c; }
        code, pre {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
        }
        pre {
            white-space: pre-wrap;
            word-break: break-word;
            padding: 12px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid #e5e7eb;
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
    <div class="card">
        <h1>Diagnostico de banco</h1>
        <p>Esta pagina nao usa o bootstrap principal. Ela serve para descobrir por que o site da Hostinger pode estar retornando 500.</p>
    </div>

    <?php foreach ($connections as $name => $config): ?>
        <?php $result = db_health_try_connect((array) $config); ?>
        <div class="card">
            <h2><?= htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="<?= $result['ok'] ? 'ok' : 'fail' ?>">
                <?= $result['ok'] ? 'OK' : 'Falha' ?>: <?= htmlspecialchars((string) $result['message'], ENT_QUOTES, 'UTF-8') ?>
            </p>
            <table>
                <tbody>
                    <tr><th>Host</th><td><?= htmlspecialchars((string) ($config['host'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th>Porta</th><td><?= htmlspecialchars((string) ($config['port'] ?? 3306), ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th>Banco</th><td><?= htmlspecialchars((string) ($config['database'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th>Usuario</th><td><?= htmlspecialchars((string) ($config['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th>Senha</th><td><?= (!empty($config['password'])) ? 'definida' : 'vazia' ?></td></tr>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</body>
</html>
