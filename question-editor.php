<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/question_helpers.php';
require_once __DIR__ . '/includes/question_repository.php';
require_once __DIR__ . '/includes/question_actions.php';
require_once __DIR__ . '/includes/public_sources.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];

if (is_post()) {
    abort_if_invalid_csrf();
    handle_question_request($userId);
}

$disciplines = question_disciplines();
$subjects = question_subjects();
$officialSources = official_question_sources();
[$edit, $editOptions] = question_edit_payload($userId, isset($_GET['edit']) ? (int) $_GET['edit'] : null);
$selectedSourceKey = '';

foreach ($officialSources as $sourceKey => $source) {
    if (($edit['source_name'] ?? null) === $source['name'] && ($edit['source_url'] ?? null) === $source['url']) {
        $selectedSourceKey = $sourceKey;
        break;
    }
}

$selectedType = $edit['question_type'] ?? 'multiple_choice';
$selectedVisibility = $edit['visibility'] ?? 'private';
$selectedDiscipline = (int) ($edit['discipline_id'] ?? 0);
$selectedSubject = (int) ($edit['subject_id'] ?? 0);
$selectedLevel = $edit['education_level'] ?? 'medio';
$selectedDifficulty = $edit['difficulty'] ?? 'medio';
$selectedDrawing = $edit['drawing_size'] ?? 'medium';
$selectedTrueFalseAnswer = array_key_exists('true_false_answer', (array) $edit) && $edit['true_false_answer'] !== null ? (int) $edit['true_false_answer'] : 1;
$selectedDisciplineName = '';
$selectedSubjectName = '';
$questionCode = trim((string) ($edit['question_code'] ?? ''));
$questionCode = $questionCode !== '' ? $questionCode : question_generate_code();
$promptHtml = (string) ($edit['prompt'] ?? '');
$promptPreviewHtml = question_render_rich_content_html($promptHtml);
$generatedTitle = question_prompt_title_from_html($promptHtml);

foreach ($disciplines as $discipline) {
    if ((int) $discipline['id'] === $selectedDiscipline) {
        $selectedDisciplineName = (string) $discipline['name'];
        break;
    }
}

foreach ($subjects as $subject) {
    if ((int) $subject['id'] === $selectedSubject) {
        $selectedSubjectName = (string) $subject['name'];
        break;
    }
}

render_header(
    $edit ? 'Editar questão' : 'Novo construtor',
    '',
    false
);
?>

<section class="question-editor-shell">
    <div class="question-editor-hero">
        <div>
            <p class="workspace-kicker"><?= $edit ? 'Edição de conteúdo' : 'Novo fluxo de construção' ?></p>
            <h2><?= $edit ? 'Editar item do banco' : 'Construir nova questão' ?></h2>
        </div>
        <div class="simple-action-row">
            <span class="badge question-code-badge"><i class="fa-solid fa-hashtag" aria-hidden="true"></i> <?= h($questionCode) ?></span>
            <a class="ghost-button" href="question-bank.php">Banco existente</a>
            <a class="ghost-button" href="questions.php">Central</a>
        </div>
    </div>

    <form method="post" class="question-editor-grid" data-question-editor data-next-option-index="<?= h((string) count($editOptions)) ?>">
        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="<?= $edit ? 'update_question' : 'create_question' ?>">
        <input type="hidden" name="title" value="<?= h($generatedTitle) ?>" data-question-editor-title>
        <textarea name="prompt" hidden data-question-editor-source><?= h($promptHtml) ?></textarea>
        <?php if ($edit): ?>
            <input type="hidden" name="question_id" value="<?= h((string) $edit['id']) ?>">
        <?php endif; ?>

        <section class="question-editor-main">
            <article class="question-editor-card question-taxonomy-card question-taxonomy-card-inline">
                <div class="question-taxonomy-inline-grid">
                    <div class="question-taxonomy-item question-combobox" data-question-combobox="discipline">
                        <div class="question-combobox-head">
                            <div>
                                <strong>Disciplina *</strong>
                                <span>Digite para filtrar ou criar uma nova disciplina</span>
                            </div>
                        </div>
                        <label class="question-taxonomy-field question-combobox-field">
                            <span class="sr-only">Disciplina</span>
                            <input
                                type="hidden"
                                name="discipline_id"
                                value="<?= h((string) $selectedDiscipline) ?>"
                                data-question-combobox-id
                            >
                            <div class="question-combobox-control">
                                <input
                                    type="text"
                                    name="discipline_name"
                                    value="<?= h($selectedDisciplineName) ?>"
                                    placeholder="Digite ou selecione a disciplina"
                                    required
                                    autocomplete="off"
                                    data-question-combobox-input
                                >
                            </div>
                        </label>
                        <div class="question-combobox-panel" hidden data-question-combobox-panel>
                            <?php foreach ($disciplines as $discipline): ?>
                                <button
                                    type="button"
                                    class="question-combobox-option"
                                    data-question-combobox-option
                                    data-option-id="<?= h((string) $discipline['id']) ?>"
                                    data-option-value="<?= h((string) $discipline['name']) ?>"
                                >
                                    <span><?= h((string) $discipline['name']) ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="question-taxonomy-item question-combobox" data-question-combobox="subject">
                        <div class="question-combobox-head">
                            <div>
                                <strong>Assunto *</strong>
                                <span>Digite para filtrar ou criar um novo assunto</span>
                            </div>
                        </div>
                        <label class="question-taxonomy-field question-combobox-field">
                            <span class="sr-only">Assunto</span>
                            <input
                                type="hidden"
                                name="subject_id"
                                value="<?= h((string) $selectedSubject) ?>"
                                data-question-combobox-id
                            >
                            <div class="question-combobox-control">
                                <input
                                    type="text"
                                    name="subject_name"
                                    value="<?= h($selectedSubjectName) ?>"
                                    placeholder="Digite ou selecione o assunto"
                                    required
                                    autocomplete="off"
                                    data-question-combobox-input
                                >
                            </div>
                        </label>
                        <div class="question-combobox-panel" hidden data-question-combobox-panel>
                            <?php foreach ($subjects as $subject): ?>
                                <button
                                    type="button"
                                    class="question-combobox-option"
                                    data-question-combobox-option
                                    data-option-id="<?= h((string) $subject['id']) ?>"
                                    data-option-value="<?= h((string) $subject['name']) ?>"
                                    data-discipline-id="<?= h((string) $subject['discipline_id']) ?>"
                                    data-discipline-name="<?= h((string) $subject['discipline_name']) ?>"
                                >
                                    <span><?= h($subject['name'] . ' · ' . $subject['discipline_name']) ?></span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </article>

            <article class="question-editor-card question-editor-card-content">
                <div class="question-editor-card-head">
                    <div>
                        <h3>Enunciado</h3>
                    </div>
                </div>

                <div class="editor-toolbar" data-editor-toolbar>
                    <label class="editor-block-select-wrap">
                        <span class="sr-only">Estilo do bloco</span>
                        <select class="editor-block-select" data-editor-block>
                            <option value="p">Parágrafo</option>
                            <option value="h1">Título 1</option>
                            <option value="h2">Título 2</option>
                            <option value="blockquote">Citação</option>
                            <option value="pre">Código</option>
                        </select>
                    </label>
                    <button type="button" class="editor-tool" data-editor-command="bold" title="Negrito"><i class="fa-solid fa-bold"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="italic" title="Itálico"><i class="fa-solid fa-italic"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="underline" title="Sublinhado"><i class="fa-solid fa-underline"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="strikeThrough" title="Riscado"><i class="fa-solid fa-strikethrough"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="superscript" title="Sobrescrito"><i class="fa-solid fa-superscript"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="subscript" title="Subscrito"><i class="fa-solid fa-subscript"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="undo" title="Desfazer"><i class="fa-solid fa-rotate-left"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="redo" title="Refazer"><i class="fa-solid fa-rotate-right"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="insertOrderedList" title="Lista numerada"><i class="fa-solid fa-list-ol"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="insertUnorderedList" title="Lista com marcadores"><i class="fa-solid fa-list-ul"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="insertHorizontalRule" title="Linha horizontal"><i class="fa-solid fa-minus"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="createLink" title="Inserir link"><i class="fa-solid fa-link"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="insertText" data-editor-value="\\frac{}{}" title="Fração LaTeX"><i class="fa-solid fa-divide"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="insertText" data-editor-value="\\sqrt{}" title="Raiz quadrada"><i class="fa-solid fa-square-root-variable"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="insertImageUrl" title="Inserir imagem por URL"><i class="fa-regular fa-image"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="insertImageUpload" title="Enviar imagem"><i class="fa-solid fa-upload"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="justifyLeft" title="Alinhar à esquerda"><i class="fa-solid fa-align-left"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="justifyCenter" title="Centralizar"><i class="fa-solid fa-align-center"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="justifyRight" title="Alinhar à direita"><i class="fa-solid fa-align-right"></i></button>
                    <button type="button" class="editor-tool" data-editor-command="removeFormat" title="Limpar formatação"><i class="fa-solid fa-eraser"></i></button>
                </div>

                <div class="editor-surface-wrap">
                    <div
                        class="editor-surface"
                        contenteditable="true"
                        spellcheck="true"
                        data-question-editor-body
                        aria-label="Editor do enunciado"
                        data-placeholder="Digite ou cole o conteudo aqui!"
                    ><?= $promptPreviewHtml !== '' ? $promptPreviewHtml : '' ?></div>
                </div>

                <input type="file" accept="image/*" hidden data-editor-image-upload>
            </article>

            <article class="question-editor-card">
                <div class="question-editor-card-head">
                    <div>
                        <h3>Resposta da questão</h3>
                    </div>
                </div>

                <div class="question-type-stack">
                    <section class="question-type-section" data-question-editor-section="multiple_choice">
                        <div class="question-type-section-head">
                            <div>
                                <strong>Múltipla escolha</strong>
                            </div>
                            <label class="checkbox-row"><input type="checkbox" name="allow_multiple_correct" value="1" <?= !empty($edit['allow_multiple_correct']) ? 'checked' : '' ?>> Múltiplas corretas</label>
                        </div>

                        <div class="option-list-editor" data-options-container>
                            <?php foreach ($editOptions as $index => $option): ?>
                                <div class="option-editor-row">
                                    <strong><?= h(option_label($index)) ?></strong>
                                    <textarea class="question-option-textarea" name="options[<?= h((string) $index) ?>][text]" rows="2" placeholder="Texto da alternativa" data-rich-paste><?= h(question_normalize_editor_text((string) $option['text'])) ?></textarea>
                                    <label class="checkbox-row compact"><input type="checkbox" name="options[<?= h((string) $index) ?>][is_correct]" value="1" <?= !empty($option['is_correct']) ? 'checked' : '' ?>> Correta</label>
                                    <button class="ghost-button option-remove-button" type="button" data-remove-option>&minus;</button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-actions">
                            <button class="button-secondary" type="button" data-add-option>Adicionar alternativa</button>
                        </div>
                    </section>

                    <section class="question-type-section hidden" data-question-editor-section="discursive">
                        <div class="question-type-section-head">
                            <div>
                                <strong>Resposta discursiva</strong>
                            </div>
                        </div>
                        <div class="question-editor-grid question-editor-grid-compact">
                            <label>Resposta discursiva
                                <textarea name="discursive_answer" rows="6" data-rich-paste><?= h(question_normalize_editor_text((string) ($edit['discursive_answer'] ?? ''))) ?></textarea>
                            </label>
                            <label>Número de linhas
                                <input type="number" min="1" max="30" name="response_lines" value="<?= h((string) ($edit['response_lines'] ?? 5)) ?>">
                            </label>
                        </div>
                    </section>

                    <section class="question-type-section hidden" data-question-editor-section="drawing">
                        <div class="question-type-section-head">
                            <div>
                                <strong>Linhas de resposta</strong>
                            </div>
                        </div>
                        <div class="question-drawing-layout">
                            <div class="question-drawing-config">
                                <label>Tamanho
                                    <select name="drawing_size" data-drawing-size-select>
                                        <option value="small" <?= $selectedDrawing === 'small' ? 'selected' : '' ?>>Pequeno</option>
                                        <option value="medium" <?= $selectedDrawing === 'medium' ? 'selected' : '' ?>>Médio</option>
                                        <option value="large" <?= $selectedDrawing === 'large' ? 'selected' : '' ?>>Grande</option>
                                        <option value="custom" <?= $selectedDrawing === 'custom' ? 'selected' : '' ?>>Customizado</option>
                                    </select>
                                </label>
                                <label class="<?= $selectedDrawing === 'custom' ? '' : 'hidden' ?>" data-drawing-custom-field>
                                    Altura customizada (px)
                                    <input type="number" name="drawing_height_px" min="120" max="1200" step="10" value="<?= h((string) ($edit['drawing_height_px'] ?? 320)) ?>">
                                </label>
                            </div>
                            <div class="question-drawing-examples" aria-label="Exemplos de tamanho">
                                <div class="question-drawing-example is-small">
                                    <div class="question-drawing-example-box"></div>
                                    <span>1 linha</span>
                                </div>
                                <div class="question-drawing-example is-medium">
                                    <div class="question-drawing-example-box"></div>
                                    <span>3 linhas</span>
                                </div>
                                <div class="question-drawing-example is-large">
                                    <div class="question-drawing-example-box"></div>
                                    <span>6 linhas</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="question-type-section hidden" data-question-editor-section="true_false">
                        <div class="question-type-section-head">
                            <div>
                                <strong>Verdadeiro ou falso</strong>
                            </div>
                        </div>
                        <label>Resposta correta
                            <select name="true_false_answer">
                                <option value="1" <?= $selectedTrueFalseAnswer === 1 ? 'selected' : '' ?>>Verdadeiro</option>
                                <option value="0" <?= $selectedTrueFalseAnswer === 0 ? 'selected' : '' ?>>Falso</option>
                            </select>
                        </label>
                    </section>
                </div>
            </article>

        </section>

        <aside class="question-editor-sidebar">


        
            <article class="question-editor-card question-editor-card-config">

                <div class="question-editor-card-head">



                
                    <div>
                       <div class="question-editor-actions question-editor-actions-top">
                    <a class="ghost-button" href="question-bank.php">Cancelar</a>
                    <button class="button" type="submit"><?= $edit ? 'Salvar alterações' : 'Salvar questão' ?></button>
                </div>
                    </div>
                </div>

                

                <div class="question-config-stack">
                    <div class="question-config-group">
                        <div class="question-config-group-head">
                            <span class="question-config-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
                            <div>
                                <strong>Classificação</strong>
                            </div>
                        </div>

                        <div class="question-editor-grid question-editor-grid-compact">
                            <label>Tipo
                                <select name="question_type" required data-question-editor-type>
                                    <option value="multiple_choice" <?= $selectedType === 'multiple_choice' ? 'selected' : '' ?>>Múltipla escolha</option>
                                    <option value="discursive" <?= $selectedType === 'discursive' ? 'selected' : '' ?>>Discursiva</option>
                                    <option value="drawing" <?= $selectedType === 'drawing' ? 'selected' : '' ?>>Desenho / espaço livre</option>
                                    <option value="true_false" <?= $selectedType === 'true_false' ? 'selected' : '' ?>>Verdadeiro ou falso</option>
                                </select>
                            </label>

                            <label>Visibilidade
                                <select name="visibility" required data-question-editor-visibility>
                                    <option value="private" <?= $selectedVisibility === 'private' ? 'selected' : '' ?>>Privada</option>
                                    <option value="public" <?= $selectedVisibility === 'public' ? 'selected' : '' ?>>Pública</option>
                                </select>
                            </label>

                            <label>Nível
                                <select name="education_level" required>
                                    <option value="fundamental" <?= $selectedLevel === 'fundamental' ? 'selected' : '' ?>>Ensino Fundamental</option>
                                    <option value="medio" <?= $selectedLevel === 'medio' ? 'selected' : '' ?>>Ensino Médio</option>
                                    <option value="tecnico" <?= $selectedLevel === 'tecnico' ? 'selected' : '' ?>>Técnico</option>
                                    <option value="superior" <?= $selectedLevel === 'superior' ? 'selected' : '' ?>>Superior</option>
                                </select>
                            </label>

                            <label>Dificuldade
                                <select name="difficulty" required>
                                    <option value="facil" <?= $selectedDifficulty === 'facil' ? 'selected' : '' ?>>Fácil</option>
                                    <option value="medio" <?= $selectedDifficulty === 'medio' ? 'selected' : '' ?>>Média</option>
                                    <option value="dificil" <?= $selectedDifficulty === 'dificil' ? 'selected' : '' ?>>Difícil</option>
                                </select>
                            </label>
                        </div>
                    </div>

                </div>
            </article>

            <article class="question-editor-card question-editor-card-optional">
                <div class="question-editor-card-head">
                    <div>
                        <h3>Campos avançados</h3>
                    </div>
                </div>
                <div class="question-advanced-stack">
                    <div class="question-advanced-card">
                        <div class="question-advanced-head">
                            <span class="question-config-icon"><i class="fa-solid fa-comment-dots" aria-hidden="true"></i></span>
                            <div>
                                <strong>Comentário do autor</strong>
                            </div>
                        </div>
                        <textarea name="explanation" rows="3" data-auto-grow data-rich-paste><?= h(question_normalize_editor_text((string) ($edit['explanation'] ?? ''))) ?></textarea>
                    </div>

                    <div class="question-advanced-card">
                        <div class="question-advanced-head">
                            <span class="question-config-icon"><i class="fa-solid fa-tag" aria-hidden="true"></i></span>
                            <div>
                                <strong>Fonte oficial e referência</strong>
                            </div>
                        </div>
                        <div class="question-config-editor">
                            <label>Origem oficial
                                <select name="official_source_key">
                                    <option value="">Sem origem externa</option>
                                    <?php foreach ($officialSources as $sourceKey => $source): ?>
                                        <option value="<?= h($sourceKey) ?>" <?= $selectedSourceKey === $sourceKey ? 'selected' : '' ?>>
                                            <?= h($source['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                            <label>Referência
                                <input type="text" name="source_reference" value="<?= h(question_normalize_editor_text((string) ($edit['source_reference'] ?? ''), true)) ?>" placeholder="Ex.: Caderno azul, questão 12">
                            </label>
                        </div>
                    </div>
                </div>
            </article>
        </aside>

        <div class="question-editor-actions">
            <a class="ghost-button" href="question-bank.php">Cancelar</a>
            <button class="button" type="submit"><?= $edit ? 'Salvar alterações' : 'Salvar questão' ?></button>
        </div>
    </form>
</section>

<template id="question-option-template">
    <div class="option-editor-row">
        <strong data-option-label>__LABEL__</strong>
        <textarea class="question-option-textarea" name="options[__INDEX__][text]" rows="2" placeholder="Texto da alternativa" data-rich-paste></textarea>
        <label class="checkbox-row compact"><input type="checkbox" name="options[__INDEX__][is_correct]" value="1"> Correta</label>
        <button class="ghost-button option-remove-button" type="button" data-remove-option>&minus;</button>
    </div>
</template>

<?php render_footer(); ?>
