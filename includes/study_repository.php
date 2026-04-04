<?php
declare(strict_types=1);

function study_filters_from_request(array $source): array
{
    $difficulty = trim((string) ($source['difficulty'] ?? ''));

    if (!in_array($difficulty, ['', 'facil', 'medio', 'dificil'], true)) {
        $difficulty = '';
    }

    return [
        'discipline_id' => (int) ($source['discipline_id'] ?? 0),
        'subject_id' => (int) ($source['subject_id'] ?? 0),
        'difficulty' => $difficulty,
    ];
}

function study_disciplines(): array
{
    return db()->query('SELECT id, name FROM disciplines ORDER BY name ASC')->fetchAll();
}

function study_subjects(?int $disciplineId = null): array
{
    $sql = 'SELECT subjects.id, subjects.name, subjects.discipline_id, disciplines.name AS discipline_name
            FROM subjects
            INNER JOIN disciplines ON disciplines.id = subjects.discipline_id';
    $params = [];

    if ($disciplineId !== null && $disciplineId > 0) {
        $sql .= ' WHERE subjects.discipline_id = :discipline_id';
        $params['discipline_id'] = $disciplineId;
    }

    $sql .= ' ORDER BY disciplines.name ASC, subjects.name ASC';
    $statement = db()->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

function study_find_question_ids(array $filters): array
{
    $sql = 'SELECT q.id
            FROM questions q
            WHERE q.visibility = "public"
              AND q.status = "published"
              AND q.question_type = "multiple_choice"';
    $params = [];

    if (($filters['discipline_id'] ?? 0) > 0) {
        $sql .= ' AND q.discipline_id = :discipline_id';
        $params['discipline_id'] = (int) $filters['discipline_id'];
    }

    if (($filters['subject_id'] ?? 0) > 0) {
        $sql .= ' AND q.subject_id = :subject_id';
        $params['subject_id'] = (int) $filters['subject_id'];
    }

    if (($filters['difficulty'] ?? '') !== '') {
        $sql .= ' AND q.difficulty = :difficulty';
        $params['difficulty'] = (string) $filters['difficulty'];
    }

    $sql .= ' ORDER BY RAND(), q.id DESC';
    $statement = db()->prepare($sql);
    $statement->execute($params);

    return array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));
}

function study_start_session(array $filters): array
{
    $queue = study_find_question_ids($filters);

    $_SESSION['study_mode'] = [
        'filters' => $filters,
        'queue' => $queue,
        'index' => 0,
        'started_at' => time(),
    ];

    return $_SESSION['study_mode'];
}

function study_state(): ?array
{
    return isset($_SESSION['study_mode']) && is_array($_SESSION['study_mode'])
        ? $_SESSION['study_mode']
        : null;
}

function study_clear_state(): void
{
    unset($_SESSION['study_mode']);
}

function study_queue_count(): int
{
    $state = study_state();

    return isset($state['queue']) && is_array($state['queue']) ? count($state['queue']) : 0;
}

function study_current_question_id(): ?int
{
    $state = study_state();

    if ($state === null) {
        return null;
    }

    $queue = $state['queue'] ?? [];
    $index = (int) ($state['index'] ?? 0);

    if (!is_array($queue) || !isset($queue[$index])) {
        return null;
    }

    return (int) $queue[$index];
}

function study_advance_question(): void
{
    if (!isset($_SESSION['study_mode']) || !is_array($_SESSION['study_mode'])) {
        return;
    }

    $_SESSION['study_mode']['index'] = (int) ($_SESSION['study_mode']['index'] ?? 0) + 1;
}

function study_progress(): array
{
    $state = study_state();

    if ($state === null) {
        return [
            'current' => 0,
            'total' => 0,
            'percent' => 0,
        ];
    }

    $queue = is_array($state['queue'] ?? null) ? $state['queue'] : [];
    $current = min((int) ($state['index'] ?? 0) + 1, max(count($queue), 1));
    $total = count($queue);
    $percent = $total > 0 ? (int) round(($current / $total) * 100) : 0;

    return [
        'current' => $current,
        'total' => $total,
        'percent' => min(100, max(0, $percent)),
    ];
}

function study_load_question(int $questionId): ?array
{
    $statement = db()->prepare(
        'SELECT q.*, d.name AS discipline_name, s.name AS subject_name, a.name AS author_name
         FROM questions q
         LEFT JOIN disciplines d ON d.id = q.discipline_id
         LEFT JOIN subjects s ON s.id = q.subject_id
         LEFT JOIN users a ON a.id = q.author_id
         WHERE q.id = :id
         LIMIT 1'
    );
    $statement->execute(['id' => $questionId]);
    $question = $statement->fetch();

    return $question ?: null;
}

function study_load_options(int $questionId): array
{
    $statement = db()->prepare(
        'SELECT id, option_text, is_correct, display_order
         FROM question_options
         WHERE question_id = :question_id
         ORDER BY display_order ASC, id ASC'
    );
    $statement->execute(['question_id' => $questionId]);

    return $statement->fetchAll();
}

function study_option_letters(array $options): array
{
    $letters = [];

    foreach ($options as $index => $option) {
        $letters[] = option_label((int) $index);
    }

    return $letters;
}

function study_option_text_by_letter(array $options, string $letter): ?string
{
    $letters = study_option_letters($options);
    $index = array_search($letter, $letters, true);

    if ($index === false) {
        return null;
    }

    return isset($options[$index]['option_text']) ? (string) $options[$index]['option_text'] : null;
}

function study_correct_letter(array $options): ?string
{
    foreach ($options as $index => $option) {
        if ((int) ($option['is_correct'] ?? 0) === 1) {
            return option_label((int) $index);
        }
    }

    return null;
}

function study_record_answer(int $userId, int $questionId, string $selectedLetter): array
{
    $question = study_load_question($questionId);

    if ($question === null) {
        throw new RuntimeException('Questão não encontrada.');
    }

    $options = study_load_options($questionId);
    $letters = study_option_letters($options);
    $selectedLetter = strtoupper(trim($selectedLetter));
    $selectedIndex = array_search($selectedLetter, $letters, true);

    if ($selectedIndex === false) {
        throw new InvalidArgumentException('Resposta inválida.');
    }

    $correctLetter = study_correct_letter($options);
    $selectedText = (string) ($options[$selectedIndex]['option_text'] ?? '');
    $correctText = $correctLetter !== null ? (string) (study_option_text_by_letter($options, $correctLetter) ?? '') : '';
    $isCorrect = $correctLetter !== null && $selectedLetter === $correctLetter;

    db()->beginTransaction();

    try {
        $insert = db()->prepare(
            'INSERT INTO answers (user_id, question_id, resposta, correta, data)
             VALUES (:user_id, :question_id, :resposta, :correta, NOW())'
        );
        $insert->execute([
            'user_id' => $userId,
            'question_id' => $questionId,
            'resposta' => $selectedLetter,
            'correta' => $isCorrect ? 1 : 0,
        ]);

        $answerId = (int) db()->lastInsertId();

        $usage = db()->prepare('UPDATE questions SET usage_count = usage_count + 1 WHERE id = :id');
        $usage->execute(['id' => $questionId]);

        db()->commit();
    } catch (Throwable $exception) {
        db()->rollBack();
        throw $exception;
    }

    return [
        'answer_id' => $answerId,
        'question_id' => $questionId,
        'question_title' => (string) $question['title'],
        'selected_letter' => $selectedLetter,
        'selected_text' => $selectedText,
        'correct_letter' => $correctLetter,
        'correct_text' => $correctText,
        'is_correct' => $isCorrect,
    ];
}

function study_answer_details(int $answerId, int $userId): ?array
{
    $statement = db()->prepare(
        'SELECT a.id AS answer_id,
                a.user_id,
                a.question_id,
                a.resposta,
                a.correta,
                a.data,
                q.title,
                q.prompt,
                q.explanation,
                q.difficulty,
                q.visibility,
                d.name AS discipline_name,
                s.name AS subject_name
         FROM answers a
         INNER JOIN questions q ON q.id = a.question_id
         LEFT JOIN disciplines d ON d.id = q.discipline_id
         LEFT JOIN subjects s ON s.id = q.subject_id
         WHERE a.id = :answer_id
           AND a.user_id = :user_id
         LIMIT 1'
    );
    $statement->execute([
        'answer_id' => $answerId,
        'user_id' => $userId,
    ]);

    $answer = $statement->fetch();

    if ($answer === false) {
        return null;
    }

    $options = study_load_options((int) $answer['question_id']);
    $selectedLetter = strtoupper((string) $answer['resposta']);
    $selectedText = study_option_text_by_letter($options, $selectedLetter);
    $correctLetter = study_correct_letter($options);
    $correctText = $correctLetter !== null ? study_option_text_by_letter($options, $correctLetter) : null;

    return [
        'answer' => $answer,
        'options' => $options,
        'selected_letter' => $selectedLetter,
        'selected_text' => $selectedText,
        'correct_letter' => $correctLetter,
        'correct_text' => $correctText,
        'is_correct' => (int) $answer['correta'] === 1,
    ];
}

function study_dashboard_summary(int $userId): array
{
    $statement = db()->prepare(
        'SELECT COUNT(*) AS total_answers,
                COALESCE(SUM(correta), 0) AS total_correct
         FROM answers
         WHERE user_id = :user_id'
    );
    $statement->execute(['user_id' => $userId]);
    $summary = $statement->fetch() ?: ['total_answers' => 0, 'total_correct' => 0];

    $totalAnswers = (int) $summary['total_answers'];
    $totalCorrect = (int) $summary['total_correct'];
    $totalWrong = max(0, $totalAnswers - $totalCorrect);
    $accuracy = $totalAnswers > 0 ? round(($totalCorrect / $totalAnswers) * 100, 1) : 0.0;

    return [
        'total_answers' => $totalAnswers,
        'total_correct' => $totalCorrect,
        'total_wrong' => $totalWrong,
        'accuracy' => $accuracy,
    ];
}

function study_dashboard_history(int $userId, int $limit = 10): array
{
    $statement = db()->prepare(
        'SELECT a.id AS answer_id,
                a.resposta,
                a.correta,
                a.data,
                q.id AS question_id,
                q.title,
                q.difficulty,
                d.name AS discipline_name,
                s.name AS subject_name
         FROM answers a
         INNER JOIN questions q ON q.id = a.question_id
         LEFT JOIN disciplines d ON d.id = q.discipline_id
         LEFT JOIN subjects s ON s.id = q.subject_id
         WHERE a.user_id = :user_id
         ORDER BY a.data DESC, a.id DESC
         LIMIT :limit'
    );
    $statement->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
    $statement->execute();

    return $statement->fetchAll();
}
