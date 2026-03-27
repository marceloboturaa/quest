<?php
declare(strict_types=1);

function exam_available_questions(int $userId, array $filters = []): array
{
    $query = 'SELECT questions.id, questions.title, questions.prompt, questions.question_type, questions.visibility, questions.usage_count,
                     questions.source_name, questions.education_level, questions.difficulty,
                     disciplines.name AS discipline_name, subjects.name AS subject_name, users.name AS author_name
              FROM questions
              INNER JOIN users ON users.id = questions.author_id
              LEFT JOIN disciplines ON disciplines.id = questions.discipline_id
              LEFT JOIN subjects ON subjects.id = questions.subject_id
              WHERE questions.visibility = "public" OR questions.author_id = :author_id';
    $params = ['author_id' => $userId];

    if (($filters['term'] ?? '') !== '') {
        $query .= ' AND (questions.title LIKE :term_title OR questions.prompt LIKE :term_prompt)';
        $term = '%' . trim((string) $filters['term']) . '%';
        $params['term_title'] = $term;
        $params['term_prompt'] = $term;
    }

    if (($filters['discipline_id'] ?? 0) > 0) {
        $query .= ' AND questions.discipline_id = :discipline_id';
        $params['discipline_id'] = (int) $filters['discipline_id'];
    }

    if (($filters['question_type'] ?? '') !== '' && in_array((string) $filters['question_type'], ['multiple_choice', 'discursive', 'drawing', 'true_false'], true)) {
        $query .= ' AND questions.question_type = :question_type';
        $params['question_type'] = (string) $filters['question_type'];
    }

    if (($filters['visibility'] ?? '') !== '' && in_array((string) $filters['visibility'], ['public', 'private'], true)) {
        $query .= ' AND questions.visibility = :visibility';
        $params['visibility'] = (string) $filters['visibility'];
    }

    $query .= ' ORDER BY questions.created_at DESC';

    $statement = db()->prepare($query);
    $statement->execute($params);

    return $statement->fetchAll();
}

function exam_list(int $userId): array
{
    $statement = db()->prepare(
        'SELECT exams.*, COUNT(exam_questions.id) AS total_questions
         FROM exams
         LEFT JOIN exam_questions ON exam_questions.exam_id = exams.id
         WHERE exams.user_id = :user_id
         GROUP BY exams.id
         ORDER BY exams.created_at DESC'
    );
    $statement->execute(['user_id' => $userId]);

    return $statement->fetchAll();
}

function exam_visible_question_ids(array $questionIds, int $userId): array
{
    if ($questionIds === []) {
        return [];
    }

    $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
    $params = array_merge($questionIds, [$userId]);
    $statement = db()->prepare(
        "SELECT id FROM questions WHERE id IN ($placeholders) AND (visibility = 'public' OR author_id = ?)"
    );
    $statement->execute($params);

    return array_map(static fn(array $row): int => (int) $row['id'], $statement->fetchAll());
}

function exam_find(int $examId, int $userId): ?array
{
    $statement = db()->prepare(
        'SELECT * FROM exams
         WHERE id = :id AND user_id = :user_id
         LIMIT 1'
    );
    $statement->execute([
        'id' => $examId,
        'user_id' => $userId,
    ]);

    return $statement->fetch() ?: null;
}

function exam_questions(int $examId, int $userId): array
{
    $statement = db()->prepare(
        'SELECT questions.*, exam_questions.display_order,
                disciplines.name AS discipline_name, subjects.name AS subject_name
         FROM exam_questions
         INNER JOIN exams ON exams.id = exam_questions.exam_id
         INNER JOIN questions ON questions.id = exam_questions.question_id
         LEFT JOIN disciplines ON disciplines.id = questions.discipline_id
         LEFT JOIN subjects ON subjects.id = questions.subject_id
         WHERE exam_questions.exam_id = :exam_id AND exams.user_id = :user_id
         ORDER BY exam_questions.display_order ASC'
    );
    $statement->execute([
        'exam_id' => $examId,
        'user_id' => $userId,
    ]);
    $questions = $statement->fetchAll();

    return [$questions, find_question_options(array_map(static fn(array $question): int => (int) $question['id'], $questions))];
}

function exam_question_ids(int $examId, int $userId): array
{
    $statement = db()->prepare(
        'SELECT exam_questions.question_id
         FROM exam_questions
         INNER JOIN exams ON exams.id = exam_questions.exam_id
         WHERE exam_questions.exam_id = :exam_id AND exams.user_id = :user_id
         ORDER BY exam_questions.display_order ASC'
    );
    $statement->execute([
        'exam_id' => $examId,
        'user_id' => $userId,
    ]);

    return array_map(static fn(array $row): int => (int) $row['question_id'], $statement->fetchAll());
}
