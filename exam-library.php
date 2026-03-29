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
$libraryMetrics = [
    ['label' => 'Modelos prontos', 'value' => 'Biblioteca separada', 'icon' => 'fa-regular fa-star'],
    ['label' => 'Provas recentes', 'value' => (string) count($recentExams), 'icon' => 'fa-solid fa-clock-rotate-left'],
    ['label' => 'Fluxo atual', 'value' => 'Separado por etapas', 'icon' => 'fa-solid fa-layer-group'],
];

render_header(
    'Central de provas',
    'Escolha um ponto de partida: criar, abrir os modelos em uma página própria ou reabrir uma prova recente.'
);
?>

<section class="simple-stack">
    <article class="simple-card exam-library-hero">
        <div class="simple-card-head">
            <div>
                <h2>Fluxo de provas</h2>
                <p class="helper-text">Use a central para evitar telas carregadas demais durante a montagem.</p>
            </div>
        </div>

        <div class="exam-library-metrics">
            <?php foreach ($libraryMetrics as $metric): ?>
                <div class="exam-library-metric">
                    <span class="exam-library-metric-icon"><i class="<?= h((string) $metric['icon']) ?>" aria-hidden="true"></i></span>
                    <strong><?= h($metric['value']) ?></strong>
                    <span><?= h($metric['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="simple-decision-grid">
            <a class="simple-action-card" href="exam-create.php">
                <span class="simple-action-icon"><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span>
                <span>
                    <strong>Nova prova</strong>
                    <small>Começar uma montagem do zero</small>
                </span>
            </a>
            <a class="simple-action-card" href="exams.php">
                <span class="simple-action-icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
                <span>
                    <strong>Seleção de questões</strong>
                    <small>Abrir a etapa de composição e salvamento</small>
                </span>
            </a>
            <a class="simple-action-card" href="exam-models.php">
                <span class="simple-action-icon"><i class="fa-regular fa-star" aria-hidden="true"></i></span>
                <span>
                    <strong>Modelos de prova</strong>
                    <small>Abrir a biblioteca específica de modelos</small>
                </span>
            </a>
            <a class="simple-action-card" href="#exam-history">
                <span class="simple-action-icon"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i></span>
                <span>
                    <strong>Provas recentes</strong>
                    <small>Retomar uma prova já criada</small>
                </span>
            </a>
        </div>
    </article>

    <article class="simple-card" id="exam-history">
        <div class="simple-card-head">
            <div>
                <h2>Provas recentes</h2>
                <p class="helper-text">Reabra uma prova já iniciada sem voltar para a criação completa.</p>
            </div>
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
