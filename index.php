<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (current_user() !== null) {
    redirect('dashboard.php');
}

render_header(
    'Quest para equipes de ensino',
    'Um MVP em PHP para gestao de usuarios, permissoes e criacao de questoes em multipla escolha, discursiva e verdadeiro ou falso.'
);
?>
<section class="hero-grid">
    <article class="panel">
        <p class="eyebrow">Projeto pessoal de Marcelo Botura</p>
        <h2>Controle central, operacao local e autores na mesma base.</h2>
        <p>
            Esta versao do Quest ja separa acesso de <strong>master admin</strong>, <strong>admin local</strong> e
            <strong>usuario</strong>, com autenticacao, cadastro, recuperacao de senha e cadastro de questoes.
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

        <ul class="mini-list">
            <li>Cadastro de usuarios, login e logout ja implementados.</li>
            <li>Recuperacao de senha por token com registro de teste em <code>storage/mail.log</code>.</li>
            <li>Master admin pode promover usuarios comuns para admin local.</li>
            <li>Usuarios autenticados ja podem cadastrar questoes de multipla escolha, discursiva e verdadeiro ou falso.</li>
        </ul>
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
        <strong class="metric-number">3</strong>
        <p>Multipla escolha, discursiva e verdadeiro ou falso.</p>
    </article>
    <article>
        <span class="metric-copy">Fluxos</span>
        <strong class="metric-number">4</strong>
        <p>Cadastro, login, reset de senha e criacao de questoes.</p>
    </article>
</section>
<?php render_footer(); ?>
