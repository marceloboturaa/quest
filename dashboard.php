<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();

$user = current_user();
$metrics = dashboard_metrics($user);

render_header(
    'Dashboard',
    'Painel central para acompanhar usuarios, permissoes e o banco de questoes do Quest.'
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
        <span class="metric-copy">Admins locais</span>
        <strong class="metric-number"><?= h((string) $metrics['local_admins']) ?></strong>
        <p><?= has_role('master_admin') ? 'Usuarios promovidos para operacao local.' : 'Perfil operacional acima do usuario comum.' ?></p>
    </article>
</section>

<section class="card-grid">
    <article class="panel">
        <h2>Banco de questoes</h2>
        <p>Cadastre itens de multipla escolha, discursivos e verdadeiro ou falso com metadados de status e dificuldade.</p>
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
        <h2>Recuperacao de senha</h2>
        <p>O fluxo de reset gera token seguro e usa e-mail, com registro local do conteudo enviado para facilitar testes.</p>
        <div class="form-actions">
            <a class="ghost-button" href="forgot-password.php">Testar fluxo</a>
        </div>
    </article>
</section>
<?php render_footer(); ?>
