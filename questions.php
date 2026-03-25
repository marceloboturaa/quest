<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();

$user = current_user();

if (is_post()) {
    abort_if_invalid_csrf();

    $action = (string) ($_POST['action'] ?? 'create_question');

    if ($action === 'create_question') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $prompt = trim((string) ($_POST['prompt'] ?? ''));
        $questionType = (string) ($_POST['question_type'] ?? '');
        $difficulty = (string) ($_POST['difficulty'] ?? 'medio');
        $status = can_manage_all_questions() ? (string) ($_POST['status'] ?? 'draft') : 'draft';
        $discursiveAnswer = null;
        $trueFalseAnswer = null;
        $errors = [];

        if ($title === '' || $prompt === '') {
            $errors[] = 'Titulo e enunciado sao obrigatorios.';
        }

        if (!in_array($questionType, ['multiple_choice', 'discursive', 'true_false'], true)) {
            $errors[] = 'Selecione um tipo de questao valido.';
        }

        if (!in_array($difficulty, ['facil', 'medio', 'dificil'], true)) {
            $errors[] = 'Selecione uma dificuldade valida.';
        }

        if (!in_array($status, ['draft', 'review', 'published'], true)) {
            $status = 'draft';
        }

        $options = [];
        $correctOption = (int) ($_POST['correct_option'] ?? -1);

        if ($questionType === 'multiple_choice') {
            foreach ((array) ($_POST['options'] ?? []) as $index => $option) {
                $value = trim((string) $option);
                if ($value !== '') {
                    $options[] = [
                        'text' => $value,
                        'index' => (int) $index,
                    ];
                }
            }

            if (count($options) < 2) {
                $errors[] = 'Informe pelo menos duas alternativas para multipla escolha.';
            }

            $validIndexes = array_column($options, 'index');
            if (!in_array($correctOption, $validIndexes, true)) {
                $errors[] = 'Selecione a alternativa correta.';
            }
        }

        if ($questionType === 'discursive') {
            $discursiveAnswer = trim((string) ($_POST['discursive_answer'] ?? ''));

            if ($discursiveAnswer === '') {
                $errors[] = 'Informe a resposta esperada da questao discursiva.';
            }
        }

        if ($questionType === 'true_false') {
            $value = (string) ($_POST['true_false_answer'] ?? '');

            if (!in_array($value, ['true', 'false'], true)) {
                $errors[] = 'Selecione se a resposta correta e verdadeiro ou falso.';
            } else {
                $trueFalseAnswer = $value === 'true' ? 1 : 0;
            }
        }

        if ($errors !== []) {
            flash('error', implode(' ', $errors));
            redirect('questions.php');
        }

        db()->beginTransaction();

        try {
            $insertQuestion = db()->prepare(
                'INSERT INTO questions
                    (author_id, title, prompt, question_type, difficulty, status, discursive_answer, true_false_answer, created_at, updated_at)
                 VALUES
                    (:author_id, :title, :prompt, :question_type, :difficulty, :status, :discursive_answer, :true_false_answer, NOW(), NOW())'
            );
            $insertQuestion->execute([
                'author_id' => $user['id'],
                'title' => $title,
                'prompt' => $prompt,
                'question_type' => $questionType,
                'difficulty' => $difficulty,
                'status' => $status,
                'discursive_answer' => $discursiveAnswer,
                'true_false_answer' => $trueFalseAnswer,
            ]);

            $questionId = (int) db()->lastInsertId();

            if ($questionType === 'multiple_choice') {
                $insertOption = db()->prepare(
                    'INSERT INTO question_options
                        (question_id, option_text, is_correct, display_order, created_at)
                     VALUES
                        (:question_id, :option_text, :is_correct, :display_order, NOW())'
                );

                $displayOrder = 1;

                foreach ($options as $option) {
                    $insertOption->execute([
                        'question_id' => $questionId,
                        'option_text' => $option['text'],
                        'is_correct' => $option['index'] === $correctOption ? 1 : 0,
                        'display_order' => $displayOrder,
                    ]);

                    $displayOrder++;
                }
            }

            db()->commit();
            flash('success', 'Questao criada com sucesso.');
        } catch (Throwable $throwable) {
            db()->rollBack();
            flash('error', 'Nao foi possivel criar a questao: ' . $throwable->getMessage());
        }

        redirect('questions.php');
    }

    if ($action === 'update_status' && can_manage_all_questions()) {
        $questionId = (int) ($_POST['question_id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'draft');

        if (!in_array($status, ['draft', 'review', 'published'], true)) {
            flash('error', 'Status invalido.');
            redirect('questions.php');
        }

        $update = db()->prepare('UPDATE questions SET status = :status, updated_at = NOW() WHERE id = :id');
        $update->execute([
            'status' => $status,
            'id' => $questionId,
        ]);

        flash('success', 'Status da questao atualizado.');
        redirect('questions.php');
    }

    if ($action === 'delete_question' && can_manage_all_questions()) {
        $questionId = (int) ($_POST['question_id'] ?? 0);
        $delete = db()->prepare('DELETE FROM questions WHERE id = :id');
        $delete->execute(['id' => $questionId]);
        flash('success', 'Questao removida.');
        redirect('questions.php');
    }
}

$query = 'SELECT questions.*, users.name AS author_name
          FROM questions
          INNER JOIN users ON users.id = questions.author_id';
$params = [];

if (!can_manage_all_questions()) {
    $query .= ' WHERE questions.author_id = :author_id';
    $params['author_id'] = $user['id'];
}

$query .= ' ORDER BY questions.created_at DESC';

$statement = db()->prepare($query);
$statement->execute($params);
$questions = $statement->fetchAll();
$questionOptions = find_question_options(array_map(static fn(array $question): int => (int) $question['id'], $questions));

render_header(
    'Questoes',
    'Cadastre e gerencie questoes de multipla escolha, discursivas e verdadeiro ou falso.'
);
?>
<section class="split-card">
    <section>
        <h2>Nova questao</h2>
        <p class="helper-text">Todos os perfis autenticados podem criar questoes. Admins locais e master admins podem revisar a base inteira.</p>

        <form method="post" class="form-grid" data-question-form>
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="create_question">

            <label>
                Titulo
                <input type="text" name="title" required>
            </label>

            <label>
                Enunciado
                <textarea name="prompt" required></textarea>
            </label>

            <div class="form-grid two-columns">
                <label>
                    Tipo
                    <select name="question_type" required>
                        <option value="multiple_choice">Multipla escolha</option>
                        <option value="discursive">Discursiva</option>
                        <option value="true_false">Verdadeiro ou falso</option>
                    </select>
                </label>

                <label>
                    Dificuldade
                    <select name="difficulty" required>
                        <option value="facil">Facil</option>
                        <option value="medio" selected>Medio</option>
                        <option value="dificil">Dificil</option>
                    </select>
                </label>
            </div>

            <?php if (can_manage_all_questions()): ?>
                <label>
                    Status inicial
                    <select name="status">
                        <option value="draft">Rascunho</option>
                        <option value="review">Em revisao</option>
                        <option value="published">Publicada</option>
                    </select>
                </label>
            <?php endif; ?>

            <div data-question-section="multiple_choice">
                <div class="form-grid">
                    <label>
                        Alternativa A
                        <input type="text" name="options[0]">
                    </label>
                    <label>
                        Alternativa B
                        <input type="text" name="options[1]">
                    </label>
                    <label>
                        Alternativa C
                        <input type="text" name="options[2]">
                    </label>
                    <label>
                        Alternativa D
                        <input type="text" name="options[3]">
                    </label>
                    <label>
                        Alternativa correta
                        <select name="correct_option">
                            <option value="0">A</option>
                            <option value="1">B</option>
                            <option value="2">C</option>
                            <option value="3">D</option>
                        </select>
                    </label>
                </div>
            </div>

            <div class="hidden" data-question-section="discursive">
                <label>
                    Resposta esperada
                    <textarea name="discursive_answer"></textarea>
                </label>
            </div>

            <div class="hidden" data-question-section="true_false">
                <label>
                    Resposta correta
                    <select name="true_false_answer">
                        <option value="true">Verdadeiro</option>
                        <option value="false">Falso</option>
                    </select>
                </label>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">Salvar questao</button>
            </div>
        </form>
    </section>

    <section>
        <h2>Regras de acesso</h2>
        <ul class="mini-list">
            <li><strong>Master admin:</strong> controla usuarios, perfis e toda a base de questoes.</li>
            <li><strong>Admin local:</strong> revisa, publica e remove questoes da operacao local.</li>
            <li><strong>Usuario:</strong> cria e acompanha apenas suas proprias questoes.</li>
        </ul>

        <div class="panel" style="margin-top: 20px;">
            <h2>Dicas de uso</h2>
            <p class="helper-text">Para testar o fluxo completo, crie usuarios em <code>register.php</code> e promova um deles em <code>users.php</code> com o master admin.</p>
        </div>
    </section>
</section>

<?php if ($questions === []): ?>
    <section class="empty-state">
        <h2>Nenhuma questao cadastrada</h2>
        <p>Crie a primeira questao usando o formulario acima.</p>
    </section>
<?php else: ?>
    <section class="question-list">
        <?php foreach ($questions as $question): ?>
            <article class="question-card">
                <div class="question-meta">
                    <span class="badge"><?= h(question_type_label($question['question_type'])) ?></span>
                    <span class="badge"><?= h(ucfirst($question['difficulty'])) ?></span>
                    <span class="badge <?= $question['status'] === 'published' ? 'badge-success' : ($question['status'] === 'review' ? 'badge-accent' : '') ?>">
                        <?= h(status_label($question['status'])) ?>
                    </span>
                    <span>Autor: <?= h($question['author_name']) ?></span>
                    <span>Criada em <?= h(date('d/m/Y H:i', strtotime((string) $question['created_at']))) ?></span>
                </div>

                <h3><?= h($question['title']) ?></h3>
                <p><?= nl2br(h($question['prompt'])) ?></p>

                <?php if ($question['question_type'] === 'multiple_choice'): ?>
                    <ul class="option-list">
                        <?php foreach ($questionOptions[(int) $question['id']] ?? [] as $option): ?>
                            <li class="<?= (int) $option['is_correct'] === 1 ? 'correct' : '' ?>">
                                <?= h($option['option_text']) ?>
                                <?php if ((int) $option['is_correct'] === 1): ?>
                                    <strong> - correta</strong>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php elseif ($question['question_type'] === 'discursive'): ?>
                    <p><strong>Resposta esperada:</strong> <?= nl2br(h($question['discursive_answer'] ?? '')) ?></p>
                <?php else: ?>
                    <p><strong>Resposta correta:</strong> <?= (int) $question['true_false_answer'] === 1 ? 'Verdadeiro' : 'Falso' ?></p>
                <?php endif; ?>

                <?php if (can_manage_all_questions()): ?>
                    <div class="question-actions">
                        <form method="post" class="inline-actions">
                            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="question_id" value="<?= h((string) $question['id']) ?>">
                            <select name="status">
                                <option value="draft" <?= $question['status'] === 'draft' ? 'selected' : '' ?>>Rascunho</option>
                                <option value="review" <?= $question['status'] === 'review' ? 'selected' : '' ?>>Em revisao</option>
                                <option value="published" <?= $question['status'] === 'published' ? 'selected' : '' ?>>Publicada</option>
                            </select>
                            <button class="button-secondary" type="submit">Atualizar status</button>
                        </form>

                        <form method="post">
                            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                            <input type="hidden" name="action" value="delete_question">
                            <input type="hidden" name="question_id" value="<?= h((string) $question['id']) ?>">
                            <button class="button-danger" type="submit" onclick="return confirm('Excluir esta questao?')">Excluir</button>
                        </form>
                    </div>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
<?php render_footer(); ?>
