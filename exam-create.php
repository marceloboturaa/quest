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
];
$loadedExamQuestionIds = $editingExam ? exam_question_ids($editExamId, $userId) : [];
$formData = [
    'draft_title' => (string) ($_GET['draft_title'] ?? ($editingExam['title'] ?? '')),
    'exam_template' => (string) ($_GET['exam_template'] ?? $parsedExamData['metadata']['exam_template']),
    'exam_style' => (string) ($_GET['exam_style'] ?? $parsedExamData['metadata']['exam_style']),
    'exam_label' => (string) ($_GET['exam_label'] ?? $parsedExamData['metadata']['exam_label']),
    'discipline' => (string) ($_GET['discipline'] ?? $parsedExamData['metadata']['discipline']),
    'component_name' => (string) ($_GET['component_name'] ?? $parsedExamData['metadata']['component_name']),
    'teacher_name' => (string) ($_GET['teacher_name'] ?? ($parsedExamData['metadata']['teacher_name'] !== '' ? $parsedExamData['metadata']['teacher_name'] : ($user['name'] ?? ''))),
    'school_name' => (string) ($_GET['school_name'] ?? $parsedExamData['metadata']['school_name']),
    'year_reference' => (string) ($_GET['year_reference'] ?? $parsedExamData['metadata']['year_reference']),
    'class_name' => (string) ($_GET['class_name'] ?? $parsedExamData['metadata']['class_name']),
    'application_date' => (string) ($_GET['application_date'] ?? $parsedExamData['metadata']['application_date']),
    'draft_instructions' => (string) ($_GET['draft_instructions'] ?? $parsedExamData['instructions']),
];
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
    $editingExam ? 'Editar prova' : 'Nova prova',
    'Primeiro preencha os dados principais. Depois escolha as questões.'
);
?>

<section class="simple-stack">
    <article class="simple-card">
        <div class="simple-card-head">
            <div>
                <h2>Passo 1. Dados principais</h2>
                <p class="helper-text">Preencha só o essencial para começar a montagem.</p>
            </div>
            <div class="simple-inline-list">
                <span class="badge"><?= $editingExam ? 'Edição' : 'Nova prova' ?></span>
            </div>
        </div>

        <form method="get" action="exams.php" class="simple-stack">
            <?php if ($editingExam): ?>
                <input type="hidden" name="exam_id" value="<?= h((string) $editExamId) ?>">
            <?php endif; ?>

            <label>Nome da prova
                <input
                    type="text"
                    name="draft_title"
                    required
                    placeholder="Ex.: Simulado bimestral de Matemática"
                    value="<?= h($formData['draft_title']) ?>"
                >
            </label>

            <div class="simple-filter-grid">
                <label>Modelo
                    <select name="exam_template">
                        <?php foreach (exam_template_options() as $templateValue => $templateLabel): ?>
                            <option value="<?= h($templateValue) ?>" <?= $formData['exam_template'] === $templateValue ? 'selected' : '' ?>><?= h($templateLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Formato
                    <select name="exam_style">
                        <?php foreach (exam_style_options() as $styleValue => $styleLabel): ?>
                            <option value="<?= h($styleValue) ?>" <?= $formData['exam_style'] === $styleValue ? 'selected' : '' ?>><?= h($styleLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
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
            </div>

            <details class="simple-disclosure">
                <summary>Ver campos opcionais</summary>
                <div class="simple-filter-grid simple-disclosure-body">
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

                <label>Instruções
                    <textarea name="draft_instructions" placeholder="Orientações para o aluno, tempo e observações."><?= h($formData['draft_instructions']) ?></textarea>
                </label>
            </details>

            <?php foreach ($selectedIds as $questionId): ?>
                <input type="hidden" name="question_ids[]" value="<?= h((string) $questionId) ?>">
            <?php endforeach; ?>

            <div class="simple-action-row">
                <button class="button" type="submit"><?= $editingExam ? 'Continuar edição' : 'Ir para seleção de questões' ?></button>
                <a class="ghost-button" href="question-bank.php">Voltar ao banco</a>
            </div>
        </form>
    </article>

    <section class="simple-panel-grid">
        <article class="simple-card">
            <div class="simple-card-head">
                <h2>Resumo</h2>
            </div>
            <div class="simple-list">
                <div class="simple-list-item">
                    <div>
                        <strong>Título</strong>
                        <p><?= h($formData['draft_title'] !== '' ? $formData['draft_title'] : 'Não informado') ?></p>
                    </div>
                </div>
                <div class="simple-list-item">
                    <div>
                        <strong>Formato</strong>
                        <p><?= h(exam_style_label($formData['exam_style'])) ?></p>
                    </div>
                </div>
                <div class="simple-list-item">
                    <div>
                        <strong>Professor</strong>
                        <p><?= h($formData['teacher_name'] !== '' ? $formData['teacher_name'] : 'Não informado') ?></p>
                    </div>
                </div>
            </div>
        </article>

        <article class="simple-card">
            <div class="simple-card-head">
                <h2>Questões já marcadas</h2>
            </div>
            <?php if ($selectedPreview === []): ?>
                <div class="empty-state">
                    <h2>Nenhuma questão pré-selecionada</h2>
                    <p>Você pode seguir mesmo assim e escolher tudo na próxima tela.</p>
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
    </section>
</section>

<?php render_footer(); ?>
