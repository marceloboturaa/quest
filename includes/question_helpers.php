<?php
declare(strict_types=1);

function own_question(int $id, int $userId): ?array
{
    $statement = db()->prepare('SELECT * FROM questions WHERE id = :id AND author_id = :author_id LIMIT 1');
    $statement->execute(['id' => $id, 'author_id' => $userId]);

    return $statement->fetch() ?: null;
}

function visible_question_row(int $id, int $userId): ?array
{
    $statement = db()->prepare('SELECT * FROM questions WHERE id = :id AND (visibility = "public" OR author_id = :author_id) LIMIT 1');
    $statement->execute(['id' => $id, 'author_id' => $userId]);

    return $statement->fetch() ?: null;
}

function belongs_subject(int $subjectId, int $disciplineId): bool
{
    $statement = db()->prepare('SELECT COUNT(*) FROM subjects WHERE id = :id AND discipline_id = :discipline_id');
    $statement->execute(['id' => $subjectId, 'discipline_id' => $disciplineId]);

    return (int) $statement->fetchColumn() > 0;
}

function option_rows(array $raw): array
{
    $rows = [];

    foreach ($raw as $item) {
        $text = trim((string) ($item['text'] ?? $item['option_text'] ?? ''));

        if ($text === '' && isset($item['option_text'])) {
            continue;
        }

        $rows[] = ['text' => $text, 'is_correct' => !empty($item['is_correct'])];
    }

    while (count($rows) < 4) {
        $rows[] = ['text' => '', 'is_correct' => false];
    }

    return array_values($rows);
}

function parsed_options(array $raw): array
{
    $rows = [];

    foreach ($raw as $item) {
        $text = trim((string) ($item['text'] ?? ''));

        if ($text === '') {
            continue;
        }

        $rows[] = ['text' => $text, 'is_correct' => !empty($item['is_correct']) ? 1 : 0];
    }

    return $rows;
}
