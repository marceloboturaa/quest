<?php
declare(strict_types=1);

const EXAM_META_START = '[quest_exam_meta]';
const EXAM_META_END = '[/quest_exam_meta]';

function exam_default_metadata(): array
{
    return [
        'exam_style' => 'double_column',
        'exam_label' => 'AVALIACAO',
        'discipline' => '',
        'component_name' => '',
        'year_reference' => '',
        'teacher_name' => '',
        'school_name' => '',
        'class_name' => '',
        'application_date' => '',
    ];
}

function exam_collect_metadata(array $source): array
{
    $metadata = exam_default_metadata();

    foreach ($metadata as $key => $value) {
        $metadata[$key] = trim((string) ($source[$key] ?? ''));
    }

    return $metadata;
}

function exam_build_stored_instructions(string $instructions, array $metadata): ?string
{
    $instructions = trim($instructions);
    $metadata = array_replace(exam_default_metadata(), $metadata);
    $metaLines = [];

    foreach ($metadata as $key => $value) {
        if ($value === '') {
            continue;
        }

        $metaLines[] = $key . '=' . str_replace(["\r", "\n"], ' ', $value);
    }

    if ($metaLines === []) {
        return $instructions !== '' ? $instructions : null;
    }

    $parts = [
        EXAM_META_START,
        implode("\n", $metaLines),
        EXAM_META_END,
    ];

    if ($instructions !== '') {
        $parts[] = $instructions;
    }

    return implode("\n", $parts);
}

function exam_parse_stored_instructions(?string $stored): array
{
    $metadata = exam_default_metadata();
    $stored = trim((string) $stored);

    if ($stored === '') {
        return [
            'metadata' => $metadata,
            'instructions' => '',
        ];
    }

    $pattern = '/' . preg_quote(EXAM_META_START, '/') . '\n(.*?)\n' . preg_quote(EXAM_META_END, '/') . '\n?/s';

    if (preg_match($pattern, $stored, $matches) !== 1) {
        return [
            'metadata' => $metadata,
            'instructions' => $stored,
        ];
    }

    $body = trim((string) $matches[1]);
    $instructions = trim((string) preg_replace($pattern, '', $stored, 1));

    foreach (preg_split('/\r?\n/', $body) ?: [] as $line) {
        $parts = explode('=', $line, 2);

        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        if (array_key_exists($key, $metadata)) {
            $metadata[$key] = $value;
        }
    }

    return [
        'metadata' => $metadata,
        'instructions' => $instructions,
    ];
}

function exam_metadata_labels(): array
{
    return [
        'exam_style' => 'Formato',
        'exam_label' => 'Tipo',
        'discipline' => 'Disciplina',
        'component_name' => 'Comp. Curricular',
        'year_reference' => 'Ano',
        'teacher_name' => 'Professor',
        'school_name' => 'Escola',
        'class_name' => 'Turma',
        'application_date' => 'Data',
    ];
}

function exam_metadata_summary(array $metadata): array
{
    $labels = exam_metadata_labels();
    $summary = [];

    foreach (array_replace(exam_default_metadata(), $metadata) as $key => $value) {
        if ($value === '') {
            continue;
        }

        $summary[] = [
            'label' => $labels[$key] ?? $key,
            'value' => match ($key) {
                'application_date' => exam_format_date($value),
                'exam_style' => exam_style_label($value),
                default => $value,
            },
        ];
    }

    return $summary;
}

function exam_format_date(string $value): string
{
    $timestamp = strtotime($value);

    return $timestamp ? date('d/m/Y', $timestamp) : $value;
}

function exam_style_options(): array
{
    return [
        'double_column' => 'Estilo padrao coluna dupla',
        'single_column' => 'Estilo padrao coluna simples',
        'economic' => 'Estilo economico',
        'accessibility' => 'Modo de acessibilidade',
    ];
}

function exam_style_label(string $style): string
{
    $options = exam_style_options();
    return $options[$style] ?? $options['double_column'];
}
