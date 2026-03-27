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
    $editingExam
        ? 'Revise os metadados da prova antes de ajustar a seleção de questões.'
        : 'Defina os metadados da avaliação antes de entrar na montagem das questões.'
);
?>

<section class="panel">
    <h2><?= $editingExam ? 'Dados da prova' : 'Dados da avaliação' ?></h2>
    <form method="get" action="exams.php" class="form-grid">
        <?php if ($editingExam): ?>
            <input type="hidden" name="exam_id" value="<?= h((string) $editExamId) ?>">
        <?php endif; ?>
        <label>Nome da prova
            <input
                type="text"
                name="draft_title"
                required
                placeholder="Ex.: Simulado bimestral de Matemática"
                value="<?= h((string) ($_GET['draft_title'] ?? ($editingExam['title'] ?? ''))) ?>"
            >
        </label>

        <div class="form-grid two-columns">
            <label>Modelo da prova
                <select name="exam_template">
                    <?php foreach (exam_template_options() as $templateValue => $templateLabel): ?>
                        <?php $selectedTemplate = (string) ($_GET['exam_template'] ?? $parsedExamData['metadata']['exam_template']); ?>
                        <option value="<?= h($templateValue) ?>" <?= $selectedTemplate === $templateValue ? 'selected' : '' ?>><?= h($templateLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Formato da prova
                <select name="exam_style">
                    <?php foreach (exam_style_options() as $styleValue => $styleLabel): ?>
                        <?php $selectedStyle = (string) ($_GET['exam_style'] ?? $parsedExamData['metadata']['exam_style']); ?>
                        <option value="<?= h($styleValue) ?>" <?= $selectedStyle === $styleValue ? 'selected' : '' ?>><?= h($styleLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Título do cabeçalho
                <input
                    type="text"
                    name="exam_label"
                    value="<?= h((string) ($_GET['exam_label'] ?? $parsedExamData['metadata']['exam_label'])) ?>"
                    placeholder="Ex.: AVALIAÇÃO TRIMESTRAL"
                >
            </label>
        </div>

        <div class="form-grid two-columns">
            <label>Disciplina
                <input type="text" name="discipline" placeholder="Ex.: Matemática / 6º ano" value="<?= h((string) ($_GET['discipline'] ?? $parsedExamData['metadata']['discipline'])) ?>">
            </label>
            <label>Comp. Curricular
                <input type="text" name="component_name" placeholder="Ex.: Matemática" value="<?= h((string) ($_GET['component_name'] ?? $parsedExamData['metadata']['component_name'])) ?>">
            </label>
        </div>

        <div class="form-grid two-columns">
            <label>Professor
                <input type="text" name="teacher_name" value="<?= h((string) ($_GET['teacher_name'] ?? ($parsedExamData['metadata']['teacher_name'] !== '' ? $parsedExamData['metadata']['teacher_name'] : ($user['name'] ?? '')))) ?>">
            </label>
            <label>Escola
                <input type="text" name="school_name" placeholder="Nome da escola" value="<?= h((string) ($_GET['school_name'] ?? $parsedExamData['metadata']['school_name'])) ?>">
            </label>
        </div>

        <div class="form-grid two-columns">
            <label>Ano / Série
                <input type="text" name="year_reference" placeholder="Ex.: 6º ano" value="<?= h((string) ($_GET['year_reference'] ?? $parsedExamData['metadata']['year_reference'])) ?>">
            </label>
            <label>Turma
                <input type="text" name="class_name" placeholder="Ex.: 6A" value="<?= h((string) ($_GET['class_name'] ?? $parsedExamData['metadata']['class_name'])) ?>">
            </label>
        </div>

        <div class="form-grid two-columns">
            <label>Data
                <input type="date" name="application_date" value="<?= h((string) ($_GET['application_date'] ?? $parsedExamData['metadata']['application_date'])) ?>">
            </label>
            <div></div>
        </div>

        <label>Instruções
            <textarea name="draft_instructions" placeholder="Orientações para o aluno, materiais permitidos, tempo e observações."><?= h((string) ($_GET['draft_instructions'] ?? $parsedExamData['instructions'])) ?></textarea>
        </label>

        <?php foreach ($selectedIds as $questionId): ?>
            <input type="hidden" name="question_ids[]" value="<?= h((string) $questionId) ?>">
        <?php endforeach; ?>

        <div class="form-actions">
            <button class="button" type="submit"><?= $editingExam ? 'Continuar edição' : 'Ir para montagem' ?></button>
            <a class="ghost-button" href="questions.php">Voltar para questões</a>
        </div>
    </form>
</section>

<section class="panel">
    <h2>Resumo inicial</h2>
    <?php if ($selectedPreview === []): ?>
            <div class="empty-state">
                <h2>Nenhuma questão pré-selecionada</h2>
                <p>Você pode entrar na montagem sem questões e selecionar tudo dentro do workspace.</p>
            </div>
    <?php else: ?>
        <div class="question-list compact-list">
            <?php foreach ($selectedPreview as $question): ?>
                <article class="question-card">
                    <div class="question-meta">
                        <span class="badge"><?= h(question_type_label((string) $question['question_type'])) ?></span>
                        <span class="badge"><?= h($question['discipline_name'] ?? 'Sem disciplina') ?></span>
                    </div>
                    <h3><?= h((string) $question['title']) ?></h3>
                    <p><?= h((string) ($question['subject_name'] ?? 'Sem assunto')) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php render_footer(); ?>
