<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (current_user() !== null) {
    redirect('dashboard.php');
}

render_header(
    'Quest para equipes de ensino',
    'Um MVP em PHP para gestao de usuarios, banco colaborativo de questoes e montagem inicial de provas.'
);
?>
<section class="hero-grid">
    <article class="panel">
        <p class="eyebrow">Projeto pessoal de Marcelo Botura</p>
        <h2>Uma base unica para criar, compartilhar e reutilizar questoes.</h2>
        <p>
            O Quest organiza autoria, colaboracao e montagem de provas em um unico fluxo. A plataforma ja separa
            acesso de <strong>master admin</strong>, <strong>admin local</strong> e <strong>usuario</strong>.
        </p>

        <div class="form-actions">
            <a class="button" href="login.php">Entrar no sistema</a>
            <a class="button-secondary" href="register.php">Criar nova conta</a>
        </div>
    </article>

    <article class="panel">
        <div class="status-pill">
            <span class="status-dot"></span>
            <span>MVP funcional em desenvolvimento com apoio do CNI</span>
        </div>

        <div class="kicker-grid">
            <div class="kicker-card">
                <strong>Banco colaborativo</strong>
                <span>Questoes privadas ou publicas com clonagem e rastreio de autoria base.</span>
            </div>
            <div class="kicker-card">
                <strong>Classificacao academica</strong>
                <span>Disciplina, assunto, nivel de ensino e tipo de questao em uma mesma base.</span>
            </div>
            <div class="kicker-card">
                <strong>Montagem inicial de provas</strong>
                <span>Selecao de questoes do banco com contador de uso automatico.</span>
            </div>
        </div>
    </article>
</section>

<section class="stats-grid">
    <article>
        <span class="metric-copy">Perfis</span>
        <strong class="metric-number">3</strong>
        <p>Master admin, admin local e usuario com acessos separados.</p>
    </article>
    <article>
        <span class="metric-copy">Tipos de questao</span>
        <strong class="metric-number">4</strong>
        <p>Multipla escolha, discursiva, desenho com espaco livre e verdadeiro ou falso.</p>
    </article>
    <article>
        <span class="metric-copy">Fluxos</span>
        <strong class="metric-number">4</strong>
        <p>Cadastro, login, reset de senha e montagem inicial de provas.</p>
    </article>
</section>

<section class="info-grid">
    <article class="panel">
        <h2>O que ja funciona</h2>
        <ul class="mini-list">
            <li>Cadastro e autenticacao de usuarios.</li>
            <li>Recuperacao de senha com token.</li>
            <li>Gestao de perfis pelo master admin.</li>
            <li>Questoes de multipla escolha, discursiva, desenho e verdadeiro ou falso.</li>
        </ul>
    </article>

    <article class="panel">
        <h2>Como a base cresce</h2>
        <ul class="mini-list">
            <li>O autor cria uma questao privada ou publica.</li>
            <li>Questoes publicas podem ser favoritas, clonadas e reutilizadas.</li>
            <li>Cada reutilizacao em prova incrementa o contador de uso.</li>
            <li>O historico de origem ajuda a manter autoria e contexto.</li>
        </ul>
    </article>
</section>
<?php render_footer(); ?>
