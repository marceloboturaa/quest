<?php
declare(strict_types=1);

function question_disciplines(): array
{
    return db()->query('SELECT id, name FROM disciplines ORDER BY name ASC')->fetchAll();
}

function question_subjects(): array
{
    return db()->query(
        'SELECT subjects.id, subjects.name, subjects.discipline_id, disciplines.name AS discipline_name
         FROM subjects
         INNER JOIN disciplines ON disciplines.id = subjects.discipline_id
         ORDER BY disciplines.name ASC, subjects.name ASC'
    )->fetchAll();
}

function question_authors(): array
{
    return db()->query('SELECT id, name FROM users ORDER BY name ASC')->fetchAll();
}

function question_filters(array $source): array
{
    return [
        'term' => trim((string) ($source['term'] ?? '')),
        'discipline_id' => (int) ($source['discipline_id'] ?? 0),
        'subject_id' => (int) ($source['subject_id'] ?? 0),
        'education_level' => trim((string) ($source['education_level'] ?? '')),
        'question_type' => trim((string) ($source['question_type'] ?? '')),
        'author_id' => (int) ($source['author_id'] ?? 0),
        'visibility' => trim((string) ($source['visibility'] ?? '')),
    ];
}

function question_edit_payload(int $userId, ?int $editId): array
{
    if (!$editId) {
        return [null, option_rows([])];
    }

    $edit = own_question($editId, $userId);

    if (!$edit) {
        flash('error', 'Voce so pode editar questoes da sua autoria.');
        redirect('questions.php');
    }

    $statement = db()->prepare(
        'SELECT option_text, is_correct
         FROM question_options
         WHERE question_id = :question_id
         ORDER BY display_order ASC'
    );
    $statement->execute(['question_id' => $edit['id']]);

    return [$edit, option_rows($statement->fetchAll())];
}

function question_list(array $filters, int $userId): array
{
    $query = 'SELECT questions.*, authors.name AS author_name, disciplines.name AS discipline_name,
                     subjects.name AS subject_name, base_authors.name AS based_on_author_name,
                     CASE WHEN question_favorites.id IS NULL THEN 0 ELSE 1 END AS is_favorite
              FROM questions
              INNER JOIN users AS authors ON authors.id = questions.author_id
              LEFT JOIN disciplines ON disciplines.id = questions.discipline_id
              LEFT JOIN subjects ON subjects.id = questions.subject_id
              LEFT JOIN questions AS base_questions ON base_questions.id = questions.based_on_question_id
              LEFT JOIN users AS base_authors ON base_authors.id = base_questions.author_id
              LEFT JOIN question_favorites ON question_favorites.question_id = questions.id
                   AND question_favorites.user_id = :favorite_user_id
              WHERE (questions.visibility = "public" OR questions.author_id = :current_user_id)';
    $params = [
        'favorite_user_id' => $userId,
        'current_user_id' => $userId,
    ];

    if ($filters['term'] !== '') {
        $query .= ' AND (
            questions.title LIKE :term_title
            OR questions.prompt LIKE :term_prompt
            OR authors.name LIKE :term_author
            OR COALESCE(questions.source_name, "") LIKE :term_source_name
            OR COALESCE(questions.source_reference, "") LIKE :term_source_reference
        )';
        $term = '%' . $filters['term'] . '%';
        $params['term_title'] = $term;
        $params['term_prompt'] = $term;
        $params['term_author'] = $term;
        $params['term_source_name'] = $term;
        $params['term_source_reference'] = $term;
    }

    if ($filters['discipline_id'] > 0) {
        $query .= ' AND questions.discipline_id = :discipline_id';
        $params['discipline_id'] = $filters['discipline_id'];
    }

    if ($filters['subject_id'] > 0) {
        $query .= ' AND questions.subject_id = :subject_id';
        $params['subject_id'] = $filters['subject_id'];
    }

    if ($filters['education_level'] !== '' && in_array($filters['education_level'], ['fundamental', 'medio', 'tecnico', 'superior'], true)) {
        $query .= ' AND questions.education_level = :education_level';
        $params['education_level'] = $filters['education_level'];
    }

    if ($filters['question_type'] !== '' && in_array($filters['question_type'], ['multiple_choice', 'discursive', 'drawing', 'true_false'], true)) {
        $query .= ' AND questions.question_type = :question_type';
        $params['question_type'] = $filters['question_type'];
    }

    if ($filters['author_id'] > 0) {
        $query .= ' AND questions.author_id = :author_id';
        $params['author_id'] = $filters['author_id'];
    }

    if ($filters['visibility'] !== '' && in_array($filters['visibility'], ['public', 'private'], true)) {
        $query .= ' AND questions.visibility = :visibility';
        $params['visibility'] = $filters['visibility'];
    }

    $query .= ' ORDER BY questions.created_at DESC';

    $statement = db()->prepare($query);
    $statement->execute($params);
    $questions = $statement->fetchAll();

    return [$questions, find_question_options(array_map(static fn(array $question): int => (int) $question['id'], $questions))];
}

function question_find_by_source_reference(int $userId, string $sourceName, string $sourceReference): ?array
{
    $statement = db()->prepare(
        'SELECT * FROM questions
         WHERE author_id = :author_id
           AND source_name = :source_name
           AND source_reference = :source_reference
         LIMIT 1'
    );
    $statement->execute([
        'author_id' => $userId,
        'source_name' => $sourceName,
        'source_reference' => $sourceReference,
    ]);

    return $statement->fetch() ?: null;
}

function question_find_or_create_discipline(string $name, array $aliases, int $userId): int
{
    $candidates = array_values(array_unique(array_filter(array_merge([$name], $aliases))));

    foreach ($candidates as $candidate) {
        $statement = db()->prepare('SELECT id FROM disciplines WHERE LOWER(name) = LOWER(:name) LIMIT 1');
        $statement->execute(['name' => $candidate]);
        $disciplineId = (int) $statement->fetchColumn();

        if ($disciplineId > 0) {
            return $disciplineId;
        }
    }

    foreach ($candidates as $candidate) {
        $statement = db()->prepare('SELECT id FROM disciplines WHERE LOWER(name) LIKE LOWER(:name) LIMIT 1');
        $statement->execute(['name' => $candidate . '%']);
        $disciplineId = (int) $statement->fetchColumn();

        if ($disciplineId > 0) {
            return $disciplineId;
        }
    }

    $insert = db()->prepare('INSERT INTO disciplines (name, created_by, created_at) VALUES (:name, :created_by, NOW())');
    $insert->execute([
        'name' => $name,
        'created_by' => $userId,
    ]);

    return (int) db()->lastInsertId();
}

function question_find_or_create_subject(int $disciplineId, string $name, int $userId): int
{
    $statement = db()->prepare(
        'SELECT id FROM subjects
         WHERE discipline_id = :discipline_id
           AND LOWER(name) = LOWER(:name)
         LIMIT 1'
    );
    $statement->execute([
        'discipline_id' => $disciplineId,
        'name' => $name,
    ]);

    $subjectId = (int) $statement->fetchColumn();

    if ($subjectId > 0) {
        return $subjectId;
    }

    $insert = db()->prepare(
        'INSERT INTO subjects (discipline_id, name, created_by, created_at)
         VALUES (:discipline_id, :name, :created_by, NOW())'
    );
    $insert->execute([
        'discipline_id' => $disciplineId,
        'name' => $name,
        'created_by' => $userId,
    ]);

    return (int) db()->lastInsertId();
}
