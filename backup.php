<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/includes/backup_repository.php';

require_role(['master_admin', 'local_admin']);

$user = current_user();

if (is_post()) {
    abort_if_invalid_csrf();

    if ((string) ($_POST['action'] ?? '') === 'run_backup_now') {
        try {
            backup_execute_run((int) $user['id'], 'manual');
            flash('success', 'Backup executado com sucesso e enviado ao Google Drive.');
        } catch (Throwable $throwable) {
            flash('error', $throwable->getMessage());
        }

        redirect('backup.php');
    }
}

$summary = backup_status_summary();
$history = backup_history(20);
$latestRun = $history[0] ?? null;

render_header(
    'Backup',
    'Painel de seguranca para executar backup manual, acompanhar o historico e preparar o agendamento diario no Google Drive.'
);
?>

<section class="stats-grid stats-grid-four">
    <article>
        <span class="metric-copy">Backup ativo</span>
        <strong class="metric-number"><?= $summary['enabled'] ? 'Sim' : 'Nao' ?></strong>
        <p>Rotina de backup habilitada na configuracao do sistema.</p>
    </article>
    <article>
        <span class="metric-copy">Google Drive</span>
        <strong class="metric-number"><?= $summary['google_drive_ready'] ? 'OK' : 'Pendente' ?></strong>
        <p>Credencial e pasta remota para envio automatico.</p>
    </article>
    <article>
        <span class="metric-copy">Horario diario</span>
        <strong class="metric-number"><?= h($summary['schedule_time']) ?></strong>
        <p>Horario previsto para a tarefa agendada.</p>
    </article>
    <article>
        <span class="metric-copy">Ultimo status</span>
        <strong class="metric-number"><?= h($latestRun['status'] ?? 'sem') ?></strong>
        <p><?= h($latestRun ? datetime_label((string) $latestRun['started_at']) : 'Nenhum backup executado ainda.') ?></p>
    </article>
</section>

<section class="card-grid">
    <article class="panel">
        <h2>Executar agora</h2>
        <p>Gera dump do banco, monta um ZIP do sistema e envia o arquivo para a pasta configurada no Google Drive.</p>
        <form method="post" class="form-actions">
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="run_backup_now">
            <button class="button" type="submit" <?= $summary['google_drive_ready'] && $summary['enabled'] ? '' : 'disabled' ?>>Rodar backup manual</button>
        </form>
    </article>

    <article class="panel">
        <h2>Status da integracao</h2>
        <ul class="mini-list">
            <li><strong>Credencial:</strong> <?= $summary['google_drive_credentials_exists'] ? 'Arquivo encontrado' : 'Arquivo ausente' ?></li>
            <li><strong>Pasta Drive:</strong> <?= h($summary['google_drive_folder_id'] !== '' ? $summary['google_drive_folder_id'] : 'Nao configurada') ?></li>
            <li><strong>Mysqldump:</strong> <?= h($summary['mysqldump_path']) ?></li>
            <li><strong>Pasta local:</strong> <?= h($summary['local_root']) ?></li>
        </ul>
    </article>

    <article class="panel">
        <h2>Agendamento diario</h2>
        <p>O script CLI ja esta pronto. Para registrar a tarefa no Windows, use o comando abaixo no PowerShell como administrador.</p>
        <div class="auth-note">
            <code><?= h(backup_register_task_command()) ?></code>
        </div>
        <p class="helper-text">A tarefa criada chama o PHP diretamente e executa o script de backup todos os dias.</p>
    </article>

    <article class="panel">
        <h2>Onde configurar</h2>
        <p>Defina as credenciais do Google Drive e a pasta remota no `config.local.php` ou por variaveis de ambiente.</p>
        <div class="auth-note">
            <strong>Esperado</strong><br>
            <code>QUEST_GOOGLE_DRIVE_CREDENTIALS</code><br>
            <code>QUEST_GOOGLE_DRIVE_FOLDER_ID</code><br>
            <code>QUEST_BACKUP_SCHEDULE_TIME</code>
        </div>
    </article>
</section>

<section class="panel">
    <div class="workspace-panel-head">
        <div>
            <p class="workspace-kicker">Historico</p>
            <h2>Execucoes de backup</h2>
        </div>
        <span class="badge"><?= h((string) count($history)) ?> registros</span>
    </div>

    <?php if ($history === []): ?>
        <div class="empty-state">
            <h2>Nenhum backup registrado</h2>
            <p>Depois da primeira execucao, o historico aparece aqui com status e link do arquivo remoto.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>Inicio</th>
                    <th>Tipo</th>
                    <th>Status</th>
                    <th>Arquivo</th>
                    <th>Tamanho</th>
                    <th>Responsavel</th>
                    <th>Drive</th>
                    <th>Erro</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $run): ?>
                    <tr>
                        <td><?= h(datetime_label((string) $run['started_at'])) ?></td>
                        <td><?= h($run['trigger_type'] === 'scheduled' ? 'Agendado' : 'Manual') ?></td>
                        <td>
                            <span class="badge <?= $run['status'] === 'success' ? 'badge-success' : ($run['status'] === 'failed' ? 'badge-accent' : '') ?>">
                                <?= h((string) $run['status']) ?>
                            </span>
                        </td>
                        <td><?= h((string) ($run['file_name'] ?? '-')) ?></td>
                        <td><?= h(isset($run['size_bytes']) && $run['size_bytes'] ? number_format(((int) $run['size_bytes']) / 1048576, 2, ',', '.') . ' MB' : '-') ?></td>
                        <td><?= h((string) ($run['triggered_by_name'] ?? 'Sistema')) ?></td>
                        <td>
                            <?php if (!empty($run['drive_file_link'])): ?>
                                <a class="ghost-button" href="<?= h((string) $run['drive_file_link']) ?>" target="_blank" rel="noreferrer">Abrir</a>
                            <?php else: ?>
                                <span class="helper-text">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= h((string) ($run['error_message'] ?: '-')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php render_footer(); ?>
