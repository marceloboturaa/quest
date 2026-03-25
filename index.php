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
} catch (Throwable) {
    $homeStats = [
        'questions' => 0,
        'public_questions' => 0,
    ];
}

render_header(
    'Quest para criar, organizar e montar avaliacoes',
    'Projeto pessoal de Marcelo Botura, com a ideia apoiada pelo CNI.'
);
?>
<section class="home-hero">
    <article class="home-intro">
        <p class="eyebrow">Marcelo Botura + CNI</p>
        <h2>Um banco de questoes pensado para professores que precisam ganhar tempo sem perder organizacao.</h2>
        <p class="home-lead">
            O Quest centraliza criacao de questoes, classificacao por disciplina e assunto, visibilidade publica ou
            privada e montagem de provas em um fluxo simples.
        </p>

        <div class="form-actions">
            <a class="button" href="login.php">Entrar no sistema</a>
            <a class="button-secondary" href="register.php">Criar conta</a>
        </div>

        <div class="home-signals">
            <span>Projeto pessoal de Marcelo Botura</span>
            <span>Ideia apoiada pelo CNI</span>
            <span><?= $homeStats['questions'] ?> questoes cadastradas</span>
        </div>
    </article>

    <article class="home-showcase">
        <div class="status-pill">
            <span class="status-dot"></span>
            <span>Versao ativa com recursos reais ja disponiveis</span>
        </div>

        <div class="home-showcase-grid">
            <div class="home-showcase-card">
                <span class="home-showcase-label">Criacao</span>
                <strong>Multipla escolha, discursiva, desenho e verdadeiro ou falso</strong>
            </div>
            <div class="home-showcase-card">
                <span class="home-showcase-label">Organizacao</span>
                <strong>Disciplina, assunto, nivel de ensino, filtros e favoritos</strong>
            </div>
            <div class="home-showcase-card">
                <span class="home-showcase-label">Colaboracao</span>
                <strong>Questoes publicas podem ser clonadas sem alterar a original</strong>
            </div>
            <div class="home-showcase-card">
                <span class="home-showcase-label">Provas</span>
                <strong>Selecao de questoes, contador de uso e exportacao em PDF</strong>
            </div>
        </div>
    </article>
</section>

<section class="home-metrics">
    <article>
        <span class="metric-copy">Questoes cadastradas</span>
        <strong class="metric-number"><?= $homeStats['questions'] ?></strong>
        <p><?= $homeStats['public_questions'] ?> publicas prontas para busca, clonagem e reutilizacao.</p>
    </article>
    <article>
        <span class="metric-copy">Perfis de acesso</span>
        <strong class="metric-number">3</strong>
        <p>Master admin, admin local e usuario com niveis diferentes de acao.</p>
    </article>
    <article>
        <span class="metric-copy">Tipos de questao</span>
        <strong class="metric-number">4</strong>
        <p>Base pronta para questoes objetivas, abertas, visuais e de validacao rapida.</p>
    </article>
</section>

<section class="home-strip">
    <article class="home-strip-card">
        <span class="home-strip-kicker">Hoje no produto</span>
        <h2>O sistema ja resolve o ciclo principal de trabalho.</h2>
        <p>
            O professor cria, classifica, salva como publico ou privado, pesquisa no banco, monta a prova e exporta.
        </p>
    </article>
</section>

<section class="info-grid home-detail-grid">
    <article class="panel home-panel">
        <h2>O que ja esta funcionando</h2>
        <ul class="mini-list">
            <li>Cadastro, login e recuperacao de senha.</li>
            <li>Criacao de questoes com alternativas dinamicas.</li>
            <li>Visibilidade publica ou privada com bloqueio de edicao por terceiros.</li>
            <li>Clonagem de questoes publicas com preservacao da referencia de origem.</li>
            <li>Filtros por disciplina, assunto, tipo, nivel, autor e visibilidade.</li>
        </ul>
    </article>

    <article class="panel home-panel">
        <h2>Como a base cresce</h2>
        <ul class="mini-list">
            <li>Cada usuario pode construir seu proprio acervo e reutilizar o que ja foi publicado.</li>
            <li>Questoes favoritas ajudam a montar provas mais rapido.</li>
            <li>O contador de uso mostra o que ja foi mais aproveitado em avaliacoes.</li>
            <li>A classificacao academica deixa a busca mais precisa conforme a base aumenta.</li>
            <li>A fonte oficial pode ser registrada quando a questao vem de material publico.</li>
        </ul>
    </article>
</section>

<section class="home-flow">
    <article class="panel home-flow-card">
        <span class="home-strip-kicker">Fluxo simples</span>
        <div class="home-flow-steps">
            <div>
                <strong>1. Criar</strong>
                <p>Cadastre a questao, escolha o tipo e defina a classificacao.</p>
            </div>
            <div>
                <strong>2. Organizar</strong>
                <p>Filtre por disciplina, assunto, nivel e visibilidade sempre que precisar.</p>
            </div>
            <div>
                <strong>3. Montar</strong>
                <p>Selecione as melhores questoes e gere a prova em PDF.</p>
            </div>
        </div>
    </article>
</section>
<?php render_footer(); ?>
