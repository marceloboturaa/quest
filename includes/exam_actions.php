<?php
declare(strict_types=1);

require_once __DIR__ . '/exam_metadata.php';
require_once __DIR__ . '/exam_repository.php';

function handle_exam_request(int $userId): void
{
    $action = (string) ($_POST['action'] ?? '');

    if (!in_array($action, ['create_exam', 'update_exam', 'delete_exam'], true)) {
        return;
    }

    if ($action === 'delete_exam') {
        $examId = (int) ($_POST['exam_id'] ?? 0);

        if ($examId <= 0) {
            flash('error', 'Prova não encontrada para exclusão.');
            redirect('dashboard.php');
        }

        if (!exam_delete($examId, $userId)) {
            flash('error', 'Não foi possível excluir a prova agora.');
            redirect('exam-preview.php?id=' . $examId);
        }

        flash('success', 'Prova excluída com sucesso.');
        redirect('dashboard.php');
    }

    $isUpdate = $action === 'update_exam';
    $examId = (int) ($_POST['exam_id'] ?? 0);
    $title = trim((string) ($_POST['title'] ?? ''));
    $questionIds = array_values(array_unique(array_map('intval', (array) ($_POST['question_ids'] ?? []))));
    $redirectPath = 'exams.php';

    if ($title === '') {
        flash('error', 'Informe o título da prova.');
        redirect($redirectPath);
    }

    if ($questionIds === []) {
        flash('error', 'Selecione pelo menos uma questão.');
        redirect($redirectPath);
    }

    if ($isUpdate) {
        $exam = exam_find($examId, $userId);

        if (!$exam) {
            flash('error', 'Prova não encontrada para edição.');
            redirect($redirectPath);
        }
    }

    $visibleIds = exam_visible_question_ids($questionIds, $userId);

    if (count($visibleIds) !== count($questionIds)) {
        flash('error', 'Uma ou mais questões selecionadas não podem ser usadas.');
        redirect($redirectPath);
    }

    $metadata = exam_collect_metadata($_POST);
    $sections = exam_merge_sections(exam_default_sections(), $_POST);
    $orderingMode = (string) ($metadata['ordering_mode'] ?? 'automatic_numbering');

    if ($orderingMode === 'shuffle_questions' && count($questionIds) > 1) {
        shuffle($questionIds);
    }

    db()->beginTransaction();

    try {
        if ($isUpdate) {
            $existingQuestionIds = exam_question_ids($examId, $userId);

            $updateExam = db()->prepare(
                'UPDATE exams
                 SET title = :title, instructions = :instructions, updated_at = NOW()
                 WHERE id = :id AND user_id = :user_id'
            );
            $updateExam->execute([
                'id' => $examId,
                'user_id' => $userId,
                'title' => $title,
                'instructions' => exam_build_stored_content($metadata, $sections),
            ]);

            $deleteQuestions = db()->prepare('DELETE FROM exam_questions WHERE exam_id = :exam_id');
            $deleteQuestions->execute(['exam_id' => $examId]);
        } else {
            $existingQuestionIds = [];
            $insertExam = db()->prepare(
                'INSERT INTO exams (user_id, title, instructions, created_at, updated_at)
                 VALUES (:user_id, :title, :instructions, NOW(), NOW())'
            );
            $insertExam->execute([
                'user_id' => $userId,
                'title' => $title,
                'instructions' => exam_build_stored_content($metadata, $sections),
            ]);

            $examId = (int) db()->lastInsertId();
        }

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

        $addedQuestionIds = array_values(array_diff($questionIds, $existingQuestionIds));
        $removedQuestionIds = array_values(array_diff($existingQuestionIds, $questionIds));

        if ($addedQuestionIds !== []) {
            $placeholders = implode(',', array_fill(0, count($addedQuestionIds), '?'));
            $updateUsage = db()->prepare("UPDATE questions SET usage_count = usage_count + 1 WHERE id IN ($placeholders)");
            $updateUsage->execute($addedQuestionIds);
        }

        if ($removedQuestionIds !== []) {
            $placeholders = implode(',', array_fill(0, count($removedQuestionIds), '?'));
            $decreaseUsage = db()->prepare("UPDATE questions SET usage_count = GREATEST(usage_count - 1, 0) WHERE id IN ($placeholders)");
            $decreaseUsage->execute($removedQuestionIds);
        }

        db()->commit();
        flash('success', $isUpdate ? 'Prova atualizada com sucesso.' : 'Prova criada com sucesso.');
        redirect('exam-preview.php?id=' . $examId);
    } catch (Throwable $throwable) {
        db()->rollBack();
        flash('error', $isUpdate ? 'Falha ao atualizar a prova.' : 'Falha ao criar prova.');
    }

    redirect($redirectPath);
}
