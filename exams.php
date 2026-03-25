<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_actions.php';
require_login();

$user = current_user();
$userId = (int) $user['id'];
$preselectedQuestionId = (int) ($_GET['question_id'] ?? 0);

if (is_post()) {
    abort_if_invalid_csrf();
    handle_exam_request($userId);
}

$availableQuestions = exam_available_questions($userId);
$exams = exam_list($userId);

render_header('Montagem de provas', 'Selecione questoes visiveis no banco e monte provas misturando tipos diferentes.');
?>
<section class="split-card">
    <section>
        <h2>Nova prova</h2>
        <p class="helper-text">A cada inclusao em prova, o contador de uso da questao e incrementado.</p>

        <form method="post" class="form-grid">
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="create_exam">

            <label>
                Titulo da prova
                <input type="text" name="title" required>
            </label>

            <label>
                Instrucoes (opcional)
                <textarea name="instructions"></textarea>
            </label>

            <?php if ($availableQuestions === []): ?>
                <div class="empty-state">
                    <h2>Nenhuma questao disponivel</h2>
                    <p>Crie questoes ou torne questoes publicas para comecar a montar provas.</p>
                </div>
            <?php else: ?>
                <div class="question-pick-list">
                    <?php foreach ($availableQuestions as $question): ?>
                        <label class="question-pick-item">
                            <input type="checkbox" name="question_ids[]" value="<?= h((string) $question['id']) ?>" <?= $preselectedQuestionId === (int) $question['id'] ? 'checked' : '' ?>>
                            <span>
                                <strong><?= h($question['title']) ?></strong>
                                 <small>
                                     <?= h(question_type_label($question['question_type'])) ?> |
                                     <?= h($question['discipline_name'] ?? 'Sem disciplina') ?> |
                                     <?= h($question['subject_name'] ?? 'Sem assunto') ?> |
                                     <?= !empty($question['source_name']) ? 'Fonte: ' . h($question['source_name']) : 'Autor: ' . h($question['author_name']) ?> |
                                     Uso: <?= h((string) $question['usage_count']) ?>
                                 </small>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button class="button" type="submit">Salvar prova</button>
            </div>
        </form>
    </section>

    <section>
        <h2>Minhas provas</h2>

        <?php if ($exams === []): ?>
            <div class="empty-state">
                <h2>Nenhuma prova criada</h2>
                <p>Selecione questoes e gere a primeira prova.</p>
            </div>
        <?php else: ?>
            <div class="question-list">
                <?php foreach ($exams as $exam): ?>
                    <article class="question-card">
                        <div class="question-meta">
                            <span class="badge"><?= h((string) $exam['total_questions']) ?> questoes</span>
                            <span>Criada em <?= h(date('d/m/Y H:i', strtotime((string) $exam['created_at']))) ?></span>
                        </div>
                        <h3><?= h($exam['title']) ?></h3>
                        <?php if (!empty($exam['instructions'])): ?>
                            <p><?= nl2br(h($exam['instructions'])) ?></p>
                        <?php else: ?>
                            <p class="helper-text">Sem instrucoes adicionais.</p>
                        <?php endif; ?>
                        <div class="form-actions">
                            <a class="button-secondary" href="exam-pdf.php?id=<?= h((string) $exam['id']) ?>">Exportar PDF</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>

<section class="info-grid">
    <article class="panel">
        <h2>Como usar</h2>
        <ul class="mini-list">
            <li>Selecione questoes visiveis no banco.</li>
            <li>Misture tipos diferentes na mesma prova.</li>
            <li>Salve o conjunto para reaproveitar a estrutura.</li>
        </ul>
    </article>

    <article class="panel">
        <h2>Estado atual</h2>
        <p>A montagem de provas ja funciona com exportacao PDF basica para distribuicao e impressao.</p>
    </article>
</section>
<?php render_footer(); ?>
