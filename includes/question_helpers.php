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

function question_contains_html_markup(string $text): bool
{
    return preg_match('/<\s*\/?[a-z][^>]*>/i', $text) === 1;
}

function question_strip_rich_whitespace(string $text): string
{
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = preg_replace('/[\x{00A0}\x{2007}\x{202F}]/u', ' ', $text) ?? $text;
    $text = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $text) ?? $text;
    $text = preg_replace("/[ \t]+\n/u", "\n", $text) ?? $text;
    $text = preg_replace("/\n{3,}/u", "\n\n", $text) ?? $text;

    return trim($text);
}

function question_prompt_title_from_text(string $text, int $limit = 90): string
{
    $text = question_normalize_editor_text($text, true);

    if ($text === '') {
        return 'Questão sem título';
    }

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $limit, '...');
    }

    return strlen($text) > $limit ? substr($text, 0, $limit - 3) . '...' : $text;
}

function question_prompt_title_from_html(string $html, int $limit = 90): string
{
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    return question_prompt_title_from_text($text, $limit);
}

function question_generate_code(): string
{
    $prefix = 'Q' . date('ymd');

    do {
        $suffix = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $code = $prefix . '-' . $suffix;

        $statement = db()->prepare('SELECT COUNT(*) FROM questions WHERE question_code = :question_code');
        $statement->execute(['question_code' => $code]);
    } while ((int) $statement->fetchColumn() > 0);

    return $code;
}

function question_allowed_rich_tags(): array
{
    return [
        'p', 'br', 'strong', 'em', 'u', 's', 'sup', 'sub',
        'span', 'div', 'blockquote', 'figure', 'ul', 'ol', 'li',
        'a', 'img', 'table', 'thead', 'tbody', 'tr', 'td', 'th',
        'code', 'pre', 'hr',
    ];
}

function question_sanitize_rich_html(string $html): string
{
    $html = question_strip_rich_whitespace($html);

    if ($html === '') {
        return '';
    }

    $dom = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $wrapped = '<!DOCTYPE html><html><body>' . $html . '</body></html>';
    $dom->loadHTML('<?xml encoding="UTF-8">' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $allowedTags = array_flip(question_allowed_rich_tags());
    $allowedAttributes = [
        'a' => ['href', 'title', 'target', 'rel', 'class'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'class', 'data-align'],
        'span' => ['class'],
        'div' => ['class'],
        'p' => ['class'],
        'blockquote' => ['class'],
        'figure' => ['class'],
        'table' => ['class'],
        'td' => ['colspan', 'rowspan', 'class'],
        'th' => ['colspan', 'rowspan', 'class'],
        'pre' => ['class'],
        'code' => ['class'],
    ];

    $sanitizeNode = static function (DOMNode $node) use (&$sanitizeNode, $allowedTags, $allowedAttributes): void {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tag = strtolower($node->nodeName);

            if (!isset($allowedTags[$tag])) {
                $replacement = $node->ownerDocument->createDocumentFragment();
                while ($node->firstChild) {
                    $replacement->appendChild($node->firstChild);
                }
                $node->parentNode?->replaceChild($replacement, $node);
                return;
            }

            if ($node->hasAttributes()) {
                $remove = [];

                foreach ($node->attributes as $attribute) {
                    $name = strtolower($attribute->nodeName);
                    $value = (string) $attribute->nodeValue;
                    $isAllowed = in_array($name, $allowedAttributes[$tag] ?? ['class'], true)
                        || str_starts_with($name, 'data-');

                    if (str_starts_with($name, 'on')) {
                        $isAllowed = false;
                    }

                    if ($tag === 'a' && $name === 'href') {
                        $isAllowed = !preg_match('/^\s*javascript:/i', $value);
                    }

                    if ($tag === 'img' && $name === 'src') {
                        $isAllowed = $value !== '' && !preg_match('/^\s*javascript:/i', $value);
                    }

                    if (!$isAllowed) {
                        $remove[] = $attribute->nodeName;
                    }
                }

                foreach ($remove as $attributeName) {
                    $node->removeAttribute($attributeName);
                }

                if ($tag === 'a') {
                    $node->setAttribute('rel', 'noopener noreferrer');
                    $node->setAttribute('target', '_blank');
                }
            }
        }

        if ($node->hasChildNodes()) {
            $children = [];
            foreach (iterator_to_array($node->childNodes) as $child) {
                $children[] = $child;
            }

            foreach ($children as $child) {
                $sanitizeNode($child);
            }
        }
    };

    $body = $dom->getElementsByTagName('body')->item(0);

    if ($body instanceof DOMNode) {
        $sanitizeNode($body);
        $output = '';

        foreach (iterator_to_array($body->childNodes) as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    return '';
}

function question_normalize_rich_editor_input(string $text): string
{
    $text = question_strip_rich_whitespace($text);

    if ($text === '') {
        return '';
    }

    if (question_contains_html_markup($text)) {
        return question_sanitize_rich_html($text);
    }

    $paragraphs = preg_split("/\n{2,}/u", $text) ?: [$text];
    $html = [];

    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);

        if ($paragraph === '') {
            continue;
        }

        $lines = array_map(
            static fn(string $line): string => htmlspecialchars($line, ENT_QUOTES, 'UTF-8'),
            preg_split("/\n/u", $paragraph) ?: [$paragraph]
        );
        $html[] = '<p>' . implode('<br>', $lines) . '</p>';
    }

    return implode('', $html);
}

function question_rich_content_excerpt(string $html, int $limit = 220): string
{
    $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

    if ($plain === '') {
        return 'Sem resumo disponível.';
    }

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($plain, 0, $limit, '...');
    }

    return strlen($plain) > $limit ? substr($plain, 0, $limit - 3) . '...' : $plain;
}

function question_render_formatted_text_html(?string $text): string
{
    return question_render_rich_content_html($text);
}

function question_render_rich_content_html(?string $html): string
{
    $html = question_normalize_rich_editor_input((string) $html);

    if ($html === '') {
        return '';
    }

    return $html;
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
