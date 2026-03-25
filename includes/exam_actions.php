<?php
declare(strict_types=1);

function handle_exam_request(int $userId): void
{
    if ((string) ($_POST['action'] ?? '') !== 'create_exam') {
        return;
    }

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

    $visibleIds = exam_visible_question_ids($questionIds, $userId);

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

        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
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
