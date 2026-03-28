<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/question_repository.php';
require_once __DIR__ . '/includes/exam_examples.php';
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
    'exam_label' => (string) ($_GET['exam_label'] ?? $parsedExamData['metadata']['exam_label']),
    'discipline' => (string) ($_GET['discipline'] ?? $parsedExamData['metadata']['discipline']),
    'component_name' => (string) ($_GET['component_name'] ?? $parsedExamData['metadata']['component_name']),
    'teacher_name' => (string) ($_GET['teacher_name'] ?? ($parsedExamData['metadata']['teacher_name'] !== '' ? $parsedExamData['metadata']['teacher_name'] : ($user['name'] ?? ''))),
    'school_name' => (string) ($_GET['school_name'] ?? $parsedExamData['metadata']['school_name']),
    'year_reference' => (string) ($_GET['year_reference'] ?? $parsedExamData['metadata']['year_reference']),
    'class_name' => (string) ($_GET['class_name'] ?? $parsedExamData['metadata']['class_name']),
    'application_date' => (string) ($_GET['application_date'] ?? $parsedExamData['metadata']['application_date']),
    'header_content' => $draftSections['header'],
    'body_content' => $draftSections['body'],
    'footer_content' => $draftSections['footer'],
];
$backHref = $editingExam ? 'exam-preview.php?id=' . $editExamId : 'dashboard.php';
$structureSummary = implode(' | ', array_filter([
    $formData['header_content'] !== '' ? 'Cabeçalho' : null,
    $formData['body_content'] !== '' ? 'Corpo' : null,
    $formData['footer_content'] !== '' ? 'Rodapé' : null,
])) ?: 'Somente estrutura padrão';
$selectedIds = array_values(array_unique(array_map('intval', array_merge(
    $loadedExamQuestionIds,
    (array) ($_GET['question_ids'] ?? [])
))));
$selectedPreview = [];

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

    <section class="exam-create-shell">
        <aside class="simple-card exam-create-sidebar">
            <div class="exam-create-sidebar-top">
                <span class="exam-create-kicker"><?= $editingExam ? 'Edição' : 'Nova prova' ?></span>
                <h2>Etapas da montagem</h2>
                <p class="helper-text">Navegue pelos blocos principais e finalize quando tudo estiver consistente.</p>
            </div>

            <nav class="exam-create-nav" aria-label="Seções da montagem">
                <a class="exam-create-nav-link" href="#resumo-geral"><span class="exam-create-nav-index">01</span><span>Resumo</span></a>
                <a class="exam-create-nav-link" href="#dados-basicos"><span class="exam-create-nav-index">02</span><span>Dados básicos</span></a>
                <a class="exam-create-nav-link" href="#forma-prova"><span class="exam-create-nav-index">03</span><span>Forma da prova</span></a>
                <a class="exam-create-nav-link" href="#header-section"><span class="exam-create-nav-index">04</span><span>Cabeçalho</span></a>
                <a class="exam-create-nav-link" href="#body-section"><span class="exam-create-nav-index">05</span><span>Corpo</span></a>
                <a class="exam-create-nav-link" href="#footer-section"><span class="exam-create-nav-index">06</span><span>Rodapé</span></a>
                <a class="exam-create-nav-link" href="exam-library.php"><span class="exam-create-nav-index"><i class="fa-solid fa-grid-2" aria-hidden="true"></i></span><span>Central de provas</span></a>
            </nav>

            <div class="exam-create-sidebar-status">
                <div class="exam-create-status-item">
                    <strong>Estrutura</strong>
                    <span><?= h($structureSummary) ?></span>
                </div>
                <div class="exam-create-status-item">
                    <strong>Questões</strong>
                    <span><?= h((string) count($selectedIds)) ?> marcadas</span>
                </div>
                <div class="exam-create-status-item">
                    <strong>Layout</strong>
                    <span data-summary-field="exam_style_label"><?= h(exam_style_label($formData['exam_style'])) ?></span>
                </div>
            </div>
        </aside>

        <section class="simple-stack">
            <article class="simple-card exam-create-command-bar">
                <div class="exam-create-command-copy">
                    <span class="exam-create-kicker">Montagem</span>
                    <h2><?= h($formData['draft_title'] !== '' ? $formData['draft_title'] : 'Nova prova') ?></h2>
                    <p class="helper-text">Defina a estrutura, revise o resumo e siga para a seleção de questões.</p>
                </div>

                <div class="exam-create-command-meta">
                    <span class="badge"><?= h((string) count($selectedIds)) ?> questões</span>
                    <span class="badge"><?= h($structureSummary) ?></span>
                    <span class="badge" data-summary-field="exam_style_label"><?= h(exam_style_label($formData['exam_style'])) ?></span>
                </div>

                <div class="exam-create-command-actions">
                    <button class="button" type="submit"><?= $editingExam ? 'Ir para questões' : 'Ir para seleção de questões' ?></button>
                    <?php if ($editingExam): ?>
                        <a class="button-secondary" href="exam-preview.php?id=<?= h((string) $editExamId) ?>">Preview</a>
                    <?php endif; ?>
                    <a class="ghost-button" href="<?= h($backHref) ?>">Voltar</a>
                    <a class="ghost-button" href="exam-create.php">Cancelar</a>
                </div>
            </article>

            <details class="simple-disclosure exam-create-disclosure" id="resumo-geral" open>
                <summary><span>Resumo editável</span><small>Controle o que aparece no painel de acompanhamento.</small></summary>
                <div class="simple-disclosure-body">
                    <p class="helper-text">Escolha o que aparece no resumo enquanto monta a prova.</p>

                    <div class="exam-create-summary-grid">
                    <div class="exam-create-summary-panel">
                        <div class="exam-create-summary-preview">
                            <div class="exam-create-summary-row" data-summary-item="draft_title">
                                <strong>Nome da prova</strong>
                                <span data-summary-field="draft_title"><?= h($formData['draft_title'] !== '' ? $formData['draft_title'] : 'Não informado') ?></span>
                            </div>
                            <div class="exam-create-summary-row" data-summary-item="exam_label">
                                <strong>Tipo</strong>
                                <span data-summary-field="exam_label"><?= h($formData['exam_label'] !== '' ? $formData['exam_label'] : 'AVALIAÇÃO') ?></span>
                            </div>
                            <div class="exam-create-summary-row" data-summary-item="school_name">
                                <strong>Escola</strong>
                                <span data-summary-field="school_name"><?= h($formData['school_name'] !== '' ? $formData['school_name'] : EXAM_DEFAULT_SCHOOL_NAME) ?></span>
                            </div>
                            <div class="exam-create-summary-row" data-summary-item="teacher_name">
                                <strong>Professor</strong>
                                <span data-summary-field="teacher_name"><?= h($formData['teacher_name'] !== '' ? $formData['teacher_name'] : 'Professor não informado') ?></span>
                            </div>
                            <div class="exam-create-summary-row" data-summary-item="component_name">
                                <strong>Componente</strong>
                                <span data-summary-field="component_name"><?= h($formData['component_name'] !== '' ? $formData['component_name'] : ($formData['discipline'] !== '' ? $formData['discipline'] : 'Não informado')) ?></span>
                            </div>
                            <div class="exam-create-summary-row" data-summary-item="class_name">
                                <strong>Turma</strong>
                                <span data-summary-field="class_name"><?= h($formData['class_name'] !== '' ? $formData['class_name'] : 'Não informado') ?></span>
                            </div>
                            <div class="exam-create-summary-row" data-summary-item="application_date">
                                <strong>Data</strong>
                                <span data-summary-field="application_date"><?= h($formData['application_date'] !== '' ? exam_format_date($formData['application_date']) : 'Não informada') ?></span>
                            </div>
                            <div class="exam-create-summary-row" data-summary-item="exam_style_label">
                                <strong>Forma</strong>
                                <span data-summary-field="exam_style_label"><?= h(exam_style_label($formData['exam_style'])) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="exam-create-summary-controls">
                        <strong>Mostrar no resumo</strong>
                        <div class="exam-create-summary-toggles">
                            <?php foreach ([
                                'draft_title' => 'Nome da prova',
                                'exam_label' => 'Tipo',
                                'school_name' => 'Escola',
                                'teacher_name' => 'Professor',
                                'component_name' => 'Componente',
                                'class_name' => 'Turma',
                                'application_date' => 'Data',
                                'exam_style_label' => 'Forma',
                            ] as $fieldName => $fieldLabel): ?>
                                <label class="exam-create-toggle">
                                    <input type="checkbox" data-summary-visibility-toggle="<?= h($fieldName) ?>" checked>
                                    <span><?= h($fieldLabel) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <div class="simple-note exam-create-summary-note">
                            <strong>Selecionadas para a próxima etapa</strong>
                            <p><?= h((string) count($selectedIds)) ?> questão(ões) já estão ligadas a esta montagem.</p>
                        </div>
                    </div>
                </div>
                </div>
            </details>
            <details class="simple-disclosure exam-create-disclosure" id="dados-basicos" open>
                <summary><span>Dados básicos</span><small>Título, professor, turma, disciplina e data.</small></summary>
                <div class="simple-disclosure-body">
                    <p class="helper-text">Informações centrais da prova.</p>

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
                    <label>Ano / Série
                        <input type="text" name="year_reference" placeholder="Ex.: 6º ano" value="<?= h($formData['year_reference']) ?>">
                    </label>
                </div>
                </div>
            </details>

            <details class="simple-disclosure exam-create-disclosure" id="forma-prova">
                <summary><span>Forma da prova</span><small>Layout, resposta, organização e identificação.</small></summary>
                <div class="simple-disclosure-body">
                    <p class="helper-text">Escolha layout, modo de resposta, organização e identificação.</p>

                    <div class="simple-filter-grid exam-builder-form-grid">
                    <label>Modelo visual
                        <select name="exam_template">
                            <?php foreach (exam_template_options() as $templateValue => $templateLabel): ?>
                                <option value="<?= h($templateValue) ?>" <?= $formData['exam_template'] === $templateValue ? 'selected' : '' ?>><?= h($templateLabel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Layout
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
                    <strong>Suporte imediato no render</strong>
                    <p>Layouts, folha separada, gabarito lateral, leitura por bolinhas, embaralhamento e versão já alteram a saída. Blocos, modular, anexos e banco dinâmico ficam registrados na configuração desta montagem.</p>
                </div>
                </div>
            </details>

            <details class="simple-disclosure exam-create-disclosure" id="header-section">
                <summary><span>Cabeçalho</span><small>Orientações iniciais e recados visíveis no topo.</small></summary>
                <div class="simple-disclosure-body">
                    <p class="helper-text">Recados iniciais, observações e instruções antes das questões.</p>
                <label>Conteúdo do cabeçalho
                    <textarea name="draft_header_content" placeholder="Recados, orientações iniciais, tempo de prova ou observações que devem aparecer no cabeçalho."><?= h($formData['header_content']) ?></textarea>
                </label>
                </div>
            </details>

            <details class="simple-disclosure exam-create-disclosure" id="body-section">
                <summary><span>Corpo</span><small>Texto introdutório antes das questões.</small></summary>
                <div class="simple-disclosure-body">
                    <p class="helper-text">Texto introdutório ou orientação central antes das questões.</p>
                <label>Conteúdo do corpo
                    <textarea name="draft_body_content" placeholder="Texto introdutório antes das questões."><?= h($formData['body_content']) ?></textarea>
                </label>
                </div>
            </details>

            <details class="simple-disclosure exam-create-disclosure" id="footer-section">
                <summary><span>Rodapé</span><small>Mensagem final de conferência e entrega.</small></summary>
                <div class="simple-disclosure-body">
                    <p class="helper-text">Avisos finais e mensagem de conferência ao entregar a prova.</p>
                <label>Conteúdo do rodapé
                    <textarea name="draft_footer_content" placeholder="Avisos finais, critérios ou observações para repetir no rodapé da prova."><?= h($formData['footer_content']) ?></textarea>
                </label>
                </div>
            </details>

            <article class="simple-card exam-create-shortcut-card">
                <div class="simple-card-head">
                    <div>
                        <h2>Central de provas</h2>
                        <p class="helper-text">Abra uma página separada para modelos prontos e histórico recente.</p>
                    </div>
                    <a class="button-secondary" href="exam-library.php">Abrir central</a>
                </div>
            </article>

            <details class="simple-disclosure exam-create-disclosure">
                <summary><span>Questões já marcadas</span><small>Pré-seleção que seguirá para a próxima etapa.</small></summary>
                <div class="simple-disclosure-body">
                <?php if ($selectedPreview === []): ?>
                    <div class="empty-state">
                        <h2>Nenhuma questão pré-selecionada</h2>
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
                </div>
            </details>
        </section>
    </section>
</form>

<?php render_footer(); ?>
