<?php
declare(strict_types=1);

function own_question(int $id, int $userId): ?array
{
    if (can_manage_all_questions()) {
        $statement = db()->prepare('SELECT * FROM questions WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

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

function question_cleanup_math_artifacts(string $text): string
{
    $text = preg_replace(
        '/(?<!\d)(\d)\s*(\d)\s*\\\\(?:d)?frac\s*\{\s*\1\s*\}\s*\{\s*\2\s*\}\s*\2\s*\1(?!\d)/u',
        '\\frac{$1}{$2}',
        $text
    ) ?? $text;

    return $text;
}

function question_render_math_fragments_html(string $text): string
{
    $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    return preg_replace_callback(
        '/\\\\(?:d)?frac\s*\{\s*([^{}]+?)\s*\}\s*\{\s*([^{}]+?)\s*\}/u',
        static function (array $matches): string {
            $numerator = trim($matches[1]);
            $denominator = trim($matches[2]);

            return '<span class="math-fraction" aria-label="' . $numerator . ' sobre ' . $denominator . '">'
                . '<span class="math-fraction-top">' . $numerator . '</span>'
                . '<span class="math-fraction-bottom">' . $denominator . '</span>'
                . '</span>';
        },
        $escaped
    ) ?? $escaped;
}

function question_is_math_expression_line(string $line): bool
{
    $line = trim($line);

    if ($line === '') {
        return false;
    }

    if (
        !preg_match('/(?:=|\\\\(?:d)?frac|\^|[+\-*\/]=?|\b[a-z]\([^)]*\))/iu', $line)
        && !preg_match('/^[a-z]\s*$/iu', $line)
    ) {
        return false;
    }

    if (preg_match('/[!?;]/u', $line)) {
        return false;
    }

    $wordCount = preg_match_all('/\p{L}+/u', $line, $matches);

    return $wordCount <= 6;
}

function question_normalize_editor_text(string $text, bool $singleLine = false): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = str_replace("\t", '    ', $text);
    $text = preg_replace('/[\x{00A0}\x{2007}\x{202F}]/u', ' ', $text) ?? $text;
    $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text) ?? $text;
    $text = preg_replace("/[ \t]+\n/u", "\n", $text) ?? $text;
    $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;
    $text = question_cleanup_math_artifacts($text);
    $text = trim($text);

    if ($singleLine) {
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
        return trim($text);
    }

    $lines = preg_split("/\n/u", $text) ?: [];
    $lines = array_map(static fn(string $line): string => rtrim($line), $lines);

    return trim(implode("\n", $lines));
}

function question_render_formatted_text_html(?string $text): string
{
    $text = question_cleanup_math_artifacts(str_replace(["\r\n", "\r"], "\n", trim((string) $text)));

    if ($text === '') {
        return '';
    }

    $lines = preg_split("/\n/u", $text) ?: [];
    $htmlLines = [];

    foreach ($lines as $line) {
        $rendered = question_render_math_fragments_html($line);

        if (question_is_math_expression_line($line)) {
            $rendered = '<span class="math-expression-block">' . $rendered . '</span>';
        }

        $htmlLines[] = $rendered;
    }

    return implode('<br>', $htmlLines);
}

function option_rows(array $raw): array
{
    $rows = [];

    foreach ($raw as $item) {
        $text = question_normalize_editor_text((string) ($item['text'] ?? $item['option_text'] ?? ''));

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
        $text = question_normalize_editor_text((string) ($item['text'] ?? ''));

        if ($text === '') {
            continue;
        }

        $rows[] = ['text' => $text, 'is_correct' => !empty($item['is_correct']) ? 1 : 0];
    }

    return $rows;
}
