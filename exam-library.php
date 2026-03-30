<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_metadata.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];

if (is_post()) {
    abort_if_invalid_csrf();

    if ((string) ($_POST['action'] ?? '') === 'delete_exam') {
        $examId = (int) ($_POST['exam_id'] ?? 0);

        if ($examId <= 0 || !exam_delete($examId, $userId)) {
            flash('error', 'Não foi possível excluir a prova agora.');
        } else {
            flash('success', 'Prova excluída com sucesso.');
        }

        redirect('exam-library.php#exam-history');
    }
}

$recentExams = array_slice(exam_list($userId), 0, 8);
$recentCount = count($recentExams);

render_header(
    'Central de provas',
    'Escolha um ponto de partida: criar, abrir os modelos em uma página própria ou reabrir uma prova recente.'
);
?>

<section class="simple-stack">
    <article class="simple-card exam-library-hero">
        <div class="exam-library-hero-copy">
            <span class="exam-library-kicker">Organização por etapas</span>
            <h2>Comece por um único caminho</h2>
            <p class="helper-text">A central ficou mais curta. Escolha só o que você precisa agora: criar, usar um modelo ou retomar uma prova recente.</p>
        </div>

        <div class="exam-library-entry-grid">
            <a class="exam-library-entry-card is-primary" href="exam-create.php">
                <span class="exam-library-entry-icon"><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span>
                <div>
                    <strong>Nova prova</strong>
                    <p>Preencha os dados principais e siga para as questões.</p>
                </div>
                <small>Começar do zero</small>
            </a>

            <a class="exam-library-entry-card" href="exam-models.php">
                <span class="exam-library-entry-icon"><i class="fa-regular fa-star" aria-hidden="true"></i></span>
                <div>
                    <strong>Modelos de prova</strong>
                    <p>Abra modelos prontos e carregue uma base já preenchida.</p>
                </div>
                <small>Biblioteca separada</small>
            </a>

            <a class="exam-library-entry-card" href="#exam-history">
                <span class="exam-library-entry-icon"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i></span>
                <div>
                    <strong>Provas recentes</strong>
                    <p>Retome uma prova já iniciada sem voltar ao começo.</p>
                </div>
                <small><?= h((string) $recentCount) ?> disponível(is)</small>
            </a>
        </div>
    </article>

    <article class="simple-card" id="exam-history">
        <div class="simple-card-head">
            <div>
                <h2>Provas recentes</h2>
                <p class="helper-text">Reabra uma prova já iniciada sem voltar para a criação completa.</p>
            </div>
            <span class="badge"><?= h((string) $recentCount) ?> registro(s)</span>
        </div>

        <?php if ($recentExams === []): ?>
            <div class="empty-state">
                <h2>Nenhuma prova recente</h2>
                <p>Crie a primeira prova para começar.</p>
            </div>
        <?php else: ?>
            <div class="simple-list">
                <?php foreach ($recentExams as $exam): ?>
                    <?php $parsed = exam_parse_stored_instructions($exam['instructions'] ?? null); ?>
                    <article class="simple-list-item exam-history-item">
                        <div>
                            <strong><?= h((string) $exam['title']) ?></strong>
                            <p>
                                <?= h((string) ($parsed['metadata']['class_name'] !== '' ? $parsed['metadata']['class_name'] : 'Turma não informada')) ?>
                                · <?= h(exam_format_date((string) ($parsed['metadata']['application_date'] ?? ''))) ?>
                                · <?= h((string) ($exam['total_questions'] ?? 0)) ?> questões
                            </p>
                        </div>
                        <div class="simple-action-row">
                            <a class="button-secondary" href="exam-preview.php?id=<?= h((string) $exam['id']) ?>">Abrir</a>
                            <a class="ghost-button" href="exam-create.php?edit=<?= h((string) $exam['id']) ?>">Editar</a>
                            <a class="ghost-button" href="exams.php?exam_id=<?= h((string) $exam['id']) ?>">Questões</a>
                            <form method="post" class="inline-actions">
                                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_exam">
                                <input type="hidden" name="exam_id" value="<?= h((string) $exam['id']) ?>">
                                <button class="button-danger" type="submit" onclick="return confirm('Excluir esta prova recente?');">Excluir</button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>

<?php render_footer(); ?>
