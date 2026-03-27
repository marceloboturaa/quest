<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_metadata.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$examId = (int) ($_GET['id'] ?? 0);
$exam = $examId > 0 ? exam_find($examId, $userId) : null;

if (!$exam) {
    flash('error', 'Prova nao encontrada.');
    redirect('exams.php');
}

[$questions, $questionOptions] = exam_questions($examId, $userId);
$parsed = exam_parse_stored_instructions((string) ($exam['instructions'] ?? ''));
$metadataSummary = exam_metadata_summary($parsed['metadata']);
$metadata = array_replace(exam_default_metadata(), $parsed['metadata']);
$style = (string) $metadata['exam_style'];
$defaultHeaderTitle = function_exists('mb_strtoupper') ? mb_strtoupper((string) $exam['title']) : strtoupper((string) $exam['title']);

render_header(
    'Visualizacao da prova',
    'Confira o cabecalho, a ordem das questoes e o formato de impressao antes de exportar.'
);
?>

<section class="split-card">
    <section class="panel">
        <div class="workspace-panel-head">
            <div>
                <p class="workspace-kicker">Preview</p>
                <h2><?= h((string) $exam['title']) ?></h2>
            </div>
            <div class="form-actions">
                <a class="ghost-button" href="exams.php">Voltar</a>
                <a class="button-secondary" href="exam-pdf.php?id=<?= h((string) $exam['id']) ?>">Abrir PDF</a>
            </div>
        </div>

        <article class="exam-preview-sheet exam-preview-sheet-<?= h($style) ?>">
            <header class="exam-paper-header">
                <div class="exam-paper-brand">
                    <div class="exam-paper-logo">Q</div>
                    <div>
                        <strong><?= h($metadata['school_name'] !== '' ? $metadata['school_name'] : 'QUEST - PLATAFORMA DE AVALIACOES') ?></strong>
                        <small><?= h($metadata['discipline'] !== '' ? $metadata['discipline'] : 'ENSINO FUNDAMENTAL, MEDIO E PROFISSIONALIZANTE') ?></small>
                    </div>
                </div>

                <div class="exam-paper-grid">
                    <div class="exam-paper-cell exam-paper-cell-title">
                        <span><?= h($metadata['exam_label'] !== '' ? $metadata['exam_label'] : $defaultHeaderTitle) ?></span>
                    </div>
                    <div class="exam-paper-cell"><span>Prof.:</span><strong><?= h($metadata['teacher_name']) ?></strong></div>
                    <div class="exam-paper-cell"><span>Comp. Curricular:</span><strong><?= h($metadata['component_name']) ?></strong></div>
                    <div class="exam-paper-cell"><span>Aluno(a):</span><strong>&nbsp;</strong></div>
                    <div class="exam-paper-cell"><span>Ano:</span><strong><?= h($metadata['year_reference']) ?></strong></div>
                    <div class="exam-paper-cell"><span>Turma:</span><strong><?= h($metadata['class_name']) ?></strong></div>
                    <div class="exam-paper-cell"><span>Data:</span><strong><?= h(exam_format_date((string) $metadata['application_date'])) ?></strong></div>
                    <div class="exam-paper-cell"><span>Assin. Resp. (a):</span><strong>&nbsp;</strong></div>
                    <div class="exam-paper-cell"><span>Valor obtido:</span><strong>&nbsp;</strong></div>
                </div>
            </header>

            <?php if ($parsed['instructions'] !== ''): ?>
                <section class="exam-preview-instructions">
                    <strong>Instrucoes</strong>
                    <p><?= nl2br(h($parsed['instructions'])) ?></p>
                </section>
            <?php endif; ?>

            <section class="exam-preview-questions exam-preview-questions-<?= h($style) ?>">
                <?php foreach ($questions as $index => $question): ?>
                    <article class="exam-preview-question">
                        <h4><?= h((string) ($index + 1)) ?>. <?= h((string) $question['title']) ?></h4>
                        <p><?= nl2br(h((string) $question['prompt'])) ?></p>

                        <?php if ($question['question_type'] === 'multiple_choice'): ?>
                            <ul class="option-list">
                                <?php foreach ($questionOptions[(int) $question['id']] ?? [] as $optionIndex => $option): ?>
                                    <li><?= h(option_label($optionIndex) . ') ' . (string) $option['option_text']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php elseif ($question['question_type'] === 'true_false'): ?>
                            <p>( ) Verdadeiro &nbsp;&nbsp;&nbsp; ( ) Falso</p>
                        <?php elseif ($question['question_type'] === 'discursive'): ?>
                            <?php for ($i = 0; $i < max(3, min(12, (int) ($question['response_lines'] ?? 5))); $i++): ?>
                                <div class="exam-preview-line"></div>
                            <?php endfor; ?>
                        <?php else: ?>
                            <div class="exam-preview-drawing-space"></div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </section>
        </article>
    </section>

    <aside class="panel">
        <h2>Checklist</h2>
        <ul class="mini-list">
            <li>Confira se o cabecalho esta completo.</li>
            <li>Revise a ordem das questoes antes do PDF.</li>
            <li>Use o PDF para impressao e distribuicao.</li>
        </ul>
    </aside>
</section>

<?php render_footer(); ?>
