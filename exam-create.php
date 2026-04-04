<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();

$user = current_user();

render_header(
    'Criação de provas',
    'Espaço reservado para montagem de provas e geração de modelos.',
    true,
    true
);
?>

<section class="simple-stack">
    <article class="simple-card">
        <div class="simple-card-head">
            <div>
                <span class="exam-create-maintenance-pill">Em manutenção</span>
                <h2>Criação de provas</h2>
                <p class="helper-text">A central de montagem de provas ainda está sendo ajustada para o novo fluxo do sistema.</p>
            </div>
            <div class="simple-inline-list">
                <span class="badge"><?= h(role_label((string) $user['role'])) ?></span>
                <span class="badge">Acesso em breve</span>
            </div>
        </div>

        <div class="simple-list">
            <div class="simple-list-item">
                <div>
                    <strong>Montagem automática</strong>
                    <p>Escolha questões, organize seções e gere a prova com o padrão do seu site.</p>
                </div>
                <span class="exam-create-future-pill">Futuro</span>
            </div>
            <div class="simple-list-item">
                <div>
                    <strong>Identificação da prova</strong>
                    <p>Nome do professor, instituição e código da prova no formato do painel.</p>
                </div>
                <span class="exam-create-future-pill">Futuro</span>
            </div>
            <div class="simple-list-item">
                <div>
                    <strong>Impressão e Xerox</strong>
                    <p>Envio direto para a fila de impressão quando o módulo estiver pronto.</p>
                </div>
                <span class="exam-create-future-pill">Futuro</span>
            </div>
        </div>

        <div class="simple-action-row" style="margin-top: 18px;">
            <a class="ghost-button" href="questions.php">Voltar para Questões</a>
            <a class="button" href="dashboard.php">Ir para início</a>
        </div>
    </article>
</section>

<?php render_footer(); ?>
