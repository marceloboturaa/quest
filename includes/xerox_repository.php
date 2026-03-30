<?php
declare(strict_types=1);

function xerox_operator_list(): array
{
    return db()
        ->query("SELECT id, name, email FROM users WHERE role = 'xerox' ORDER BY name ASC")
        ->fetchAll();
}

function xerox_operator_count(): int
{
    return count(xerox_operator_list());
}

function xerox_is_available(): bool
{
    return xerox_operator_count() > 0;
}

function xerox_manageable_users(): array
{
    return db()
        ->query("SELECT id, name, email, role, created_at FROM users WHERE role IN ('user', 'xerox') ORDER BY name ASC")
        ->fetchAll();
}

function exam_find_by_id(int $examId): ?array
{
    $statement = db()->prepare(
        'SELECT exams.*, users.name AS owner_name, xerox_user.name AS xerox_owner_name
         FROM exams
         INNER JOIN users ON users.id = exams.user_id
         LEFT JOIN users AS xerox_user ON xerox_user.id = exams.xerox_target_user_id
         WHERE exams.id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => $examId]);

    return $statement->fetch() ?: null;
}

function exam_find_accessible(int $examId, array $user): ?array
{
    $exam = exam_find_by_id($examId);

    if ($exam === null) {
        return null;
    }

    if ((int) $exam['user_id'] === (int) $user['id']) {
        return $exam;
    }

    if (in_array((string) $user['role'], ['master_admin', 'local_admin'], true)) {
        return $exam;
    }

    if ((string) $user['role'] === 'xerox' && in_array((string) $exam['xerox_status'], ['sent', 'in_progress', 'finished'], true)) {
        return $exam;
    }

    return null;
}

function exam_questions_for_view(int $examId): array
{
    $statement = db()->prepare(
        'SELECT questions.*, exam_questions.display_order,
                disciplines.name AS discipline_name, subjects.name AS subject_name
         FROM exam_questions
         INNER JOIN questions ON questions.id = exam_questions.question_id
         LEFT JOIN disciplines ON disciplines.id = questions.discipline_id
         LEFT JOIN subjects ON subjects.id = questions.subject_id
         WHERE exam_questions.exam_id = :exam_id
         ORDER BY exam_questions.display_order ASC'
    );
    $statement->execute(['exam_id' => $examId]);
    $rows = $statement->fetchAll();
    $questions = [];
    $seenQuestionIds = [];

    foreach ($rows as $question) {
        $questionId = (int) ($question['id'] ?? 0);

        if ($questionId <= 0 || isset($seenQuestionIds[$questionId])) {
            continue;
        }

        $seenQuestionIds[$questionId] = true;
        $questions[] = $question;
    }

    return [$questions, find_question_options(array_map(static fn(array $question): int => (int) $question['id'], $questions))];
}

function xerox_owner_exam_list(int $userId): array
{
    $statement = db()->prepare(
        'SELECT exams.*, COUNT(exam_questions.id) AS total_questions, xerox_user.name AS xerox_owner_name
         FROM exams
         LEFT JOIN exam_questions ON exam_questions.exam_id = exams.id
         LEFT JOIN users AS xerox_user ON xerox_user.id = exams.xerox_target_user_id
         WHERE exams.user_id = :user_id
         GROUP BY exams.id, xerox_user.name
         ORDER BY exams.updated_at DESC'
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

function xerox_queue_list(array $statuses = ['sent', 'in_progress', 'finished']): array
{
    if ($statuses === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($statuses), '?'));
    $statement = db()->prepare(
        "SELECT exams.*, users.name AS owner_name, xerox_user.name AS xerox_owner_name, COUNT(exam_questions.id) AS total_questions
         FROM exams
         INNER JOIN users ON users.id = exams.user_id
         LEFT JOIN users AS xerox_user ON xerox_user.id = exams.xerox_target_user_id
         LEFT JOIN exam_questions ON exam_questions.exam_id = exams.id
         WHERE exams.xerox_status IN ($placeholders)
         GROUP BY exams.id, users.name, xerox_user.name
         ORDER BY exams.updated_at DESC"
    );
    $statement->execute($statuses);

    return $statement->fetchAll();
}

function xerox_queue_totals(): array
{
    $rows = db()
        ->query("SELECT xerox_status, COUNT(*) AS total FROM exams GROUP BY xerox_status")
        ->fetchAll();

    $totals = [
        'not_sent' => 0,
        'sent' => 0,
        'in_progress' => 0,
        'finished' => 0,
    ];

    foreach ($rows as $row) {
        $totals[(string) $row['xerox_status']] = (int) $row['total'];
    }

    return $totals;
}

function xerox_submit_exam(int $examId, int $ownerUserId): bool
{
    if (!xerox_is_available()) {
        return false;
    }

    $statement = db()->prepare(
        "UPDATE exams
         SET xerox_status = 'sent',
             xerox_target_user_id = NULL,
             xerox_requested_at = NOW(),
             xerox_started_at = NULL,
             xerox_finished_at = NULL,
             updated_at = NOW()
         WHERE id = :id
           AND user_id = :user_id
           AND xerox_status = 'not_sent'"
    );
    $statement->execute([
        'id' => $examId,
        'user_id' => $ownerUserId,
    ]);

    return $statement->rowCount() === 1;
}

function xerox_cancel_exam(int $examId, int $ownerUserId): bool
{
    $statement = db()->prepare(
        "UPDATE exams
         SET xerox_status = 'not_sent',
             xerox_target_user_id = NULL,
             xerox_requested_at = NULL,
             xerox_started_at = NULL,
             xerox_finished_at = NULL,
             updated_at = NOW()
         WHERE id = :id
           AND user_id = :user_id
           AND xerox_status = 'sent'"
    );
    $statement->execute([
        'id' => $examId,
        'user_id' => $ownerUserId,
    ]);

    return $statement->rowCount() === 1;
}

function xerox_resend_exam(int $examId, int $ownerUserId): bool
{
    if (!xerox_is_available()) {
        return false;
    }

    $statement = db()->prepare(
        "UPDATE exams
         SET xerox_status = 'sent',
             xerox_target_user_id = NULL,
             xerox_requested_at = NOW(),
             xerox_started_at = NULL,
             xerox_finished_at = NULL,
             updated_at = NOW()
         WHERE id = :id
           AND user_id = :user_id
           AND xerox_status = 'finished'"
    );
    $statement->execute([
        'id' => $examId,
        'user_id' => $ownerUserId,
    ]);

    return $statement->rowCount() === 1;
}

function xerox_accept_exam(int $examId, int $xeroxUserId): bool
{
    $statement = db()->prepare(
        "UPDATE exams
         SET xerox_status = 'in_progress',
             xerox_target_user_id = :xerox_user_id,
             xerox_started_at = NOW(),
             updated_at = NOW()
         WHERE id = :id
           AND xerox_status = 'sent'"
    );
    $statement->execute([
        'id' => $examId,
        'xerox_user_id' => $xeroxUserId,
    ]);

    return $statement->rowCount() === 1;
}

function xerox_finish_exam(int $examId, int $xeroxUserId): bool
{
    $statement = db()->prepare(
        "UPDATE exams
         SET xerox_status = 'finished',
             xerox_target_user_id = COALESCE(xerox_target_user_id, :xerox_user_id_set),
             xerox_finished_at = NOW(),
             updated_at = NOW()
         WHERE id = :id
           AND xerox_status = 'in_progress'
           AND (xerox_target_user_id IS NULL OR xerox_target_user_id = :xerox_user_id_match)"
    );
    $statement->execute([
        'id' => $examId,
        'xerox_user_id_set' => $xeroxUserId,
        'xerox_user_id_match' => $xeroxUserId,
    ]);

    return $statement->rowCount() === 1;
}

function xerox_set_user_role(int $userId, string $newRole): bool
{
    if (!in_array($newRole, ['user', 'xerox'], true)) {
        return false;
    }

    $statement = db()->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
    $statement->execute(['id' => $userId]);
    $target = $statement->fetch();

    if (!$target || !in_array((string) $target['role'], ['user', 'xerox'], true)) {
        return false;
    }

    if ((string) $target['role'] === $newRole) {
        return true;
    }

    $update = db()->prepare('UPDATE users SET role = :role, updated_at = NOW() WHERE id = :id');
    $update->execute([
        'role' => $newRole,
        'id' => $userId,
    ]);

    return $update->rowCount() === 1;
}
