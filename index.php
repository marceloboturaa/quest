<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (current_user() !== null) {
    redirect('dashboard.php');
}

$homeStats = [
    'questions' => 0,
    'public_questions' => 0,
    'exams' => 0,
];

try {
    $homeStats['questions'] = (int) db()->query('SELECT COUNT(*) FROM questions')->fetchColumn();
    $homeStats['public_questions'] = (int) db()->query('SELECT COUNT(*) FROM questions WHERE visibility = "public"')->fetchColumn();
    $homeStats['exams'] = (int) db()->query('SELECT COUNT(*) FROM exams')->fetchColumn();
} catch (Throwable) {
    $homeStats = [
        'questions' => 0,
        'public_questions' => 0,
        'exams' => 0,
    ];
}

render_header(
    'Crie provas em minutos, com inteligencia e organizacao profissional',
    'Banco de questoes, integracao ENEM, montagem guiada e exportacao profissional em um fluxo unico.',
    false
);
?>

<section class="home-stage">
    <div class="home-stage-copy">
        <p class="eyebrow">Quest 2.0</p>
        <h1>Crie, organize e publique avaliacoes com um fluxo mais rapido.</h1>
        <p class="page-subtitle">Monte provas em poucos minutos com busca inteligente, banco proprio, importacao da API ENEM e saida pronta para distribuicao.</p>
        <div class="home-actions form-actions">
            <a class="button" href="login.php">Entrar</a>
            <a class="button-secondary" href="register.php">Criar conta</a>
        </div>
        <div class="home-signals form-actions">
            <span class="badge"><?= h((string) $homeStats['questions']) ?> questoes cadastradas</span>
            <span class="badge"><?= h((string) $homeStats['public_questions']) ?> questoes publicas</span>
            <span class="badge"><?= h((string) $homeStats['exams']) ?> provas geradas</span>
        </div>
    </div>

    <div class="home-stage-panel">
        <div class="home-panel-list">
            <article class="home-panel-item">
                <strong>Banco de questoes</strong>
                <p>Cadastre itens de multipla escolha, discursiva, verdadeiro ou falso e desenho em um unico ambiente.</p>
            </article>
            <article class="home-panel-item">
                <strong>Integracao ENEM</strong>
                <p>Busque questoes oficiais por ano e idioma e importe para o banco interno em poucos cliques.</p>
            </article>
            <article class="home-panel-item">
                <strong>Geracao automatizada</strong>
                <p>Parta do cadastro da prova, filtre o banco e monte a avaliacao no workspace de selecao.</p>
            </article>
            <article class="home-panel-item">
                <strong>Exportacao profissional</strong>
                <p>Revise em preview e gere PDF com cabecalho e estrutura pronta para imprimir.</p>
            </article>
        </div>
    </div>
</section>

<section class="home-metrics">
    <article>
        <span class="metric-copy">Banco de questoes</span>
        <strong class="metric-number"><?= h((string) $homeStats['questions']) ?></strong>
        <p>Base organizada por disciplina, assunto, nivel, tipo e visibilidade.</p>
    </article>
    <article>
        <span class="metric-copy">Integracao ENEM</span>
        <strong class="metric-number">2009+</strong>
        <p>Importacao de questoes oficiais para acelerar a producao de avaliacoes adaptadas.</p>
    </article>
    <article>
        <span class="metric-copy">Fluxo profissional</span>
        <strong class="metric-number">4 etapas</strong>
        <p>Criar, montar, visualizar e exportar em um processo mais claro para o usuario.</p>
    </article>
</section>

<section class="home-columns">
    <article class="panel home-card">
        <span class="home-card-kicker">O que voce ganha</span>
        <h2>Uma plataforma de avaliacao mais organizada e escalavel.</h2>
        <ul class="mini-list">
            <li>Banco de questoes proprio com compartilhamento publico e privado.</li>
            <li>Montagem de provas a partir de itens internos ou importados do ENEM.</li>
            <li>Preview da avaliacao antes da exportacao em PDF.</li>
            <li>Controle de usuarios, perfis e catalogos de disciplina e assunto.</li>
        </ul>
    </article>

    <article class="panel home-flow-card">
        <div class="home-flow-head">
            <span class="home-card-kicker">Fluxo</span>
            <h2>Da questao ao PDF sem pular etapas</h2>
        </div>
        <div class="home-flow-steps">
            <div>
                <strong>1. Busque</strong>
                <p>Pesquise no banco interno ou na API ENEM.</p>
            </div>
            <div>
                <strong>2. Monte</strong>
                <p>Escolha os dados da prova e selecione as questoes.</p>
            </div>
            <div>
                <strong>3. Revise</strong>
                <p>Abra o preview e exporte a versao final em PDF.</p>
            </div>
        </div>
    </article>
</section>

<?php render_footer(); ?>
