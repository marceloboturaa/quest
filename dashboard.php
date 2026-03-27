<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/dashboard_repository.php';

require_login();

$user = current_user();
$metrics = dashboard_metrics($user);
$recentQuestions = dashboard_recent_questions((int) $user['id'], can_manage_all_questions(), 5);
$recentExams = dashboard_recent_exams((int) $user['id'], can_manage_all_questions(), 5);
$publicQuestionsTotal = dashboard_public_questions_total();
$myQuestionsTotal = dashboard_user_questions_total((int) $user['id']);

render_header(
    'Dashboard',
    'Centro de controle para acompanhar questoes, provas, publicacao de itens e proximos passos da operacao.'
);
?>

<section class="stats-grid">
    <article>
        <span class="metric-copy">Total de questoes</span>
        <strong class="metric-number"><?= h((string) $metrics['questions']) ?></strong>
        <p>Volume atual do banco pronto para busca, reutilizacao e montagem.</p>
    </article>
    <article>
        <span class="metric-copy">Total de provas</span>
        <strong class="metric-number"><?= h((string) $metrics['exams']) ?></strong>
        <p>Avaliacoes criadas no ambiente com contador de questoes e exportacao.</p>
    </article>
    <article>
        <span class="metric-copy">Questoes publicas</span>
        <strong class="metric-number"><?= h((string) $publicQuestionsTotal) ?></strong>
        <p>Itens compartilhados no banco colaborativo e disponiveis para outras contas.</p>
    </article>
</section>

<section class="card-grid">
    <article class="panel">
        <h2>Suas questoes</h2>
        <p><?= h((string) $myQuestionsTotal) ?> itens vinculados a sua conta para edicao, prova e publicacao.</p>
        <div class="form-actions">
            <a class="button" href="questions.php">Abrir workspace</a>
            <a class="ghost-button" href="questions.php?new=1">Criar questao</a>
        </div>
    </article>

    <article class="panel">
        <h2>Nova prova</h2>
        <p>Comece pelos dados da avaliacao e siga para a montagem com filtros e selecao direta de questoes.</p>
        <div class="form-actions">
            <a class="button-secondary" href="exam-create.php">+ Nova prova</a>
        </div>
    </article>

    <article class="panel">
        <h2>Importacao ENEM</h2>
        <p>Busque questoes oficiais por ano e idioma e traga para o banco interno como base de trabalho.</p>
        <div class="form-actions">
            <a class="ghost-button" href="enem.php">Abrir API ENEM</a>
        </div>
    </article>

    <article class="panel">
        <h2>Controle de acesso</h2>
        <p><?= can_manage_users() ? 'Voce pode administrar usuarios e perfis do sistema.' : (can_manage_all_questions() ? 'Seu perfil acompanha a operacao ampliada do banco de questoes.' : 'Seu perfil atual esta focado na sua producao e nas suas provas.') ?></p>
        <div class="form-actions">
            <?php if (can_manage_users()): ?>
                <a class="button-secondary" href="users.php">Gerenciar usuarios</a>
            <?php else: ?>
                <span class="badge"><?= h(role_label($user['role'])) ?></span>
            <?php endif; ?>
        </div>
    </article>
</section>

<section class="split-card">
    <section>
        <h2>Provas recentes</h2>
        <?php if ($recentExams === []): ?>
            <div class="empty-state">
                <h2>Nenhuma prova recente</h2>
                <p>Crie a primeira avaliacao para acompanhar o historico aqui.</p>
            </div>
        <?php else: ?>
            <div class="workspace-quick-list">
                <?php foreach ($recentExams as $exam): ?>
                    <article class="workspace-quick-item">
                        <strong><?= h((string) $exam['title']) ?></strong>
                        <p><?= h((string) $exam['total_questions']) ?> questoes | <?= h(date('d/m/Y H:i', strtotime((string) $exam['created_at']))) ?></p>
                        <div class="form-actions">
                            <a class="ghost-button" href="exam-create.php?edit=<?= h((string) $exam['id']) ?>">Editar</a>
                            <a class="ghost-button" href="exam-preview.php?id=<?= h((string) $exam['id']) ?>">Preview</a>
                            <a class="button-secondary" href="exam-pdf.php?id=<?= h((string) $exam['id']) ?>">PDF</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section>
        <h2>Questoes recentes</h2>
        <?php if ($recentQuestions === []): ?>
            <div class="empty-state">
                <h2>Nenhuma questao recente</h2>
                <p>Crie ou importe uma questao para iniciar o banco.</p>
            </div>
        <?php else: ?>
            <div class="workspace-quick-list">
                <?php foreach ($recentQuestions as $question): ?>
                    <article class="workspace-quick-item">
                        <strong><?= h((string) $question['title']) ?></strong>
                        <p><?= h(question_type_label((string) $question['question_type'])) ?> | <?= h($question['discipline_name'] ?? 'Sem disciplina') ?></p>
                        <div class="form-actions">
                            <span class="badge"><?= h(visibility_label((string) $question['visibility'])) ?></span>
                            <a class="ghost-button" href="questions.php?edit=<?= h((string) $question['id']) ?>">Abrir</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</section>

<section class="info-grid">
    <article class="panel">
        <h2>Acoes rapidas</h2>
        <div class="form-actions">
            <a class="button" href="exam-create.php">+ Nova prova</a>
            <a class="button-secondary" href="questions.php?new=1">+ Nova questao</a>
            <a class="ghost-button" href="enem.php">Importar ENEM</a>
        </div>
    </article>

    <article class="panel">
        <h2>Leitura do sistema</h2>
        <ul class="mini-list">
            <li>Questoes entram pelo workspace ou pela importacao ENEM.</li>
            <li>Provas agora seguem um fluxo separado: criar, montar, visualizar e exportar.</li>
            <li>O dashboard virou um painel operacional, nao apenas um resumo numerico.</li>
        </ul>
    </article>
</section>

<?php render_footer(); ?>
