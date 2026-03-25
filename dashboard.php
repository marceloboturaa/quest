<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();

$user = current_user();
$metrics = dashboard_metrics($user);

render_header(
    'Dashboard',
    'Painel central para acompanhar usuarios, permissoes, banco de questoes e montagem de provas.'
);
?>
<section class="stats-grid">
    <article>
        <span class="metric-copy">Usuarios</span>
        <strong class="metric-number"><?= h((string) $metrics['users']) ?></strong>
        <p><?= has_role('user') ? 'Seu acesso individual no sistema.' : 'Visao do total de usuarios cadastrados.' ?></p>
    </article>
    <article>
        <span class="metric-copy">Questoes</span>
        <strong class="metric-number"><?= h((string) $metrics['questions']) ?></strong>
        <p><?= has_role('user') ? 'Quantidade de questoes criadas pela sua conta.' : 'Volume atual do banco de questoes.' ?></p>
    </article>
    <article>
        <span class="metric-copy">Provas</span>
        <strong class="metric-number"><?= h((string) $metrics['exams']) ?></strong>
        <p><?= has_role('user') ? 'Quantidade de provas montadas pela sua conta.' : 'Volume atual de provas geradas no sistema.' ?></p>
    </article>
</section>

<section class="card-grid">
    <article class="panel">
        <h2>Banco de questoes</h2>
        <p>Cadastre questoes de multipla escolha, discursivas, verdadeiro ou falso e desenho com classificacao, visibilidade e colaboracao.</p>
        <div class="form-actions">
            <a class="button" href="questions.php">Abrir questoes</a>
        </div>
    </article>

    <article class="panel">
        <h2>Controle de acesso</h2>
        <p>
            <?php if (can_manage_users()): ?>
                O master admin pode promover usuarios para admin local e acompanhar toda a base.
            <?php elseif (can_manage_all_questions()): ?>
                O admin local tem visao operacional ampliada sobre as questoes do sistema.
            <?php else: ?>
                Seu perfil atual permite criar e acompanhar suas proprias questoes.
            <?php endif; ?>
        </p>
        <div class="form-actions">
            <?php if (can_manage_users()): ?>
                <a class="button-secondary" href="users.php">Gerenciar usuarios</a>
            <?php else: ?>
                <span class="badge"><?= h(role_label($user['role'])) ?></span>
            <?php endif; ?>
        </div>
    </article>

    <article class="panel">
        <h2>Montagem de provas</h2>
        <p>Selecione questoes do banco colaborativo, misture tipos e gere provas com contador automatico de uso.</p>
        <div class="form-actions">
            <a class="ghost-button" href="exams.php">Abrir provas</a>
        </div>
    </article>
</section>

<section class="info-grid">
    <article class="panel">
        <h2>Leitura rapida do sistema</h2>
        <ul class="mini-list">
            <li>Usuarios criam e editam as proprias questoes.</li>
            <li>Admins locais acompanham a operacao do banco de questoes.</li>
            <li>Master admin gerencia os perfis da plataforma.</li>
            <li>Provas aproveitam questoes visiveis no banco colaborativo.</li>
        </ul>
    </article>

    <article class="panel">
        <h2>Acoes sugeridas</h2>
        <div class="form-actions">
            <a class="button" href="questions.php">Criar questao</a>
            <a class="button-secondary" href="exams.php">Montar prova</a>
            <?php if (can_manage_users()): ?>
                <a class="ghost-button" href="users.php">Revisar usuarios</a>
            <?php endif; ?>
        </div>
    </article>
</section>
<?php render_footer(); ?>
