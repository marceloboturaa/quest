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
    $edit ? 'Editar questão' : 'Nova questão',
    'Editor dedicado para escrever, revisar e salvar sem modal.',
    false
);
?>

<section class="workspace-results-shell">
    <div class="workspace-results-head">
        <div>
            <p class="workspace-kicker"><?= $edit ? 'Edição dedicada' : 'Criação dedicada' ?></p>
            <h2><?= $edit ? 'Editar item do banco' : 'Escrever nova questão' ?></h2>
        </div>
        <div class="form-actions">
            <a class="ghost-button" href="questions.php">Central</a>
            <a class="ghost-button" href="question-bank.php">Banco existente</a>
        </div>
    </div>

    <form method="post" class="question-builder" data-question-form data-next-option-index="<?= h((string) count($editOptions)) ?>">
        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="action" value="<?= $edit ? 'update_question' : 'create_question' ?>">
        <?php if ($edit): ?><input type="hidden" name="question_id" value="<?= h((string) $edit['id']) ?>"><?php endif; ?>

        <div class="question-builder-layout">
            <section class="question-builder-main">
                <article class="question-builder-card question-builder-card-primary">
                    <div class="question-builder-card-head">
                        <div>
                            <h3>Conteúdo</h3>
                            <p>Escreva o título, cole o enunciado e revise a prévia ao lado.</p>
                        </div>
                        <span class="badge"><?= h($edit ? 'Modo de edição' : 'Novo rascunho') ?></span>
                    </div>

                    <label>Título
                        <input type="text" name="title" required value="<?= h(question_normalize_editor_text((string) ($edit['title'] ?? ''), true)) ?>" data-question-preview-title-source>
                    </label>
                    <label>Enunciado
                        <textarea class="question-rich-textarea" name="prompt" rows="12" required data-rich-paste data-question-preview-source><?= h(question_normalize_editor_text((string) ($edit['prompt'] ?? ''))) ?></textarea>
                    </label>
                    <p class="helper-text question-format-note">Cole direto do ChatGPT, Claude, Gemini ou DeepSeek. O editor remove duplicações, melhora a colagem e mostra a prévia imediatamente.</p>
                    <label>Imagem do enunciado (URL)
                        <input type="url" name="prompt_image_url" value="<?= h((string) ($edit['prompt_image_url'] ?? '')) ?>" placeholder="https://...">
                    </label>
                </article>

                <article class="question-builder-card">
                    <div class="question-builder-card-head">
                        <div>
                            <h3>Configuração</h3>
                            <p>Classifique a questão sem sobrecarregar a escrita.</p>
                        </div>
                    </div>
                    <div class="question-builder-grid question-builder-grid-compact">
                        <label>Tipo
                            <select name="question_type" required>
                                <option value="multiple_choice" <?= $selectedType === 'multiple_choice' ? 'selected' : '' ?>>Múltipla escolha</option>
                                <option value="discursive" <?= $selectedType === 'discursive' ? 'selected' : '' ?>>Discursiva</option>
                                <option value="drawing" <?= $selectedType === 'drawing' ? 'selected' : '' ?>>Desenho / espaço livre</option>
                                <option value="true_false" <?= $selectedType === 'true_false' ? 'selected' : '' ?>>Verdadeiro ou falso</option>
                            </select>
                        </label>
                        <label>Visibilidade
                            <select name="visibility" required data-question-summary-visibility>
                                <option value="private" <?= $selectedVisibility === 'private' ? 'selected' : '' ?>>Privada</option>
                                <option value="public" <?= $selectedVisibility === 'public' ? 'selected' : '' ?>>Pública</option>
                            </select>
                        </label>
                        <label>Disciplina
                            <select name="discipline_id" required data-discipline-select data-target="question-subject-select" data-question-summary-discipline>
                                <option value="">Selecione</option>
                                <?php foreach ($disciplines as $discipline): ?>
                                    <option value="<?= h((string) $discipline['id']) ?>" <?= $selectedDiscipline === (int) $discipline['id'] ? 'selected' : '' ?>><?= h($discipline['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Assunto
                            <select name="subject_id" id="question-subject-select" required data-subject-select data-question-summary-subject>
                                <option value="">Selecione</option>
                                <?php foreach ($subjects as $subject): ?>
                                    <option value="<?= h((string) $subject['id']) ?>" data-discipline-id="<?= h((string) $subject['discipline_id']) ?>" <?= $selectedSubject === (int) $subject['id'] ? 'selected' : '' ?>><?= h($subject['discipline_name'] . ' - ' . $subject['name']) ?></option>
                                <?php endforeach; ?>
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
                                <option value="medio" <?= $selectedDifficulty === 'medio' ? 'selected' : '' ?>>Médio</option>
                                <option value="dificil" <?= $selectedDifficulty === 'dificil' ? 'selected' : '' ?>>Difícil</option>
                            </select>
                        </label>
                    </div>
                </article>

                <article class="question-builder-card question-builder-card-optional">
                    <details class="question-builder-disclosure">
                        <summary>Fonte oficial e referência</summary>
                        <div class="question-builder-grid question-builder-grid-compact">
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
                    </details>
                </article>

                <article class="question-builder-card" data-question-section="multiple_choice">
                    <div class="question-builder-card-head">
                        <div>
                            <h3>Alternativas</h3>
                            <p>Organize as opções e marque as corretas.</p>
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
                    <div class="form-actions"><button class="button-secondary" type="button" data-add-option>Adicionar alternativa</button></div>
                </article>

                <article class="question-builder-card hidden" data-question-section="discursive">
                    <div class="question-builder-card-head">
                        <div>
                            <h3>Resposta discursiva</h3>
                            <p>Defina o espaço e a resposta de referência, se precisar.</p>
                        </div>
                    </div>
                    <div class="question-builder-grid question-builder-grid-compact">
                        <label>Número de linhas
                            <input type="number" min="1" max="30" name="response_lines" value="<?= h((string) ($edit['response_lines'] ?? 5)) ?>">
                        </label>
                        <label>Resposta de referência
                            <textarea name="discursive_answer" rows="8" data-rich-paste><?= h(question_normalize_editor_text((string) ($edit['discursive_answer'] ?? ''))) ?></textarea>
                        </label>
                    </div>
                </article>

                <article class="question-builder-card hidden" data-question-section="drawing">
                    <div class="question-builder-card-head">
                        <div>
                            <h3>Espaço de resposta</h3>
                            <p>Escolha a altura da área livre para o aluno responder.</p>
                        </div>
                    </div>
                    <div class="question-builder-grid question-builder-grid-compact">
                        <label>Altura do espaço
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
                </article>

                <article class="question-builder-card hidden" data-question-section="true_false">
                    <div class="question-builder-card-head">
                        <div>
                            <h3>Resposta correta</h3>
                            <p>Escolha a alternativa correta para a questão.</p>
                        </div>
                    </div>
                    <label>Resposta correta
                        <select name="true_false_answer">
                            <option value="1" <?= $selectedTrueFalseAnswer === 1 ? 'selected' : '' ?>>Verdadeiro</option>
                            <option value="0" <?= $selectedTrueFalseAnswer === 0 ? 'selected' : '' ?>>Falso</option>
                        </select>
                    </label>
                </article>
            </section>

            <aside class="question-builder-side">
                <article class="question-live-preview">
                    <div class="question-builder-card-head">
                        <div>
                            <h3>Prévia</h3>
                            <p>Mostra como a questão aparece no banco.</p>
                        </div>
                    </div>
                    <div class="question-preview-meta">
                        <span class="badge" data-question-preview-type><?= h(question_type_label($selectedType)) ?></span>
                        <span class="badge" data-question-preview-visibility><?= h(visibility_label($selectedVisibility)) ?></span>
                        <span class="badge" data-question-preview-discipline><?= h($selectedDisciplineName !== '' ? $selectedDisciplineName : 'Sem disciplina') ?></span>
                        <span class="badge" data-question-preview-subject><?= h($selectedSubjectName !== '' ? $selectedSubjectName : 'Sem assunto') ?></span>
                    </div>
                    <h3 class="question-preview-title" data-question-preview-title><?= h(question_normalize_editor_text((string) ($edit['title'] ?? ''), true) !== '' ? question_normalize_editor_text((string) ($edit['title'] ?? ''), true) : 'Título da questão') ?></h3>
                    <div class="question-live-preview-body" data-question-preview-output><?= question_render_formatted_text_html((string) ($edit['prompt'] ?? '')) ?></div>
                </article>
            </aside>
        </div>

        <div class="question-builder-actions">
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
