<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/question_repository.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_metadata.php';

require_login();

function exam_create_steps(): array
{
    return [
        'cabecalho' => 'Cabeçalho',
        'corpo' => 'Corpo e formato',
        'avisos' => 'Avisos e rodapé',
        'modelo' => 'Modelo visual',
    ];
}

function exam_create_visible_fields(string $step): array
{
    return match ($step) {
        'cabecalho' => ['draft_title', 'application_date', 'exam_label', 'teacher_name', 'component_name', 'class_name', 'school_name', 'school_subtitle', 'response_mode', 'answer_preview_quantity', 'answer_preview_orientation', 'answer_preview_width_mode', 'answer_preview_size_cm', 'answer_preview_height_cm', 'answer_preview_font_size', 'answer_preview_card_color', 'answer_preview_surface_color', 'answer_preview_accent_color', 'answer_preview_line_color', 'header_logo_left', 'header_logo_right', 'header_background_color', 'header_title_color', 'header_subtitle_color', 'header_title_size', 'header_subtitle_size', 'header_logo_size', 'header_min_height'],
        'corpo' => ['content_font_size'],
        'avisos' => ['header_content', 'body_content', 'footer_content'],
        default => [],
    };
}

function exam_create_style_preview_class(string $style): string
{
    return match ($style) {
        'double_column' => 'is-two-columns',
        'triple_column' => 'is-three-columns',
        default => 'is-one-column',
    };
}

function exam_create_excerpt(?string $text, int $limit = 180): string
{
    $plain = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $text), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

    if ($plain === '') {
        return 'Questão simulada para a pré-visualização.';
    }

    return function_exists('mb_strimwidth') ? mb_strimwidth($plain, 0, $limit, '...') : (strlen($plain) > $limit ? substr($plain, 0, $limit - 3) . '...' : $plain);
}

function exam_create_question_type_label(string $type): string
{
    return match ($type) {
        'discursive' => 'Discursiva',
        'true_false' => 'Verdadeiro ou falso',
        'drawing' => 'Desenho',
        default => 'Múltipla escolha',
    };
}

function exam_create_format_cm(float $value): string
{
    $formatted = number_format($value, 2, '.', '');
    return rtrim(rtrim($formatted, '0'), '.');
}

function exam_create_normalize_cm(?string $value, float $defaultCm, bool $allowLegacyPixels = false, float $legacyThreshold = 20.0): string
{
    $raw = trim(str_replace(',', '.', (string) $value));
    $numeric = is_numeric($raw) ? (float) $raw : 0.0;

    if ($numeric <= 0) {
        return exam_create_format_cm($defaultCm);
    }

    $cmValue = $allowLegacyPixels && $numeric > $legacyThreshold
        ? $numeric / 37.7952755906
        : $numeric;

    return exam_create_format_cm($cmValue);
}

function exam_create_cm_to_px(?string $value, float $defaultCm): int
{
    $raw = trim(str_replace(',', '.', (string) $value));
    $numeric = is_numeric($raw) ? (float) $raw : 0.0;
    $cmValue = $numeric > 0 ? $numeric : $defaultCm;

    return (int) round($cmValue * 37.7952755906);
}

function exam_create_normalize_color(?string $value, string $fallback): string
{
    $candidate = trim((string) $value);
    return preg_match('/^#[0-9a-fA-F]{6}$/', $candidate) === 1 ? strtolower($candidate) : $fallback;
}

function exam_create_normalize_int(?string $value, int $default, int $min, int $max): string
{
    $filtered = filter_var(trim((string) $value), FILTER_VALIDATE_INT);
    $number = $filtered !== false ? (int) $filtered : $default;
    $number = max($min, min($max, $number));

    return (string) $number;
}

function exam_create_preview_questions(array $selectedPreview): array
{
    if ($selectedPreview !== []) {
        return array_map(static fn(array $question): array => [
            'title' => (string) ($question['title'] ?? 'Questão'),
            'prompt' => exam_create_excerpt((string) ($question['prompt'] ?? '')),
            'type' => (string) ($question['question_type'] ?? 'multiple_choice'),
        ], array_slice($selectedPreview, 0, 4));
    }

    return [
        ['title' => 'Leitura e interpretação', 'prompt' => 'Leia o texto-base e marque a alternativa que melhor representa a ideia principal do autor.', 'type' => 'multiple_choice'],
        ['title' => 'Cálculo e raciocínio', 'prompt' => 'Resolva a situação-problema abaixo e apresente o resultado final com clareza.', 'type' => 'discursive'],
        ['title' => 'Aplicação prática', 'prompt' => 'Observe a proposta e organize a resposta conforme as orientações da prova.', 'type' => 'multiple_choice'],
    ];
}

function exam_create_exam_code(array $formData): string
{
    $classToken = preg_replace('/[^A-Za-z0-9]/', '', strtoupper((string) ($formData['class_name'] ?? 'GER')));
    $classToken = $classToken !== '' ? substr($classToken, 0, 4) : 'GER';
    $dateToken = preg_replace('/[^0-9]/', '', (string) ($formData['application_date'] ?? ''));
    $dateToken = $dateToken !== '' ? $dateToken : date('Ymd');
    $component = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', trim((string) ($formData['component_name'] ?? $formData['discipline'] ?? 'GERAL'))) ?? 'GERAL';
    $token = '';

    foreach (preg_split('/\s+/u', $component) ?: [] as $part) {
        if ($part !== '') {
            $token .= strtoupper(mb_substr($part, 0, 2));
        }
    }

    return 'PRV-' . $dateToken . '-' . $classToken . '-' . substr($token !== '' ? $token : 'GER', 0, 4);
}

function exam_create_school_acronym(string $schoolName): string
{
    $ignore = ['de', 'da', 'do', 'das', 'dos', 'e'];
    $letters = [];

    foreach (preg_split('/\s+/u', trim($schoolName)) ?: [] as $part) {
        $clean = mb_strtolower(trim($part));

        if ($clean !== '' && !in_array($clean, $ignore, true)) {
            $letters[] = mb_strtoupper(mb_substr($part, 0, 1));
        }
    }

    return $letters !== [] ? implode('', $letters) : 'ESC';
}

function exam_create_step_query(array $formData, array $selectedIds, int $editExamId, string $step): string
{
    $params = ['step' => $step];

    foreach ($formData as $key => $value) {
        $params[match ($key) {
            'header_content' => 'draft_header_content',
            'body_content' => 'draft_body_content',
            'footer_content' => 'draft_footer_content',
            default => $key,
        }] = $value;
    }

    if ($editExamId > 0) {
        $params['edit'] = $editExamId;
    }

    if ($selectedIds !== []) {
        $params['question_ids'] = $selectedIds;
    }

    return 'exam-create.php?' . http_build_query($params);
}

function exam_create_hidden_fields(array $formData, array $exclude = []): string
{
    ob_start();
    foreach ($formData as $name => $value) {
        if (in_array($name, $exclude, true)) {
            continue;
        }

        $fieldName = match ($name) {
            'header_content' => 'draft_header_content',
            'body_content' => 'draft_body_content',
            'footer_content' => 'draft_footer_content',
            default => $name,
        };

        echo '<input type="hidden" name="' . h($fieldName) . '" value="' . h((string) $value) . '">' . PHP_EOL;
    }

    return (string) ob_get_clean();
}

$user = current_user();
$userId = (int) $user['id'];
$editExamId = (int) ($_GET['edit'] ?? 0);
$editingExam = $editExamId > 0 ? exam_find($editExamId, $userId) : null;
$parsedExamData = $editingExam ? exam_parse_stored_instructions($editingExam['instructions'] ?? null) : ['metadata' => exam_default_metadata(), 'instructions' => '', 'sections' => exam_default_sections()];
$baseExamDefaults = exam_default_metadata();
$userExamDefaults = exam_user_profile_defaults($user);
$resolveProfileAwareValue = static function (string $key, string $fallback = '') use ($parsedExamData, $baseExamDefaults, $userExamDefaults): string {
    $metadataValue = trim((string) ($parsedExamData['metadata'][$key] ?? ''));
    $baseValue = trim((string) ($baseExamDefaults[$key] ?? ''));
    $profileValue = trim((string) ($userExamDefaults[$key] ?? $fallback));
    if ($metadataValue !== '' && $metadataValue !== $baseValue) {
        return $metadataValue;
    }
    if ($profileValue !== '') {
        return $profileValue;
    }
    return $metadataValue !== '' ? $metadataValue : $fallback;
};
$draftSections = exam_merge_sections($parsedExamData['sections'] ?? exam_default_sections(), $_GET, 'draft_');
$loadedExamQuestionIds = $editingExam ? exam_question_ids($editExamId, $userId) : [];
$formData = [
    'draft_title' => (string) ($_GET['draft_title'] ?? ($editingExam['title'] ?? '')),
    'exam_template' => (string) ($_GET['exam_template'] ?? $parsedExamData['metadata']['exam_template']),
    'exam_style' => (string) ($_GET['exam_style'] ?? $parsedExamData['metadata']['exam_style']),
    'response_mode' => (string) ($_GET['response_mode'] ?? $parsedExamData['metadata']['response_mode']),
    'composition_mode' => (string) ($_GET['composition_mode'] ?? $parsedExamData['metadata']['composition_mode']),
    'ordering_mode' => (string) ($_GET['ordering_mode'] ?? $parsedExamData['metadata']['ordering_mode']),
    'identification_mode' => (string) ($_GET['identification_mode'] ?? $parsedExamData['metadata']['identification_mode']),
    'variant_label' => (string) ($_GET['variant_label'] ?? $parsedExamData['metadata']['variant_label']),
    'exam_label' => (string) ($_GET['exam_label'] ?? $resolveProfileAwareValue('exam_label')),
    'discipline' => (string) ($_GET['discipline'] ?? $resolveProfileAwareValue('discipline')),
    'component_name' => (string) ($_GET['component_name'] ?? $resolveProfileAwareValue('component_name')),
    'teacher_name' => (string) ($_GET['teacher_name'] ?? $resolveProfileAwareValue('teacher_name', (string) ($user['name'] ?? ''))),
    'school_name' => (string) ($_GET['school_name'] ?? $resolveProfileAwareValue('school_name', EXAM_DEFAULT_SCHOOL_NAME)),
    'school_subtitle' => (string) ($_GET['school_subtitle'] ?? $resolveProfileAwareValue('school_subtitle', EXAM_DEFAULT_SCHOOL_SUBTITLE)),
    'year_reference' => (string) ($_GET['year_reference'] ?? $resolveProfileAwareValue('year_reference')),
    'class_name' => (string) ($_GET['class_name'] ?? $resolveProfileAwareValue('class_name')),
    'application_date' => (string) ($_GET['application_date'] ?? $parsedExamData['metadata']['application_date']),
    'header_logo_left' => (string) ($_GET['header_logo_left'] ?? $resolveProfileAwareValue('header_logo_left', EXAM_DEFAULT_LOGO_URL)),
    'header_logo_right' => (string) ($_GET['header_logo_right'] ?? $resolveProfileAwareValue('header_logo_right')),
    'header_background_color' => (string) ($_GET['header_background_color'] ?? ($parsedExamData['metadata']['header_background_color'] !== '' ? $parsedExamData['metadata']['header_background_color'] : '#ffffff')),
    'header_title_color' => (string) ($_GET['header_title_color'] ?? ($parsedExamData['metadata']['header_title_color'] !== '' ? $parsedExamData['metadata']['header_title_color'] : '#334155')),
    'header_subtitle_color' => (string) ($_GET['header_subtitle_color'] ?? ($parsedExamData['metadata']['header_subtitle_color'] !== '' ? $parsedExamData['metadata']['header_subtitle_color'] : '#64748b')),
    'header_title_size' => (string) ($_GET['header_title_size'] ?? ($parsedExamData['metadata']['header_title_size'] !== '' ? $parsedExamData['metadata']['header_title_size'] : '20')),
    'header_subtitle_size' => (string) ($_GET['header_subtitle_size'] ?? ($parsedExamData['metadata']['header_subtitle_size'] !== '' ? $parsedExamData['metadata']['header_subtitle_size'] : '16')),
    'header_logo_size' => (string) ($_GET['header_logo_size'] ?? ($parsedExamData['metadata']['header_logo_size'] !== '' ? $parsedExamData['metadata']['header_logo_size'] : '80')),
    'header_min_height' => (string) ($_GET['header_min_height'] ?? ($parsedExamData['metadata']['header_min_height'] !== '' ? $parsedExamData['metadata']['header_min_height'] : '120')),
    'content_font_size' => (string) ($_GET['content_font_size'] ?? ($parsedExamData['metadata']['content_font_size'] !== '' ? $parsedExamData['metadata']['content_font_size'] : '11')),
    'answer_preview_quantity' => (string) ($_GET['answer_preview_quantity'] ?? ($parsedExamData['metadata']['answer_preview_quantity'] ?? '10')),
    'answer_preview_orientation' => (string) ($_GET['answer_preview_orientation'] ?? ($parsedExamData['metadata']['answer_preview_orientation'] ?? 'vertical')),
    'answer_preview_width_mode' => (string) ($_GET['answer_preview_width_mode'] ?? ($parsedExamData['metadata']['answer_preview_width_mode'] ?? 'custom')),
    'answer_preview_size_cm' => (string) ($_GET['answer_preview_size_cm'] ?? ($parsedExamData['metadata']['answer_preview_size_cm'] ?? '18')),
    'answer_preview_height_cm' => (string) ($_GET['answer_preview_height_cm'] ?? ($parsedExamData['metadata']['answer_preview_height_cm'] ?? '7')),
    'answer_preview_font_size' => (string) ($_GET['answer_preview_font_size'] ?? ($parsedExamData['metadata']['answer_preview_font_size'] ?? '13')),
    'answer_preview_card_color' => (string) ($_GET['answer_preview_card_color'] ?? ($parsedExamData['metadata']['answer_preview_card_color'] ?? '#f7f3ff')),
    'answer_preview_surface_color' => (string) ($_GET['answer_preview_surface_color'] ?? ($parsedExamData['metadata']['answer_preview_surface_color'] ?? '#ffffff')),
    'answer_preview_accent_color' => (string) ($_GET['answer_preview_accent_color'] ?? ($parsedExamData['metadata']['answer_preview_accent_color'] ?? '#5b34d6')),
    'answer_preview_line_color' => (string) ($_GET['answer_preview_line_color'] ?? ($parsedExamData['metadata']['answer_preview_line_color'] ?? '#cbd5e1')),
    'header_content' => $draftSections['header'],
    'body_content' => $draftSections['body'],
    'footer_content' => $draftSections['footer'],
];
$formData['exam_template'] = 'version_1';
$formData['exam_style'] = 'double_column';
$formData['composition_mode'] = 'mixed';
$formData['ordering_mode'] = 'automatic_numbering';
$formData['identification_mode'] = 'standard';
$formData['variant_label'] = '';
$steps = exam_create_steps();
$currentStep = (string) ($_GET['step'] ?? 'cabecalho');
$currentStep = array_key_exists($currentStep, $steps) ? $currentStep : 'cabecalho';
$stepKeys = array_keys($steps);
$stepIndex = array_search($currentStep, $stepKeys, true);
$previousStep = $stepIndex !== false && $stepIndex > 0 ? $stepKeys[$stepIndex - 1] : null;
$nextStep = $stepIndex !== false && $stepIndex < count($stepKeys) - 1 ? $stepKeys[$stepIndex + 1] : null;
$visibleFields = exam_create_visible_fields($currentStep);
$backHref = $editingExam ? 'exam-preview.php?id=' . $editExamId : 'exam-library.php';
$selectedIds = array_values(array_unique(array_map('intval', array_merge($loadedExamQuestionIds, (array) ($_GET['question_ids'] ?? [])))));
$selectedPreview = [];
$selectedCount = count($selectedIds);

if ($selectedIds !== []) {
    [$selectedQuestions] = question_list(['term' => '', 'discipline_id' => 0, 'subject_id' => 0, 'education_level' => '', 'question_type' => '', 'author_id' => 0, 'visibility' => ''], $userId);
    $selectedPreview = array_values(array_filter($selectedQuestions, static fn(array $question): bool => in_array((int) $question['id'], $selectedIds, true)));
}

$previewQuestions = exam_create_preview_questions($selectedPreview);
$previewStyleClass = exam_create_style_preview_class($formData['exam_style']);
$previewCode = exam_create_exam_code($formData);
$schoolAcronym = exam_create_school_acronym($formData['school_name']);
$applicationDateLabel = $formData['application_date'] !== '' ? exam_format_date($formData['application_date']) : '';
$applicationDateMeta = $applicationDateLabel !== '' ? 'Aplicação em ' . $applicationDateLabel : 'Data da aplicação em definição';
$formData['header_logo_size'] = exam_create_normalize_cm($formData['header_logo_size'], 2.2, true);
$formData['header_min_height'] = exam_create_normalize_cm($formData['header_min_height'], 3.2, true);
$formData['answer_preview_width_mode'] = $formData['answer_preview_width_mode'] === 'full' ? 'full' : 'custom';
$formData['answer_preview_size_cm'] = exam_create_normalize_cm($formData['answer_preview_size_cm'], 18.0);
$formData['answer_preview_height_cm'] = exam_create_normalize_cm($formData['answer_preview_height_cm'], 7.0);
$formData['answer_preview_font_size'] = exam_create_normalize_int($formData['answer_preview_font_size'], 13, 10, 24);
$formData['answer_preview_card_color'] = exam_create_normalize_color($formData['answer_preview_card_color'], '#f7f3ff');
$formData['answer_preview_surface_color'] = exam_create_normalize_color($formData['answer_preview_surface_color'], '#ffffff');
$formData['answer_preview_accent_color'] = exam_create_normalize_color($formData['answer_preview_accent_color'], '#5b34d6');
$formData['answer_preview_line_color'] = exam_create_normalize_color($formData['answer_preview_line_color'], '#cbd5e1');
$headerLogoSizePx = exam_create_cm_to_px($formData['header_logo_size'], 2.2);
$headerMinHeightPx = exam_create_cm_to_px($formData['header_min_height'], 3.2);
$previewQuantityDefault = 10;
$previewQuantity = max(1, (int) ($formData['answer_preview_quantity'] !== '' ? $formData['answer_preview_quantity'] : (string) $previewQuantityDefault));
$formData['answer_preview_quantity'] = (string) $previewQuantity;
$formData['answer_preview_orientation'] = in_array($formData['answer_preview_orientation'], ['horizontal', 'vertical'], true) ? $formData['answer_preview_orientation'] : 'vertical';
$answerPreviewNumbers = range(1, $previewQuantity);
$answerPreviewTotalLabel = $previewQuantity . ' questão(ões) na prévia';
$answerPreviewScrollable = $previewQuantity > 12;
$answerPreviewOrientationClass = $formData['answer_preview_orientation'] === 'horizontal' ? 'is-horizontal' : 'is-vertical';
$examPreviewWidth = $formData['answer_preview_width_mode'] === 'full' ? '100%' : $formData['answer_preview_size_cm'] . 'cm';
$examPreviewUiStyle = '--answer-preview-width:' . $examPreviewWidth . ';'
    . '--answer-preview-height:' . $formData['answer_preview_height_cm'] . 'cm;'
    . '--answer-preview-font-size:' . $formData['answer_preview_font_size'] . 'px;'
    . '--answer-preview-card-bg:' . $formData['answer_preview_card_color'] . ';'
    . '--answer-preview-surface:' . $formData['answer_preview_surface_color'] . ';'
    . '--answer-preview-accent:' . $formData['answer_preview_accent_color'] . ';'
    . '--answer-preview-line:' . $formData['answer_preview_line_color'] . ';';
$responseModeLabelsJson = htmlspecialchars((string) json_encode(exam_response_mode_options(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$styleModeLabelsJson = htmlspecialchars((string) json_encode([
    'double_column' => 'Questões em card em duas colunas',
    'triple_column' => 'Questões em card em três colunas',
    'single_column' => 'Questões em card em uma coluna',
    'economic' => 'Questões em card compactas',
    'accessibility' => 'Questões em card com fonte ampliada',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ENT_QUOTES, 'UTF-8');
$answerPatternCards = [
    [
        'mode' => 'lateral_answer_key',
        'eyebrow' => 'Na própria questão',
        'title' => 'Gabarito lateral',
        'description' => 'O número da questão aparece ao lado das alternativas em linha horizontal.',
    ],
    [
        'mode' => 'bubble_answer_sheet',
        'eyebrow' => 'Leitura por marcação',
        'title' => 'Folha com bolinhas',
        'description' => 'Cada questão ganha uma linha com número fixo e bolhas para marcar A, B, C, D ou E.',
    ],
    [
        'mode' => 'separate_answer_sheet',
        'eyebrow' => 'Resposta separada',
        'title' => 'Folha de resposta',
        'description' => 'O aluno responde em uma folha própria, com número da questão e campo dedicado.',
    ],
    [
        'mode' => 'discursive_space',
        'eyebrow' => 'Escrita na prova',
        'title' => 'Espaço discursivo',
        'description' => 'A questão já abre com linhas de escrita logo abaixo do enunciado.',
    ],
];

render_header('Nova prova', 'Monte a prova por etapas, com prévias do cabeçalho, do corpo, dos avisos e do rodapé antes de seguir para as questões.');
?>
<form method="get" action="exam-create.php" class="exam-create-wizard" data-exam-meta-form data-response-mode-labels="<?= $responseModeLabelsJson ?>" data-style-labels="<?= $styleModeLabelsJson ?>" data-selected-question-count="<?= h((string) $selectedCount) ?>" style="<?= h($examPreviewUiStyle) ?>">
    <?php if ($editExamId > 0): ?>
        <input type="hidden" name="edit" value="<?= h((string) $editExamId) ?>">
        <input type="hidden" name="exam_id" value="<?= h((string) $editExamId) ?>">
    <?php endif; ?>
    <?php foreach ($selectedIds as $questionId): ?>
        <input type="hidden" name="question_ids[]" value="<?= h((string) $questionId) ?>">
    <?php endforeach; ?>
    <?= exam_create_hidden_fields($formData, $visibleFields) ?>

    <section class="exam-create-hero-card">
        <div>
            <span class="exam-create-kicker">Novo construtor</span>
            <h2><?= h($editingExam ? 'Editar montagem da prova' : 'Construir prova por partes') ?></h2>
            <p class="helper-text">Agora o fluxo começa pelo cabeçalho, passa pelo corpo e termina na visualização completa do modelo.</p>
        </div>
        <div class="exam-create-hero-actions">
            <span class="badge"><?= h((string) $selectedCount) ?> questão(ões) pré-selecionada(s)</span>
            <a class="ghost-button" href="profile.php">Editar dados no meu painel</a>
            <a class="ghost-button" href="<?= h($backHref) ?>">Voltar</a>
        </div>
    </section>

    <nav class="exam-create-stepbar" aria-label="Etapas da montagem">
        <?php foreach ($steps as $stepKey => $stepLabel): ?>
            <a class="exam-create-stepchip <?= $stepKey === $currentStep ? 'is-active' : '' ?>" href="<?= h(exam_create_step_query($formData, $selectedIds, $editExamId, $stepKey)) ?>">
                <span><?= h((string) (array_search($stepKey, $stepKeys, true) + 1)) ?></span>
                <strong><?= h($stepLabel) ?></strong>
            </a>
        <?php endforeach; ?>
    </nav>

    <section class="exam-create-stage-grid">
        <article class="simple-card exam-create-stage-main">
            <?php if ($currentStep === 'cabecalho'): ?>
                <div class="simple-card-head">
                    <div>
                        <h2>Cabeçalho da prova</h2>
                        <p class="helper-text">A base do cabeçalho vem do seu painel. Aqui você só ajusta o visual e confere como a prova vai abrir.</p>
                    </div>
                </div>

                <input type="hidden" name="response_mode" value="<?= h($formData['response_mode']) ?>" data-response-mode-input>

                <section class="exam-builder-sheet-preview" data-header-preview>
                    <div class="exam-builder-sheet-preview-topbar">
                        <div class="exam-builder-sheet-preview-topcopy">
                            <strong>Cabeçalho real da prova</strong>
                            <p>Os dados abaixo já vêm do seu painel. Se precisar, você pode editar só para esta prova sem alterar seus dados padrão.</p>
                        </div>
                        <div class="simple-action-row">
                            <button class="ghost-button" type="button" data-open-header-editor>Editar dados desta prova</button>
                            <a class="ghost-button" href="profile.php">Ajustar dados padrão</a>
                        </div>
                    </div>

                    <div class="exam-builder-sheet-header" data-header-preview-shell style="--header-preview-logo-size: <?= h((string) $headerLogoSizePx) ?>px; min-height: <?= h((string) $headerMinHeightPx) ?>px;">
                        <div class="exam-builder-sheet-logo" data-header-preview-logo-left>
                            <img src="<?= h($formData['header_logo_left']) ?>" alt="Logo da escola">
                        </div>
                        <div class="exam-builder-sheet-copy">
                            <strong data-header-preview-title><?= h($formData['school_name']) ?></strong>
                            <span data-header-preview-subtitle><?= h($formData['school_subtitle']) ?></span>
                        </div>
                        <div class="exam-builder-sheet-code">
                            <small>Código da prova</small>
                            <strong data-header-preview-code><?= h($previewCode) ?></strong>
                        </div>
                    </div>
                    <div class="exam-builder-sheet-identity">
                        <small>Nome da prova</small>
                        <strong data-header-preview-draft-title><?= h($formData['draft_title'] !== '' ? $formData['draft_title'] : 'Prova sem título') ?></strong>
                        <span data-header-preview-date-meta><?= h($applicationDateMeta) ?></span>
                    </div>
                    <div class="exam-builder-sheet-header-grid">
                        <span><small>Avaliação</small><strong data-header-preview-exam-label><?= h($formData['exam_label']) ?></strong></span>
                        <span><small>Professor</small><strong data-header-preview-teacher><?= h($formData['teacher_name']) ?></strong></span>
                        <span><small>Comp. Curricular</small><strong data-header-preview-component><?= h($formData['component_name'] !== '' ? $formData['component_name'] : $formData['discipline']) ?></strong></span>
                        <span><small>Turma</small><strong data-header-preview-class><?= h($formData['class_name'] !== '' ? $formData['class_name'] : 'Não informada') ?></strong></span>
                    </div>
                    <div class="exam-builder-answer-preview-card">
                        <div class="exam-builder-answer-preview-head">
                            <div class="exam-builder-answer-preview-titleblock">
                                <strong>Identificação do aluno e gabarito</strong>
                                <small>Se precisar, ajuste a data e os dados acima.</small>
                            </div>
                            <div class="exam-builder-answer-preview-actions">
                                <div class="exam-builder-answer-preview-state">
                                    <span data-answer-preview-label>Escolhido: <?= h(exam_response_mode_label($formData['response_mode'])) ?></span>
                                    <small data-answer-preview-count-label><?= h($answerPreviewTotalLabel) ?></small>
                                </div>
                                <button class="ghost-button" type="button" data-open-header-editor>Editar</button>
                            </div>
                        </div>

                        <div class="exam-builder-answer-preview-fields">
                            <div class="exam-builder-answer-preview-field is-wide">
                                <small>Aluno(a)</small>
                                <strong>Nome do aluno</strong>
                            </div>
                            <div class="exam-builder-answer-preview-field">
                                <small>Nº</small>
                                <strong>Número</strong>
                            </div>
                            <div class="exam-builder-answer-preview-field">
                                <small>Turma</small>
                                <strong data-answer-preview-class><?= h($formData['class_name'] !== '' ? $formData['class_name'] : 'Não informada') ?></strong>
                            </div>
                            <div class="exam-builder-answer-preview-field">
                                <small>Data</small>
                                <strong data-answer-preview-date><?= h($applicationDateLabel !== '' ? $applicationDateLabel : 'Definir data') ?></strong>
                            </div>
                            <div class="exam-builder-answer-preview-field is-wide">
                                <small>Assinatura</small>
                                <strong class="is-placeholder">Assinatura do responsável</strong>
                            </div>
                            <div class="exam-builder-answer-preview-field">
                                <small>Valor</small>
                                <strong class="is-placeholder">Nota / valor</strong>
                            </div>
                        </div>

                        <div class="exam-builder-answer-preview-mode">
                            <div class="exam-builder-answer-preview-mode-head">
                                <strong>Prévia do gabarito escolhido</strong>
                                <div class="exam-builder-answer-preview-mode-meta">
                                    <span data-answer-preview-mode-name><?= h(exam_response_mode_label($formData['response_mode'])) ?></span>
                                    <small data-answer-preview-count-label><?= h($answerPreviewTotalLabel) ?></small>
                                </div>
                            </div>
                            <div class="exam-builder-answer-preview-list <?= $answerPreviewScrollable ? 'is-scrollable' : '' ?> <?= h($answerPreviewOrientationClass) ?>" data-answer-preview-mode="lateral_answer_key" <?= $formData['response_mode'] === 'lateral_answer_key' ? '' : 'hidden' ?>>
                                <?php foreach ($answerPreviewNumbers as $questionNumber): ?>
                                    <div class="exam-builder-answer-pattern-row exam-builder-answer-pattern-row--inline">
                                        <span class="exam-builder-answer-number"><?= h(str_pad((string) $questionNumber, 2, '0', STR_PAD_LEFT)) ?></span>
                                        <span class="exam-builder-answer-chip">A</span>
                                        <span class="exam-builder-answer-chip">B</span>
                                        <span class="exam-builder-answer-chip">C</span>
                                        <span class="exam-builder-answer-chip">D</span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="exam-builder-answer-preview-list <?= $answerPreviewScrollable ? 'is-scrollable' : '' ?> <?= h($answerPreviewOrientationClass) ?>" data-answer-preview-mode="bubble_answer_sheet" <?= $formData['response_mode'] === 'bubble_answer_sheet' ? '' : 'hidden' ?>>
                                <?php foreach ($answerPreviewNumbers as $questionNumber): ?>
                                    <div class="exam-builder-answer-pattern-row">
                                        <span class="exam-builder-answer-number"><?= h(str_pad((string) $questionNumber, 2, '0', STR_PAD_LEFT)) ?></span>
                                        <span class="exam-builder-answer-bubble-option"><small>A</small><span class="exam-builder-answer-bubble"></span></span>
                                        <span class="exam-builder-answer-bubble-option"><small>B</small><span class="exam-builder-answer-bubble"></span></span>
                                        <span class="exam-builder-answer-bubble-option"><small>C</small><span class="exam-builder-answer-bubble"></span></span>
                                        <span class="exam-builder-answer-bubble-option"><small>D</small><span class="exam-builder-answer-bubble"></span></span>
                                        <span class="exam-builder-answer-bubble-option"><small>E</small><span class="exam-builder-answer-bubble"></span></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="exam-builder-answer-preview-list <?= $answerPreviewScrollable ? 'is-scrollable' : '' ?> <?= h($answerPreviewOrientationClass) ?>" data-answer-preview-mode="separate_answer_sheet" <?= $formData['response_mode'] === 'separate_answer_sheet' ? '' : 'hidden' ?>>
                                <?php foreach ($answerPreviewNumbers as $questionNumber): ?>
                                    <div class="exam-builder-answer-pattern-row exam-builder-answer-pattern-row--sheet">
                                        <span class="exam-builder-answer-number"><?= h(str_pad((string) $questionNumber, 2, '0', STR_PAD_LEFT)) ?></span>
                                        <span class="exam-builder-answer-line"></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="exam-builder-answer-preview-list <?= $answerPreviewScrollable ? 'is-scrollable' : '' ?> <?= h($answerPreviewOrientationClass) ?>" data-answer-preview-mode="discursive_space" <?= $formData['response_mode'] === 'discursive_space' ? '' : 'hidden' ?>>
                                <?php foreach ($answerPreviewNumbers as $questionNumber): ?>
                                    <div class="exam-builder-answer-pattern-row exam-builder-answer-pattern-row--sheet">
                                        <span class="exam-builder-answer-number"><?= h(str_pad((string) $questionNumber, 2, '0', STR_PAD_LEFT)) ?></span>
                                        <span class="exam-builder-answer-line"></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <details class="simple-disclosure exam-create-disclosure exam-create-inline-editor" id="identificacao-prova">
                        <summary><span>Editar dados desta prova</span><small>Essas alterações valem só para esta prova e atualizam o cabeçalho acima.</small></summary>
                        <div class="simple-disclosure-body">
                            <p class="helper-text exam-create-inline-editor-copy">Os campos abaixo já começam com os dados puxados do seu painel. Você pode ajustar o que precisar aqui sem alterar seus dados padrão.</p>
                            <div class="exam-create-form-grid">
                                <label>Nome interno da prova
                                    <input type="text" name="draft_title" value="<?= h($formData['draft_title']) ?>" placeholder="Ex.: Prova A - 1º trimestre">
                                </label>
                                <label>Data da aplicação
                                    <input type="date" name="application_date" value="<?= h($formData['application_date']) ?>">
                                </label>
                                <label>Tipo da prova
                                    <input type="text" name="exam_label" value="<?= h($formData['exam_label']) ?>" placeholder="Ex.: Avaliação trimestral">
                                </label>
                                <label>Professor
                                    <input type="text" name="teacher_name" value="<?= h($formData['teacher_name']) ?>" placeholder="Nome do professor">
                                </label>
                                <label>Comp. curricular
                                    <input type="text" name="component_name" value="<?= h($formData['component_name']) ?>" placeholder="Ex.: Matemática">
                                </label>
                                <label>Turma
                                    <input type="text" name="class_name" value="<?= h($formData['class_name']) ?>" placeholder="Ex.: 7A">
                                </label>
                                <label>Escola
                                    <input type="text" name="school_name" value="<?= h($formData['school_name']) ?>" placeholder="Nome da escola">
                                </label>
                                <label>Subtítulo da escola
                                    <input type="text" name="school_subtitle" value="<?= h($formData['school_subtitle']) ?>" placeholder="Ex.: Ensino Fundamental e Médio">
                                </label>
                            </div>
                        </div>
                    </details>
                </section>

                <section class="exam-create-panel-grid">
                    <article class="simple-note">
                        <strong>Ajustes rápidos</strong>
                        <p class="helper-text">As medidas deste bloco ficam em centímetros para você pensar no tamanho impresso do cabeçalho e do gabarito.</p>
                        <div class="exam-create-form-grid">
                            <label>Logo principal
                                <input type="text" name="header_logo_left" value="<?= h($formData['header_logo_left']) ?>" placeholder="URL da logo principal">
                            </label>
                            <label>Logo opcional
                                <input type="text" name="header_logo_right" value="<?= h($formData['header_logo_right']) ?>" placeholder="URL da logo da direita">
                            </label>
                            <label>Tamanho da logo (cm)
                                <input type="number" name="header_logo_size" min="1" max="6" step="0.1" value="<?= h($formData['header_logo_size']) ?>">
                            </label>
                            <label>Altura do cabeçalho (cm)
                                <input type="number" name="header_min_height" min="2" max="10" step="0.1" value="<?= h($formData['header_min_height']) ?>">
                            </label>
                            <label>Questões na prévia do gabarito
                                <input type="number" name="answer_preview_quantity" min="1" max="200" step="1" value="<?= h($formData['answer_preview_quantity']) ?>" data-answer-preview-quantity-input>
                            </label>
                            <label>Orientação do gabarito
                                <select name="answer_preview_orientation" data-answer-preview-orientation-input>
                                    <option value="vertical" <?= $formData['answer_preview_orientation'] === 'vertical' ? 'selected' : '' ?>>Vertical em pé</option>
                                    <option value="horizontal" <?= $formData['answer_preview_orientation'] === 'horizontal' ? 'selected' : '' ?>>Horizontal deitado</option>
                                </select>
                            </label>
                            <label>Modo da largura
                                <select name="answer_preview_width_mode">
                                    <option value="custom" <?= $formData['answer_preview_width_mode'] === 'custom' ? 'selected' : '' ?>>Medida em cm</option>
                                    <option value="full" <?= $formData['answer_preview_width_mode'] === 'full' ? 'selected' : '' ?>>Largura da prova</option>
                                </select>
                            </label>
                            <label>Largura do gabarito (cm)
                                <input type="number" name="answer_preview_size_cm" min="6" max="24" step="0.5" value="<?= h($formData['answer_preview_size_cm']) ?>">
                            </label>
                            <label>Altura do gabarito (cm)
                                <input type="number" name="answer_preview_height_cm" min="3" max="20" step="0.5" value="<?= h($formData['answer_preview_height_cm']) ?>">
                            </label>
                            <label>Tamanho da letra do gabarito
                                <input type="number" name="answer_preview_font_size" min="10" max="24" step="1" value="<?= h($formData['answer_preview_font_size']) ?>">
                            </label>
                        </div>
                    </article>

                    <article class="simple-note">
                        <strong>Cores do cabeçalho e do gabarito</strong>
                        <p class="helper-text">Você pode deixar o gabarito mais evidente mexendo no fundo, no miolo, no destaque e nas linhas.</p>
                        <div class="exam-create-form-grid">
                            <label>Cor de fundo do cabeçalho
                                <input type="color" name="header_background_color" value="<?= h($formData['header_background_color']) ?>">
                            </label>
                            <label>Cor do título
                                <input type="color" name="header_title_color" value="<?= h($formData['header_title_color']) ?>">
                            </label>
                            <label>Cor do subtítulo
                                <input type="color" name="header_subtitle_color" value="<?= h($formData['header_subtitle_color']) ?>">
                            </label>
                            <label>Tamanho do título
                                <input type="number" name="header_title_size" min="16" max="32" step="1" value="<?= h($formData['header_title_size']) ?>">
                            </label>
                            <label>Tamanho do subtítulo
                                <input type="number" name="header_subtitle_size" min="12" max="24" step="1" value="<?= h($formData['header_subtitle_size']) ?>">
                            </label>
                            <label>Fundo do gabarito
                                <input type="color" name="answer_preview_card_color" value="<?= h($formData['answer_preview_card_color']) ?>">
                            </label>
                            <label>Miolo do gabarito
                                <input type="color" name="answer_preview_surface_color" value="<?= h($formData['answer_preview_surface_color']) ?>">
                            </label>
                            <label>Cor de destaque
                                <input type="color" name="answer_preview_accent_color" value="<?= h($formData['answer_preview_accent_color']) ?>">
                            </label>
                            <label>Cor das linhas
                                <input type="color" name="answer_preview_line_color" value="<?= h($formData['answer_preview_line_color']) ?>">
                            </label>
                        </div>
                    </article>
                </section>

                <section class="simple-note exam-builder-answer-patterns">
                    <div class="exam-builder-preview-headline exam-builder-preview-headline--detail">
                        <div>
                            <strong>Escolha o gabarito da prova</strong>
                            <p>Clique em um modelo abaixo para trocar a prévia principal e ver onde aparece o número da questão ou o espaço de resposta.</p>
                        </div>
                        <span class="badge" data-answer-pattern-badge>Escolhido: <?= h(exam_response_mode_label($formData['response_mode'])) ?></span>
                    </div>

                    <div class="exam-builder-answer-pattern-grid">
                        <?php foreach ($answerPatternCards as $patternCard): ?>
                            <div class="exam-builder-answer-pattern-card <?= $formData['response_mode'] === $patternCard['mode'] ? 'is-active' : '' ?>" data-answer-pattern-card="<?= h($patternCard['mode']) ?>" data-response-mode-option="<?= h($patternCard['mode']) ?>" aria-pressed="<?= $formData['response_mode'] === $patternCard['mode'] ? 'true' : 'false' ?>" role="button" tabindex="0">
                                <div class="exam-builder-answer-pattern-head">
                                    <small><?= h($patternCard['eyebrow']) ?></small>
                                    <strong><?= h($patternCard['title']) ?></strong>
                                </div>
                                <p><?= h($patternCard['description']) ?></p>
                                <span class="exam-builder-answer-pattern-count" data-answer-pattern-count><?= h($answerPreviewTotalLabel) ?></span>

                                <?php if ($patternCard['mode'] === 'lateral_answer_key'): ?>
                                    <div class="exam-builder-answer-pattern-demo exam-builder-answer-pattern-demo--stack">
                                        <div class="exam-builder-answer-pattern-row exam-builder-answer-pattern-row--inline">
                                            <span class="exam-builder-answer-number">01</span>
                                            <span class="exam-builder-answer-chip">A</span>
                                            <span class="exam-builder-answer-chip">B</span>
                                            <span class="exam-builder-answer-chip">C</span>
                                            <span class="exam-builder-answer-chip">D</span>
                                        </div>
                                        <div class="exam-builder-answer-pattern-row exam-builder-answer-pattern-row--inline">
                                            <span class="exam-builder-answer-number">02</span>
                                            <span class="exam-builder-answer-chip">A</span>
                                            <span class="exam-builder-answer-chip">B</span>
                                            <span class="exam-builder-answer-chip">C</span>
                                            <span class="exam-builder-answer-chip">D</span>
                                        </div>
                                    </div>
                                <?php elseif ($patternCard['mode'] === 'bubble_answer_sheet'): ?>
                                    <div class="exam-builder-answer-pattern-demo exam-builder-answer-pattern-demo--stack">
                                        <div class="exam-builder-answer-pattern-row">
                                            <span class="exam-builder-answer-number">01</span>
                                            <span class="exam-builder-answer-bubble-option"><small>A</small><span class="exam-builder-answer-bubble"></span></span>
                                            <span class="exam-builder-answer-bubble-option"><small>B</small><span class="exam-builder-answer-bubble"></span></span>
                                            <span class="exam-builder-answer-bubble-option"><small>C</small><span class="exam-builder-answer-bubble"></span></span>
                                            <span class="exam-builder-answer-bubble-option"><small>D</small><span class="exam-builder-answer-bubble"></span></span>
                                            <span class="exam-builder-answer-bubble-option"><small>E</small><span class="exam-builder-answer-bubble"></span></span>
                                        </div>
                                        <div class="exam-builder-answer-pattern-row">
                                            <span class="exam-builder-answer-number">02</span>
                                            <span class="exam-builder-answer-bubble-option"><small>A</small><span class="exam-builder-answer-bubble"></span></span>
                                            <span class="exam-builder-answer-bubble-option"><small>B</small><span class="exam-builder-answer-bubble"></span></span>
                                            <span class="exam-builder-answer-bubble-option"><small>C</small><span class="exam-builder-answer-bubble"></span></span>
                                            <span class="exam-builder-answer-bubble-option"><small>D</small><span class="exam-builder-answer-bubble"></span></span>
                                            <span class="exam-builder-answer-bubble-option"><small>E</small><span class="exam-builder-answer-bubble"></span></span>
                                        </div>
                                    </div>
                                <?php elseif ($patternCard['mode'] === 'separate_answer_sheet'): ?>
                                    <div class="exam-builder-answer-pattern-demo exam-builder-answer-pattern-demo--stack">
                                        <div class="exam-builder-answer-pattern-row exam-builder-answer-pattern-row--sheet">
                                            <span class="exam-builder-answer-number">01</span>
                                            <span class="exam-builder-answer-line"></span>
                                        </div>
                                        <div class="exam-builder-answer-pattern-row exam-builder-answer-pattern-row--sheet">
                                            <span class="exam-builder-answer-number">02</span>
                                            <span class="exam-builder-answer-line"></span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="exam-builder-answer-pattern-demo exam-builder-answer-pattern-demo--discursive">
                                        <span class="exam-builder-answer-number">01</span>
                                        <span class="exam-builder-answer-line"></span>
                                        <span class="exam-builder-answer-line"></span>
                                        <span class="exam-builder-answer-line"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php elseif ($currentStep === 'corpo'): ?>
                <div class="simple-card-head">
                    <div>
                        <h2>Corpo da prova e formato</h2>
                        <p class="helper-text">O corpo agora segue o modelo padrão da plataforma. Aqui você só confere a estrutura fixa e ajusta o tamanho da letra.</p>
                    </div>
                </div>

                <div class="exam-create-panel-grid">
                    <article class="simple-note">
                        <strong>Formato da prova</strong>
                        <p class="helper-text">Sem escolhas extras nesta etapa: o sistema mantém o modelo inicial com cabeçalho, gabarito, questões em card e rodapé no mesmo padrão.</p>
                        <div class="exam-create-profile-list">
                            <div class="exam-create-profile-row"><small>Modelo visual</small><strong>Modelo padrão</strong></div>
                            <div class="exam-create-profile-row"><small>Estrutura do corpo</small><strong>Questões em card em duas colunas</strong></div>
                            <div class="exam-create-profile-row"><small>Composição</small><strong>Sequência padrão da prova</strong></div>
                            <div class="exam-create-profile-row"><small>Organização</small><strong>Numeração automática</strong></div>
                            <div class="exam-create-profile-row"><small>Identificação</small><strong>Padrão da plataforma</strong></div>
                            <div class="exam-create-profile-row"><small>Modo de resposta</small><strong><?= h(exam_response_mode_label($formData['response_mode'])) ?></strong></div>
                        </div>
                        <div class="exam-create-form-grid">
                            <label>Tamanho da letra
                                <input type="number" name="content_font_size" min="10" max="18" step="1" value="<?= h($formData['content_font_size']) ?>">
                            </label>
                        </div>
                    </article>

                    <article class="simple-note">
                        <strong>Leitura do padrão</strong>
                        <div class="exam-create-profile-list">
                            <div class="exam-create-profile-row"><small>Modelo visual</small><strong>Modelo padrão</strong></div>
                            <div class="exam-create-profile-row"><small>Corpo</small><strong data-model-preview-style-label>Questões em card em duas colunas</strong></div>
                            <div class="exam-create-profile-row"><small>Resposta</small><strong data-model-preview-response-label><?= h(exam_response_mode_label($formData['response_mode'])) ?></strong></div>
                            <div class="exam-create-profile-row"><small>Fluxo</small><strong>Segue o padrão inicial da página</strong></div>
                        </div>
                    </article>
                </div>

                <section class="exam-builder-body-preview simple-note">
                    <div class="exam-builder-preview-headline">
                        <strong>Prévia do corpo</strong>
                        <span class="badge" data-model-preview-style-badge>Questões em card em duas colunas</span>
                    </div>
                    <div class="exam-builder-question-stream <?= h($previewStyleClass) ?>" data-body-preview-shell style="--exam-preview-font-size: <?= h((string) ((int) $formData['content_font_size'])) ?>px;">
                        <?php foreach ($previewQuestions as $index => $question): ?>
                            <article class="exam-builder-question-card">
                                <strong><?= h((string) ($index + 1)) ?>. <?= h($question['title']) ?></strong>
                                <p><?= h($question['prompt']) ?></p>
                                <?php if ($question['type'] === 'discursive' || $formData['response_mode'] === 'discursive_space'): ?>
                                    <div class="exam-builder-answer-lines"><span></span><span></span><span></span></div>
                                <?php else: ?>
                                    <div class="exam-builder-option-row"><span>A)</span><span>B)</span><span>C)</span><span>D)</span></div>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="exam-builder-response-preview">
                        <?php if ($formData['response_mode'] === 'lateral_answer_key'): ?>
                            <strong>Gabarito lateral</strong>
                            <div class="exam-builder-bubble-strip"><span>01</span><span>02</span><span>03</span><span>04</span></div>
                        <?php elseif ($formData['response_mode'] === 'bubble_answer_sheet'): ?>
                            <strong>Leitura por bolinhas</strong>
                            <div class="exam-builder-bubble-strip"><span>A</span><span>B</span><span>C</span><span>D</span><span>E</span></div>
                        <?php elseif ($formData['response_mode'] === 'discursive_space'): ?>
                            <strong>Resposta no corpo da prova</strong>
                            <p>As linhas de resposta aparecem logo abaixo das questões discursivas.</p>
                        <?php else: ?>
                            <strong>Folha de resposta separada</strong>
                            <p>O gabarito do aluno sai em página separada.</p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php elseif ($currentStep === 'avisos'): ?>
                <div class="simple-card-head">
                    <div>
                        <h2>Comentários e rodapé</h2>
                        <p class="helper-text">Defina os comentários que aparecem na prova e confira o rodapé final de forma separada.</p>
                    </div>
                </div>

                <div class="exam-create-panel-grid">
                    <article class="simple-note">
                        <strong>Comentários da prova</strong>
                        <div class="exam-create-text-grid">
                            <label>Comentário no topo
                                <textarea name="draft_header_content" placeholder="Ex.: Use caneta azul ou preta e confira o cabeçalho antes de começar."><?= h($formData['header_content']) ?></textarea>
                            </label>
                            <label>Comentário antes das questões
                                <textarea name="draft_body_content" placeholder="Ex.: Organize o tempo e responda primeiro as questões que você considera mais simples."><?= h($formData['body_content']) ?></textarea>
                            </label>
                            <label>Texto do rodapé
                                <textarea name="draft_footer_content" placeholder="Ex.: Confira nome, turma e se todas as questões foram respondidas."><?= h($formData['footer_content']) ?></textarea>
                            </label>
                        </div>
                    </article>

                    <article class="simple-note">
                        <strong>Prévia dos comentários</strong>
                        <div class="exam-builder-notice-preview">
                            <div class="exam-builder-notice-card">
                                <strong>Comentário no topo</strong>
                                <p data-alert-preview-text><?= h($formData['header_content'] !== '' ? $formData['header_content'] : 'Use caneta azul ou preta e confira o cabeçalho antes de começar.') ?></p>
                            </div>
                            <div class="exam-builder-notice-card">
                                <strong>Comentário antes das questões</strong>
                                <p data-body-preview-intro><?= h($formData['body_content'] !== '' ? $formData['body_content'] : 'Organize o tempo e resolva a prova com calma.') ?></p>
                            </div>
                        </div>
                        <div class="exam-builder-footer-preview">
                            <strong>Prévia do rodapé</strong>
                            <p data-footer-preview-text><?= h($formData['footer_content'] !== '' ? $formData['footer_content'] : 'Confira nome, turma e se todas as questões foram respondidas.') ?></p>
                            <div class="exam-builder-footer-meta-line">
                                <small><?= h($previewCode) ?> | <?= h($schoolAcronym) ?> | <?= h($formData['draft_title'] !== '' ? $formData['draft_title'] : 'Prova sem título') ?> | Página 1 de 3</small>
                            </div>
                        </div>
                    </article>
                </div>
            <?php else: ?>
                <div class="simple-card-head">
                    <div>
                        <h2>Modelo visual completo</h2>
                        <p class="helper-text">Veja a prova simulada com cabeçalho, corpo, avisos e rodapé antes de abrir a seleção de questões.</p>
                    </div>
                    <div class="simple-action-row">
                        <a class="ghost-button" href="exam-models.php">Abrir modelos</a>
                    </div>
                </div>

                <section class="exam-builder-model-sheet" data-model-preview-shell style="--exam-preview-font-size: <?= h((string) ((int) $formData['content_font_size'])) ?>px;">
                    <header class="exam-builder-model-header" style="background: <?= h($formData['header_background_color']) ?>;">
                        <div class="exam-builder-model-header-top" style="--header-preview-logo-size: <?= h((string) $headerLogoSizePx) ?>px; min-height: <?= h((string) $headerMinHeightPx) ?>px;">
                            <div class="exam-builder-model-logo"><img src="<?= h($formData['header_logo_left']) ?>" alt="Logo principal"></div>
                            <div class="exam-builder-model-copy">
                                <strong style="color: <?= h($formData['header_title_color']) ?>; font-size: <?= h($formData['header_title_size']) ?>px;"><?= h($formData['school_name']) ?></strong>
                                <span style="color: <?= h($formData['header_subtitle_color']) ?>; font-size: <?= h($formData['header_subtitle_size']) ?>px;"><?= h($formData['school_subtitle']) ?></span>
                            </div>
                            <div class="exam-builder-model-code"><small>Código</small><strong><?= h($previewCode) ?></strong></div>
                        </div>
                        <div class="exam-builder-model-meta">
                            <span><?= h($formData['draft_title'] !== '' ? $formData['draft_title'] : 'Prova sem título') ?></span>
                            <span><?= h($formData['exam_label']) ?></span>
                            <span><?= h($formData['teacher_name']) ?></span>
                            <span><?= h($formData['component_name'] !== '' ? $formData['component_name'] : $formData['discipline']) ?></span>
                        </div>
                    </header>

                    <div class="exam-builder-answer-preview-card">
                        <div class="exam-builder-answer-preview-head">
                            <strong>Gabarito após o cabeçalho</strong>
                            <span><?= h(exam_response_mode_label($formData['response_mode'])) ?></span>
                        </div>
                        <div class="exam-builder-answer-preview-grid">
                            <span>Aluno(a)</span>
                            <span>Nº</span>
                            <span>Turma</span>
                            <span>Data</span>
                            <span>Assinatura</span>
                            <span>Valor</span>
                        </div>
                        <div class="exam-builder-bubble-strip"><span>01</span><span>02</span><span>03</span><span>04</span><span>05</span><span>06</span></div>
                    </div>

                    <?php if ($formData['header_content'] !== ''): ?><div class="exam-builder-model-alert" data-alert-preview-text><?= h($formData['header_content']) ?></div><?php endif; ?>
                    <?php if ($formData['body_content'] !== ''): ?><div class="exam-builder-model-intro" data-body-preview-intro><?= h($formData['body_content']) ?></div><?php endif; ?>

                    <div class="exam-builder-model-body <?= h($previewStyleClass) ?>">
                        <?php foreach ($previewQuestions as $index => $question): ?>
                            <article class="exam-builder-model-question">
                                <div class="exam-builder-model-question-head">
                                    <span class="exam-builder-model-question-index">Questão <?= h(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                                    <small class="exam-builder-model-question-type"><?= h(exam_create_question_type_label((string) $question['type'])) ?></small>
                                </div>
                                <div class="exam-builder-model-question-body">
                                    <strong><?= h($question['title']) ?></strong>
                                    <p><?= h($question['prompt']) ?></p>
                                </div>
                                <div class="exam-builder-model-question-response">
                                    <?php if ($question['type'] === 'discursive' || $formData['response_mode'] === 'discursive_space'): ?>
                                        <div class="exam-builder-answer-lines"><span></span><span></span><span></span></div>
                                    <?php else: ?>
                                        <div class="exam-builder-option-row"><span>A)</span><span>B)</span><span>C)</span><span>D)</span></div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <footer class="exam-builder-model-footer">
                        <p data-footer-preview-text><?= h($formData['footer_content'] !== '' ? $formData['footer_content'] : 'Confira nome, turma e se todas as questões foram respondidas.') ?></p>
                        <div class="exam-builder-footer-meta-line">
                            <small><?= h($previewCode) ?> | <?= h($schoolAcronym) ?> | <?= h($formData['draft_title'] !== '' ? $formData['draft_title'] : 'Prova sem título') ?> | Página 1 de 3</small>
                        </div>
                    </footer>
                </section>
            <?php endif; ?>

            <div class="exam-create-stage-actions">
                <div class="simple-action-row">
                    <?php if ($previousStep !== null): ?>
                        <button class="ghost-button" type="submit" name="step" value="<?= h($previousStep) ?>">Voltar etapa</button>
                    <?php endif; ?>
                    <?php if ($nextStep !== null): ?>
                        <button class="button" type="submit" name="step" value="<?= h($nextStep) ?>">Salvar e continuar</button>
                    <?php else: ?>
                        <button class="button" type="submit" formaction="exams.php">Ir para seleção de questões</button>
                    <?php endif; ?>
                    <a class="button-secondary" href="exam-models.php">Modelos</a>
                    <a class="ghost-button" href="profile.php">Meu painel</a>
                    <a class="ghost-button" href="exam-create.php">Cancelar</a>
                </div>
            </div>
        </article>
    </section>
</form>

<?php render_footer(); ?>
