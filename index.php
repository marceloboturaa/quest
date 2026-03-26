<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (current_user() !== null) {
    redirect('dashboard.php');
}

$homeStats = [
    'questions' => 0,
    'public_questions' => 0,
];

try {
    $homeStats['questions'] = (int) db()->query('SELECT COUNT(*) FROM questions')->fetchColumn();
    $homeStats['public_questions'] = (int) db()->query("SELECT COUNT(*) FROM questions WHERE visibility = 'public'")->fetchColumn();
} catch (Throwable $exception) {
    $homeStats = [
        'questions' => 0,
        'public_questions' => 0,
    ];
}

render_header(
    'Quest para criar, organizar e montar avaliações',
    'Projeto pessoal de Marcelo Botura, com a ideia apoiada pelo CNI.',
    false
);
?>

<section class="home-metrics">
    <article>
        <span class="metric-copy">Questões cadastradas</span>
        <strong class="metric-number"><?= $homeStats['questions'] ?></strong>
        <p><?= $homeStats['public_questions'] ?> públicas prontas para busca, clonagem e reutilização.</p>
    </article>
    <article>
        <span class="metric-copy">Perfis de acesso</span>
        <strong class="metric-number">3</strong>
        <p>Master admin, admin local e usuário com níveis diferentes de ação.</p>
    </article>
    <article>
        <span class="metric-copy">Tipos de questão</span>
        <strong class="metric-number">4</strong>
        <p>Base pronta para questões objetivas, abertas, visuais e de validação rápida.</p>
    </article>
</section>


<?php render_footer(); ?>
