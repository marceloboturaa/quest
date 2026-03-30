<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_examples.php';

require_login();

$user = current_user();
$examExamples = exam_example_presets((string) ($user['name'] ?? ''));

render_header(
    'Modelo de prova',
    'Use o modelo padrão da plataforma como base para cabeçalho, gabarito, questões em card e rodapé.'
);
?>

<section class="simple-stack">
    <article class="simple-card exam-library-hero">
        <div class="simple-card-head">
            <div>
                <h2>Modelo padrão</h2>
                <p class="helper-text">A página agora mantém um único padrão para evitar variações visuais e de estrutura.</p>
            </div>
            <div class="simple-action-row">
                <a class="button-secondary" href="exam-library.php">Voltar para central</a>
                <a class="ghost-button" href="exam-create.php">Montagem manual</a>
            </div>
        </div>

        <div class="simple-note">
            <strong>Estrutura fixa</strong>
            <p>Esse padrão já sai com cabeçalho completo, gabarito visível, questões em card e rodapé pronto para editar.</p>
        </div>
    </article>

    <article class="simple-card">
        <div class="simple-card-head">
            <div>
                <h2>Padrão disponível</h2>
                <p class="helper-text">Esse é o único modelo mantido na plataforma e já carrega os dados básicos completos.</p>
            </div>
        </div>

        <div class="exam-example-grid">
            <?php foreach ($examExamples as $example): ?>
                <?php
                $exampleQuery = exam_example_query($example);
                $filteredExampleQuery = array_filter(
                    $exampleQuery,
                    static fn(mixed $value): bool => $value !== null && $value !== ''
                );
                $isWildcard = true;
                ?>
                <article class="simple-note exam-example-card<?= $isWildcard ? ' exam-example-card-highlight' : '' ?>">
                    <div class="exam-example-card-head">
                        <strong><?= h($example['title']) ?></strong>
                        <?php if ($isWildcard): ?>
                            <span class="badge badge-selected">Padrão da plataforma</span>
                        <?php endif; ?>
                    </div>
                    <p><?= h($example['description']) ?></p>
                    <div class="simple-inline-list">
                        <?php foreach ($example['tags'] as $tag): ?>
                            <span class="badge<?= $isWildcard ? ' badge-selected' : '' ?>"><?= h($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="exam-example-card-actions">
                        <a class="ghost-button" href="exam-model-preview.php?model=<?= h((string) ($example['slug'] ?? '')) ?>">Preview</a>
                        <a class="button-secondary" href="exam-create.php?<?= h(http_build_query($filteredExampleQuery)) ?>">Usar modelo</a>
                        <a class="button" href="exams.php?<?= h(http_build_query($filteredExampleQuery)) ?>">Ir para questões</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<?php render_footer(); ?>
