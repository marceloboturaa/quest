<?php
declare(strict_types=1);

const EXAM_META_START = '[quest_exam_meta]';
const EXAM_META_END = '[/quest_exam_meta]';
const EXAM_SECTIONS_START = '[quest_exam_sections]';
const EXAM_SECTIONS_END = '[/quest_exam_sections]';
const EXAM_DEFAULT_SCHOOL_NAME = 'COLÉGIO ESTADUAL CÍVICO-MILITAR TANCREDO DE ALMEIDA NEVES';
const EXAM_DEFAULT_SCHOOL_SUBTITLE = 'ENSINO FUNDAMENTAL, MÉDIO E PROFISSIONALIZANTE';

function exam_default_metadata(): array
{
    return [
        'exam_template' => 'version_1',
        'exam_style' => 'double_column',
        'response_mode' => 'separate_answer_sheet',
        'composition_mode' => 'mixed',
        'ordering_mode' => 'automatic_numbering',
        'identification_mode' => 'standard',
        'variant_label' => '',
        'exam_label' => 'AVALIAÇÃO TRIMESTRAL',
        'discipline' => '',
        'component_name' => '',
        'year_reference' => '',
        'teacher_name' => '',
        'school_name' => EXAM_DEFAULT_SCHOOL_NAME,
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

function exam_default_sections(): array
{
    return [
        'header' => '',
        'body' => '',
        'footer' => '',
    ];
}

function exam_normalize_section_text(?string $value): string
{
    $normalized = str_replace(["\r\n", "\r"], "\n", (string) $value);
    return trim($normalized);
}

function exam_merge_sections(array $baseSections, array $source, string $prefix = ''): array
{
    $sections = array_replace(exam_default_sections(), $baseSections);
    $hasExplicitSections = false;

    foreach (array_keys(exam_default_sections()) as $key) {
        $field = $prefix . $key . '_content';

        if (!array_key_exists($field, $source)) {
            continue;
        }

        $sections[$key] = exam_normalize_section_text((string) $source[$field]);
        $hasExplicitSections = true;
    }

    $legacyField = $prefix . 'instructions';

    if (!$hasExplicitSections && array_key_exists($legacyField, $source)) {
        $sections['header'] = exam_normalize_section_text((string) $source[$legacyField]);
    }

    return $sections;
}

function exam_collect_sections(array $source, string $prefix = ''): array
{
    return exam_merge_sections(exam_default_sections(), $source, $prefix);
}

function exam_has_sections_content(array $sections): bool
{
    foreach (array_replace(exam_default_sections(), $sections) as $value) {
        if (exam_normalize_section_text((string) $value) !== '') {
            return true;
        }
    }

    return false;
}

function exam_extract_block(string $stored, string $startMarker, string $endMarker): array
{
    $pattern = '/'
        . preg_quote($startMarker, '/')
        . '\r?\n(.*?)\r?\n'
        . preg_quote($endMarker, '/')
        . '\r?\n?/s';

    if (preg_match($pattern, $stored, $matches) !== 1) {
        return [null, $stored];
    }

    return [
        trim((string) $matches[1]),
        trim((string) preg_replace($pattern, '', $stored, 1)),
    ];
}

function exam_build_stored_content(array $metadata, array $sections): ?string
{
    $metadata = array_replace(exam_default_metadata(), $metadata);
    $sections = array_replace(exam_default_sections(), $sections);
    $metaLines = [];

    foreach ($metadata as $key => $value) {
        $value = trim((string) $value);

        if ($value === '') {
            continue;
        }

        $metaLines[] = $key . '=' . str_replace(["\r", "\n"], ' ', $value);
    }

    foreach ($sections as $key => $value) {
        $sections[$key] = exam_normalize_section_text((string) $value);
    }

    if ($metaLines === [] && !exam_has_sections_content($sections)) {
        return null;
    }

    $parts = [];

    if ($metaLines !== []) {
        $parts[] = EXAM_META_START;
        $parts[] = implode("\n", $metaLines);
        $parts[] = EXAM_META_END;
    }

    if (exam_has_sections_content($sections)) {
        $parts[] = EXAM_SECTIONS_START;
        $parts[] = (string) json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $parts[] = EXAM_SECTIONS_END;
    }

    return implode("\n", $parts);
}

function exam_build_stored_instructions(string $instructions, array $metadata): ?string
{
    return exam_build_stored_content($metadata, [
        'header' => $instructions,
        'body' => '',
        'footer' => '',
    ]);
}

function exam_parse_stored_instructions(?string $stored): array
{
    $metadata = exam_default_metadata();
    $sections = exam_default_sections();
    $stored = trim((string) $stored);

    if ($stored === '') {
        return [
            'metadata' => $metadata,
            'instructions' => '',
            'sections' => $sections,
        ];
    }

    [$metaBlock, $remaining] = exam_extract_block($stored, EXAM_META_START, EXAM_META_END);

    if ($metaBlock !== null) {
        foreach (preg_split('/\r?\n/', $metaBlock) ?: [] as $line) {
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
    }

    [$sectionsBlock, $remaining] = exam_extract_block($remaining, EXAM_SECTIONS_START, EXAM_SECTIONS_END);

    if ($sectionsBlock !== null) {
        $decoded = json_decode($sectionsBlock, true);

        if (is_array($decoded)) {
            foreach (array_keys($sections) as $key) {
                if (!array_key_exists($key, $decoded)) {
                    continue;
                }

                $sections[$key] = exam_normalize_section_text((string) $decoded[$key]);
            }
        }
    }

    $legacyInstructions = exam_normalize_section_text($remaining);

    if (!exam_has_sections_content($sections) && $legacyInstructions !== '') {
        $sections['header'] = $legacyInstructions;
    }

    $primaryInstructions = $sections['body'] !== ''
        ? $sections['body']
        : ($sections['header'] !== '' ? $sections['header'] : $legacyInstructions);

    return [
        'metadata' => $metadata,
        'instructions' => $primaryInstructions,
        'sections' => $sections,
    ];
}

function exam_metadata_labels(): array
{
    return [
        'exam_template' => 'Modelo',
        'exam_style' => 'Layout',
        'response_mode' => 'Resposta',
        'composition_mode' => 'Composição',
        'ordering_mode' => 'Organização',
        'identification_mode' => 'Identificação',
        'variant_label' => 'Versão',
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
                'exam_template' => exam_template_label($value),
                'exam_style' => exam_style_label($value),
                'response_mode' => exam_response_mode_label($value),
                'composition_mode' => exam_composition_mode_label($value),
                'ordering_mode' => exam_ordering_mode_label($value),
                'identification_mode' => exam_identification_mode_label($value),
                'variant_label' => exam_variant_label($value),
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
        'double_column' => 'Coluna dupla (padrão ENEM)',
        'single_column' => 'Coluna simples',
        'economic' => 'Estilo econômico (mais compacto)',
        'accessibility' => 'Modo acessibilidade (fonte grande e espaçamento maior)',
    ];
}

function exam_response_mode_options(): array
{
    return [
        'separate_answer_sheet' => 'Prova com folha de resposta separada',
        'lateral_answer_key' => 'Prova com gabarito lateral',
        'bubble_answer_sheet' => 'Prova com leitura por gabarito (bolinhas)',
        'discursive_space' => 'Prova discursiva com espaço para resposta',
    ];
}

function exam_composition_mode_options(): array
{
    return [
        'mixed' => 'Prova mista (objetiva + discursiva)',
        'booklet' => 'Prova estilo caderno (várias páginas)',
        'blocks_content' => 'Prova por blocos (por conteúdo)',
        'blocks_difficulty' => 'Prova por blocos (por dificuldade)',
        'dynamic_bank' => 'Prova com banco de questões dinâmico',
        'modular' => 'Prova modular (por competências/habilidades)',
        'attachments' => 'Prova com anexos (tabelas, textos, gráficos)',
    ];
}

function exam_ordering_mode_options(): array
{
    return [
        'automatic_numbering' => 'Prova com numeração automática',
        'shuffle_questions' => 'Prova com embaralhamento de questões',
        'variants' => 'Prova com versões (A, B, C, D...)',
    ];
}

function exam_identification_mode_options(): array
{
    return [
        'standard' => 'Identificação padrão',
        'qr_code' => 'Prova com QR Code de identificação',
    ];
}

function exam_variant_label_options(): array
{
    return [
        '' => 'Sem versão',
        'A' => 'Versão A',
        'B' => 'Versão B',
        'C' => 'Versão C',
        'D' => 'Versão D',
    ];
}

function exam_template_options(): array
{
    return [
        'version_1' => 'Avaliacao - Versao 1',
        'version_2' => 'Avaliacao - Versao 2',
        'version_3_1' => 'Avaliacao - Versao 3.1',
    ];
}

function exam_template_label(string $template): string
{
    $options = exam_template_options();
    return $options[$template] ?? $options['version_1'];
}

function exam_style_label(string $style): string
{
    $options = exam_style_options();
    return $options[$style] ?? $options['double_column'];
}

function exam_response_mode_label(string $mode): string
{
    $options = exam_response_mode_options();
    return $options[$mode] ?? $options['separate_answer_sheet'];
}

function exam_composition_mode_label(string $mode): string
{
    $options = exam_composition_mode_options();
    return $options[$mode] ?? $options['mixed'];
}

function exam_ordering_mode_label(string $mode): string
{
    $options = exam_ordering_mode_options();
    return $options[$mode] ?? $options['automatic_numbering'];
}

function exam_identification_mode_label(string $mode): string
{
    $options = exam_identification_mode_options();
    return $options[$mode] ?? $options['standard'];
}

function exam_variant_label(string $variant): string
{
    $options = exam_variant_label_options();
    return $options[$variant] ?? $options[''];
}

function exam_feature_catalog(): array
{
    return [
        [
            'title' => 'Estrutura da prova',
            'status' => 'ativa',
            'items' => [
                'Cabeçalho editável',
                'Corpo editável',
                'Rodapé editável',
            ],
        ],
        [
            'title' => 'Layouts',
            'status' => 'ativa',
            'items' => array_values(exam_style_options()),
        ],
        [
            'title' => 'Modo de resposta',
            'status' => 'ativa',
            'items' => array_values(exam_response_mode_options()),
        ],
        [
            'title' => 'Composição da prova',
            'status' => 'catalogada',
            'items' => array_values(exam_composition_mode_options()),
        ],
        [
            'title' => 'Organização',
            'status' => 'ativa',
            'items' => array_values(exam_ordering_mode_options()),
        ],
        [
            'title' => 'Identificação',
            'status' => 'catalogada',
            'items' => array_values(exam_identification_mode_options()),
        ],
        [
            'title' => 'Versões disponíveis',
            'status' => 'ativa',
            'items' => array_values(array_filter(
                exam_variant_label_options(),
                static fn(string $label, string $key): bool => $key !== '',
                ARRAY_FILTER_USE_BOTH
            )),
        ],
    ];
}
