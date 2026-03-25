<?php
declare(strict_types=1);

function question_redirect(?int $editId = null): never
{
    redirect('questions.php' . ($editId ? '?edit=' . $editId : ''));
}

function handle_question_request(int $userId): void
{
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create_discipline' && can_manage_catalogs()) {
        question_create_discipline($userId);
    }

    if ($action === 'create_subject' && can_manage_catalogs()) {
        question_create_subject($userId);
    }

    if ($action === 'update_discipline' && can_manage_catalogs()) {
        question_update_discipline();
    }

    if ($action === 'delete_discipline' && can_manage_catalogs()) {
        question_delete_discipline();
    }

    if ($action === 'update_subject' && can_manage_catalogs()) {
        question_update_subject();
    }

    if ($action === 'delete_subject' && can_manage_catalogs()) {
        question_delete_subject();
    }

    if ($action === 'toggle_favorite') {
        question_toggle_favorite($userId);
    }

    if ($action === 'clone_question') {
        question_clone($userId);
    }

    if ($action === 'delete_question') {
        question_delete($userId);
    }

    if (in_array($action, ['create_question', 'update_question'], true)) {
        question_save($userId, $action === 'update_question');
    }
}

function question_create_discipline(int $userId): never
{
    $name = trim((string) ($_POST['discipline_name'] ?? ''));

    if ($name === '') {
        flash('error', 'Informe a disciplina.');
        question_redirect();
    }

    $insert = db()->prepare('INSERT IGNORE INTO disciplines (name, created_by, created_at) VALUES (:name, :created_by, NOW())');
    $insert->execute(['name' => $name, 'created_by' => $userId]);
    flash('success', 'Disciplina cadastrada.');
    question_redirect();
}

function question_create_subject(int $userId): never
{
    $disciplineId = (int) ($_POST['discipline_id'] ?? 0);
    $name = trim((string) ($_POST['subject_name'] ?? ''));

    if ($disciplineId <= 0 || $name === '') {
        flash('error', 'Informe disciplina e assunto.');
        question_redirect();
    }

    $insert = db()->prepare('INSERT IGNORE INTO subjects (discipline_id, name, created_by, created_at) VALUES (:discipline_id, :name, :created_by, NOW())');
    $insert->execute([
        'discipline_id' => $disciplineId,
        'name' => $name,
        'created_by' => $userId,
    ]);
    flash('success', 'Assunto cadastrado.');
    question_redirect();
}

function question_update_discipline(): never
{
    $disciplineId = (int) ($_POST['discipline_id'] ?? 0);
    $name = trim((string) ($_POST['discipline_name'] ?? ''));

    if ($disciplineId <= 0 || $name === '') {
        flash('error', 'Informe uma disciplina valida.');
        question_redirect();
    }

    try {
        $update = db()->prepare('UPDATE disciplines SET name = :name WHERE id = :id');
        $update->execute(['name' => $name, 'id' => $disciplineId]);
        flash('success', 'Disciplina atualizada.');
    } catch (Throwable) {
        flash('error', 'Nao foi possivel atualizar a disciplina.');
    }

    question_redirect();
}

function question_delete_discipline(): never
{
    $disciplineId = (int) ($_POST['discipline_id'] ?? 0);

    if ($disciplineId <= 0) {
        flash('error', 'Disciplina invalida.');
        question_redirect();
    }

    $delete = db()->prepare('DELETE FROM disciplines WHERE id = :id');
    $delete->execute(['id' => $disciplineId]);
    flash('success', 'Disciplina removida.');
    question_redirect();
}

function question_update_subject(): never
{
    $subjectId = (int) ($_POST['subject_id'] ?? 0);
    $disciplineId = (int) ($_POST['discipline_id'] ?? 0);
    $name = trim((string) ($_POST['subject_name'] ?? ''));

    if ($subjectId <= 0 || $disciplineId <= 0 || $name === '') {
        flash('error', 'Informe um assunto valido.');
        question_redirect();
    }

    try {
        $update = db()->prepare('UPDATE subjects SET discipline_id = :discipline_id, name = :name WHERE id = :id');
        $update->execute([
            'discipline_id' => $disciplineId,
            'name' => $name,
            'id' => $subjectId,
        ]);
        flash('success', 'Assunto atualizado.');
    } catch (Throwable) {
        flash('error', 'Nao foi possivel atualizar o assunto.');
    }

    question_redirect();
}

function question_delete_subject(): never
{
    $subjectId = (int) ($_POST['subject_id'] ?? 0);

    if ($subjectId <= 0) {
        flash('error', 'Assunto invalido.');
        question_redirect();
    }

    $delete = db()->prepare('DELETE FROM subjects WHERE id = :id');
    $delete->execute(['id' => $subjectId]);
    flash('success', 'Assunto removido.');
    question_redirect();
}

function question_toggle_favorite(int $userId): never
{
    $questionId = (int) ($_POST['question_id'] ?? 0);
    $question = visible_question_row($questionId, $userId);

    if (!$question) {
        flash('error', 'Questao nao encontrada.');
        question_redirect();
    }

    $statement = db()->prepare('SELECT id FROM question_favorites WHERE question_id = :question_id AND user_id = :user_id LIMIT 1');
    $statement->execute(['question_id' => $questionId, 'user_id' => $userId]);
    $favorite = $statement->fetch();

    if ($favorite) {
        $delete = db()->prepare('DELETE FROM question_favorites WHERE id = :id');
        $delete->execute(['id' => $favorite['id']]);
        flash('success', 'Questao removida dos favoritos.');
    } else {
        $insert = db()->prepare('INSERT INTO question_favorites (question_id, user_id, created_at) VALUES (:question_id, :user_id, NOW())');
        $insert->execute(['question_id' => $questionId, 'user_id' => $userId]);
        flash('success', 'Questao favoritada.');
    }

    question_redirect();
}

function question_clone(int $userId): never
{
    $questionId = (int) ($_POST['question_id'] ?? 0);
    $source = visible_question_row($questionId, $userId);

    if (!$source || ($source['visibility'] !== 'public' && (int) $source['author_id'] !== $userId)) {
        flash('error', 'Questao nao pode ser clonada.');
        question_redirect();
    }

    $originId = $source['based_on_question_id'] ? (int) $source['based_on_question_id'] : (int) $source['id'];
    db()->beginTransaction();

    try {
        $insert = db()->prepare(
            'INSERT INTO questions
             (author_id,based_on_question_id,title,prompt,prompt_image_url,question_type,visibility,discipline_id,subject_id,education_level,difficulty,status,allow_multiple_correct,discursive_answer,response_lines,drawing_size,drawing_height_px,true_false_answer,source_name,source_url,source_reference,usage_count,created_at,updated_at)
             VALUES
             (:author_id,:based_on_question_id,:title,:prompt,:prompt_image_url,:question_type,:visibility,:discipline_id,:subject_id,:education_level,:difficulty,:status,:allow_multiple_correct,:discursive_answer,:response_lines,:drawing_size,:drawing_height_px,:true_false_answer,:source_name,:source_url,:source_reference,0,NOW(),NOW())'
        );
        $insert->execute([
            'author_id' => $userId,
            'based_on_question_id' => $originId,
            'title' => $source['title'] . ' (copia)',
            'prompt' => $source['prompt'],
            'prompt_image_url' => $source['prompt_image_url'],
            'question_type' => $source['question_type'],
            'visibility' => 'private',
            'discipline_id' => $source['discipline_id'],
            'subject_id' => $source['subject_id'],
            'education_level' => $source['education_level'],
            'difficulty' => $source['difficulty'],
            'status' => $source['status'],
            'allow_multiple_correct' => $source['allow_multiple_correct'],
            'discursive_answer' => $source['discursive_answer'],
            'response_lines' => $source['response_lines'],
            'drawing_size' => $source['drawing_size'],
            'drawing_height_px' => $source['drawing_height_px'],
            'true_false_answer' => $source['true_false_answer'],
            'source_name' => $source['source_name'],
            'source_url' => $source['source_url'],
            'source_reference' => $source['source_reference'],
        ]);

        $newId = (int) db()->lastInsertId();
        $statement = db()->prepare(
            'SELECT option_text, is_correct, display_order
             FROM question_options
             WHERE question_id = :question_id
             ORDER BY display_order ASC'
        );
        $statement->execute(['question_id' => $questionId]);

        foreach ($statement->fetchAll() as $row) {
            $insertOption = db()->prepare(
                'INSERT INTO question_options (question_id, option_text, is_correct, display_order, created_at)
                 VALUES (:question_id, :option_text, :is_correct, :display_order, NOW())'
            );
            $insertOption->execute([
                'question_id' => $newId,
                'option_text' => $row['option_text'],
                'is_correct' => $row['is_correct'],
                'display_order' => $row['display_order'],
            ]);
        }

        db()->commit();
        flash('success', 'Questao clonada como privada.');
    } catch (Throwable $throwable) {
        db()->rollBack();
        flash('error', 'Falha ao clonar: ' . $throwable->getMessage());
    }

    question_redirect();
}

function question_delete(int $userId): never
{
    $questionId = (int) ($_POST['question_id'] ?? 0);
    $question = own_question($questionId, $userId);

    if (!$question) {
        flash('error', 'Somente o autor pode excluir.');
        question_redirect();
    }

    $delete = db()->prepare('DELETE FROM questions WHERE id = :id');
    $delete->execute(['id' => $questionId]);
    flash('success', 'Questao excluida.');
    question_redirect();
}

function question_save(int $userId, bool $isUpdate): never
{
    $questionId = (int) ($_POST['question_id'] ?? 0);
    $editing = $isUpdate ? own_question($questionId, $userId) : null;

    if ($isUpdate && !$editing) {
        flash('error', 'Questao nao encontrada.');
        question_redirect();
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    $prompt = trim((string) ($_POST['prompt'] ?? ''));
    $promptImageUrl = trim((string) ($_POST['prompt_image_url'] ?? ''));
    $type = (string) ($_POST['question_type'] ?? '');
    $visibility = (string) ($_POST['visibility'] ?? 'private');
    $disciplineId = (int) ($_POST['discipline_id'] ?? 0);
    $subjectId = (int) ($_POST['subject_id'] ?? 0);
    $level = (string) ($_POST['education_level'] ?? 'medio');
    $difficulty = (string) ($_POST['difficulty'] ?? 'medio');
    $allowMulti = !empty($_POST['allow_multiple_correct']) ? 1 : 0;
    $discursiveAnswer = trim((string) ($_POST['discursive_answer'] ?? ''));
    $responseLines = (int) ($_POST['response_lines'] ?? 5);
    $drawingSize = (string) ($_POST['drawing_size'] ?? 'medium');
    $drawingHeightPx = (int) ($_POST['drawing_height_px'] ?? 0);
    $trueFalseAnswer = isset($_POST['true_false_answer']) && in_array((string) $_POST['true_false_answer'], ['0', '1'], true)
        ? (int) $_POST['true_false_answer']
        : null;
    $officialSourceKey = trim((string) ($_POST['official_source_key'] ?? ''));
    $sourceReference = trim((string) ($_POST['source_reference'] ?? ''));
    $options = parsed_options((array) ($_POST['options'] ?? []));
    $officialSources = function_exists('official_question_sources') ? official_question_sources() : [];
    $sourceName = null;
    $sourceUrl = null;
    $errors = [];

    if ($officialSourceKey !== '') {
        if (!array_key_exists($officialSourceKey, $officialSources)) {
            $errors[] = 'Fonte oficial invalida.';
        } else {
            $sourceName = $officialSources[$officialSourceKey]['name'];
            $sourceUrl = $officialSources[$officialSourceKey]['url'];
        }
    }

    if ($title === '' || $prompt === '') {
        $errors[] = 'Titulo e enunciado sao obrigatorios.';
    }

    if ($promptImageUrl !== '' && !filter_var($promptImageUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'Imagem deve ser uma URL valida.';
    }

    if (!in_array($type, ['multiple_choice', 'discursive', 'drawing', 'true_false'], true)) {
        $errors[] = 'Tipo de questao invalido.';
    }

    if (!in_array($visibility, ['private', 'public'], true)) {
        $errors[] = 'Visibilidade invalida.';
    }

    if ($disciplineId <= 0 || $subjectId <= 0 || !belongs_subject($subjectId, $disciplineId)) {
        $errors[] = 'Disciplina e assunto precisam ser validos.';
    }

    if (!in_array($level, ['fundamental', 'medio', 'tecnico', 'superior'], true)) {
        $errors[] = 'Nivel invalido.';
    }

    if (!in_array($difficulty, ['facil', 'medio', 'dificil'], true)) {
        $errors[] = 'Dificuldade invalida.';
    }

    if ($type === 'multiple_choice') {
        $correctCount = count(array_filter($options, static fn(array $option): bool => (int) $option['is_correct'] === 1));

        if (count($options) < 2) {
            $errors[] = 'Informe pelo menos duas alternativas.';
        }

        if ($correctCount === 0) {
            $errors[] = 'Marque ao menos uma alternativa correta.';
        }

        if ($allowMulti === 0 && $correctCount > 1) {
            $errors[] = 'Sem multiplas corretas, marque apenas uma alternativa.';
        }
    } elseif ($type === 'true_false') {
        $allowMulti = 0;
        $options = [];

        if ($trueFalseAnswer === null) {
            $errors[] = 'Informe a resposta correta de verdadeiro ou falso.';
        }
    } else {
        $allowMulti = 0;
        $options = [];
    }

    if ($type === 'discursive' && $responseLines < 1) {
        $errors[] = 'Numero de linhas invalido.';
    }

    if ($type !== 'discursive') {
        $responseLines = null;
        $discursiveAnswer = '';
    }

    if ($type === 'drawing') {
        if (!in_array($drawingSize, ['small', 'medium', 'large', 'custom'], true)) {
            $errors[] = 'Tamanho do espaco invalido.';
        }

        if ($drawingSize === 'custom') {
            if ($drawingHeightPx < 120 || $drawingHeightPx > 1200) {
                $errors[] = 'Altura customizada deve ficar entre 120 e 1200 pixels.';
            }
        } else {
            $drawingHeightPx = null;
        }
    } else {
        $drawingSize = null;
        $drawingHeightPx = null;
    }

    if ($type !== 'true_false') {
        $trueFalseAnswer = null;
    }

    if ($errors !== []) {
        flash('error', implode(' ', $errors));
        question_redirect($editing ? (int) $editing['id'] : null);
    }

    db()->beginTransaction();

    try {
        if ($editing) {
            $update = db()->prepare(
                'UPDATE questions SET
                 title = :title,
                 prompt = :prompt,
                 prompt_image_url = :prompt_image_url,
                 question_type = :question_type,
                 visibility = :visibility,
                 discipline_id = :discipline_id,
                 subject_id = :subject_id,
                 education_level = :education_level,
                 difficulty = :difficulty,
                 allow_multiple_correct = :allow_multiple_correct,
                 discursive_answer = :discursive_answer,
                 response_lines = :response_lines,
                 drawing_size = :drawing_size,
                 drawing_height_px = :drawing_height_px,
                 true_false_answer = :true_false_answer,
                 source_name = :source_name,
                 source_url = :source_url,
                 source_reference = :source_reference,
                 updated_at = NOW()
                 WHERE id = :id AND author_id = :author_id'
            );
            $update->execute([
                'title' => $title,
                'prompt' => $prompt,
                'prompt_image_url' => $promptImageUrl !== '' ? $promptImageUrl : null,
                'question_type' => $type,
                'visibility' => $visibility,
                'discipline_id' => $disciplineId,
                'subject_id' => $subjectId,
                'education_level' => $level,
                'difficulty' => $difficulty,
                'allow_multiple_correct' => $allowMulti,
                'discursive_answer' => $discursiveAnswer !== '' ? $discursiveAnswer : null,
                'response_lines' => $responseLines,
                'drawing_size' => $drawingSize,
                'drawing_height_px' => $drawingHeightPx,
                'true_false_answer' => $trueFalseAnswer,
                'source_name' => $sourceName,
                'source_url' => $sourceUrl,
                'source_reference' => $sourceReference !== '' ? $sourceReference : null,
                'id' => $editing['id'],
                'author_id' => $userId,
            ]);

            $questionId = (int) $editing['id'];
            $deleteOptions = db()->prepare('DELETE FROM question_options WHERE question_id = :question_id');
            $deleteOptions->execute(['question_id' => $questionId]);
        } else {
            $insert = db()->prepare(
                'INSERT INTO questions
                 (author_id, based_on_question_id, title, prompt, prompt_image_url, question_type, visibility, discipline_id, subject_id, education_level, difficulty, status, allow_multiple_correct, discursive_answer, response_lines, drawing_size, drawing_height_px, true_false_answer, source_name, source_url, source_reference, usage_count, created_at, updated_at)
                 VALUES
                 (:author_id, NULL, :title, :prompt, :prompt_image_url, :question_type, :visibility, :discipline_id, :subject_id, :education_level, :difficulty, :status, :allow_multiple_correct, :discursive_answer, :response_lines, :drawing_size, :drawing_height_px, :true_false_answer, :source_name, :source_url, :source_reference, 0, NOW(), NOW())'
            );
            $insert->execute([
                'author_id' => $userId,
                'title' => $title,
                'prompt' => $prompt,
                'prompt_image_url' => $promptImageUrl !== '' ? $promptImageUrl : null,
                'question_type' => $type,
                'visibility' => $visibility,
                'discipline_id' => $disciplineId,
                'subject_id' => $subjectId,
                'education_level' => $level,
                'difficulty' => $difficulty,
                'status' => 'published',
                'allow_multiple_correct' => $allowMulti,
                'discursive_answer' => $discursiveAnswer !== '' ? $discursiveAnswer : null,
                'response_lines' => $responseLines,
                'drawing_size' => $drawingSize,
                'drawing_height_px' => $drawingHeightPx,
                'true_false_answer' => $trueFalseAnswer,
                'source_name' => $sourceName,
                'source_url' => $sourceUrl,
                'source_reference' => $sourceReference !== '' ? $sourceReference : null,
            ]);

            $questionId = (int) db()->lastInsertId();
        }

        if ($type === 'multiple_choice') {
            $insertOption = db()->prepare(
                'INSERT INTO question_options (question_id, option_text, is_correct, display_order, created_at)
                 VALUES (:question_id, :option_text, :is_correct, :display_order, NOW())'
            );

            foreach (array_values($options) as $index => $option) {
                $insertOption->execute([
                    'question_id' => $questionId,
                    'option_text' => $option['text'],
                    'is_correct' => $option['is_correct'],
                    'display_order' => $index + 1,
                ]);
            }
        }

        db()->commit();
        flash('success', $editing ? 'Questao atualizada.' : 'Questao criada.');
    } catch (Throwable $throwable) {
        db()->rollBack();
        flash('error', 'Falha ao salvar questao: ' . $throwable->getMessage());
    }

    question_redirect();
}
