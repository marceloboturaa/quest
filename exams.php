<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_login();

$user = current_user();
$userId = (int) $user['id'];
$preselectedQuestionId = (int) ($_GET['question_id'] ?? 0);

if (is_post()) {
    abort_if_invalid_csrf();

    if ((string) ($_POST['action'] ?? '') === 'create_exam') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $instructions = trim((string) ($_POST['instructions'] ?? ''));
        $questionIds = array_values(array_unique(array_map('intval', (array) ($_POST['question_ids'] ?? []))));

        if ($title === '') {
            flash('error', 'Informe o titulo da prova.');
            redirect('exams.php');
        }

        if ($questionIds === []) {
            flash('error', 'Selecione pelo menos uma questao.');
            redirect('exams.php');
        }

        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $params = array_merge($questionIds, [$userId]);
        $visible = db()->prepare(
            "SELECT id FROM questions WHERE id IN ($placeholders) AND (visibility = 'public' OR author_id = ?)"
        );
        $visible->execute($params);
        $visibleIds = array_map(static fn(array $row): int => (int) $row['id'], $visible->fetchAll());

        if (count($visibleIds) !== count($questionIds)) {
            flash('error', 'Uma ou mais questoes selecionadas nao podem ser usadas.');
            redirect('exams.php');
        }

        db()->beginTransaction();

        try {
            $insertExam = db()->prepare(
                'INSERT INTO exams (user_id, title, instructions, created_at, updated_at)
                 VALUES (:user_id, :title, :instructions, NOW(), NOW())'
            );
            $insertExam->execute([
                'user_id' => $userId,
                'title' => $title,
                'instructions' => $instructions !== '' ? $instructions : null,
            ]);

            $examId = (int) db()->lastInsertId();
            $insertQuestion = db()->prepare(
                'INSERT INTO exam_questions (exam_id, question_id, display_order, created_at)
                 VALUES (:exam_id, :question_id, :display_order, NOW())'
            );

            foreach ($questionIds as $index => $questionId) {
                $insertQuestion->execute([
                    'exam_id' => $examId,
                    'question_id' => $questionId,
                    'display_order' => $index + 1,
                ]);
            }

            $updateUsage = db()->prepare("UPDATE questions SET usage_count = usage_count + 1 WHERE id IN ($placeholders)");
            $updateUsage->execute($questionIds);

            db()->commit();
            flash('success', 'Prova criada com sucesso.');
        } catch (Throwable $throwable) {
            db()->rollBack();
            flash('error', 'Falha ao criar prova: ' . $throwable->getMessage());
        }

        redirect('exams.php');
    }
}

$availableQuestions = db()->prepare(
    'SELECT questions.id, questions.title, questions.question_type, questions.visibility, questions.usage_count,
            disciplines.name AS discipline_name, subjects.name AS subject_name, users.name AS author_name
     FROM questions
     INNER JOIN users ON users.id = questions.author_id
     LEFT JOIN disciplines ON disciplines.id = questions.discipline_id
     LEFT JOIN subjects ON subjects.id = questions.subject_id
     WHERE questions.visibility = "public" OR questions.author_id = :author_id
     ORDER BY questions.created_at DESC'
);
$availableQuestions->execute(['author_id' => $userId]);
$availableQuestions = $availableQuestions->fetchAll();

$exams = db()->prepare(
    'SELECT exams.*, COUNT(exam_questions.id) AS total_questions
     FROM exams
     LEFT JOIN exam_questions ON exam_questions.exam_id = exams.id
     WHERE exams.user_id = :user_id
     GROUP BY exams.id
     ORDER BY exams.created_at DESC'
);
$exams->execute(['user_id' => $userId]);
$exams = $exams->fetchAll();

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
                                    Autor: <?= h($question['author_name']) ?> |
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
        <p>A montagem inicial de provas ja funciona. Exportacao PDF e refinamentos de impressao ainda ficam para a proxima etapa.</p>
    </article>
</section>
<?php render_footer(); ?>
