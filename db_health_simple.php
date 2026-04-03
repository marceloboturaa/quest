<?php

$env = @include __DIR__ . '/env.php';
if (!is_array($env)) {
    $env = array();
}

$connections = isset($env['connections']) && is_array($env['connections']) ? $env['connections'] : array();

function db_health_simple_connect($config)
{
    $host = isset($config['host']) ? $config['host'] : '';
    $port = isset($config['port']) ? (int) $config['port'] : 3306;
    $database = isset($config['database']) ? $config['database'] : '';
    $username = isset($config['username']) ? $config['username'] : '';
    $password = isset($config['password']) ? $config['password'] : '';
    $charset = isset($config['charset']) ? $config['charset'] : 'utf8mb4';

    $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database . ';charset=' . $charset;

    try {
        if (!class_exists('PDO')) {
            return array(
                'ok' => false,
                'message' => 'PDO nao esta habilitado neste servidor.',
            );
        }

        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->query('SELECT 1');

        return array(
            'ok' => true,
            'message' => 'Conexao OK',
        );
    } catch (Exception $e) {
        return array(
            'ok' => false,
            'message' => $e->getMessage(),
        );
    }
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostico DB simples</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 24px; color: #111827; background: #f8fafc; }
        .card { max-width: 900px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
        .ok { color: #166534; }
        .fail { color: #b91c1c; }
        table { border-collapse: collapse; width: 100%; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px 10px; text-align: left; }
        th { background: #f9fafb; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Diagnostico simples de banco</h1>
        <p>Use esta pagina para descobrir se o erro 500 vem de PHP, PDO ou da conexao com MySQL.</p>
    </div>

    <div class="card">
        <h2>Ambiente PHP</h2>
        <table>
            <tbody>
                <tr><th>PHP</th><td><?php echo htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><th>SAPI</th><td><?php echo htmlspecialchars(php_sapi_name(), ENT_QUOTES, 'UTF-8'); ?></td></tr>
                <tr><th>PDO</th><td><?php echo class_exists('PDO') ? 'habilitado' : 'desabilitado'; ?></td></tr>
                <tr><th>PDO MySQL</th><td><?php echo extension_loaded('pdo_mysql') ? 'habilitado' : 'desabilitado'; ?></td></tr>
            </tbody>
        </table>
    </div>

    <?php foreach ($connections as $name => $config): ?>
        <?php $result = db_health_simple_connect($config); ?>
        <div class="card">
            <h2><?php echo htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="<?php echo $result['ok'] ? 'ok' : 'fail'; ?>">
                <?php echo $result['ok'] ? 'OK' : 'Falha'; ?>: <?php echo htmlspecialchars((string) $result['message'], ENT_QUOTES, 'UTF-8'); ?>
            </p>
            <table>
                <tbody>
                    <tr><th>Host</th><td><?php echo htmlspecialchars((string) (isset($config['host']) ? $config['host'] : ''), ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><th>Porta</th><td><?php echo htmlspecialchars((string) (isset($config['port']) ? $config['port'] : 3306), ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><th>Banco</th><td><?php echo htmlspecialchars((string) (isset($config['database']) ? $config['database'] : ''), ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><th>Usuario</th><td><?php echo htmlspecialchars((string) (isset($config['username']) ? $config['username'] : ''), ENT_QUOTES, 'UTF-8'); ?></td></tr>
                    <tr><th>Senha</th><td><?php echo !empty($config['password']) ? 'definida' : 'vazia'; ?></td></tr>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
</body>
</html>
