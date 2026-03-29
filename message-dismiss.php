<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();
abort_if_invalid_csrf();

$user = current_user();
$messageId = (int) ($_POST['message_id'] ?? 0);

header('Content-Type: application/json; charset=UTF-8');

if ($messageId <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false], JSON_UNESCAPED_UNICODE);
    exit;
}

$ok = messages_mark_as_read($messageId, (int) $user['id']);
echo json_encode(['ok' => $ok], JSON_UNESCAPED_UNICODE);
