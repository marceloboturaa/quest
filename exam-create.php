<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/question_repository.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_metadata.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$editExamId = (int) ($_GET['edit'] ?? 0);
$editingExam = $editExamId > 0 ? exam_find($editExamId, $userId) : null;
$parsedExamData = $editingExam ? exam_parse_stored_instructions($editingExam['instructions'] ?? null) : [
    'metadata' => exam_default_metadata(),
    'instructions' => '',
    'sections' => exam_default_sections(),
];
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
    'header_content' => $draftSections['header'],
    'body_content' => $draftSections['body'],
    'footer_content' => $draftSections['footer'],
];
$backHref = $editingExam ? 'exam-preview.php?id=' . $editExamId : 'dashboard.php';
$selectedIds = array_values(array_unique(array_map('intval', array_merge(
    $loadedExamQuestionIds,
    (array) ($_GET['question_ids'] ?? [])
))));
$selectedPreview = [];
$selectedCount = count($selectedIds);

if ($selectedIds !== []) {
    [$selectedQuestions] = question_list([
        'term' => '',
        'discipline_id' => 0,
        'subject_id' => 0,
        'education_level' => '',
        'question_type' => '',
        'author_id' => 0,
        'visibility' => '',
    ], $userId);

    $selectedPreview = array_values(array_filter(
        $selectedQuestions,
        static fn(array $question): bool => in_array((int) $question['id'], $selectedIds, true)
    ));
}

render_header(
    'Montagem da prova',
    'Organize os dados principais e siga para a seleção de questões.'
);
?>

<form method="get" action="exams.php" class="simple-stack" data-exam-meta-form>
    <?php if ($editingExam): ?>
        <input type="hidden" name="exam_id" value="<?= h((string) $editExamId) ?>">
    <?php endif; ?>
    <?php foreach ($selectedIds as $questionId): ?>
        <input type="hidden" name="question_ids[]" value="<?= h((string) $questionId) ?>">
    <?php endforeach; ?>

    <section class="exam-create-shell exam-create-shell--streamlined">
        <aside class="simple-card exam-create-sidebar">
            <div class="exam-create-sidebar-top">
                <span class="exam-create-kicker"><?= $editingExam ? 'Edição' : 'Montagem' ?></span>
                <h2><?= $editingExam ? 'Editar prova' : 'Nova prova' ?></h2>
                <p class="helper-text">A página ficou dividida em quatro blocos principais para evitar excesso de informação.</p>
            </div>

            <nav class="exam-create-nav" aria-label="Seções da montagem">
                <a class="exam-create-nav-link" href="#dados-principais"><span class="exam-create-nav-index">01</span><span>Dados principais</span></a>
                <a class="exam-create-nav-link" href="#formato-prova"><span class="exam-create-nav-index">02</span><span>Formato</span></a>
                <a class="exam-create-nav-link" href="#cabecalho-visual"><span class="exam-create-nav-index">03</span><span>Cabeçalho</span></a>
                <a class="exam-create-nav-link" href="#textos-prova"><span class="exam-create-nav-index">04</span><span>Textos</span></a>
                <a class="exam-create-nav-link" href="#atalhos-prova"><span class="exam-create-nav-index">05</span><span>Modelos e atalhos</span></a>
            </nav>

            <div class="exam-create-sidebar-status">
                <div class="exam-create-status-item">
                    <strong>Questões marcadas</strong>
                    <span><?= h((string) $selectedCount) ?> pronta(s) para a próxima etapa</span>
                </div>
                <div class="exam-create-status-item">
                    <strong>Forma atual</strong>
                    <span data-summary-field="exam_style_label"><?= h(exam_style_label($formData['exam_style'])) ?></span>
                </div>
            </div>

            <?php if ($selectedPreview !== []): ?>
                <div class="exam-create-sidebar-note">
                    <strong>Pré-seleção</strong>
                    <div class="exam-create-selected-mini-list">
                        <?php foreach (array_slice($selectedPreview, 0, 3) as $index => $question): ?>
                            <div class="exam-create-selected-mini-item">
                                <span><?= h((string) ($index + 1)) ?></span>
                                <p><?= h((string) $question['title']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>

        <section class="simple-stack">
            <article class="simple-card exam-create-command-bar">
                <div class="exam-create-command-copy">
                    <span class="exam-create-kicker">Fluxo curto</span>
                    <h2 data-summary-field="draft_title"><?= h($formData['draft_title'] !== '' ? $formData['draft_title'] : 'Não informado') ?></h2>
                    <p class="helper-text">Preencha o essencial, ajuste o cabeçalho e siga para a seleção de questões.</p>
                </div>

                <div class="exam-create-quickfacts">
                    <span class="badge"><?= h((string) $selectedCount) ?> questões marcadas</span>
                    <span class="badge" data-summary-field="exam_style_label"><?= h(exam_style_label($formData['exam_style'])) ?></span>
                    <?php if ($formData['class_name'] !== ''): ?>
                        <span class="badge"><?= h($formData['class_name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="exam-create-command-actions">
                    <button class="button" type="submit"><?= $editingExam ? 'Ir para questões' : 'Ir para seleção de questões' ?></button>
                    <?php if ($editingExam): ?>
                        <a class="button-secondary" href="exam-preview.php?id=<?= h((string) $editExamId) ?>">Preview</a>
                    <?php endif; ?>
                    <a class="ghost-button" href="exam-models.php">Modelos</a>
                    <a class="ghost-button" href="<?= h($backHref) ?>">Voltar</a>
                    <a class="ghost-button" href="exam-create.php">Cancelar</a>
                </div>
            </article>

            <details class="simple-disclosure exam-create-disclosure" id="dados-principais" open>
                <summary><span>Dados principais</span><small>Título, professor, turma, disciplina e identificação da prova.</small></summary>
                <div class="simple-disclosure-body">
                    <p class="helper-text">Preencha os dados que precisam aparecer no cabeçalho e na identificação da prova.</p>

                    <div class="simple-filter-grid exam-builder-form-grid">
                    <label>Nome da prova
                        <input
                            type="text"
                            name="draft_title"
                            required
                            placeholder="Ex.: Simulado bimestral de Matemática"
                            value="<?= h($formData['draft_title']) ?>"
                        >
                    </label>
                    <label>Disciplina
                        <input type="text" name="discipline" placeholder="Ex.: Matemática" value="<?= h($formData['discipline']) ?>">
                    </label>
                    <label>Professor
                        <input type="text" name="teacher_name" value="<?= h($formData['teacher_name']) ?>">
                    </label>
                    <label>Turma
                        <input type="text" name="class_name" placeholder="Ex.: 6A" value="<?= h($formData['class_name']) ?>">
                    </label>
                    <label>Data
                        <input type="date" name="application_date" value="<?= h($formData['application_date']) ?>">
                    </label>
                    <label>Título do cabeçalho
                        <input type="text" name="exam_label" value="<?= h($formData['exam_label']) ?>" placeholder="Ex.: AVALIAÇÃO">
                    </label>
                    <label>Comp. Curricular
                        <input type="text" name="component_name" placeholder="Ex.: Matemática" value="<?= h($formData['component_name']) ?>">
                    </label>
                    <label>Escola
                        <input type="text" name="school_name" placeholder="Nome da escola" value="<?= h($formData['school_name']) ?>">
                    </label>
                    <label>Subtítulo da escola
                        <input type="text" name="school_subtitle" placeholder="Ex.: Ensino Fundamental, Médio e Profissionalizante" value="<?= h($formData['school_subtitle']) ?>">
                    </label>
                    <label>Ano / Série
                        <input type="text" name="year_reference" placeholder="Ex.: 6º ano" value="<?= h($formData['year_reference']) ?>">
                    </label>
                </div>
                </div>
            </details>

            <details class="simple-disclosure exam-create-disclosure" id="formato-prova" open>
                <summary><span>Formato da prova</span><small>Colunas, resposta, organização e versão.</small></summary>
                <div class="simple-disclosure-body">
                    <p class="helper-text">Escolha como a prova será organizada antes de seguir para as questões.</p>

                    <div class="simple-filter-grid exam-builder-form-grid">
                    <label>Modelo visual
                        <select name="exam_template">
                            <?php foreach (exam_template_options() as $templateValue => $templateLabel): ?>
                                <option value="<?= h($templateValue) ?>" <?= $formData['exam_template'] === $templateValue ? 'selected' : '' ?>><?= h($templateLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Corpo / colunas
                        <select name="exam_style">
                            <?php foreach (exam_style_options() as $styleValue => $styleLabel): ?>
                                <option value="<?= h($styleValue) ?>" <?= $formData['exam_style'] === $styleValue ? 'selected' : '' ?>><?= h($styleLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Modo de resposta
                        <select name="response_mode">
                            <?php foreach (exam_response_mode_options() as $optionValue => $optionLabel): ?>
                                <option value="<?= h($optionValue) ?>" <?= $formData['response_mode'] === $optionValue ? 'selected' : '' ?>><?= h($optionLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Composição
                        <select name="composition_mode">
                            <?php foreach (exam_composition_mode_options() as $optionValue => $optionLabel): ?>
                                <option value="<?= h($optionValue) ?>" <?= $formData['composition_mode'] === $optionValue ? 'selected' : '' ?>><?= h($optionLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Organização
                        <select name="ordering_mode">
                            <?php foreach (exam_ordering_mode_options() as $optionValue => $optionLabel): ?>
                                <option value="<?= h($optionValue) ?>" <?= $formData['ordering_mode'] === $optionValue ? 'selected' : '' ?>><?= h($optionLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Identificação
                        <select name="identification_mode">
                            <?php foreach (exam_identification_mode_options() as $optionValue => $optionLabel): ?>
                                <option value="<?= h($optionValue) ?>" <?= $formData['identification_mode'] === $optionValue ? 'selected' : '' ?>><?= h($optionLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Versão
                        <select name="variant_label">
                            <?php foreach (exam_variant_label_options() as $optionValue => $optionLabel): ?>
                                <option value="<?= h($optionValue) ?>" <?= $formData['variant_label'] === $optionValue ? 'selected' : '' ?>><?= h($optionLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="simple-note">
                    <strong>O que muda na saída</strong>
                    <p>Layout, modo de resposta, folha separada, gabarito lateral, versão e embaralhamento já alteram o preview e o PDF.</p>
                </div>
                </div>
            </details>

            <details class="simple-disclosure exam-create-disclosure" id="cabecalho-visual" open>
                <summary><span>Cabeçalho da prova</span><small>Logo, nome da escola e visual do topo.</small></summary>
                <div class="simple-disclosure-body">
                    <p class="helper-text">Primeiro ajuste o básico. Se precisar, abra as opções avançadas.</p>

                    <div class="simple-note exam-header-builder-preview" data-header-preview>
                        <div class="exam-header-builder-preview-top" data-header-preview-shell>
                            <div class="exam-header-builder-preview-logo" data-header-preview-logo-left>
                                <img src="<?= h($formData['header_logo_left']) ?>" alt="Logo esquerda">
                            </div>
                            <div class="exam-header-builder-preview-copy">
                                <strong data-header-preview-title><?= h($formData['school_name']) ?></strong>
                                <span data-header-preview-subtitle><?= h($formData['school_subtitle']) ?></span>
                            </div>
                            <div class="exam-header-builder-preview-side">
                                <div class="exam-header-builder-preview-side-logo" data-header-preview-logo-right></div>
                                <span class="exam-header-builder-preview-code-label">Código da prova</span>
                                <strong class="exam-header-builder-preview-code-token" data-header-preview-code><?= h('PRV-' . date('Ymd') . '-0001') ?></strong>
                            </div>
                        </div>
                        <div class="exam-header-builder-preview-grid">
                            <span><small>Avaliação</small><strong data-header-preview-exam-label><?= h($formData['exam_label'] !== '' ? $formData['exam_label'] : 'AVALIAÇÃO') ?></strong></span>
                            <span><small>Prof.:</small><strong data-header-preview-teacher><?= h($formData['teacher_name'] !== '' ? $formData['teacher_name'] : 'Professor') ?></strong></span>
                            <span><small>Comp. Curricular:</small><strong data-header-preview-component><?= h($formData['component_name'] !== '' ? $formData['component_name'] : ($formData['discipline'] !== '' ? $formData['discipline'] : 'Componente não informado')) ?></strong></span>
                        </div>
                    </div>

                    <div class="exam-header-editor-grid">
                        <section class="simple-note exam-header-editor-card">
                            <strong>Ajustes rápidos</strong>
                            <p>Estes campos resolvem a maior parte das provas sem precisar abrir o bloco avançado.</p>
                            <div class="exam-header-editor-fields">
                                <label>Logo principal da escola
                                    <input type="text" name="header_logo_left" placeholder="URL da logo principal" value="<?= h($formData['header_logo_left']) ?>">
                                </label>
                                <label>Logo opcional da direita
                                    <input type="text" name="header_logo_right" placeholder="Pode deixar em branco" value="<?= h($formData['header_logo_right']) ?>">
                                </label>
                                <label>Tamanho da letra da prova
                                    <input type="number" name="content_font_size" min="10" max="18" step="1" value="<?= h($formData['content_font_size']) ?>">
                                </label>
                                <label>Tamanho das logos
                                    <input type="number" name="header_logo_size" min="48" max="140" step="2" value="<?= h($formData['header_logo_size']) ?>">
                                </label>
                                <label>Altura do cabeçalho
                                    <input type="number" name="header_min_height" min="90" max="220" step="5" value="<?= h($formData['header_min_height']) ?>">
                                </label>
                            </div>
                        </section>

                        <section class="simple-note exam-header-editor-card">
                            <strong>Tamanho dos textos</strong>
                            <p>Se o nome da escola estiver grande demais ou pequeno demais, ajuste aqui.</p>
                            <div class="exam-header-editor-fields">
                                <label>Tamanho do nome da escola
                                    <input type="number" name="header_title_size" min="16" max="32" step="1" value="<?= h($formData['header_title_size']) ?>">
                                </label>
                                <label>Tamanho do subtítulo
                                    <input type="number" name="header_subtitle_size" min="12" max="24" step="1" value="<?= h($formData['header_subtitle_size']) ?>">
                                </label>
                            </div>
                        </section>
                    </div>

                    <details class="exam-header-editor-advanced">
                        <summary>Ajustes avançados do cabeçalho</summary>
                        <div class="exam-header-editor-advanced-body">
                            <p class="helper-text">Abra apenas se quiser trocar as cores do topo da prova.</p>
                            <div class="exam-header-editor-color-grid">
                                <label>Cor de fundo
                                    <input type="color" name="header_background_color" value="<?= h($formData['header_background_color']) ?>">
                                </label>
                                <label>Cor do nome da escola
                                    <input type="color" name="header_title_color" value="<?= h($formData['header_title_color']) ?>">
                                </label>
                                <label>Cor do subtítulo
                                    <input type="color" name="header_subtitle_color" value="<?= h($formData['header_subtitle_color']) ?>">
                                </label>
                            </div>
                        </div>
                    </details>

                    <div class="simple-note exam-header-editor-tip">
                        <strong>Dica prática</strong>
                        <p>Se estiver em dúvida, deixe as cores como estão e altere apenas logo, altura e tamanho da letra.</p>
                    </div>
                </div>
            </details>

            <details class="simple-disclosure exam-create-disclosure" id="textos-prova" open>
                <summary><span>Textos da prova</span><small>Orientações do topo, introdução e mensagem final.</small></summary>
                <div class="simple-disclosure-body">
                    <p class="helper-text">Os três campos abaixo aparecem em momentos diferentes da prova e ajudam a evitar improviso depois.</p>
                    <div class="exam-create-text-grid">
                        <label>Orientações do topo
                            <textarea name="draft_header_content" placeholder="Recados, orientações iniciais, tempo de prova ou observações que devem aparecer no topo."><?= h($formData['header_content']) ?></textarea>
                        </label>
                        <label>Texto antes das questões
                            <textarea name="draft_body_content" placeholder="Texto introdutório antes das questões."><?= h($formData['body_content']) ?></textarea>
                        </label>
                        <label>Mensagem final
                            <textarea name="draft_footer_content" placeholder="Avisos finais, critérios ou observações para repetir no rodapé da prova."><?= h($formData['footer_content']) ?></textarea>
                        </label>
                    </div>
                </div>
            </details>

            <article class="simple-card exam-create-shortcut-card" id="atalhos-prova">
                <div class="simple-card-head">
                    <div>
                        <h2>Modelos e atalhos</h2>
                        <p class="helper-text">Use a central de provas ou carregue um modelo pronto em uma página separada, sem poluir esta etapa.</p>
                    </div>
                    <div class="simple-action-row">
                        <a class="button-secondary" href="exam-library.php">Abrir central</a>
                        <a class="ghost-button" href="exam-models.php">Modelos de prova</a>
                    </div>
                </div>
                <?php if ($selectedPreview === []): ?>
                    <div class="simple-note">
                        <strong>Nenhuma questão pré-selecionada</strong>
                        <p>Você pode seguir mesmo assim e escolher tudo na próxima etapa.</p>
                    </div>
                <?php else: ?>
                    <div class="simple-list">
                        <?php foreach (array_slice($selectedPreview, 0, 4) as $question): ?>
                            <article class="simple-list-item">
                                <div>
                                    <strong><?= h((string) $question['title']) ?></strong>
                                    <p><?= h((string) ($question['subject_name'] ?? 'Sem assunto')) ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </article>

            <article class="simple-card exam-create-footer-actions">
                <div class="exam-create-footer-actions-copy">
                    <strong>Próximo passo</strong>
                    <p class="helper-text">Quando terminar os ajustes desta etapa, siga para a seleção de questões ou volte sem perder o fluxo.</p>
                </div>
                <div class="exam-create-command-actions exam-create-command-actions--bottom">
                    <button class="button" type="submit"><?= $editingExam ? 'Ir para questões' : 'Ir para seleção de questões' ?></button>
                    <a class="button-secondary" href="exam-models.php">Modelos</a>
                    <a class="ghost-button" href="<?= h($backHref) ?>">Voltar</a>
                    <a class="ghost-button" href="exam-create.php">Cancelar</a>
                </div>
            </article>
        </section>
    </section>
</form>

<?php render_footer(); ?>
