<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/question_repository.php';
require_once __DIR__ . '/includes/exam_metadata.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$selectedIds = array_values(array_unique(array_map('intval', (array) ($_GET['question_ids'] ?? []))));
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
    'Nova prova',
    'Defina os metadados da avaliacao antes de entrar na montagem das questoes.'
);
?>

<section class="split-card">
    <section>
        <h2>Dados da avaliacao</h2>
        <form method="get" action="exams.php" class="form-grid">
            <label>Nome da prova
                <input type="text" name="draft_title" required placeholder="Ex.: Simulado bimestral de Matematica">
            </label>

            <div class="form-grid two-columns">
                <label>Formato da prova
                    <select name="exam_style">
                        <?php foreach (exam_style_options() as $styleValue => $styleLabel): ?>
                            <option value="<?= h($styleValue) ?>"><?= h($styleLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Titulo do cabecalho
                    <input type="text" name="exam_label" value="AVALIACAO" placeholder="Ex.: AVALIACAO TRIMESTRAL">
                </label>
            </div>

            <div class="form-grid two-columns">
                <label>Disciplina
                    <input type="text" name="discipline" placeholder="Ex.: Matematica / 6o ano">
                </label>
                <label>Comp. Curricular
                    <input type="text" name="component_name" placeholder="Ex.: Matematica">
                </label>
            </div>

            <div class="form-grid two-columns">
                <label>Professor
                    <input type="text" name="teacher_name" value="<?= h($user['name'] ?? '') ?>">
                </label>
                <label>Escola
                    <input type="text" name="school_name" placeholder="Nome da escola">
                </label>
            </div>

            <div class="form-grid two-columns">
                <label>Ano / Serie
                    <input type="text" name="year_reference" placeholder="Ex.: 6o ano">
                </label>
                <label>Turma
                    <input type="text" name="class_name" placeholder="Ex.: 6A">
                </label>
            </div>

            <div class="form-grid two-columns">
                <label>Data
                    <input type="date" name="application_date">
                </label>
                <div></div>
            </div>

            <label>Instrucoes
                <textarea name="draft_instructions" placeholder="Orientacoes para o aluno, materiais permitidos, tempo e observacoes."></textarea>
            </label>

            <?php foreach ($selectedIds as $questionId): ?>
                <input type="hidden" name="question_ids[]" value="<?= h((string) $questionId) ?>">
            <?php endforeach; ?>

            <div class="form-actions">
                <button class="button" type="submit">Ir para montagem</button>
                <a class="ghost-button" href="questions.php">Voltar para questoes</a>
            </div>
        </form>
    </section>

    <section>
        <h2>Resumo inicial</h2>
        <?php if ($selectedPreview === []): ?>
            <div class="empty-state">
                <h2>Nenhuma questao preselecionada</h2>
                <p>Voce pode entrar na montagem sem questoes e selecionar tudo dentro do workspace.</p>
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
</section>

<?php render_footer(); ?>
