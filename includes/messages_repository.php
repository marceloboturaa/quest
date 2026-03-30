<?php
declare(strict_types=1);

function messages_master_admin_ids(): array
{
    $statement = db()->query("SELECT id FROM users WHERE role = 'master_admin' ORDER BY id ASC");
    return array_map('intval', array_column($statement->fetchAll(), 'id'));
}

function messages_xerox_ids(): array
{
    $statement = db()->query("SELECT id FROM users WHERE role = 'xerox' ORDER BY id ASC");
    return array_map('intval', array_column($statement->fetchAll(), 'id'));
}

function messages_recipient_ids_for_broadcast(int $senderId): array
{
    $statement = db()->prepare('SELECT id FROM users WHERE id <> :sender_id ORDER BY id ASC');
    $statement->execute(['sender_id' => $senderId]);
    return array_map('intval', array_column($statement->fetchAll(), 'id'));
}

function messages_insert_many(?int $senderId, array $recipientIds, string $kind, string $subject, string $body, ?int $replyToId = null, ?string $deliveryGroup = null): int
{
    $recipientIds = array_values(array_unique(array_filter(array_map('intval', $recipientIds), static fn(int $id): bool => $id > 0)));

    if ($recipientIds === []) {
        return 0;
    }

    $statement = db()->prepare(
        'INSERT INTO user_messages
            (sender_user_id, recipient_user_id, kind, subject, body, status, reply_to_id, delivery_group, created_at, updated_at)
         VALUES
            (:sender_user_id, :recipient_user_id, :kind, :subject, :body, :status, :reply_to_id, :delivery_group, NOW(), NOW())'
    );

    $count = 0;

    foreach ($recipientIds as $recipientId) {
        $statement->execute([
            'sender_user_id' => $senderId,
            'recipient_user_id' => $recipientId,
            'kind' => $kind,
            'subject' => $subject,
            'body' => $body,
            'status' => 'unread',
            'reply_to_id' => $replyToId,
            'delivery_group' => $deliveryGroup,
        ]);
        $count++;
    }

    return $count;
}

function messages_send_suggestion(int $senderId, string $subject, string $body): int
{
    $masters = array_values(array_filter(
        messages_master_admin_ids(),
        static fn(int $id): bool => $id !== $senderId
    ));

    return messages_insert_many($senderId, $masters, 'suggestion', $subject, $body, null, 'suggestion-' . bin2hex(random_bytes(8)));
}

function messages_send_to_xerox_team(int $senderId, string $subject, string $body): int
{
    $xeroxRecipients = array_values(array_filter(
        messages_xerox_ids(),
        static fn(int $id): bool => $id !== $senderId
    ));

    return messages_insert_many($senderId, $xeroxRecipients, 'direct', $subject, $body, null, 'xerox-' . bin2hex(random_bytes(8)));
}

function messages_send_reply(int $senderId, int $recipientUserId, string $subject, string $body, int $replyToId): bool
{
    $count = messages_insert_many($senderId, [$recipientUserId], 'reply', $subject, $body, $replyToId);

    if ($count > 0) {
        $update = db()->prepare(
            'UPDATE user_messages
             SET status = :status, updated_at = NOW()
             WHERE id = :id'
        );
        $update->execute([
            'status' => 'replied',
            'id' => $replyToId,
        ]);
    }

    return $count > 0;
}

function messages_send_broadcast(int $senderId, string $subject, string $body): int
{
    $recipients = messages_recipient_ids_for_broadcast($senderId);
    return messages_insert_many($senderId, $recipients, 'broadcast', $subject, $body, null, 'broadcast-' . bin2hex(random_bytes(8)));
}

function messages_send_direct(int $senderId, int $recipientUserId, string $subject, string $body): bool
{
    return messages_insert_many($senderId, [$recipientUserId], 'direct', $subject, $body, null, 'direct-' . bin2hex(random_bytes(8))) > 0;
}

function messages_mark_as_read(int $messageId, int $userId): bool
{
    $statement = db()->prepare(
        'UPDATE user_messages
         SET status = CASE WHEN status = "unread" THEN "read" ELSE status END,
             updated_at = NOW()
         WHERE id = :id AND recipient_user_id = :recipient_user_id'
    );
    $statement->execute([
        'id' => $messageId,
        'recipient_user_id' => $userId,
    ]);

    return $statement->rowCount() > 0;
}

function messages_delete_for_recipient(int $messageId, int $userId): bool
{
    $statement = db()->prepare(
        'DELETE FROM user_messages
         WHERE id = :id AND recipient_user_id = :recipient_user_id'
    );
    $statement->execute([
        'id' => $messageId,
        'recipient_user_id' => $userId,
    ]);

    return $statement->rowCount() > 0;
}

function messages_delete_broadcast_group(string $deliveryGroup, int $senderUserId): bool
{
    $deliveryGroup = trim($deliveryGroup);

    if ($deliveryGroup === '') {
        return false;
    }

    $statement = db()->prepare(
        'DELETE FROM user_messages
         WHERE sender_user_id = :sender_user_id
           AND kind = "broadcast"
           AND delivery_group = :delivery_group'
    );
    $statement->execute([
        'sender_user_id' => $senderUserId,
        'delivery_group' => $deliveryGroup,
    ]);

    return $statement->rowCount() > 0;
}

function messages_unread_count(int $userId): int
{
    $statement = db()->prepare(
        'SELECT COUNT(*)
         FROM user_messages
         WHERE recipient_user_id = :recipient_user_id AND status = "unread"'
    );
    $statement->execute(['recipient_user_id' => $userId]);

    return (int) $statement->fetchColumn();
}

function messages_latest_toast_for_user(int $userId): ?array
{
    $statement = db()->prepare(
        'SELECT user_messages.*, users.name AS sender_name
         FROM user_messages
         LEFT JOIN users ON users.id = user_messages.sender_user_id
         WHERE user_messages.recipient_user_id = :recipient_user_id
           AND user_messages.status = "unread"
           AND user_messages.kind IN ("broadcast", "reply", "direct", "suggestion")
         ORDER BY user_messages.created_at DESC
         LIMIT 1'
    );
    $statement->execute(['recipient_user_id' => $userId]);

    $message = $statement->fetch();
    return $message ?: null;
}

function messages_inbox_for_user(int $userId, int $limit = 40): array
{
    $limit = max(1, $limit);

    $statement = db()->prepare(
        'SELECT user_messages.*, users.name AS sender_name, users.email AS sender_email
         FROM user_messages
         LEFT JOIN users ON users.id = user_messages.sender_user_id
         WHERE user_messages.recipient_user_id = :recipient_user_id
         ORDER BY
            CASE WHEN user_messages.status = "unread" THEN 0 ELSE 1 END,
            user_messages.created_at DESC
         LIMIT ' . $limit
    );
    $statement->execute(['recipient_user_id' => $userId]);

    return $statement->fetchAll();
}

function messages_find_for_recipient(int $messageId, int $userId): ?array
{
    $statement = db()->prepare(
        'SELECT user_messages.*, users.name AS sender_name, users.email AS sender_email
         FROM user_messages
         LEFT JOIN users ON users.id = user_messages.sender_user_id
         WHERE user_messages.id = :id AND user_messages.recipient_user_id = :recipient_user_id
         LIMIT 1'
    );
    $statement->execute([
        'id' => $messageId,
        'recipient_user_id' => $userId,
    ]);

    $message = $statement->fetch();
    return $message ?: null;
}

function messages_recent_broadcast_groups(int $senderId, int $limit = 12): array
{
    $limit = max(1, $limit);

    $statement = db()->prepare(
        'SELECT
            MAX(user_messages.id) AS id,
            user_messages.delivery_group,
            MAX(user_messages.subject) AS subject,
            MAX(user_messages.body) AS body,
            MAX(user_messages.created_at) AS created_at,
            COUNT(*) AS total_recipients
         FROM user_messages
         WHERE user_messages.sender_user_id = :sender_user_id
           AND user_messages.kind = "broadcast"
           AND user_messages.delivery_group IS NOT NULL
         GROUP BY user_messages.delivery_group
         ORDER BY MAX(user_messages.created_at) DESC
         LIMIT ' . $limit
    );
    $statement->execute(['sender_user_id' => $senderId]);

    return $statement->fetchAll();
}

function messages_user_directory(int $currentUserId): array
{
    $statement = db()->prepare(
        'SELECT id, name, email, role
         FROM users
         WHERE id <> :current_user_id
         ORDER BY
            CASE role
                WHEN "master_admin" THEN 0
                WHEN "local_admin" THEN 1
                WHEN "xerox" THEN 2
                ELSE 3
            END,
            name ASC'
    );
    $statement->execute(['current_user_id' => $currentUserId]);

    return $statement->fetchAll();
}

function message_kind_label(string $kind): string
{
    return match ($kind) {
        'suggestion' => 'Sugestão',
        'reply' => 'Resposta',
        'broadcast' => 'Aviso geral',
        'direct' => 'Mensagem direta',
        default => 'Mensagem',
    };
}
