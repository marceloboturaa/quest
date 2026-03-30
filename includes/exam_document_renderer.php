<?php
declare(strict_types=1);

require_once __DIR__ . '/exam_metadata.php';
require_once __DIR__ . '/question_helpers.php';

function exam_document_escape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function exam_document_question_number_map(array $questions): array
{
    $map = [];

    foreach ($questions as $index => $question) {
        $map[(int) ($question['id'] ?? 0)] = $index + 1;
    }

    return $map;
}

function exam_document_answer_labels(array $question, array $options): array
{
    if (($question['question_type'] ?? '') === 'true_false') {
        return ['V', 'F'];
    }

    if (($question['question_type'] ?? '') !== 'multiple_choice' || $options === []) {
        return [];
    }

    $labels = [];

    foreach ($options as $index => $option) {
        $labels[] = option_label((int) $index);
    }

    return $labels;
}

function exam_document_answer_sheet_rows(array $questions, array $questionOptions, array $numberMap): array
{
    $rows = [];

    foreach ($questions as $question) {
        $questionId = (int) ($question['id'] ?? 0);
        $labels = exam_document_answer_labels($question, $questionOptions[$questionId] ?? []);

        if ($labels === []) {
            continue;
        }

        $rows[] = [
            'question_id' => $questionId,
            'number' => $numberMap[$questionId] ?? (count($rows) + 1),
            'labels' => $labels,
        ];
    }

    return $rows;
}

function exam_document_logo_data_uri(): ?string
{
    $baseDir = dirname(__DIR__);
    $candidates = [
        $baseDir . '/assets/images/cecmtancredoneves.svg',
        $baseDir . '/assets/images/exam-logo.svg',
        $baseDir . '/assets/images/exam-logo.png',
        $baseDir . '/assets/images/exam-logo.jpg',
        $baseDir . '/assets/images/exam-logo.jpeg',
    ];

    foreach ($candidates as $path) {
        if (!is_file($path) || !is_readable($path)) {
            continue;
        }

        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $mimeType = match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => '',
        };

        if ($mimeType === '') {
            continue;
        }

        $contents = @file_get_contents($path);

        if ($contents === false || $contents === '') {
            continue;
        }

        return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    }

    return null;
}

function exam_document_color_value(?string $value, string $fallback): string
{
    $value = trim((string) $value);
    return preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1 ? $value : $fallback;
}

function exam_document_float_value(mixed $value, float $fallback): float
{
    $normalized = trim(str_replace(',', '.', (string) $value));

    if ($normalized === '' || !is_numeric($normalized)) {
        return $fallback;
    }

    $number = (float) $normalized;
    return $number > 0 ? $number : $fallback;
}

function exam_document_int_value(mixed $value, int $fallback, int $min, int $max): int
{
    $parsed = (int) $value;

    if ($parsed <= 0) {
        $parsed = $fallback;
    }

    return max($min, min($max, $parsed));
}

function exam_document_image_reference(?string $value): string
{
    return trim((string) $value);
}

function exam_document_dimension_px(mixed $value, float $defaultCm, int $minPx, int $maxPx, bool $allowLegacyPixels = true, float $legacyThreshold = 20.0): int
{
    $numeric = exam_document_float_value($value, $defaultCm);
    $pixels = $allowLegacyPixels && $numeric > $legacyThreshold
        ? $numeric
        : ($numeric * 37.7952755906);

    return max($minPx, min($maxPx, (int) round($pixels)));
}

function exam_document_header_config(array $document): array
{
    $metadata = $document['metadata'] ?? [];
    $leftLogo = exam_document_image_reference((string) ($metadata['header_logo_left'] ?? EXAM_DEFAULT_LOGO_URL));
    $rightLogo = exam_document_image_reference((string) ($metadata['header_logo_right'] ?? ''));

    return [
        'school_subtitle' => trim((string) (($metadata['school_subtitle'] ?? '') !== '' ? $metadata['school_subtitle'] : EXAM_DEFAULT_SCHOOL_SUBTITLE)),
        'background_color' => exam_document_color_value((string) ($metadata['header_background_color'] ?? ''), '#ffffff'),
        'title_color' => exam_document_color_value((string) ($metadata['header_title_color'] ?? ''), '#334155'),
        'subtitle_color' => exam_document_color_value((string) ($metadata['header_subtitle_color'] ?? ''), '#64748b'),
        'title_size' => exam_document_int_value($metadata['header_title_size'] ?? 20, 20, 16, 32),
        'subtitle_size' => exam_document_int_value($metadata['header_subtitle_size'] ?? 16, 16, 12, 24),
        'logo_size' => exam_document_dimension_px($metadata['header_logo_size'] ?? 2.2, 2.2, 48, 140),
        'min_height' => exam_document_dimension_px($metadata['header_min_height'] ?? 3.2, 3.2, 90, 220),
        'content_font_size' => exam_document_int_value($metadata['content_font_size'] ?? 11, 11, 10, 18),
        'left_logo' => $leftLogo !== '' ? $leftLogo : ((string) ($document['logo_data_uri'] ?? '') !== '' ? (string) $document['logo_data_uri'] : EXAM_DEFAULT_LOGO_URL),
        'right_logo' => $rightLogo,
    ];
}

function exam_document_header_style(array $document): string
{
    $config = exam_document_header_config($document);

    return '--exam-header-bg:' . exam_document_escape($config['background_color']) . ';'
        . '--exam-header-title-color:' . exam_document_escape($config['title_color']) . ';'
        . '--exam-header-subtitle-color:' . exam_document_escape($config['subtitle_color']) . ';'
        . '--exam-header-title-size:' . exam_document_escape((string) $config['title_size']) . 'px;'
        . '--exam-header-subtitle-size:' . exam_document_escape((string) $config['subtitle_size']) . 'px;'
        . '--exam-header-logo-size:' . exam_document_escape((string) $config['logo_size']) . 'px;'
        . '--exam-header-min-height:' . exam_document_escape((string) $config['min_height']) . 'px;'
        . '--exam-content-font-size:' . exam_document_escape((string) $config['content_font_size']) . 'px;';
}

function exam_document_font_data_uri(string $relativePath, string $mimeType): ?string
{
    static $cache = [];

    if (array_key_exists($relativePath, $cache)) {
        return $cache[$relativePath];
    }

    $path = dirname(__DIR__) . '/' . ltrim($relativePath, '/');

    if (!is_file($path) || !is_readable($path)) {
        $cache[$relativePath] = null;
        return null;
    }

    $contents = @file_get_contents($path);

    if ($contents === false || $contents === '') {
        $cache[$relativePath] = null;
        return null;
    }

    $cache[$relativePath] = 'data:' . $mimeType . ';base64,' . base64_encode($contents);
    return $cache[$relativePath];
}

function exam_document_font_face_css(): string
{
    $fontData = exam_document_font_data_uri('assets/fonts/Poppins-Black.ttf', 'font/ttf');

    if ($fontData === null) {
        return '';
    }

    return "@font-face {\n"
        . "    font-family: 'Quest Poppins Black';\n"
        . "    src: url('" . $fontData . "') format('truetype');\n"
        . "    font-style: normal;\n"
        . "    font-weight: 900;\n"
        . "}\n";
}

function exam_document_strip_formatting(string $text): string
{
    $text = preg_replace('/!\[[^\]]*\]\(([^)]+)\)/', ' ', $text) ?? $text;
    $text = preg_replace('/!\s*\(([^)]+)\)/', ' ', $text) ?? $text;
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $text = strip_tags($text);
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    return trim($text);
}

function exam_document_chars_per_line(string $style): int
{
    return match ($style) {
        'triple_column' => 28,
        'single_column' => 78,
        'accessibility' => 60,
        'economic' => 40,
        default => 36,
    };
}

function exam_document_text_weight(string $text, int $charsPerLine, int $minimum = 1): int
{
    $normalized = exam_document_strip_formatting($text);

    if ($normalized === '') {
        return 0;
    }

    return max($minimum, (int) ceil(mb_strlen($normalized, 'UTF-8') / max(18, $charsPerLine)));
}

function exam_document_split_words_chunk(string $text, int $maxChars): array
{
    $words = preg_split('/\s+/u', trim($text)) ?: [];

    if ($words === []) {
        return [];
    }

    $chunks = [];
    $current = '';

    foreach ($words as $word) {
        $candidate = $current === '' ? $word : ($current . ' ' . $word);

        if ($current !== '' && mb_strlen($candidate, 'UTF-8') > $maxChars) {
            $chunks[] = $current;
            $current = $word;
            continue;
        }

        $current = $candidate;
    }

    if ($current !== '') {
        $chunks[] = $current;
    }

    return $chunks;
}

function exam_document_text_chunks(string $text, int $maxChars): array
{
    $normalized = trim(preg_replace('/\s+/u', ' ', str_replace(["\r\n", "\r", "\n"], ' ', $text)) ?? $text);

    if ($normalized === '') {
        return [];
    }

    if (mb_strlen($normalized, 'UTF-8') <= $maxChars) {
        return [$normalized];
    }

    $sentences = preg_split('/(?<=[\.\!\?\:\;])\s+/u', $normalized) ?: [];
    $chunks = [];
    $current = '';

    foreach ($sentences as $sentence) {
        $sentence = trim($sentence);

        if ($sentence === '') {
            continue;
        }

        if (mb_strlen($sentence, 'UTF-8') > $maxChars) {
            if ($current !== '') {
                $chunks[] = $current;
                $current = '';
            }

            foreach (exam_document_split_words_chunk($sentence, $maxChars) as $wordChunk) {
                $chunks[] = $wordChunk;
            }

            continue;
        }

        $candidate = $current === '' ? $sentence : ($current . ' ' . $sentence);

        if ($current !== '' && mb_strlen($candidate, 'UTF-8') > $maxChars) {
            $chunks[] = $current;
            $current = $sentence;
            continue;
        }

        $current = $candidate;
    }

    if ($current !== '') {
        $chunks[] = $current;
    }

    return $chunks === [] ? [$normalized] : $chunks;
}

function exam_document_question_weight(array $question, array $options, string $style): int
{
    $charsPerLine = exam_document_chars_per_line($style);

    $promptText = exam_document_strip_formatting((string) ($question['prompt'] ?? ''));
    $titleText = exam_document_strip_formatting((string) ($question['title'] ?? ''));
    $sourceText = exam_document_strip_formatting((string) ($question['source_name'] ?? ''));
    $promptParagraphs = preg_split("/\n{2,}/", trim((string) ($question['prompt'] ?? ''))) ?: [];
    $weight = 10;
    $weight += max(1, (int) ceil(mb_strlen($titleText, 'UTF-8') / max(18, $charsPerLine - 10)));
    $weight += max(3, (int) ceil(mb_strlen($promptText, 'UTF-8') / $charsPerLine));
    $weight += max(0, count($promptParagraphs) - 1) * 2;

    foreach ($options as $option) {
        $optionText = exam_document_strip_formatting((string) ($option['option_text'] ?? ''));
        $weight += max(1, (int) ceil(mb_strlen($optionText, 'UTF-8') / max(20, $charsPerLine - 8)));
    }

    if (($question['question_type'] ?? '') === 'discursive') {
        $weight += max(10, ((int) ($question['response_lines'] ?? 6)) * 2);
    }

    if (($question['question_type'] ?? '') === 'drawing') {
        $weight += 20;
    }

    if (trim((string) ($question['prompt_image_url'] ?? '')) !== '') {
        $weight += 16;
    }

    if ($sourceText !== '') {
        $weight += max(1, (int) ceil(mb_strlen($sourceText, 'UTF-8') / max(22, $charsPerLine - 6)));
    }

    return $weight;
}

function exam_document_page_capacity(array $document): int
{
    $style = (string) ($document['style'] ?? 'double_column');
    $template = (string) ($document['template'] ?? 'version_1');
    $headerConfig = exam_document_header_config($document);
    $footerContent = exam_document_footer_message($document);

    $capacity = match ($style) {
        'triple_column' => 92,
        'single_column' => 48,
        'accessibility' => 38,
        'economic' => 78,
        default => 80,
    };

    if ($template === 'version_3_1') {
        $capacity -= 10;
    }

    $capacity -= max(0, (int) ceil(($headerConfig['min_height'] - 120) / 6));
    $capacity -= 6;

    if ($footerContent !== '') {
        $capacity -= max(2, (int) ceil(mb_strlen(exam_document_strip_formatting($footerContent), 'UTF-8') / 90));
    }

    return max(36, $capacity);
}

function exam_document_paper_dimensions(string $paperSize): array
{
    return match ($paperSize) {
        'A3' => ['width' => '297mm', 'height' => '420mm', 'dompdf' => 'A3'],
        'A5' => ['width' => '148mm', 'height' => '210mm', 'dompdf' => 'A5'],
        'Letter' => ['width' => '216mm', 'height' => '279mm', 'dompdf' => 'letter'],
        'Legal' => ['width' => '216mm', 'height' => '356mm', 'dompdf' => 'legal'],
        default => ['width' => '210mm', 'height' => '297mm', 'dompdf' => 'A4'],
    };
}

function exam_document_resolve_paper_size(?string $paperSize): string
{
    $paperSize = trim((string) $paperSize);
    $options = exam_paper_size_options();

    return array_key_exists($paperSize, $options) ? $paperSize : 'A4';
}

function exam_document_paginate_questions(array $document): array
{
    $pages = [];
    $currentPage = [];
    $baseCapacity = exam_document_page_capacity($document);
    $firstPageCapacity = $baseCapacity - exam_document_inline_answer_capacity_penalty($document);
    $headerCopy = exam_normalize_section_text((string) ($document['sections']['header'] ?? ''));
    $bodyCopy = exam_normalize_section_text((string) ($document['sections']['body'] ?? ''));
    $columnCount = exam_document_effective_column_count($document);

    if ($headerCopy !== '') {
        $firstPageCapacity -= max(2, (int) ceil(mb_strlen(exam_document_strip_formatting($headerCopy), 'UTF-8') / 100));
    }

    if ($bodyCopy !== '') {
        $firstPageCapacity -= max(3, (int) ceil(mb_strlen(exam_document_strip_formatting($bodyCopy), 'UTF-8') / 110));
    }

    $firstPageCapacity -= 6;

    $laterPageCapacity = $baseCapacity;
    $capacity = max(36, $firstPageCapacity);
    $columnWeights = array_fill(0, $columnCount, 0);
    $segments = $document['question_segments'] ?? [];

    foreach ($segments as $question) {
        $weight = (int) ($question['segment_weight'] ?? 0);
        $targetColumn = array_search(min($columnWeights), $columnWeights, true);
        $targetColumn = $targetColumn === false ? 0 : (int) $targetColumn;

        if ($currentPage !== [] && (($columnWeights[$targetColumn] ?? 0) + $weight) > $capacity) {
            $pages[] = $currentPage;
            $currentPage = [];
            $capacity = max(36, $laterPageCapacity);
            $columnWeights = array_fill(0, $columnCount, 0);
            $targetColumn = 0;
        }

        $currentPage[] = $question;
        $columnWeights[$targetColumn] += $weight;
    }

    if ($currentPage !== []) {
        $pages[] = $currentPage;
    }

    return $pages === [] ? [[]] : $pages;
}

function exam_document_view_data(array $exam, array $questions, array $questionOptions): array
{
    $parsed = exam_parse_stored_instructions((string) ($exam['instructions'] ?? ''));
    $metadata = array_replace(exam_default_metadata(), $parsed['metadata']);
    $numberMap = exam_document_question_number_map($questions);
    $answerRows = exam_document_answer_sheet_rows($questions, $questionOptions, $numberMap);

    $schoolName = trim((string) ($metadata['school_name'] !== '' ? $metadata['school_name'] : EXAM_DEFAULT_SCHOOL_NAME));
    $courseLabel = trim((string) (($metadata['school_subtitle'] ?? '') !== '' ? $metadata['school_subtitle'] : EXAM_DEFAULT_SCHOOL_SUBTITLE));
    $headerTitle = trim((string) ($metadata['exam_label'] !== '' ? $metadata['exam_label'] : 'AVALIAÇÃO TRIMESTRAL'));
    $variantLabel = trim((string) ($metadata['variant_label'] ?? ''));

    if ($variantLabel !== '') {
        $headerTitle .= ' | VERSÃO ' . (function_exists('mb_strtoupper') ? mb_strtoupper($variantLabel, 'UTF-8') : strtoupper($variantLabel));
    }

    $template = (string) ($metadata['exam_template'] !== '' ? $metadata['exam_template'] : 'version_1');
    $style = (string) ($metadata['exam_style'] !== '' ? $metadata['exam_style'] : 'double_column');
    $paperSize = exam_document_resolve_paper_size((string) ($exam['paper_size'] ?? 'A4'));

    $document = [
        'exam' => $exam,
        'questions' => $questions,
        'question_options' => $questionOptions,
        'question_number_map' => $numberMap,
        'answer_sheet_rows' => $answerRows,
        'parsed' => $parsed,
        'metadata' => $metadata,
        'metadata_summary' => exam_metadata_summary($metadata),
        'sections' => $parsed['sections'] ?? exam_default_sections(),
        'template' => $template,
        'style' => $style,
        'paper_size' => $paperSize,
        'school_name' => $schoolName,
        'course_label' => $courseLabel,
        'header_title' => $headerTitle,
        'logo_data_uri' => exam_document_logo_data_uri(),
    ];

    $document['question_segments'] = exam_document_segments_for_document($document);

    return $document;
}

function exam_document_is_usable_image_url(?string $url): bool
{
    $url = trim((string) $url);

    if ($url === '' || stripos($url, 'broken-image.svg') !== false) {
        return false;
    }

    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

function exam_document_prepare_prompt(string $prompt): string
{
    $prompt = str_replace(["\r\n", "\r"], "\n", trim($prompt));
    $prompt = preg_replace('/^\s*!\s*\((https?:\/\/[^)\s]+)\)\s*$/mi', '', $prompt) ?? $prompt;
    $prompt = preg_replace('/^\s*!\[\]\((https?:\/\/[^)\s]+)\)\s*$/mi', '', $prompt) ?? $prompt;
    $prompt = preg_replace('/^\s*!\[[^\]]*\]\((https?:\/\/[^)\s]+)\)\s*$/mi', '', $prompt) ?? $prompt;
    $prompt = preg_replace("/\n{3,}/", "\n\n", $prompt) ?? $prompt;

    return trim($prompt);
}

function exam_document_prompt_block_class(string $block): string
{
    $normalized = trim($block);

    if ($normalized === '') {
        return 'exam-question-paragraph';
    }

    if (preg_match('/^(texto|texto\s+[ivxlcdm]+|leia|observe|considere|quest[aã]o|figura|charge|tirinha)/iu', $normalized) === 1) {
        return 'exam-question-heading-block';
    }

    if (preg_match('/^dispon[ií]vel em:/iu', $normalized) === 1) {
        return 'exam-question-source-block';
    }

    return 'exam-question-paragraph';
}

function exam_document_render_prompt_html(array $question): string
{
    $prompt = exam_document_prepare_prompt((string) ($question['prompt'] ?? ''));
    $imageUrl = (string) ($question['prompt_image_url'] ?? '');
    $htmlParts = [];

    if ($prompt !== '') {
        $blocks = preg_split("/\n{2,}/", $prompt) ?: [];

        foreach ($blocks as $block) {
            $lines = array_values(array_filter(array_map(
                static fn(string $line): string => trim($line),
                preg_split("/\n/", trim($block)) ?: []
            ), static fn(string $line): bool => $line !== ''));

            if ($lines === []) {
                continue;
            }

            $className = exam_document_prompt_block_class(implode(' ', $lines));
            $content = question_render_formatted_text_html(implode("\n", $lines));
            $htmlParts[] = '<p class="' . $className . '">' . $content . '</p>';
        }
    }

    if (exam_document_is_usable_image_url($imageUrl)) {
        $htmlParts[] = '<div class="exam-question-image-wrap"><img class="exam-question-image" src="'
            . exam_document_escape($imageUrl)
            . '" alt="Imagem complementar da questão"></div>';
    }

    return implode('', $htmlParts);
}

function exam_document_render_inline_multiline_text(string $text): string
{
    return question_render_formatted_text_html($text);
}

function exam_document_prompt_components(array $question, string $style): array
{
    $prompt = exam_document_prepare_prompt((string) ($question['prompt'] ?? ''));
    $imageUrl = (string) ($question['prompt_image_url'] ?? '');
    $charsPerLine = exam_document_chars_per_line($style);
    $maxChunkChars = max(120, $charsPerLine * 4);
    $components = [];

    if ($prompt !== '') {
        $blocks = preg_split("/\n{2,}/", $prompt) ?: [];

        foreach ($blocks as $block) {
            $lines = array_values(array_filter(array_map(
                static fn(string $line): string => trim($line),
                preg_split("/\n/", trim($block)) ?: []
            ), static fn(string $line): bool => $line !== ''));

            if ($lines === []) {
                continue;
            }

            $className = exam_document_prompt_block_class(implode(' ', $lines));

            foreach (exam_document_text_chunks(implode("\n", $lines), $maxChunkChars) as $chunk) {
                $components[] = [
                    'kind' => 'prompt',
                    'class' => $className,
                    'html' => question_render_formatted_text_html($chunk),
                    'weight' => exam_document_text_weight($chunk, $charsPerLine, $className === 'exam-question-heading-block' ? 2 : 3),
                ];
            }
        }
    }

    if (exam_document_is_usable_image_url($imageUrl)) {
        $components[] = [
            'kind' => 'image',
            'url' => $imageUrl,
            'weight' => 16,
        ];
    }

    return $components;
}

function exam_document_question_components(array $question, array $options, string $style): array
{
    $components = exam_document_prompt_components($question, $style);
    $charsPerLine = exam_document_chars_per_line($style);
    $optionChunkChars = max(96, max(22, $charsPerLine - 8) * 3);

    if (($question['question_type'] ?? '') === 'multiple_choice') {
        foreach ($options as $index => $option) {
            $optionText = trim((string) ($option['option_text'] ?? ''));

            if ($optionText === '') {
                continue;
            }

            foreach (exam_document_text_chunks($optionText, $optionChunkChars) as $chunk) {
                $components[] = [
                    'kind' => 'option',
                    'label' => option_label((int) $index),
                    'html' => question_render_formatted_text_html($chunk),
                    'weight' => exam_document_text_weight($chunk, max(20, $charsPerLine - 8), 1) + 1,
                ];
            }
        }
    } elseif (($question['question_type'] ?? '') === 'true_false') {
        $components[] = [
            'kind' => 'option',
            'label' => 'V',
            'html' => 'Verdadeiro',
            'weight' => 2,
        ];
        $components[] = [
            'kind' => 'option',
            'label' => 'F',
            'html' => 'Falso',
            'weight' => 2,
        ];
    } elseif (($question['question_type'] ?? '') === 'discursive') {
        $remainingLines = max(4, min(14, (int) ($question['response_lines'] ?? 6)));

        while ($remainingLines > 0) {
            $chunkLines = min(4, $remainingLines);
            $components[] = [
                'kind' => 'discursive_lines',
                'count' => $chunkLines,
                'weight' => max(6, $chunkLines * 2),
            ];
            $remainingLines -= $chunkLines;
        }
    } elseif (($question['question_type'] ?? '') === 'drawing') {
        $components[] = [
            'kind' => 'drawing',
            'weight' => 20,
        ];
    }

    $sourceText = trim((string) ($question['source_name'] ?? ''));

    if ($sourceText !== '') {
        $components[] = [
            'kind' => 'source',
            'text' => $sourceText,
            'weight' => exam_document_text_weight($sourceText, max(22, $charsPerLine - 6), 1),
        ];
    }

    return $components;
}

function exam_document_segment_capacity(array $document): int
{
    return max(18, min(34, exam_document_page_capacity($document) - 18));
}

function exam_document_question_segments(array $question, array $options, int $displayNumber, array $document): array
{
    $style = (string) ($document['style'] ?? 'double_column');
    $limit = exam_document_segment_capacity($document);
    $title = trim((string) ($question['title'] ?? ''));
    $questionId = (int) ($question['id'] ?? 0);
    $components = exam_document_question_components($question, $options, $style);
    $segments = [];
    $currentComponents = [];
    $currentWeight = $title !== '' ? 5 : 4;

    foreach ($components as $component) {
        $componentWeight = (int) ($component['weight'] ?? 0);

        if ($currentComponents !== [] && ($currentWeight + $componentWeight) > $limit) {
            $segments[] = $currentComponents;
            $currentComponents = [];
            $currentWeight = 4;
        }

        $currentComponents[] = $component;
        $currentWeight += $componentWeight;
    }

    if ($currentComponents !== [] || $segments === []) {
        $segments[] = $currentComponents;
    }

    $totalSegments = count($segments);
    $resolvedSegments = [];

    foreach ($segments as $index => $segmentComponents) {
        $position = 'single';

        if ($totalSegments > 1) {
            if ($index === 0) {
                $position = 'start';
            } elseif ($index === $totalSegments - 1) {
                $position = 'end';
            } else {
                $position = 'middle';
            }
        }

        $resolvedSegments[] = [
            'segment_id' => $questionId . '-' . ($index + 1),
            'question_id' => $questionId,
            'display_number' => $displayNumber,
            'title' => $title,
            'segment_position' => $position,
            'is_question_start' => $index === 0,
            'components' => $segmentComponents,
            'segment_weight' => array_sum(array_map(
                static fn(array $component): int => (int) ($component['weight'] ?? 0),
                $segmentComponents
            )) + ($index === 0 && $title !== '' ? 5 : 4),
        ];
    }

    return $resolvedSegments;
}

function exam_document_segments_for_document(array $document): array
{
    $segments = [];

    foreach ($document['questions'] as $question) {
        $questionId = (int) ($question['id'] ?? 0);
        $segments = array_merge(
            $segments,
            exam_document_question_segments(
                $question,
                $document['question_options'][$questionId] ?? [],
                $document['question_number_map'][$questionId] ?? 0,
                $document
            )
        );
    }

    return $segments;
}

function exam_document_response_mode(array $document): string
{
    return (string) ($document['metadata']['response_mode'] ?? 'separate_answer_sheet');
}

function exam_document_composition_mode(array $document): string
{
    return (string) ($document['metadata']['composition_mode'] ?? 'mixed');
}

function exam_document_identification_mode(array $document): string
{
    return (string) ($document['metadata']['identification_mode'] ?? 'standard');
}

function exam_document_should_render_answer_sheet_page(array $document): bool
{
    if ($document['answer_sheet_rows'] === []) {
        return false;
    }

    return exam_document_response_mode($document) === 'separate_answer_sheet';
}

function exam_document_should_render_inline_answer_block(array $document): bool
{
    if ($document['answer_sheet_rows'] === []) {
        return false;
    }

    return exam_document_response_mode($document) === 'bubble_answer_sheet';
}

function exam_document_should_render_answer_sidebar(array $document): bool
{
    if ($document['answer_sheet_rows'] === []) {
        return false;
    }

    return exam_document_response_mode($document) === 'lateral_answer_key';
}

function exam_document_page_answer_rows(array $document, array $questionsOnPage): array
{
    $questionIds = [];

    foreach ($questionsOnPage as $question) {
        if (!($question['is_question_start'] ?? true)) {
            continue;
        }

        $questionId = (int) ($question['question_id'] ?? $question['id'] ?? 0);

        if ($questionId > 0) {
            $questionIds[$questionId] = true;
        }
    }

    return array_values(array_filter(
        $document['answer_sheet_rows'],
        static fn(array $row): bool => isset($questionIds[(int) ($row['question_id'] ?? 0)])
    ));
}

function exam_document_inline_answer_capacity_penalty(array $document): int
{
    if (!exam_document_should_render_inline_answer_block($document)) {
        return 0;
    }

    return min(32, 10 + ((int) ceil(count($document['answer_sheet_rows']) / 4) * 2));
}

function exam_document_identifier_token(array $document): string
{
    $metadata = $document['metadata'] ?? [];
    $examId = (int) ($document['exam']['id'] ?? 0);
    $variant = trim((string) ($metadata['variant_label'] ?? ''));
    $date = trim((string) ($metadata['application_date'] ?? ''));
    $classCode = exam_document_identifier_fragment((string) ($metadata['class_name'] ?? ''), 4);
    $componentLabel = trim((string) (($metadata['component_name'] ?? '') !== '' ? $metadata['component_name'] : ($metadata['discipline'] ?? '')));
    $componentCode = exam_document_identifier_fragment($componentLabel, 4);
    $parts = ['PRV'];

    if ($date !== '') {
        $parts[] = str_replace('-', '', $date);
    }

    if ($classCode !== '') {
        $parts[] = $classCode;
    }

    if ($componentCode !== '') {
        $parts[] = $componentCode;
    }

    if ($examId > 0) {
        $parts[] = str_pad((string) $examId, 4, '0', STR_PAD_LEFT);
    }

    if ($variant !== '') {
        $parts[] = strtoupper($variant);
    }

    return implode('-', $parts);
}

function exam_document_identifier_fragment(string $value, int $maxLength = 4): string
{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if (is_string($converted) && $converted !== '') {
            $value = $converted;
        }
    }

    $value = strtoupper($value);
    $value = preg_replace('/[^A-Z0-9]+/', '', $value) ?? $value;

    if ($value === '') {
        return '';
    }

    return substr($value, 0, max(1, $maxLength));
}

function exam_document_school_abbreviation(string $schoolName): string
{
    $words = preg_split('/\s+/u', trim($schoolName)) ?: [];
    $ignored = ['de', 'da', 'do', 'das', 'dos', 'e'];
    $parts = [];

    foreach ($words as $word) {
        $normalized = function_exists('mb_strtolower')
            ? mb_strtolower($word, 'UTF-8')
            : strtolower($word);

        if ($normalized === '' || in_array($normalized, $ignored, true)) {
            continue;
        }

        $parts[] = function_exists('mb_substr') && function_exists('mb_strtoupper')
            ? mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8')
            : strtoupper(substr($word, 0, 1));
    }

    return implode('', $parts);
}

function exam_document_render_identifier_panel(array $document): string
{
    $token = exam_document_identifier_token($document);
    $identificationMode = exam_document_identification_mode($document);

    ob_start();
    ?>
<div class="exam-identifier-panel">
    <div class="exam-identifier-copy">
        <span class="exam-identifier-label"><?= $identificationMode === 'qr_code' ? 'Identificação da prova' : 'Código da prova' ?></span>
        <strong class="exam-identifier-token"><?= exam_document_escape($token) ?></strong>
    </div>
    <?php if ($identificationMode === 'qr_code'): ?>
        <div class="exam-identifier-qr" aria-hidden="true">
            <?php
            $hash = md5($token);
            for ($i = 0; $i < 81; $i++):
                $char = hexdec($hash[$i % strlen($hash)]);
                $isFilled = ($char % 2) === 0;
            ?>
                <span class="<?= $isFilled ? 'is-filled' : '' ?>"></span>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>
    <?php

    return (string) ob_get_clean();
}

function exam_document_composition_banner(array $document): string
{
    $mode = exam_document_composition_mode($document);

    $text = match ($mode) {
        'booklet' => 'Formato em caderno: mantenha a sequência das páginas ao responder.',
        'blocks_content' => 'Prova organizada por blocos de conteúdo.',
        'blocks_difficulty' => 'Prova organizada por blocos de dificuldade crescente.',
        'dynamic_bank' => 'Montagem dinâmica a partir do banco de questões selecionado.',
        'modular' => 'Prova modular organizada por competências e habilidades.',
        'attachments' => 'Esta prova utiliza anexos, textos de apoio ou gráficos complementares.',
        default => '',
    };

    if ($text === '') {
        return '';
    }

    return '<div class="exam-composition-banner">' . exam_document_escape($text) . '</div>';
}

function exam_document_footer_message(array $document): string
{
    $footerContent = exam_normalize_section_text((string) ($document['sections']['footer'] ?? ''));

    if ($footerContent !== '') {
        return $footerContent;
    }

    return 'Confira nome, turma e se todas as questões foram respondidas.';
}

function exam_document_column_count(array $document): int
{
    $style = (string) ($document['style'] ?? 'double_column');

    if ($style === 'triple_column') {
        return 3;
    }

    if (in_array($style, ['double_column', 'economic'], true)) {
        return 2;
    }

    return 1;
}

function exam_document_effective_column_count(array $document): int
{
    $columnCount = exam_document_column_count($document);

    if ((string) ($document['template'] ?? 'version_1') === 'version_3_1') {
        return 1;
    }

    if (in_array(exam_document_composition_mode($document), ['booklet', 'blocks_content', 'blocks_difficulty', 'modular', 'attachments'], true)) {
        return 1;
    }

    return max(1, $columnCount);
}

function exam_document_split_questions_into_columns(array $document, array $questionsOnPage): array
{
    $columnCount = exam_document_effective_column_count($document);

    if ($questionsOnPage === []) {
        return [$questionsOnPage];
    }

    if ($columnCount <= 1) {
        return [$questionsOnPage];
    }

    $columns = array_fill(0, $columnCount, []);
    $weights = array_fill(0, $columnCount, 0);

    foreach ($questionsOnPage as $question) {
        $questionId = (int) ($question['id'] ?? 0);
        $options = $document['question_options'][$questionId] ?? [];
        $weight = exam_document_question_weight($question, $options, (string) ($document['style'] ?? 'double_column'));

        $targetColumn = array_search(min($weights), $weights, true);
        $columns[$targetColumn][] = $question;
        $weights[$targetColumn] += $weight;
    }

    return $columns;
}

function exam_document_styles(bool $forPdf = false, ?array $document = null): string
{
    $bodyBackground = $forPdf ? '#ffffff' : '#eef2f6';
    $shadow = $forPdf ? 'none' : '0 18px 42px rgba(20, 39, 62, 0.12)';
    $fontFaceCss = exam_document_font_face_css();
    $paper = exam_document_paper_dimensions(exam_document_resolve_paper_size((string) ($document['paper_size'] ?? 'A4')));
    $pageWidth = $paper['width'];
    $pageHeight = $paper['height'];
    $innerHeight = 'calc(' . $pageHeight . ' - 10mm)';

    return <<<CSS
{$fontFaceCss}body {
    margin: 0;
    background: {$bodyBackground};
    color: #222222;
    font-family: Arial, Helvetica, sans-serif;
}

@page {
    size: {$pageWidth} {$pageHeight};
    margin: 5mm;
}

* {
    box-sizing: border-box;
}

.math-fraction {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    vertical-align: middle;
    margin: 0 0.08em;
    line-height: 1;
    font-size: 0.92em;
}

.math-fraction-top,
.math-fraction-bottom {
    display: block;
    padding: 0 0.14em;
}

.math-fraction-top {
    border-bottom: 1.2px solid currentColor;
    padding-bottom: 0.06em;
}

.math-fraction-bottom {
    padding-top: 0.06em;
}

.math-expression-block {
    display: block;
    margin: 0.28rem 0;
    text-align: center;
    font-family: inherit;
    font-size: 1em;
    line-height: 1.35;
    font-style: normal;
}

.exam-document-stack {
    display: grid;
    gap: 24px;
    width: {$pageWidth};
    margin: 0 auto;
}

.exam-document-canvas {
    width: 100%;
    overflow-x: auto;
    overflow-y: visible;
    padding-bottom: 6px;
}

.exam-page {
    width: {$pageWidth};
    min-width: {$pageWidth};
    max-width: {$pageWidth};
    background: #ffffff;
    border: 1px solid rgba(36, 52, 71, 0.12);
    border-radius: 8px;
    box-shadow: {$shadow};
}

.exam-page-inner {
    width: 100%;
    min-height: {$innerHeight};
    height: {$innerHeight};
    padding: 4.5mm;
    overflow: hidden;
}

.exam-sheet {
    min-height: 100%;
    height: 100%;
    border: 1.2px solid #8f8f8f;
    border-radius: 8px;
    padding: 6px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.exam-header {
    display: grid;
    gap: 8px;
    flex: 0 0 auto;
    min-height: var(--exam-header-min-height, 120px);
    padding: 8px;
    margin-bottom: 6px;
    border: 1px solid #d7dee6;
    border-radius: 10px;
    background: var(--exam-header-bg, #ffffff);
}

.exam-brand {
    display: grid;
    grid-template-columns: 96px minmax(0, 1fr);
    gap: 14px;
    align-items: center;
}

.exam-brand.has-brand-side {
    grid-template-columns: 96px minmax(0, 1fr) auto;
}

.exam-brand-mark {
    width: var(--exam-header-logo-size, 80px);
    height: var(--exam-header-logo-size, 80px);
    border-radius: 18px;
    overflow: hidden;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #d7dee6;
}

.exam-brand-mark img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}

.exam-brand-copy {
    text-align: center;
}

.exam-brand-side {
    display: grid;
    justify-items: end;
    gap: 10px;
}

.exam-brand-mark-right {
    justify-self: end;
}

.exam-identifier-panel {
    display: grid;
    justify-items: end;
    gap: 6px;
}

.exam-identifier-copy {
    display: grid;
    gap: 2px;
    text-align: right;
}

.exam-identifier-label {
    font-size: 9px;
    font-weight: 700;
    color: #63758a;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}

.exam-identifier-token {
    font-size: 10px;
    color: #213446;
    letter-spacing: 0.03em;
}

.exam-identifier-qr {
    display: grid;
    grid-template-columns: repeat(9, 6px);
    gap: 1px;
    padding: 4px;
    border: 1px solid #b9c6d3;
    background: #ffffff;
}

.exam-identifier-qr span {
    width: 6px;
    height: 6px;
    background: transparent;
}

.exam-identifier-qr span.is-filled {
    background: #1f3244;
}

.exam-school-name,
.exam-school-subtitle {
    display: block;
    text-transform: uppercase;
}

.exam-school-name {
    font-family: 'Quest Poppins Black', Arial, Helvetica, sans-serif;
    font-size: var(--exam-header-title-size, 20px);
    line-height: 1.05;
    letter-spacing: 0.01em;
    color: var(--exam-header-title-color, #334155);
}

.exam-school-subtitle {
    margin-top: 2px;
    font-family: 'Quest Poppins Black', Arial, Helvetica, sans-serif;
    font-size: var(--exam-header-subtitle-size, 16px);
    line-height: 1.06;
    letter-spacing: 0;
    color: var(--exam-header-subtitle-color, #64748b);
}

.exam-proof-summary-grid {
    display: grid;
    grid-template-columns: repeat(6, minmax(0, 1fr));
    gap: 6px;
}

.exam-proof-card,
.exam-student-card {
    display: grid;
    min-width: 0;
    padding: 8px 10px;
    border: 1px solid #cfd7e1;
    border-radius: 10px;
    background: #ffffff;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
    gap: 4px;
    align-content: start;
}

.exam-proof-card.is-proof-title {
    grid-column: span 2;
    background: linear-gradient(180deg, #ffffff 0%, #f7f9ff 100%);
}

.exam-proof-card-label,
.exam-student-label {
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.01em;
}

.exam-proof-card-value,
.exam-student-value {
    color: #111827;
    font-size: 12px;
    font-weight: 700;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.exam-proof-card-meta {
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    line-height: 1.3;
}

.exam-student-grid {
    display: grid;
    grid-template-columns: repeat(8, minmax(0, 1fr));
    gap: 6px;
}

.exam-student-card {
    grid-column: span 2;
    min-height: 48px;
}

.exam-student-card.is-wide {
    grid-column: span 4;
}

.exam-student-card.is-medium {
    grid-column: span 3;
}

.exam-student-card.is-narrow {
    grid-column: span 1;
}

.exam-student-value.is-placeholder {
    color: #475569;
}

.exam-section-copy {
    overflow-wrap: anywhere;
}

.exam-section-copy br + br {
    line-height: 1.8;
}

.exam-header-copy {
    padding: 6px 8px;
    border: 1px solid #cfd6df;
    border-radius: 8px;
    background: #f8fafc;
    font-size: 10.5px;
    line-height: 1.35;
}

.exam-body-copy {
    margin-top: 0;
    padding: 8px 10px;
    border: 1px solid #d7dee6;
    border-radius: 8px;
    background: #fbfcfe;
    font-size: 10.5px;
    line-height: 1.4;
}

.exam-composition-banner {
    margin-top: 8px;
    padding: 8px 10px;
    border-radius: 8px;
    border: 1px solid #d7e0e8;
    background: #f6f9fc;
    font-size: 10px;
    font-weight: 700;
    color: #425a70;
}

.exam-questions {
    margin-top: 0;
    flex: 1 1 0;
    min-height: 0;
    overflow: hidden;
}

.exam-questions.is-double-column,
.exam-questions.is-economic {
    column-count: 2;
    column-gap: 10px;
    column-fill: auto;
}

.exam-question-columns {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
    align-items: stretch;
    grid-auto-flow: row;
    grid-auto-rows: max-content;
}

.exam-question-columns.is-three-columns {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
}

.exam-question-column {
    display: grid;
    gap: 8px;
    align-content: start;
    min-width: 0;
}

.exam-body-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    flex: 1 1 auto;
    min-height: 0;
}

.exam-body-layout.has-answer-sidebar {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 96px;
    gap: 12px;
    align-items: start;
}

.exam-body-main {
    min-width: 0;
    min-height: 0;
    display: flex;
    flex-direction: column;
    gap: 6px;
    overflow: hidden;
}

.exam-answer-sidebar {
    position: relative;
    padding: 8px 6px;
    border: 1px solid #ccd5de;
    border-radius: 10px;
    background: #fbfcfe;
}

.exam-answer-sidebar-title {
    margin-bottom: 8px;
    text-align: center;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    color: #607080;
    letter-spacing: 0.05em;
}

.exam-answer-sidebar-grid {
    display: grid;
    gap: 6px;
}

.exam-answer-sidebar-row {
    display: grid;
    justify-items: center;
    gap: 4px;
}

.exam-answer-sidebar-number {
    font-size: 9px;
    font-weight: 700;
    color: #3b4a59;
}

.exam-answer-sidebar-options {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 3px;
}

.exam-answer-sidebar-option {
    width: 16px;
    height: 16px;
    border: 1px solid #758292;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
}

.exam-questions.is-single-column,
.exam-questions.is-accessibility {
    column-count: 1;
    column-gap: 0;
}

.exam-question {
    display: block;
    width: auto;
    margin: 0;
    min-width: 0;
    margin-bottom: 8px;
    padding: 8px 10px;
    border: 1px solid #d7dee6;
    border-radius: 10px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.7);
    font-size: var(--exam-content-font-size, 11px);
    line-height: 1.38;
    overflow-wrap: break-word;
    word-break: normal;
    -webkit-column-break-inside: auto;
    break-inside: auto;
    page-break-inside: auto;
    box-decoration-break: clone;
    -webkit-box-decoration-break: clone;
}

.exam-question:last-child {
    margin-bottom: 0;
}

.exam-question-head {
    margin-bottom: 7px;
    font-size: calc(var(--exam-content-font-size, 11px) + 0.35px);
    line-height: 1.34;
    font-weight: 700;
    break-after: avoid;
    letter-spacing: 0.01em;
    color: #233448;
}

.exam-question-continuation {
    display: inline-flex;
    align-items: center;
    margin-left: 8px;
    padding: 2px 8px;
    border-radius: 999px;
    background: #eef2ff;
    color: #4f46e5;
    font-size: 0.78em;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: uppercase;
}

.exam-question-body {
    break-inside: auto;
    page-break-inside: auto;
}

.exam-question-paragraph,
.exam-question-heading-block,
.exam-question-source-block {
    margin: 0 0 4px;
    line-height: 1.38;
    overflow-wrap: break-word;
    word-break: normal;
}

.exam-question-paragraph {
    text-align: left;
}

.exam-question-heading-block {
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0;
}

.exam-question-source-block,
.exam-question-source {
    font-size: 9.2px;
    line-height: 1.35;
    letter-spacing: 0;
    color: #6f7b88;
}

.exam-question-image-wrap {
    margin: 6px 0 8px;
}

.exam-question-image {
    display: block;
    max-width: 100%;
    max-height: 155px;
    width: auto;
    height: auto;
    margin: 0 auto;
}

.exam-question-source {
    margin-top: 4px;
}

.exam-option-list {
    list-style: none;
    margin: 8px 0 0;
    padding: 0;
}

.exam-option-list li {
    margin-bottom: 3px;
    line-height: 1.34;
    overflow-wrap: break-word;
    word-break: normal;
    break-inside: avoid;
    page-break-inside: avoid;
}

.exam-option-prefix {
    display: inline-block;
    min-width: 20px;
    font-weight: 700;
}

.exam-discursive-lines {
    display: grid;
    gap: 8px;
    margin-top: 8px;
}

.exam-discursive-line {
    border-bottom: 1px solid #9ca7b3;
    height: 14px;
}

.exam-drawing-box {
    margin-top: 8px;
    min-height: 120px;
    border: 1px dashed #95a2b0;
    border-radius: 8px;
}

.exam-footer {
    flex: 0 0 auto;
    margin-top: auto;
    display: grid;
    gap: 4px;
}

.exam-footer-copy {
    padding: 8px 10px;
    border: 1px solid #d7dee6;
    border-radius: 8px;
    background: #f7fafc;
    font-size: 9.4px;
    line-height: 1.45;
    color: #415666;
    text-align: left;
}

.exam-footer-meta {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
    text-align: center;
    font-size: 8.8px;
    color: #5f7080;
    text-transform: none;
    letter-spacing: 0;
    line-height: 1.35;
    overflow-wrap: anywhere;
}

.exam-footer-meta-piece {
    display: inline-block;
}

.exam-footer-meta-separator {
    color: #94a2b0;
}

.exam-sheet.template-version_2 .exam-sheet,
.exam-sheet.template-version_2 {
    border-color: #8999ac;
}

.exam-sheet.template-version_3_1 .exam-questions {
    column-count: 1;
}

.exam-sheet.template-version_3_1 .exam-question {
    font-size: 12px;
    line-height: 1.42;
}

.exam-sheet.composition-booklet .exam-questions,
.exam-sheet.composition-blocks_content .exam-questions,
.exam-sheet.composition-blocks_difficulty .exam-questions,
.exam-sheet.composition-modular .exam-questions,
.exam-sheet.composition-attachments .exam-questions {
    column-count: 1;
}

.exam-sheet.composition-booklet .exam-question,
.exam-sheet.composition-modular .exam-question {
    margin-bottom: 12px;
}

.exam-sheet.composition-blocks_content .exam-question,
.exam-sheet.composition-blocks_difficulty .exam-question {
    padding-left: 8px;
    border-left: 3px solid #d5dee7;
}

.exam-sheet.identification-qr_code .exam-header {
    gap: 10px;
}

.exam-sheet.style-accessibility .exam-questions {
    column-count: 1;
}

.exam-sheet.style-accessibility .exam-question,
.exam-sheet.style-accessibility .exam-question-head,
.exam-sheet.style-accessibility .exam-option-list li,
.exam-sheet.style-accessibility .exam-question-paragraph,
.exam-sheet.style-accessibility .exam-question-heading-block {
    font-size: 15px;
    line-height: 1.55;
}

.exam-sheet.style-accessibility .exam-school-name {
    font-size: 21px;
}

.exam-sheet.style-economic .exam-question,
.exam-sheet.style-economic .exam-question-head,
.exam-sheet.style-economic .exam-option-list li,
.exam-sheet.style-economic .exam-question-paragraph,
.exam-sheet.style-economic .exam-question-heading-block {
    font-size: 10px;
    line-height: 1.2;
}

.exam-answer-page .exam-page-inner {
    min-height: auto;
    height: auto;
}

.exam-answer-title {
    margin: 4px 0 12px;
    text-align: center;
    font-size: 16px;
    font-weight: 700;
}

.exam-answer-meta {
    display: grid;
    gap: 8px;
    margin-bottom: 14px;
}

.exam-answer-meta-row {
    display: grid;
    grid-template-columns: 1.8fr 0.5fr 0.8fr;
    gap: 8px;
}

.exam-answer-meta-row-secondary {
    grid-template-columns: 1.6fr 1fr;
}

.exam-answer-field {
    min-height: 42px;
    padding: 10px 12px;
    border: 1px solid #c8d2dd;
    border-radius: 10px;
    background: #ffffff;
    font-size: 11px;
    font-weight: 700;
    color: #334155;
}

.exam-answer-field-fill {
    width: 100%;
}

.exam-inline-answer-block {
    display: grid;
    gap: 8px;
    margin-bottom: 8px;
    padding: 8px 10px;
    border: 1px solid #d7dee6;
    border-radius: 8px;
    background: #fbfcfe;
}

.exam-inline-answer-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    font-size: 10px;
}

.exam-inline-answer-head strong {
    color: #334155;
}

.exam-inline-answer-head span {
    color: #64748b;
}

.exam-inline-answer-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 6px 12px;
}

.exam-inline-answer-row {
    display: flex;
    align-items: center;
    gap: 6px;
    min-width: 0;
}

.exam-inline-answer-number {
    width: 24px;
    font-size: 10px;
    font-weight: 700;
    color: #334155;
}

.exam-inline-answer-options {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.exam-inline-answer-option {
    width: 18px;
    height: 18px;
    border: 1px solid #758292;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    color: #4b5563;
    background: #ffffff;
}

.exam-answer-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px 18px;
}

.exam-answer-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    font-size: 12px;
}

.exam-answer-number {
    width: 38px;
    font-weight: 700;
}

.exam-answer-option {
    width: 20px;
    height: 20px;
    border: 1px solid #758292;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

.exam-preview-notes {
    display: grid;
    gap: 14px;
}

.exam-preview-note-panel {
    padding: 18px 20px;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid rgba(36, 52, 71, 0.12);
    box-shadow: 0 10px 24px rgba(36, 52, 71, 0.06);
}

.exam-preview-note-panel h3 {
    margin: 0 0 10px;
    font-size: 16px;
    color: #243447;
}

.exam-preview-note-panel p,
.exam-preview-note-panel li {
    font-size: 14px;
    line-height: 1.45;
}

.exam-preview-note-panel ul {
    margin: 0;
    padding-left: 18px;
}

.exam-preview-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.exam-preview-summary-grid div {
    padding: 12px 14px;
    border-radius: 12px;
    background: #f8fafc;
    border: 1px solid rgba(36, 52, 71, 0.08);
}

.exam-preview-summary-grid strong,
.exam-preview-summary-grid span {
    display: block;
}

.exam-preview-summary-grid strong {
    margin-bottom: 4px;
    font-size: 12px;
    text-transform: uppercase;
    color: #66768a;
}

.exam-preview-summary-grid span {
    font-size: 14px;
    color: #243447;
}

@media screen and (max-width: 920px) {
    .exam-page {
        width: {$pageWidth};
        min-width: {$pageWidth};
    }

    .exam-brand {
        grid-template-columns: 1fr;
        justify-items: center;
    }

    .exam-identifier-panel,
    .exam-identifier-copy {
        justify-items: center;
        text-align: center;
    }

    .exam-body-layout.has-answer-sidebar {
        grid-template-columns: 1fr;
    }

    .exam-inline-answer-head {
        display: grid;
        justify-items: start;
    }

    .exam-header-row,
    .exam-proof-summary-grid,
    .exam-student-grid,
    .exam-answer-meta-row,
    .exam-answer-grid,
    .exam-preview-summary-grid,
    .exam-question-columns,
    .exam-inline-answer-grid {
        grid-template-columns: 1fr;
    }

    .exam-proof-card.is-proof-title,
    .exam-student-card,
    .exam-student-card.is-wide,
    .exam-student-card.is-medium,
    .exam-student-card.is-narrow {
        grid-column: span 1;
    }

    .exam-questions,
    .exam-questions.is-double-column,
    .exam-questions.is-economic {
        column-count: 1;
    }
}

@media print {
    body {
        background: #ffffff;
    }

    .exam-document-stack {
        gap: 0;
        width: 100%;
    }

    .exam-document-canvas {
        overflow: visible;
        padding-bottom: 0;
    }

    .exam-page {
        width: 100%;
        min-width: 0;
        max-width: none;
        border: 0;
        border-radius: 0;
        box-shadow: none;
        page-break-after: always;
        break-after: page;
    }

    .exam-page:last-child {
        page-break-after: auto;
        break-after: auto;
    }
}
CSS;
}

function exam_document_render_section_copy(string $content, string $className): string
{
    $content = exam_normalize_section_text($content);

    if ($content === '') {
        return '';
    }

    return '<div class="' . $className . '"><div class="exam-section-copy">'
        . question_render_formatted_text_html($content)
        . '</div></div>';
}

function exam_document_render_page_header(array $document): string
{
    $metadata = $document['metadata'];
    $headerConfig = exam_document_header_config($document);
    $headerContent = exam_normalize_section_text((string) ($document['sections']['header'] ?? ''));
    $proofTitle = trim((string) (($document['exam']['title'] ?? '') !== '' ? $document['exam']['title'] : $document['header_title']));
    $dateLabel = exam_format_date((string) $metadata['application_date']);
    $componentLabel = trim((string) ($metadata['component_name'] !== '' ? $metadata['component_name'] : $metadata['discipline']));
    $teacherLabel = trim((string) $metadata['teacher_name']);
    $classLabel = trim((string) $metadata['class_name']);
    $proofCodePanel = exam_document_render_identifier_panel($document);
    $proofTitle = $proofTitle !== '' ? $proofTitle : 'Prova sem título';
    $componentLabel = $componentLabel !== '' ? $componentLabel : 'Não informado';
    $teacherLabel = $teacherLabel !== '' ? $teacherLabel : 'Não informado';
    $classLabel = $classLabel !== '' ? $classLabel : 'Não informada';

    ob_start();
    ?>
<header class="exam-header" style="<?= exam_document_header_style($document) ?>">
    <div class="exam-brand<?= $headerConfig['right_logo'] !== '' || $proofCodePanel !== '' ? ' has-brand-side' : '' ?>">
        <div class="exam-brand-mark">
            <?php if ($headerConfig['left_logo'] !== ''): ?>
                <img src="<?= exam_document_escape((string) $headerConfig['left_logo']) ?>" alt="Logo esquerda">
            <?php else: ?>
                ESC
            <?php endif; ?>
        </div>
        <div class="exam-brand-copy">
            <strong class="exam-school-name"><?= exam_document_escape((string) $document['school_name']) ?></strong>
            <span class="exam-school-subtitle"><?= exam_document_escape((string) $headerConfig['school_subtitle']) ?></span>
        </div>
        <?php if ($headerConfig['right_logo'] !== '' || $proofCodePanel !== ''): ?>
        <div class="exam-brand-side">
            <?php if ($headerConfig['right_logo'] !== ''): ?>
                <div class="exam-brand-mark exam-brand-mark-right">
                    <img src="<?= exam_document_escape((string) $headerConfig['right_logo']) ?>" alt="Logo direita">
                </div>
            <?php endif; ?>
            <?= $proofCodePanel ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="exam-proof-summary-grid">
        <div class="exam-proof-card is-proof-title">
            <span class="exam-proof-card-label">Nome da prova</span>
            <strong class="exam-proof-card-value"><?= exam_document_escape($proofTitle) ?></strong>
            <?php if ($dateLabel !== ''): ?>
                <small class="exam-proof-card-meta">Aplicação em <?= exam_document_escape($dateLabel) ?></small>
            <?php endif; ?>
        </div>

        <div class="exam-proof-card">
            <span class="exam-proof-card-label">Avaliação</span>
            <strong class="exam-proof-card-value"><?= exam_document_escape((string) $document['header_title']) ?></strong>
        </div>

        <div class="exam-proof-card">
            <span class="exam-proof-card-label">Professor</span>
            <strong class="exam-proof-card-value"><?= exam_document_escape($teacherLabel) ?></strong>
        </div>

        <div class="exam-proof-card">
            <span class="exam-proof-card-label">Comp. curricular</span>
            <strong class="exam-proof-card-value"><?= exam_document_escape($componentLabel) ?></strong>
        </div>

        <div class="exam-proof-card">
            <span class="exam-proof-card-label">Turma</span>
            <strong class="exam-proof-card-value"><?= exam_document_escape($classLabel) ?></strong>
        </div>
    </div>

    <div class="exam-student-grid">
        <div class="exam-student-card is-wide">
            <span class="exam-student-label">Aluno(a)</span>
            <strong class="exam-student-value is-placeholder">Nome do aluno</strong>
        </div>

        <div class="exam-student-card is-narrow">
            <span class="exam-student-label">Nº</span>
            <strong class="exam-student-value is-placeholder">Número</strong>
        </div>

        <div class="exam-student-card is-medium">
            <span class="exam-student-label">Turma</span>
            <strong class="exam-student-value"><?= exam_document_escape($classLabel) ?></strong>
        </div>

        <div class="exam-student-card">
            <span class="exam-student-label">Data</span>
            <strong class="exam-student-value is-placeholder"><?= exam_document_escape($dateLabel !== '' ? $dateLabel : 'Definir data') ?></strong>
        </div>

        <div class="exam-student-card is-wide">
            <span class="exam-student-label">Assinatura</span>
            <strong class="exam-student-value is-placeholder">Assinatura do responsável</strong>
        </div>

        <div class="exam-student-card">
            <span class="exam-student-label">Valor</span>
            <strong class="exam-student-value is-placeholder">Nota / valor</strong>
        </div>
    </div>

    <?php if ($headerContent !== ''): ?>
        <?= exam_document_render_section_copy($headerContent, 'exam-header-copy') ?>
    <?php endif; ?>
</header>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_question(array $question): string
{
    $displayNumber = (int) ($question['display_number'] ?? 0);
    $title = trim((string) ($question['title'] ?? ''));
    $position = (string) ($question['segment_position'] ?? 'single');
    $components = is_array($question['components'] ?? null) ? $question['components'] : [];
    $isContinuation = in_array($position, ['middle', 'end'], true);

    ob_start();
    ?>
<article class="exam-question">
    <div class="exam-question-head">
        <?= exam_document_escape((string) $displayNumber) ?>.
        <?php if ($title !== '' && !$isContinuation): ?>
            (<?= exam_document_escape($title) ?>)
        <?php endif; ?>
        <?php if ($isContinuation): ?>
            <span class="exam-question-continuation">continuação</span>
        <?php endif; ?>
    </div>

    <div class="exam-question-body">
        <?php $pendingOptions = []; ?>
        <?php $flushOptions = static function (array &$optionItems): string {
            if ($optionItems === []) {
                return '';
            }

            ob_start();
            ?>
<ul class="exam-option-list">
    <?php foreach ($optionItems as $optionItem): ?>
        <li>
            <span class="exam-option-prefix"><?= exam_document_escape((string) ($optionItem['label'] ?? '')) ?>)</span>
            <?= (string) ($optionItem['html'] ?? '') ?>
        </li>
    <?php endforeach; ?>
</ul>
            <?php
            $optionItems = [];
            return (string) ob_get_clean();
        }; ?>

        <?php foreach ($components as $component): ?>
            <?php if (($component['kind'] ?? '') === 'option') {
                $pendingOptions[] = $component;
                continue;
            } ?>

            <?= $flushOptions($pendingOptions) ?>

            <?php if (($component['kind'] ?? '') === 'prompt'): ?>
                <p class="<?= exam_document_escape((string) ($component['class'] ?? 'exam-question-paragraph')) ?>"><?= (string) ($component['html'] ?? '') ?></p>
            <?php elseif (($component['kind'] ?? '') === 'image'): ?>
                <div class="exam-question-image-wrap"><img class="exam-question-image" src="<?= exam_document_escape((string) ($component['url'] ?? '')) ?>" alt="Imagem complementar da questão"></div>
            <?php elseif (($component['kind'] ?? '') === 'discursive_lines'): ?>
                <div class="exam-discursive-lines">
                    <?php for ($i = 0; $i < max(1, (int) ($component['count'] ?? 0)); $i++): ?>
                        <div class="exam-discursive-line"></div>
                    <?php endfor; ?>
                </div>
            <?php elseif (($component['kind'] ?? '') === 'drawing'): ?>
                <div class="exam-drawing-box"></div>
            <?php elseif (($component['kind'] ?? '') === 'source'): ?>
                <div class="exam-question-source">Fonte: <?= exam_document_escape((string) ($component['text'] ?? '')) ?></div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?= $flushOptions($pendingOptions) ?>
    </div>
</article>
    <?php

    return (string) ob_get_clean();
}

function exam_document_question_container_class(array $document): string
{
    return match ($document['style']) {
        'single_column' => 'exam-questions is-single-column',
        'economic' => 'exam-questions is-economic',
        'accessibility' => 'exam-questions is-accessibility',
        default => 'exam-questions is-double-column',
    };
}

function exam_document_render_answer_sidebar(array $rows): string
{
    if ($rows === []) {
        return '';
    }

    ob_start();
    ?>
<aside class="exam-answer-sidebar">
    <div class="exam-answer-sidebar-title">Gabarito</div>
    <div class="exam-answer-sidebar-grid">
        <?php foreach ($rows as $row): ?>
            <div class="exam-answer-sidebar-row">
                <span class="exam-answer-sidebar-number"><?= exam_document_escape((string) $row['number']) ?></span>
                <div class="exam-answer-sidebar-options">
                    <?php foreach ($row['labels'] as $label): ?>
                        <span class="exam-answer-sidebar-option"><?= exam_document_escape($label) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</aside>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_inline_answer_block(array $document): string
{
    if (!exam_document_should_render_inline_answer_block($document)) {
        return '';
    }

    ob_start();
    ?>
<section class="exam-inline-answer-block">
    <div class="exam-inline-answer-head">
        <strong>Gabarito do aluno</strong>
        <span>Preencha somente uma alternativa por questão.</span>
    </div>

    <div class="exam-inline-answer-grid">
        <?php foreach ($document['answer_sheet_rows'] as $row): ?>
            <div class="exam-inline-answer-row">
                <span class="exam-inline-answer-number"><?= exam_document_escape((string) $row['number']) ?></span>
                <div class="exam-inline-answer-options">
                    <?php foreach ($row['labels'] as $label): ?>
                        <span class="exam-inline-answer-option"><?= exam_document_escape($label) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_page_body(array $document, array $questionsOnPage, bool $includeCustomBody = false): string
{
    $questionClass = exam_document_question_container_class($document);
    $bodyContent = $includeCustomBody ? exam_normalize_section_text((string) ($document['sections']['body'] ?? '')) : '';
    $pageAnswerRows = exam_document_should_render_answer_sidebar($document)
        ? exam_document_page_answer_rows($document, $questionsOnPage)
        : [];
    $layoutClass = $pageAnswerRows !== [] ? 'exam-body-layout has-answer-sidebar' : 'exam-body-layout';

    ob_start();
    ?>
<div class="<?= $layoutClass ?>">
    <div class="exam-body-main">
        <?php if ($includeCustomBody): ?>
            <?= exam_document_render_inline_answer_block($document) ?>
        <?php endif; ?>

        <?php if ($bodyContent !== ''): ?>
            <?= exam_document_render_section_copy($bodyContent, 'exam-body-copy') ?>
        <?php endif; ?>

        <div class="<?= $questionClass ?>">
            <?php foreach ($questionsOnPage as $question): ?>
                <?= exam_document_render_question($question) ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($pageAnswerRows !== []): ?>
        <?= exam_document_render_answer_sidebar($pageAnswerRows) ?>
    <?php endif; ?>
</div>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_page_footer(array $document, string $title, string $pageSuffix = ''): string
{
    $footerContent = exam_document_footer_message($document);
    $schoolShort = exam_document_school_abbreviation((string) $document['school_name']);
    $proofCode = exam_document_identifier_token($document);
    $pageLabel = $pageSuffix !== '' ? ltrim($pageSuffix, ' |') : '';

    ob_start();
    ?>
<div class="exam-footer">
    <?= exam_document_render_section_copy($footerContent, 'exam-footer-copy') ?>

    <div class="exam-footer-meta">
        <span class="exam-footer-meta-piece"><?= exam_document_escape($proofCode) ?></span>
        <span class="exam-footer-meta-separator">|</span>
        <span class="exam-footer-meta-piece"><?= exam_document_escape($schoolShort !== '' ? $schoolShort : (string) $document['school_name']) ?></span>
        <span class="exam-footer-meta-separator">|</span>
        <span class="exam-footer-meta-piece"><?= exam_document_escape($title) ?></span>
        <?php if ($pageLabel !== ''): ?>
            <span class="exam-footer-meta-separator">|</span>
            <span class="exam-footer-meta-piece"><?= exam_document_escape($pageLabel) ?></span>
        <?php endif; ?>
    </div>
</div>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_question_page(array $document, array $questionsOnPage, int $pageNumber, int $totalPages): string
{
    $sheetClass = 'exam-sheet'
        . ' template-' . exam_document_escape($document['template'])
        . ' style-' . exam_document_escape($document['style'])
        . ' composition-' . exam_document_escape(exam_document_composition_mode($document))
        . ' identification-' . exam_document_escape(exam_document_identification_mode($document))
        . ' response-' . exam_document_escape(exam_document_response_mode($document));
    $pageSuffix = $totalPages > 1 ? ' | Página ' . $pageNumber . ' de ' . $totalPages : '';

    ob_start();
    ?>
<section class="exam-page">
    <div class="exam-page-inner">
        <div class="<?= $sheetClass ?>">
            <?php if ($pageNumber === 1): ?>
                <?= exam_document_render_page_header($document) ?>
            <?php endif; ?>

            <?= exam_document_render_page_body($document, $questionsOnPage, $pageNumber === 1) ?>

            <?= exam_document_render_page_footer($document, (string) $document['exam']['title'], $pageSuffix) ?>
        </div>
    </div>
</section>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_answer_sheet_page(array $document): string
{
    if (!exam_document_should_render_answer_sheet_page($document)) {
        return '';
    }

    $answerTitle = 'Folha de gabarito';
    $metadata = $document['metadata'];
    $dateLabel = exam_format_date((string) ($metadata['application_date'] ?? ''));
    $classLabel = trim((string) ($metadata['class_name'] ?? ''));

    ob_start();
    ?>
<section class="exam-page exam-answer-page">
    <div class="exam-page-inner">
        <div class="exam-sheet template-<?= exam_document_escape($document['template']) ?> style-<?= exam_document_escape($document['style']) ?> composition-<?= exam_document_escape(exam_document_composition_mode($document)) ?> identification-<?= exam_document_escape(exam_document_identification_mode($document)) ?> response-<?= exam_document_escape(exam_document_response_mode($document)) ?>">
            <div class="exam-answer-title"><?= exam_document_escape($answerTitle) ?></div>

            <div class="exam-answer-meta">
                <div class="exam-answer-meta-row">
                    <div class="exam-answer-field exam-answer-field-fill">Aluno(a):</div>
                    <div class="exam-answer-field">Nº:</div>
                    <div class="exam-answer-field">Turma: <?= exam_document_escape($classLabel !== '' ? $classLabel : '_____') ?></div>
                </div>
                <div class="exam-answer-meta-row exam-answer-meta-row-secondary">
                    <div class="exam-answer-field exam-answer-field-fill">Assinatura:</div>
                    <div class="exam-answer-field">Data: <?= exam_document_escape($dateLabel !== '' ? $dateLabel : '____/____/________') ?></div>
                </div>
            </div>

            <div class="exam-answer-grid">
                <?php foreach ($document['answer_sheet_rows'] as $row): ?>
                    <div class="exam-answer-row">
                        <span class="exam-answer-number"><?= exam_document_escape((string) $row['number']) ?>.</span>
                        <?php foreach ($row['labels'] as $label): ?>
                            <span class="exam-answer-option"><?= exam_document_escape($label) ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_sheet(array $document): string
{
    $questionPages = exam_document_paginate_questions($document);
    $html = '<div class="exam-document-canvas"><div class="exam-document-stack">';
    $totalPages = count($questionPages);

    foreach ($questionPages as $index => $questionsOnPage) {
        $html .= exam_document_render_question_page($document, $questionsOnPage, $index + 1, $totalPages);
    }

    $html .= exam_document_render_answer_sheet_page($document);
    $html .= '</div></div>';

    return $html;
}

function exam_document_render_preview_notes(array $document): string
{
    $metadata = $document['metadata'];
    $responseMode = exam_document_response_mode($document);
    $answerChecklist = match ($responseMode) {
        'bubble_answer_sheet' => 'Revise o gabarito logo abaixo do cabeçalho e confira se a grade de marcação ficou completa.',
        'separate_answer_sheet' => 'Revise a folha de gabarito separada antes de exportar.',
        'lateral_answer_key' => 'Confira o gabarito lateral junto das questões objetivas.',
        default => 'Confira se a estrutura da prova está completa antes de exportar.',
    };
    $summaryItems = [
        ['label' => 'Colégio', 'value' => (string) $document['school_name']],
        ['label' => 'Professor', 'value' => (string) $metadata['teacher_name']],
        ['label' => 'Turma', 'value' => (string) $metadata['class_name']],
        ['label' => 'Questões', 'value' => (string) count($document['questions'])],
    ];

    ob_start();
    ?>
<section class="exam-preview-notes">
    <article class="exam-preview-note-panel">
        <h3>Resumo da prova</h3>
        <div class="exam-preview-summary-grid">
            <?php foreach ($summaryItems as $item): ?>
                <div>
                    <strong><?= exam_document_escape($item['label']) ?></strong>
                    <span><?= exam_document_escape($item['value']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>

    <article class="exam-preview-note-panel">
        <h3>Checklist de revisão</h3>
        <ul>
            <li>Confira cabeçalho, corpo introdutório e rodapé antes de exportar.</li>
            <li>Valide data, turma, professor e o espaçamento do modelo escolhido.</li>
            <li><?= exam_document_escape($answerChecklist) ?></li>
        </ul>
    </article>
</section>
    <?php

    return (string) ob_get_clean();
}

function exam_document_render_html(array $document): string
{
    $title = exam_document_escape((string) ($document['exam']['title'] ?? 'Prova'));
    $styles = exam_document_styles(true, $document);
    $sheet = exam_document_render_sheet($document);

    return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon/favicon.ico">
    <link rel="icon" type="image/svg+xml" href="/favicon/favicon.svg">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon/favicon-96x96.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/favicon/android-chrome-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/favicon/android-chrome-512x512.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <meta name="theme-color" content="#4f2ec9">
    <title>{$title}</title>
    <style>
        {$styles}
    </style>
</head>
<body>
    {$sheet}
</body>
</html>
HTML;
}
