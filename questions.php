<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_login();

$user = current_user();
$userId = (int) $user['id'];

function own_question(int $id, int $userId): ?array
{
    $s = db()->prepare('SELECT * FROM questions WHERE id = :id AND author_id = :author_id LIMIT 1');
    $s->execute(['id' => $id, 'author_id' => $userId]);
    return $s->fetch() ?: null;
}

function visible_question_row(int $id, int $userId): ?array
{
    $s = db()->prepare('SELECT * FROM questions WHERE id = :id AND (visibility = "public" OR author_id = :author_id) LIMIT 1');
    $s->execute(['id' => $id, 'author_id' => $userId]);
    return $s->fetch() ?: null;
}

function belongs_subject(int $subjectId, int $disciplineId): bool
{
    $s = db()->prepare('SELECT COUNT(*) FROM subjects WHERE id = :id AND discipline_id = :discipline_id');
    $s->execute(['id' => $subjectId, 'discipline_id' => $disciplineId]);
    return (int) $s->fetchColumn() > 0;
}

function option_rows(array $raw): array
{
    $rows = [];
    foreach ($raw as $item) {
        $text = trim((string) ($item['text'] ?? $item['option_text'] ?? ''));
        if ($text === '' && isset($item['option_text'])) {
            continue;
        }
        $rows[] = ['text' => $text, 'is_correct' => !empty($item['is_correct'])];
    }
    while (count($rows) < 4) {
        $rows[] = ['text' => '', 'is_correct' => false];
    }
    return array_values($rows);
}

function parsed_options(array $raw): array
{
    $rows = [];
    foreach ($raw as $item) {
        $text = trim((string) ($item['text'] ?? ''));
        if ($text === '') {
            continue;
        }
        $rows[] = ['text' => $text, 'is_correct' => !empty($item['is_correct']) ? 1 : 0];
    }
    return $rows;
}

if (is_post()) {
    abort_if_invalid_csrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create_discipline' && can_manage_catalogs()) {
        $name = trim((string) ($_POST['discipline_name'] ?? ''));
        if ($name === '') {
            flash('error', 'Informe a disciplina.');
        } else {
            $i = db()->prepare('INSERT IGNORE INTO disciplines (name, created_by, created_at) VALUES (:name, :created_by, NOW())');
            $i->execute(['name' => $name, 'created_by' => $userId]);
            flash('success', 'Disciplina cadastrada.');
        }
        redirect('questions.php');
    }

    if ($action === 'create_subject' && can_manage_catalogs()) {
        $disciplineId = (int) ($_POST['discipline_id'] ?? 0);
        $name = trim((string) ($_POST['subject_name'] ?? ''));
        if ($disciplineId <= 0 || $name === '') {
            flash('error', 'Informe disciplina e assunto.');
        } else {
            $i = db()->prepare('INSERT IGNORE INTO subjects (discipline_id, name, created_by, created_at) VALUES (:discipline_id, :name, :created_by, NOW())');
            $i->execute(['discipline_id' => $disciplineId, 'name' => $name, 'created_by' => $userId]);
            flash('success', 'Assunto cadastrado.');
        }
        redirect('questions.php');
    }

    if ($action === 'toggle_favorite') {
        $questionId = (int) ($_POST['question_id'] ?? 0);
        $q = visible_question_row($questionId, $userId);
        if (!$q) {
            flash('error', 'Questao nao encontrada.');
            redirect('questions.php');
        }
        $s = db()->prepare('SELECT id FROM question_favorites WHERE question_id = :question_id AND user_id = :user_id LIMIT 1');
        $s->execute(['question_id' => $questionId, 'user_id' => $userId]);
        $favorite = $s->fetch();
        if ($favorite) {
            $d = db()->prepare('DELETE FROM question_favorites WHERE id = :id');
            $d->execute(['id' => $favorite['id']]);
            flash('success', 'Questao removida dos favoritos.');
        } else {
            $i = db()->prepare('INSERT INTO question_favorites (question_id, user_id, created_at) VALUES (:question_id, :user_id, NOW())');
            $i->execute(['question_id' => $questionId, 'user_id' => $userId]);
            flash('success', 'Questao favoritada.');
        }
        redirect('questions.php');
    }

    if ($action === 'clone_question') {
        $questionId = (int) ($_POST['question_id'] ?? 0);
        $source = visible_question_row($questionId, $userId);
        if (!$source || ($source['visibility'] !== 'public' && (int) $source['author_id'] !== $userId)) {
            flash('error', 'Questao nao pode ser clonada.');
            redirect('questions.php');
        }
        $originId = $source['based_on_question_id'] ? (int) $source['based_on_question_id'] : (int) $source['id'];
        db()->beginTransaction();
        try {
            $i = db()->prepare('INSERT INTO questions
                (author_id,based_on_question_id,title,prompt,prompt_image_url,question_type,visibility,discipline_id,subject_id,education_level,difficulty,status,allow_multiple_correct,discursive_answer,response_lines,drawing_size,true_false_answer,usage_count,created_at,updated_at)
                VALUES
                (:author_id,:based_on_question_id,:title,:prompt,:prompt_image_url,:question_type,:visibility,:discipline_id,:subject_id,:education_level,:difficulty,:status,:allow_multiple_correct,:discursive_answer,:response_lines,:drawing_size,:true_false_answer,0,NOW(),NOW())');
            $i->execute([
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
                'true_false_answer' => $source['true_false_answer'],
            ]);
            $newId = (int) db()->lastInsertId();
            $s = db()->prepare('SELECT option_text,is_correct,display_order FROM question_options WHERE question_id = :question_id ORDER BY display_order ASC');
            $s->execute(['question_id' => $questionId]);
            foreach ($s->fetchAll() as $row) {
                $opt = db()->prepare('INSERT INTO question_options (question_id,option_text,is_correct,display_order,created_at) VALUES (:question_id,:option_text,:is_correct,:display_order,NOW())');
                $opt->execute([
                    'question_id' => $newId,
                    'option_text' => $row['option_text'],
                    'is_correct' => $row['is_correct'],
                    'display_order' => $row['display_order'],
                ]);
            }
            db()->commit();
            flash('success', 'Questao clonada como privada.');
        } catch (Throwable $e) {
            db()->rollBack();
            flash('error', 'Falha ao clonar: ' . $e->getMessage());
        }
        redirect('questions.php');
    }

    if ($action === 'delete_question') {
        $questionId = (int) ($_POST['question_id'] ?? 0);
        $q = own_question($questionId, $userId);
        if (!$q) {
            flash('error', 'Somente o autor pode excluir.');
        } else {
            $d = db()->prepare('DELETE FROM questions WHERE id = :id');
            $d->execute(['id' => $questionId]);
            flash('success', 'Questao excluida.');
        }
        redirect('questions.php');
    }

    if (in_array($action, ['create_question', 'update_question'], true)) {
        $questionId = (int) ($_POST['question_id'] ?? 0);
        $editing = $action === 'update_question' ? own_question($questionId, $userId) : null;
        if ($action === 'update_question' && !$editing) {
            flash('error', 'Questao nao encontrada.');
            redirect('questions.php');
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
        $options = parsed_options((array) ($_POST['options'] ?? []));
        $errors = [];

        if ($title === '' || $prompt === '') {
            $errors[] = 'Titulo e enunciado sao obrigatorios.';
        }
        if ($promptImageUrl !== '' && !filter_var($promptImageUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Imagem deve ser uma URL valida.';
        }
        if (!in_array($type, ['multiple_choice', 'discursive', 'drawing'], true)) {
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
            $correctCount = count(array_filter($options, static fn(array $o): bool => (int) $o['is_correct'] === 1));
            if (count($options) < 2) {
                $errors[] = 'Informe pelo menos duas alternativas.';
            }
            if ($correctCount === 0) {
                $errors[] = 'Marque ao menos uma alternativa correta.';
            }
            if ($allowMulti === 0 && $correctCount > 1) {
                $errors[] = 'Sem multiplas corretas, marque apenas uma alternativa.';
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
        if ($type === 'drawing' && !in_array($drawingSize, ['small', 'medium', 'large'], true)) {
            $errors[] = 'Tamanho do espaco invalido.';
        }
        if ($type !== 'drawing') {
            $drawingSize = null;
        }

        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('questions.php' . ($editing ? '?edit=' . $editing['id'] : ''));
        }

        db()->beginTransaction();
        try {
            if ($editing) {
                $u = db()->prepare('UPDATE questions SET
                    title=:title,prompt=:prompt,prompt_image_url=:prompt_image_url,question_type=:question_type,visibility=:visibility,
                    discipline_id=:discipline_id,subject_id=:subject_id,education_level=:education_level,difficulty=:difficulty,
                    allow_multiple_correct=:allow_multiple_correct,discursive_answer=:discursive_answer,response_lines=:response_lines,
                    drawing_size=:drawing_size,updated_at=NOW()
                    WHERE id=:id AND author_id=:author_id');
                $u->execute([
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
                    'id' => $editing['id'],
                    'author_id' => $userId,
                ]);
                $questionId = (int) $editing['id'];
                $d = db()->prepare('DELETE FROM question_options WHERE question_id = :question_id');
                $d->execute(['question_id' => $questionId]);
            } else {
                $i = db()->prepare('INSERT INTO questions
                    (author_id,based_on_question_id,title,prompt,prompt_image_url,question_type,visibility,discipline_id,subject_id,education_level,difficulty,status,allow_multiple_correct,discursive_answer,response_lines,drawing_size,usage_count,created_at,updated_at)
                    VALUES
                    (:author_id,NULL,:title,:prompt,:prompt_image_url,:question_type,:visibility,:discipline_id,:subject_id,:education_level,:difficulty,:status,:allow_multiple_correct,:discursive_answer,:response_lines,:drawing_size,0,NOW(),NOW())');
                $i->execute([
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
                ]);
                $questionId = (int) db()->lastInsertId();
            }
            if ($type === 'multiple_choice') {
                $i = db()->prepare('INSERT INTO question_options (question_id,option_text,is_correct,display_order,created_at) VALUES (:question_id,:option_text,:is_correct,:display_order,NOW())');
                foreach (array_values($options) as $index => $option) {
                    $i->execute([
                        'question_id' => $questionId,
                        'option_text' => $option['text'],
                        'is_correct' => $option['is_correct'],
                        'display_order' => $index + 1,
                    ]);
                }
            }
            db()->commit();
            flash('success', $editing ? 'Questao atualizada.' : 'Questao criada.');
        } catch (Throwable $e) {
            db()->rollBack();
            flash('error', 'Falha ao salvar questao: ' . $e->getMessage());
        }
        redirect('questions.php');
    }
}

$disciplines = db()->query('SELECT id,name FROM disciplines ORDER BY name ASC')->fetchAll();
$subjects = db()->query('SELECT subjects.id,subjects.name,subjects.discipline_id,disciplines.name AS discipline_name FROM subjects INNER JOIN disciplines ON disciplines.id = subjects.discipline_id ORDER BY disciplines.name ASC, subjects.name ASC')->fetchAll();

$edit = null;
$editOptions = option_rows([]);
if (isset($_GET['edit'])) {
    $edit = own_question((int) $_GET['edit'], $userId);
    if (!$edit) {
        flash('error', 'Voce so pode editar questoes da sua autoria.');
        redirect('questions.php');
    }
    $s = db()->prepare('SELECT option_text,is_correct FROM question_options WHERE question_id = :question_id ORDER BY display_order ASC');
    $s->execute(['question_id' => $edit['id']]);
    $editOptions = option_rows($s->fetchAll());
}

$filters = [
    'discipline_id' => (int) ($_GET['discipline_id'] ?? 0),
    'subject_id' => (int) ($_GET['subject_id'] ?? 0),
    'education_level' => trim((string) ($_GET['education_level'] ?? '')),
    'question_type' => trim((string) ($_GET['question_type'] ?? '')),
    'author_id' => (int) ($_GET['author_id'] ?? 0),
    'visibility' => trim((string) ($_GET['visibility'] ?? '')),
];

$query = 'SELECT questions.*,authors.name AS author_name,disciplines.name AS discipline_name,subjects.name AS subject_name,base_authors.name AS based_on_author_name,CASE WHEN question_favorites.id IS NULL THEN 0 ELSE 1 END AS is_favorite
FROM questions
INNER JOIN users AS authors ON authors.id = questions.author_id
LEFT JOIN disciplines ON disciplines.id = questions.discipline_id
LEFT JOIN subjects ON subjects.id = questions.subject_id
LEFT JOIN questions AS base_questions ON base_questions.id = questions.based_on_question_id
LEFT JOIN users AS base_authors ON base_authors.id = base_questions.author_id
LEFT JOIN question_favorites ON question_favorites.question_id = questions.id AND question_favorites.user_id = :favorite_user_id
WHERE (questions.visibility = "public" OR questions.author_id = :current_user_id)';
$params = ['favorite_user_id' => $userId, 'current_user_id' => $userId];

if ($filters['discipline_id'] > 0) {
    $query .= ' AND questions.discipline_id = :discipline_id';
    $params['discipline_id'] = $filters['discipline_id'];
}
if ($filters['subject_id'] > 0) {
    $query .= ' AND questions.subject_id = :subject_id';
    $params['subject_id'] = $filters['subject_id'];
}
if ($filters['education_level'] !== '' && in_array($filters['education_level'], ['fundamental', 'medio', 'tecnico', 'superior'], true)) {
    $query .= ' AND questions.education_level = :education_level';
    $params['education_level'] = $filters['education_level'];
}
if ($filters['question_type'] !== '' && in_array($filters['question_type'], ['multiple_choice', 'discursive', 'drawing'], true)) {
    $query .= ' AND questions.question_type = :question_type';
    $params['question_type'] = $filters['question_type'];
}
if ($filters['author_id'] > 0) {
    $query .= ' AND questions.author_id = :author_id';
    $params['author_id'] = $filters['author_id'];
}
if ($filters['visibility'] !== '' && in_array($filters['visibility'], ['public', 'private'], true)) {
    $query .= ' AND questions.visibility = :visibility';
    $params['visibility'] = $filters['visibility'];
}
$query .= ' ORDER BY questions.created_at DESC';

$s = db()->prepare($query);
$s->execute($params);
$questions = $s->fetchAll();
$questionOptions = find_question_options(array_map(static fn(array $q): int => (int) $q['id'], $questions));
$authors = db()->query('SELECT id,name FROM users ORDER BY name ASC')->fetchAll();

render_header('Banco de questoes', 'Crie questoes privadas e publicas, classifique por disciplina e assunto, favorite, clone e reutilize em provas.');
?>
<section class="panel">
    <h2>Filtros do banco</h2>
    <form method="get" class="form-grid two-columns filter-grid">
        <label>Disciplina
            <select name="discipline_id" data-discipline-select data-target="filter-subject-select">
                <option value="0">Todas</option>
                <?php foreach ($disciplines as $discipline): ?>
                    <option value="<?= h((string) $discipline['id']) ?>" <?= $filters['discipline_id'] === (int) $discipline['id'] ? 'selected' : '' ?>><?= h($discipline['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Assunto
            <select name="subject_id" id="filter-subject-select" data-subject-select>
                <option value="0">Todos</option>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= h((string) $subject['id']) ?>" data-discipline-id="<?= h((string) $subject['discipline_id']) ?>" <?= $filters['subject_id'] === (int) $subject['id'] ? 'selected' : '' ?>><?= h($subject['discipline_name'] . ' - ' . $subject['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Nivel
            <select name="education_level">
                <option value="">Todos</option>
                <option value="fundamental" <?= $filters['education_level'] === 'fundamental' ? 'selected' : '' ?>>Ensino Fundamental</option>
                <option value="medio" <?= $filters['education_level'] === 'medio' ? 'selected' : '' ?>>Ensino Medio</option>
                <option value="tecnico" <?= $filters['education_level'] === 'tecnico' ? 'selected' : '' ?>>Tecnico</option>
                <option value="superior" <?= $filters['education_level'] === 'superior' ? 'selected' : '' ?>>Superior</option>
            </select>
        </label>
        <label>Tipo
            <select name="question_type">
                <option value="">Todos</option>
                <option value="multiple_choice" <?= $filters['question_type'] === 'multiple_choice' ? 'selected' : '' ?>>Multipla escolha</option>
                <option value="discursive" <?= $filters['question_type'] === 'discursive' ? 'selected' : '' ?>>Discursiva</option>
                <option value="drawing" <?= $filters['question_type'] === 'drawing' ? 'selected' : '' ?>>Desenho / espaco livre</option>
            </select>
        </label>
        <label>Autor
            <select name="author_id">
                <option value="0">Todos</option>
                <?php foreach ($authors as $author): ?>
                    <option value="<?= h((string) $author['id']) ?>" <?= $filters['author_id'] === (int) $author['id'] ? 'selected' : '' ?>><?= h($author['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Visibilidade
            <select name="visibility">
                <option value="">Todas</option>
                <option value="public" <?= $filters['visibility'] === 'public' ? 'selected' : '' ?>>Publica</option>
                <option value="private" <?= $filters['visibility'] === 'private' ? 'selected' : '' ?>>Privada</option>
            </select>
        </label>
        <div class="form-actions">
            <button class="button" type="submit">Filtrar</button>
            <a class="ghost-button" href="questions.php">Limpar</a>
        </div>
    </form>
</section>

<section class="split-card">
    <section>
        <h2><?= $edit ? 'Editar questao' : 'Nova questao' ?></h2>
        <p class="helper-text">Questoes publicas podem ser reutilizadas e clonadas. Questoes privadas ficam restritas ao autor.</p>
        <form method="post" class="form-grid" data-question-form data-next-option-index="<?= h((string) count($editOptions)) ?>">
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="<?= $edit ? 'update_question' : 'create_question' ?>">
            <?php if ($edit): ?><input type="hidden" name="question_id" value="<?= h((string) $edit['id']) ?>"><?php endif; ?>
            <label>Titulo<input type="text" name="title" required value="<?= h($edit['title'] ?? '') ?>"></label>
            <label>Enunciado<textarea name="prompt" required><?= h($edit['prompt'] ?? '') ?></textarea></label>
            <label>Imagem do enunciado (URL)<input type="url" name="prompt_image_url" value="<?= h($edit['prompt_image_url'] ?? '') ?>" placeholder="https://..."></label>
            <div class="form-grid two-columns">
                <label>Tipo
                    <?php $selectedType = $edit['question_type'] ?? 'multiple_choice'; ?>
                    <select name="question_type" required>
                        <option value="multiple_choice" <?= $selectedType === 'multiple_choice' ? 'selected' : '' ?>>Multipla escolha</option>
                        <option value="discursive" <?= $selectedType === 'discursive' ? 'selected' : '' ?>>Discursiva</option>
                        <option value="drawing" <?= $selectedType === 'drawing' ? 'selected' : '' ?>>Desenho / espaco livre</option>
                    </select>
                </label>
                <label>Visibilidade
                    <?php $selectedVisibility = $edit['visibility'] ?? 'private'; ?>
                    <select name="visibility" required>
                        <option value="private" <?= $selectedVisibility === 'private' ? 'selected' : '' ?>>Privada</option>
                        <option value="public" <?= $selectedVisibility === 'public' ? 'selected' : '' ?>>Publica</option>
                    </select>
                </label>
            </div>
            <div class="form-grid two-columns">
                <label>Disciplina
                    <?php $selectedDiscipline = (int) ($edit['discipline_id'] ?? 0); ?>
                    <select name="discipline_id" required data-discipline-select data-target="question-subject-select">
                        <option value="">Selecione</option>
                        <?php foreach ($disciplines as $discipline): ?>
                            <option value="<?= h((string) $discipline['id']) ?>" <?= $selectedDiscipline === (int) $discipline['id'] ? 'selected' : '' ?>><?= h($discipline['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Assunto
                    <?php $selectedSubject = (int) ($edit['subject_id'] ?? 0); ?>
                    <select name="subject_id" id="question-subject-select" required data-subject-select>
                        <option value="">Selecione</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= h((string) $subject['id']) ?>" data-discipline-id="<?= h((string) $subject['discipline_id']) ?>" <?= $selectedSubject === (int) $subject['id'] ? 'selected' : '' ?>><?= h($subject['discipline_name'] . ' - ' . $subject['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="form-grid two-columns">
                <label>Nivel de ensino
                    <?php $selectedLevel = $edit['education_level'] ?? 'medio'; ?>
                    <select name="education_level" required>
                        <option value="fundamental" <?= $selectedLevel === 'fundamental' ? 'selected' : '' ?>>Ensino Fundamental</option>
                        <option value="medio" <?= $selectedLevel === 'medio' ? 'selected' : '' ?>>Ensino Medio</option>
                        <option value="tecnico" <?= $selectedLevel === 'tecnico' ? 'selected' : '' ?>>Tecnico</option>
                        <option value="superior" <?= $selectedLevel === 'superior' ? 'selected' : '' ?>>Superior</option>
                    </select>
                </label>
                <label>Dificuldade
                    <?php $selectedDifficulty = $edit['difficulty'] ?? 'medio'; ?>
                    <select name="difficulty" required>
                        <option value="facil" <?= $selectedDifficulty === 'facil' ? 'selected' : '' ?>>Facil</option>
                        <option value="medio" <?= $selectedDifficulty === 'medio' ? 'selected' : '' ?>>Medio</option>
                        <option value="dificil" <?= $selectedDifficulty === 'dificil' ? 'selected' : '' ?>>Dificil</option>
                    </select>
                </label>
            </div>
            <div data-question-section="multiple_choice">
                <label class="checkbox-row"><input type="checkbox" name="allow_multiple_correct" value="1" <?= !empty($edit['allow_multiple_correct']) ? 'checked' : '' ?>> Permitir multiplas corretas</label>
                <div class="option-list-editor" data-options-container>
                    <?php foreach ($editOptions as $index => $option): ?>
                        <div class="option-editor-row">
                            <strong><?= h(option_label($index)) ?></strong>
                            <input type="text" name="options[<?= h((string) $index) ?>][text]" value="<?= h($option['text']) ?>" placeholder="Texto da alternativa">
                            <label class="checkbox-row compact"><input type="checkbox" name="options[<?= h((string) $index) ?>][is_correct]" value="1" <?= !empty($option['is_correct']) ? 'checked' : '' ?>> Correta</label>
                            <button class="ghost-button option-remove-button" type="button" data-remove-option>&minus;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-actions"><button class="button-secondary" type="button" data-add-option>Adicionar alternativa</button></div>
            </div>
            <div class="hidden" data-question-section="discursive">
                <div class="form-grid two-columns">
                    <label>Numero de linhas<input type="number" min="1" max="30" name="response_lines" value="<?= h((string) ($edit['response_lines'] ?? 5)) ?>"></label>
                    <label>Resposta de referencia (opcional)<textarea name="discursive_answer"><?= h($edit['discursive_answer'] ?? '') ?></textarea></label>
                </div>
            </div>
            <div class="hidden" data-question-section="drawing">
                <?php $selectedDrawing = $edit['drawing_size'] ?? 'medium'; ?>
                <label>Altura do espaco
                    <select name="drawing_size">
                        <option value="small" <?= $selectedDrawing === 'small' ? 'selected' : '' ?>>Pequeno</option>
                        <option value="medium" <?= $selectedDrawing === 'medium' ? 'selected' : '' ?>>Medio</option>
                        <option value="large" <?= $selectedDrawing === 'large' ? 'selected' : '' ?>>Grande</option>
                    </select>
                </label>
            </div>
            <div class="form-actions">
                <button class="button" type="submit"><?= $edit ? 'Salvar alteracoes' : 'Salvar questao' ?></button>
                <?php if ($edit): ?><a class="ghost-button" href="questions.php">Cancelar edicao</a><?php endif; ?>
            </div>
        </form>
    </section>
    <section>
        <h2>Regras colaborativas</h2>
        <ul class="mini-list">
            <li><strong>Privada:</strong> so o autor ve e edita.</li>
            <li><strong>Publica:</strong> outros usuarios podem visualizar, favoritar, usar em provas e clonar.</li>
            <li><strong>Clone:</strong> gera nova questao sem alterar a original.</li>
            <li><strong>Exclusao:</strong> apenas o autor pode excluir.</li>
        </ul>
<?php if (can_manage_catalogs()): ?>
        <div class="stack-panel">
            <div class="panel panel-nested">
                <h2>Nova disciplina</h2>
                <form method="post" class="form-grid">
                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="create_discipline">
                    <label>Nome da disciplina<input type="text" name="discipline_name" required></label>
                    <button class="button-secondary" type="submit">Cadastrar disciplina</button>
                </form>
            </div>
            <div class="panel panel-nested">
                <h2>Novo assunto</h2>
                <form method="post" class="form-grid">
                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="create_subject">
                    <label>Disciplina
                        <select name="discipline_id" required>
                            <option value="">Selecione</option>
                            <?php foreach ($disciplines as $discipline): ?>
                                <option value="<?= h((string) $discipline['id']) ?>"><?= h($discipline['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Nome do assunto<input type="text" name="subject_name" required></label>
                    <button class="button-secondary" type="submit">Cadastrar assunto</button>
                </form>
            </div>
        </div>
<?php endif; ?>
    </section>
</section>
<?php if ($questions === []): ?>
<section class="empty-state"><h2>Nenhuma questao encontrada</h2><p>Crie a primeira questao ou ajuste os filtros.</p></section>
<?php else: ?>
<section class="question-list">
<?php foreach ($questions as $question): ?>
    <article class="question-card">
        <div class="question-meta">
            <span class="badge"><?= h(question_type_label($question['question_type'])) ?></span>
            <span class="badge"><?= h(visibility_label($question['visibility'])) ?></span>
            <span class="badge"><?= h(education_level_label($question['education_level'])) ?></span>
            <span class="badge"><?= h($question['discipline_name'] ?? 'Sem disciplina') ?></span>
            <span class="badge"><?= h($question['subject_name'] ?? 'Sem assunto') ?></span>
            <span>Autor: <?= h($question['author_name']) ?></span>
            <span>Uso em provas: <?= h((string) $question['usage_count']) ?></span>
        </div>
        <h3><?= h($question['title']) ?></h3>
        <p><?= nl2br(h($question['prompt'])) ?></p>
        <?php if (!empty($question['prompt_image_url'])): ?><p><a href="<?= h($question['prompt_image_url']) ?>" target="_blank" rel="noreferrer">Abrir imagem do enunciado</a></p><?php endif; ?>
        <?php if (!empty($question['based_on_author_name'])): ?><p class="helper-text">Baseada na questao de <?= h($question['based_on_author_name']) ?>.</p><?php endif; ?>
        <?php if ($question['question_type'] === 'multiple_choice'): ?>
            <ul class="option-list">
                <?php foreach ($questionOptions[(int) $question['id']] ?? [] as $option): ?>
                    <li class="<?= (int) $option['is_correct'] === 1 ? 'correct' : '' ?>"><?= h($option['option_text']) ?><?php if ((int) $option['is_correct'] === 1): ?> <strong>- correta</strong><?php endif; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php elseif ($question['question_type'] === 'discursive'): ?>
            <p><strong>Linhas:</strong> <?= h((string) ($question['response_lines'] ?? 5)) ?></p>
            <?php if (!empty($question['discursive_answer'])): ?><p><strong>Resposta de referencia:</strong> <?= nl2br(h($question['discursive_answer'])) ?></p><?php endif; ?>
        <?php else: ?>
            <p><strong>Espaco:</strong> <?= h(drawing_size_label($question['drawing_size'])) ?></p>
        <?php endif; ?>
        <div class="question-actions">
            <form method="post">
                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                <input type="hidden" name="action" value="toggle_favorite">
                <input type="hidden" name="question_id" value="<?= h((string) $question['id']) ?>">
                <button class="<?= (int) $question['is_favorite'] === 1 ? 'button-secondary' : 'ghost-button' ?>" type="submit"><?= (int) $question['is_favorite'] === 1 ? 'Favoritada' : 'Favoritar' ?></button>
            </form>
            <a class="ghost-button" href="exams.php?question_id=<?= h((string) $question['id']) ?>">Usar em prova</a>
            <?php if ($question['visibility'] === 'public' || (int) $question['author_id'] === $userId): ?>
                <form method="post">
                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="clone_question">
                    <input type="hidden" name="question_id" value="<?= h((string) $question['id']) ?>">
                    <button class="ghost-button" type="submit">Clonar</button>
                </form>
            <?php endif; ?>
            <?php if ((int) $question['author_id'] === $userId): ?>
                <a class="button-secondary" href="questions.php?edit=<?= h((string) $question['id']) ?>">Editar</a>
                <form method="post">
                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="delete_question">
                    <input type="hidden" name="question_id" value="<?= h((string) $question['id']) ?>">
                    <button class="button-danger" type="submit" onclick="return confirm('Excluir esta questao?')">Excluir</button>
                </form>
            <?php endif; ?>
        </div>
    </article>
<?php endforeach; ?>
</section>
<?php endif; ?>

<template id="question-option-template">
    <div class="option-editor-row">
        <strong data-option-label>__LABEL__</strong>
        <input type="text" name="options[__INDEX__][text]" placeholder="Texto da alternativa">
        <label class="checkbox-row compact"><input type="checkbox" name="options[__INDEX__][is_correct]" value="1"> Correta</label>
        <button class="ghost-button option-remove-button" type="button" data-remove-option>&minus;</button>
    </div>
</template>
<?php render_footer(); ?>
