<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_examples.php';
require_once __DIR__ . '/includes/exam_repository.php';
require_once __DIR__ . '/includes/exam_metadata.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$examExamples = exam_example_presets((string) ($user['name'] ?? ''));
$recentExams = array_slice(exam_list($userId), 0, 8);

render_header(
    'Central de provas',
    'Escolha um ponto de partida: criar, usar um modelo pronto ou reabrir uma prova recente.'
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
            <a class="simple-action-card" href="#exam-presets">
                <span class="simple-action-icon"><i class="fa-regular fa-star" aria-hidden="true"></i></span>
                <span>
                    <strong>Modelos prontos</strong>
                    <small>Carregar um exemplo já configurado</small>
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

    <article class="simple-card" id="exam-presets">
        <div class="simple-card-head">
            <div>
                <h2>Modelos prontos</h2>
                <p class="helper-text">Escolha uma base pronta para acelerar a criação.</p>
            </div>
            <a class="ghost-button" href="exam-create.php">Abrir montagem manual</a>
        </div>

        <div class="exam-example-grid">
            <?php foreach ($examExamples as $example): ?>
                <?php
                $exampleQuery = exam_example_query($example);
                $filteredExampleQuery = array_filter(
                    $exampleQuery,
                    static fn(mixed $value): bool => $value !== null && $value !== ''
                );
                ?>
                <article class="simple-note exam-example-card">
                    <strong><?= h($example['title']) ?></strong>
                    <p><?= h($example['description']) ?></p>
                    <div class="simple-inline-list">
                        <?php foreach ($example['tags'] as $tag): ?>
                            <span class="badge"><?= h($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="simple-action-row">
                        <a class="button-secondary" href="exam-create.php?<?= h(http_build_query($filteredExampleQuery)) ?>">Carregar exemplo</a>
                        <a class="ghost-button" href="exams.php?<?= h(http_build_query($filteredExampleQuery)) ?>">Ir para questões</a>
                    </div>
                </article>
            <?php endforeach; ?>
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
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>

<?php render_footer(); ?>
