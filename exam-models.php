<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_examples.php';

require_login();

$user = current_user();
$examExamples = exam_example_presets((string) ($user['name'] ?? ''));

render_header(
    'Modelos de prova',
    'Escolha um modelo pronto. Quando faltarem dados, o sistema usa o modelo coringa com informações completas.'
);
?>

<section class="simple-stack">
    <article class="simple-card exam-library-hero">
        <div class="simple-card-head">
            <div>
                <h2>Modelos prontos</h2>
                <p class="helper-text">Abra um modelo completo, carregue para edição ou siga direto para a seleção de questões.</p>
            </div>
            <div class="simple-action-row">
                <a class="button-secondary" href="exam-library.php">Voltar para central</a>
                <a class="ghost-button" href="exam-create.php">Montagem manual</a>
            </div>
        </div>

        <div class="simple-note">
            <strong>Modelo coringa</strong>
            <p>Use quando quiser uma prova pronta para editar, com escola, professor, turma, data, cabeçalho, corpo e rodapé já preenchidos.</p>
        </div>
    </article>

    <article class="simple-card">
        <div class="simple-card-head">
            <div>
                <h2>Biblioteca de modelos</h2>
                <p class="helper-text">Os modelos já saem com os dados básicos completos para evitar campos vazios.</p>
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
                $isWildcard = (string) ($example['slug'] ?? '') === 'modelo-coringa';
                ?>
                <article class="simple-note exam-example-card<?= $isWildcard ? ' exam-example-card-highlight' : '' ?>">
                    <div class="exam-example-card-head">
                        <strong><?= h($example['title']) ?></strong>
                        <?php if ($isWildcard): ?>
                            <span class="badge badge-selected">Modelo base</span>
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
