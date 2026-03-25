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
    'Quest para criar, organizar e montar avaliações',
    'Projeto pessoal de Marcelo Botura, com a ideia apoiada pelo CNI.',
    false
);
?>
<section class="home-stage">
    <article class="home-stage-copy">
        <p class="eyebrow">Projeto pessoal de Marcelo Botura</p>
        <h1>Banco de questões para criar, organizar e montar avaliações com mais clareza.</h1>
        <p class="home-lead">
            O Quest reúne criação de questões, classificação por disciplina e assunto, visibilidade pública ou privada
            e montagem de provas em um fluxo direto, sem complicar o trabalho do professor.
        </p>

        <div class="form-actions home-actions">
            <a class="button" href="login.php">Entrar no sistema</a>
            <a class="button-secondary" href="register.php">Criar conta</a>
        </div>

        <div class="home-signals">
            <span>Ideia apoiada pelo CNI</span>
            <span><?= $homeStats['questions'] ?> questões cadastradas</span>
            <span><?= $homeStats['public_questions'] ?> públicas disponíveis</span>
        </div>
    </article>

    <aside class="home-stage-panel">
        <div class="status-pill">
            <span class="status-dot"></span>
            <span>Plataforma em desenvolvimento ativo</span>
        </div>

        <div class="home-panel-list">
            <div class="home-panel-item">
                <strong>Questões em um só lugar</strong>
                <p>Múltipla escolha, discursiva, desenho e verdadeiro ou falso na mesma base.</p>
            </div>
            <div class="home-panel-item">
                <strong>Organização de verdade</strong>
                <p>Filtros por disciplina, assunto, nível, autor e visibilidade.</p>
            </div>
            <div class="home-panel-item">
                <strong>Reuso com controle</strong>
                <p>Questões públicas podem ser clonadas sem alterar a original.</p>
            </div>
        </div>
    </aside>
</section>

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

<section class="home-columns">
    <article class="panel home-card">
        <span class="home-card-kicker">O que já funciona</span>
        <h2>O núcleo do sistema já está pronto para uso.</h2>
        <ul class="mini-list">
            <li>Cadastro, login e recuperação de senha.</li>
            <li>Criação de questões com alternativas dinâmicas.</li>
            <li>Controle de visibilidade pública ou privada.</li>
            <li>Clonagem de questões públicas com referência de origem.</li>
            <li>Busca com filtros por disciplina, assunto, tipo, nível, autor e visibilidade.</li>
        </ul>
    </article>

    <article class="panel home-card">
        <span class="home-card-kicker">Como cresce</span>
        <h2>A base fica mais útil conforme o uso aumenta.</h2>
        <ul class="mini-list">
            <li>Cada usuário pode montar seu próprio acervo e reaproveitar o que foi publicado.</li>
            <li>Favoritos aceleram a seleção para novas provas.</li>
            <li>O contador de uso mostra o que já foi mais aproveitado em avaliações.</li>
            <li>A classificação por disciplina e assunto melhora a pesquisa da base.</li>
            <li>A origem pode ser registrada quando a questão vier de fonte pública.</li>
        </ul>
    </article>
</section>

<section class="panel home-flow-card">
    <div class="home-flow-head">
        <span class="home-card-kicker">Fluxo simples</span>
        <h2>Três passos para sair da questão até a prova.</h2>
    </div>
    <div class="home-flow-steps">
        <div>
            <strong>1. Criar</strong>
            <p>Cadastre a questão, escolha o tipo e defina a classificação.</p>
        </div>
        <div>
            <strong>2. Organizar</strong>
            <p>Filtre por disciplina, assunto, nível e visibilidade sempre que precisar.</p>
        </div>
        <div>
            <strong>3. Montar</strong>
            <p>Selecione as melhores questões e gere a prova em PDF.</p>
        </div>
    </div>
</section>
<?php render_footer(); ?>
