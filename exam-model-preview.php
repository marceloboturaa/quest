<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_examples.php';
require_once __DIR__ . '/includes/exam_document_renderer.php';

require_login();

$user = current_user();
$slug = trim((string) ($_GET['model'] ?? 'modelo-padrao'));
$example = exam_example_find($slug, (string) ($user['name'] ?? '')) ?? exam_example_find('modelo-padrao', (string) ($user['name'] ?? ''));

if ($example === null) {
    flash('error', 'Modelo de prova não encontrado.');
    redirect('exam-models.php');
}

$previewPayload = exam_example_preview_payload($example);
$document = exam_document_view_data(
    $previewPayload['exam'],
    $previewPayload['questions'],
    $previewPayload['question_options']
);
$exampleQuery = exam_example_query($example);
$query = http_build_query(array_filter(
    $exampleQuery,
    static fn(mixed $value): bool => $value !== null && $value !== ''
));

render_header(
    'Preview do modelo padrão',
    'Veja a prova simulada completa antes de usar o padrão único no fluxo normal de construção.'
);
?>

<style>
<?= exam_document_styles(false) ?>
</style>

<section class="simple-stack">
    <article class="simple-card exam-model-preview-hero">
        <div class="simple-card-head">
            <div>
                <h2><?= h((string) ($example['title'] ?? 'Modelo de prova')) ?></h2>
                <p class="helper-text"><?= h((string) ($example['description'] ?? 'Modelo pronto para edição.')) ?></p>
            </div>
            <div class="simple-action-row">
                <a class="ghost-button" href="exam-models.php">Voltar</a>
                <a class="button-secondary" href="exam-create.php?<?= h($query) ?>">Usar modelo</a>
                <a class="button" href="exams.php?<?= h($query) ?>">Usar e ir para questões</a>
            </div>
        </div>

        <div class="simple-inline-list">
            <?php foreach ((array) ($example['tags'] ?? []) as $tag): ?>
                <span class="badge"><?= h((string) $tag) ?></span>
            <?php endforeach; ?>
            <span class="badge badge-selected"><?= h((string) count($previewPayload['questions'])) ?> questões simuladas</span>
        </div>
    </article>

    <section class="panel">
        <?= exam_document_render_sheet($document) ?>
    </section>
</section>

<?php render_footer(); ?>
