<?php
declare(strict_types=1);

function question_redirect(?int $editId = null): never
{
    redirect('question-bank.php' . ($editId ? '?edit=' . $editId : ''));
}

function question_redirect_query(string $query = ''): never
{
    $query = ltrim(trim($query), '?');
    redirect('question-bank.php' . ($query !== '' ? '?' . $query : ''));
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

    if ($action === 'bulk_delete_questions') {
        question_bulk_delete_selected($userId);
    }

    if ($action === 'delete_all_questions') {
        question_delete_all_questions($userId);
    }

    if ($action === 'import_enem_question') {
        question_import_enem($userId);
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
        flash('error', 'Informe uma disciplina válida.');
        question_redirect();
    }

    try {
        $update = db()->prepare('UPDATE disciplines SET name = :name WHERE id = :id');
        $update->execute(['name' => $name, 'id' => $disciplineId]);
        flash('success', 'Disciplina atualizada.');
    } catch (Throwable) {
        flash('error', 'Não foi possível atualizar a disciplina.');
    }

    question_redirect();
}

function question_delete_discipline(): never
{
    $disciplineId = (int) ($_POST['discipline_id'] ?? 0);

    if ($disciplineId <= 0) {
        flash('error', 'Disciplina inválida.');
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
        flash('error', 'Informe um assunto válido.');
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
        flash('error', 'Não foi possível atualizar o assunto.');
    }

    question_redirect();
}

function question_delete_subject(): never
{
    $subjectId = (int) ($_POST['subject_id'] ?? 0);

    if ($subjectId <= 0) {
        flash('error', 'Assunto inválido.');
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
        flash('error', 'Questão não encontrada.');
        question_redirect();
    }

    $statement = db()->prepare('SELECT id FROM question_favorites WHERE question_id = :question_id AND user_id = :user_id LIMIT 1');
    $statement->execute(['question_id' => $questionId, 'user_id' => $userId]);
    $favorite = $statement->fetch();

    if ($favorite) {
        $delete = db()->prepare('DELETE FROM question_favorites WHERE id = :id');
        $delete->execute(['id' => $favorite['id']]);
        flash('success', 'Questão removida dos favoritos.');
    } else {
        $insert = db()->prepare('INSERT INTO question_favorites (question_id, user_id, created_at) VALUES (:question_id, :user_id, NOW())');
        $insert->execute(['question_id' => $questionId, 'user_id' => $userId]);
        flash('success', 'Questão favoritada.');
    }

    question_redirect();
}

function question_clone(int $userId): never
{
    $questionId = (int) ($_POST['question_id'] ?? 0);
    $source = visible_question_row($questionId, $userId);

    if (!$source || ($source['visibility'] !== 'public' && (int) $source['author_id'] !== $userId)) {
        flash('error', 'Esta questão não pode ser clonada.');
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
            'title' => $source['title'] . ' (cópia)',
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
        flash('success', 'Questão clonada como privada.');
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
        flash('error', can_manage_all_questions() ? 'Questão não encontrada.' : 'Somente o autor pode excluir.');
        question_redirect();
    }

    $delete = db()->prepare('DELETE FROM questions WHERE id = :id');
    $delete->execute(['id' => $questionId]);
    flash('success', 'Questão excluída.');
    question_redirect();
}

function question_posted_question_ids(): array
{
    $ids = $_POST['question_ids'] ?? [];

    if (!is_array($ids)) {
        return [];
    }

    $ids = array_map(static fn(mixed $value): int => (int) $value, $ids);
    $ids = array_values(array_unique(array_filter($ids, static fn(int $id): bool => $id > 0)));

    return $ids;
}

function question_bulk_delete_selected(int $userId): never
{
    if (!can_manage_question_bulk_deletion()) {
        flash('error', 'A exclusão em massa é restrita ao master admin.');
        question_redirect();
    }

    $questionIds = question_posted_question_ids();

    if ($questionIds === []) {
        flash('error', 'Selecione ao menos uma questão para excluir.');
        question_redirect();
    }

    $placeholders = implode(',', array_fill(0, count($questionIds), '?'));

    db()->beginTransaction();

    try {
        $delete = db()->prepare('DELETE FROM questions WHERE id IN (' . $placeholders . ')');
        $delete->execute($questionIds);
        db()->commit();

        flash('success', count($questionIds) . ' questão(ões) excluída(s).');
    } catch (Throwable $throwable) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }

        flash('error', 'Falha ao excluir as questões selecionadas: ' . $throwable->getMessage());
    }

    question_redirect();
}

function question_delete_all_questions(int $userId): never
{
    if (!can_manage_question_bulk_deletion()) {
        flash('error', 'A exclusão em massa é restrita ao master admin.');
        question_redirect();
    }

    db()->beginTransaction();

    try {
        $total = (int) db()->query('SELECT COUNT(*) FROM questions')->fetchColumn();

        if ($total <= 0) {
            db()->rollBack();
            flash('error', 'Não há questões para excluir.');
            question_redirect();
        }

        db()->exec('DELETE FROM questions');
        db()->commit();

        flash('success', 'Todas as questões foram excluídas.');
    } catch (Throwable $throwable) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }

        flash('error', 'Falha ao excluir todas as questões: ' . $throwable->getMessage());
    }

    question_redirect();
}

function question_save(int $userId, bool $isUpdate): never
{
    $questionId = (int) ($_POST['question_id'] ?? 0);
    $editing = $isUpdate ? own_question($questionId, $userId) : null;
    $canManageAll = can_manage_all_questions();

    if ($isUpdate && !$editing) {
        flash('error', 'Questão não encontrada.');
        redirect('question-bank.php');
    }

    $title = question_normalize_editor_text((string) ($_POST['title'] ?? ''), true);
    $prompt = question_normalize_editor_text((string) ($_POST['prompt'] ?? ''));
    $promptImageUrl = trim((string) ($_POST['prompt_image_url'] ?? ''));
    $type = (string) ($_POST['question_type'] ?? '');
    $visibility = (string) ($_POST['visibility'] ?? 'private');
    $disciplineId = (int) ($_POST['discipline_id'] ?? 0);
    $subjectId = (int) ($_POST['subject_id'] ?? 0);
    $level = (string) ($_POST['education_level'] ?? 'medio');
    $difficulty = (string) ($_POST['difficulty'] ?? 'medio');
    $allowMulti = !empty($_POST['allow_multiple_correct']) ? 1 : 0;
    $discursiveAnswer = question_normalize_editor_text((string) ($_POST['discursive_answer'] ?? ''));
    $responseLines = (int) ($_POST['response_lines'] ?? 5);
    $drawingSize = (string) ($_POST['drawing_size'] ?? 'medium');
    $drawingHeightPx = (int) ($_POST['drawing_height_px'] ?? 0);
    $trueFalseAnswer = isset($_POST['true_false_answer']) && in_array((string) $_POST['true_false_answer'], ['0', '1'], true)
        ? (int) $_POST['true_false_answer']
        : null;
    $officialSourceKey = trim((string) ($_POST['official_source_key'] ?? ''));
    $sourceReference = question_normalize_editor_text((string) ($_POST['source_reference'] ?? ''), true);
    $options = parsed_options((array) ($_POST['options'] ?? []));
    $officialSources = function_exists('official_question_sources') ? official_question_sources() : [];
    $sourceName = null;
    $sourceUrl = null;
    $errors = [];

    if ($officialSourceKey !== '') {
        if (!array_key_exists($officialSourceKey, $officialSources)) {
            $errors[] = 'Fonte oficial inválida.';
        } else {
            $sourceName = $officialSources[$officialSourceKey]['name'];
            $sourceUrl = $officialSources[$officialSourceKey]['url'];
        }
    }

    if ($title === '' || $prompt === '') {
        $errors[] = 'Título e enunciado são obrigatórios.';
    }

    if ($promptImageUrl !== '' && !filter_var($promptImageUrl, FILTER_VALIDATE_URL)) {
        $errors[] = 'A imagem deve ser uma URL válida.';
    }

    if (!in_array($type, ['multiple_choice', 'discursive', 'drawing', 'true_false'], true)) {
        $errors[] = 'Tipo de questão inválido.';
    }

    if (!in_array($visibility, ['private', 'public'], true)) {
        $errors[] = 'Visibilidade inválida.';
    }

    if ($disciplineId <= 0 || $subjectId <= 0 || !belongs_subject($subjectId, $disciplineId)) {
        $errors[] = 'Disciplina e assunto precisam ser válidos.';
    }

    if (!in_array($level, ['fundamental', 'medio', 'tecnico', 'superior'], true)) {
        $errors[] = 'Nível inválido.';
    }

    if (!in_array($difficulty, ['facil', 'medio', 'dificil'], true)) {
        $errors[] = 'Dificuldade inválida.';
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
            $errors[] = 'Sem múltiplas corretas, marque apenas uma alternativa.';
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
        $errors[] = 'Número de linhas inválido.';
    }

    if ($type !== 'discursive') {
        $responseLines = null;
        $discursiveAnswer = '';
    }

    if ($type === 'drawing') {
        if (!in_array($drawingSize, ['small', 'medium', 'large', 'custom'], true)) {
            $errors[] = 'Tamanho do espaço inválido.';
        }

        if ($drawingSize === 'custom') {
            if ($drawingHeightPx < 120 || $drawingHeightPx > 1200) {
                $errors[] = 'A altura personalizada deve ficar entre 120 e 1200 pixels.';
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
        redirect('question-editor.php' . ($editing ? '?edit=' . (int) $editing['id'] : '?new=1'));
    }

    db()->beginTransaction();

    try {
        if ($editing) {
            $updateSql = 'UPDATE questions SET
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
                 WHERE id = :id';

            if (!$canManageAll) {
                $updateSql .= ' AND author_id = :author_id';
            }

            $update = db()->prepare($updateSql);
            $updateParams = [
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
            ];

            if (!$canManageAll) {
                $updateParams['author_id'] = $userId;
            }

            $update->execute($updateParams);

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
        flash('success', $editing ? 'Questão atualizada.' : 'Questão criada.');
    } catch (Throwable $throwable) {
        db()->rollBack();
        flash('error', 'Falha ao salvar a questão: ' . $throwable->getMessage());
    }

    question_redirect();
}

function question_import_enem(int $userId): never
{
    $year = (int) ($_POST['enem_year'] ?? 0);
    $index = (int) ($_POST['enem_index'] ?? 0);
    $language = trim((string) ($_POST['enem_language'] ?? ''));
    $redirectQuery = (string) ($_POST['redirect_query'] ?? '');
    $sourceName = 'API ENEM';

    if ($year <= 0 || $index <= 0) {
        flash('error', 'Informe uma questão válida do ENEM para importar.');
        question_redirect_query($redirectQuery);
    }

    if ($language !== '' && !array_key_exists($language, enem_api_supported_languages())) {
        flash('error', 'Idioma do ENEM inválido.');
        question_redirect_query($redirectQuery);
    }

    $sourceReference = enem_api_reference($year, $index, $language !== '' ? $language : null);
    $existing = question_find_by_source_reference($userId, $sourceName, $sourceReference);

    if ($existing !== null) {
        flash('success', 'Essa questão do ENEM já foi importada para a sua conta.');
        question_redirect_query($redirectQuery);
    }

    try {
        $question = enem_api_get_question($year, $index, $language !== '' ? $language : null);
        $disciplineName = enem_api_discipline_name($question['discipline'] ?? null);
        $disciplineAliases = [
            $disciplineName,
            match ((string) ($question['discipline'] ?? '')) {
                'linguagens' => 'Linguagens, Codigos e suas Tecnologias',
                'ciencias-humanas' => 'Ciencias Humanas e suas Tecnologias',
                'ciencias-natureza' => 'Ciencias da Natureza e suas Tecnologias',
                'matematica' => 'Matematica e suas Tecnologias',
                default => '',
            },
        ];
        $disciplineId = question_find_or_create_discipline($disciplineName, $disciplineAliases, $userId);
        $subjectId = question_find_or_create_subject($disciplineId, 'ENEM', $userId);
        $prompt = question_normalize_editor_text(enem_api_join_prompt($question));
        $promptImageUrl = null;
        $questionFiles = array_values(array_filter((array) ($question['files'] ?? []), static fn(mixed $file): bool => is_string($file) && trim($file) !== ''));

        if ($questionFiles !== []) {
            $promptImageUrl = $questionFiles[0];
        }

        $alternatives = [];

        foreach ((array) ($question['alternatives'] ?? []) as $alternative) {
            $text = question_normalize_editor_text(enem_api_to_text($alternative['text'] ?? null));
            $file = trim((string) ($alternative['file'] ?? ''));

            if ($file !== '') {
                $text = $text !== '' ? $text . ' [Arquivo: ' . $file . ']' : 'Arquivo: ' . $file;
            }

            if ($text === '') {
                continue;
            }

            $alternatives[] = [
                'text' => $text,
                'is_correct' => !empty($alternative['isCorrect']) ? 1 : 0,
            ];
        }

        if ($prompt === '') {
            throw new RuntimeException('A API ENEM retornou uma questão sem enunciado utilizável.');
        }

        if (count($alternatives) < 2) {
            throw new RuntimeException('A API ENEM retornou menos de duas alternativas válidas.');
        }

        $allowMultipleCorrect = count(array_filter($alternatives, static fn(array $alternative): bool => $alternative['is_correct'] === 1)) > 1 ? 1 : 0;

        db()->beginTransaction();

        $insert = db()->prepare(
            'INSERT INTO questions
             (author_id, based_on_question_id, title, prompt, prompt_image_url, question_type, visibility, discipline_id, subject_id, education_level, difficulty, status, allow_multiple_correct, discursive_answer, response_lines, drawing_size, drawing_height_px, true_false_answer, source_name, source_url, source_reference, usage_count, created_at, updated_at)
             VALUES
             (:author_id, NULL, :title, :prompt, :prompt_image_url, :question_type, :visibility, :discipline_id, :subject_id, :education_level, :difficulty, :status, :allow_multiple_correct, NULL, NULL, NULL, NULL, NULL, :source_name, :source_url, :source_reference, 0, NOW(), NOW())'
        );
        $insert->execute([
            'author_id' => $userId,
            'title' => trim((string) ($question['title'] ?? 'Questão ENEM ' . $year)),
            'prompt' => $prompt,
            'prompt_image_url' => $promptImageUrl,
            'question_type' => 'multiple_choice',
            'visibility' => 'private',
            'discipline_id' => $disciplineId,
            'subject_id' => $subjectId,
            'education_level' => 'medio',
            'difficulty' => 'medio',
            'status' => 'published',
            'allow_multiple_correct' => $allowMultipleCorrect,
            'source_name' => $sourceName,
            'source_url' => enem_api_source_url($year, $index, $language !== '' ? $language : null),
            'source_reference' => $sourceReference,
        ]);

        $questionId = (int) db()->lastInsertId();
        $insertOption = db()->prepare(
            'INSERT INTO question_options (question_id, option_text, is_correct, display_order, created_at)
             VALUES (:question_id, :option_text, :is_correct, :display_order, NOW())'
        );

        foreach (array_values($alternatives) as $position => $alternative) {
            $insertOption->execute([
                'question_id' => $questionId,
                'option_text' => $alternative['text'],
                'is_correct' => $alternative['is_correct'],
                'display_order' => $position + 1,
            ]);
        }

        db()->commit();
        flash('success', 'Questão do ENEM importada para o banco como privada.');
    } catch (Throwable $throwable) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }

        flash('error', 'Falha ao importar a questão do ENEM: ' . $throwable->getMessage());
    }

    question_redirect_query($redirectQuery);
}
