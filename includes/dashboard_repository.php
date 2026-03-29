<?php
declare(strict_types=1);

function dashboard_recent_questions(int $userId, bool $canSeeAll, int $limit = 5): array
{
    $limit = max(1, $limit);

    if ($canSeeAll) {
        $statement = db()->prepare(
            'SELECT questions.id, questions.title, questions.question_type, questions.visibility, questions.created_at,
                    disciplines.name AS discipline_name, users.name AS author_name
             FROM questions
             INNER JOIN users ON users.id = questions.author_id
             LEFT JOIN disciplines ON disciplines.id = questions.discipline_id
             ORDER BY questions.created_at DESC
             LIMIT ' . $limit
        );
        $statement->execute();
        return $statement->fetchAll();
    }

    $statement = db()->prepare(
        'SELECT questions.id, questions.title, questions.question_type, questions.visibility, questions.created_at,
                disciplines.name AS discipline_name, users.name AS author_name
         FROM questions
         INNER JOIN users ON users.id = questions.author_id
         LEFT JOIN disciplines ON disciplines.id = questions.discipline_id
         WHERE questions.author_id = :author_id OR questions.visibility = "public"
         ORDER BY questions.created_at DESC
         LIMIT ' . $limit
    );
    $statement->execute(['author_id' => $userId]);

    return $statement->fetchAll();
}

function dashboard_recent_exams(int $userId, bool $canSeeAll, int $limit = 5): array
{
    $limit = max(1, $limit);

    if ($canSeeAll) {
        $statement = db()->prepare(
            'SELECT exams.*, users.name AS owner_name, xerox_user.name AS xerox_owner_name, COUNT(exam_questions.id) AS total_questions
             FROM exams
             INNER JOIN users ON users.id = exams.user_id
             LEFT JOIN users AS xerox_user ON xerox_user.id = exams.xerox_target_user_id
             LEFT JOIN exam_questions ON exam_questions.exam_id = exams.id
             GROUP BY exams.id, users.name, xerox_user.name
             ORDER BY exams.created_at DESC
             LIMIT ' . $limit
        );
        $statement->execute();
        return $statement->fetchAll();
    }

    $statement = db()->prepare(
        'SELECT exams.*, users.name AS owner_name, xerox_user.name AS xerox_owner_name, COUNT(exam_questions.id) AS total_questions
         FROM exams
         INNER JOIN users ON users.id = exams.user_id
         LEFT JOIN users AS xerox_user ON xerox_user.id = exams.xerox_target_user_id
         LEFT JOIN exam_questions ON exam_questions.exam_id = exams.id
         WHERE exams.user_id = :user_id
         GROUP BY exams.id, users.name, xerox_user.name
         ORDER BY exams.created_at DESC
         LIMIT ' . $limit
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

function dashboard_public_questions_total(): int
{
    return (int) db()->query('SELECT COUNT(*) FROM questions WHERE visibility = "public"')->fetchColumn();
}

function dashboard_user_questions_total(int $userId): int
{
    $statement = db()->prepare('SELECT COUNT(*) FROM questions WHERE author_id = :author_id');
    $statement->execute(['author_id' => $userId]);
    return (int) $statement->fetchColumn();
}
