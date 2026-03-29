<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/xerox_repository.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];
$isMaster = has_role('master_admin');
$isXerox = has_role('xerox');
$canReplyInbox = has_role(['master_admin', 'xerox']);
$xeroxEnabled = xerox_is_available();

if (is_post()) {
    abort_if_invalid_csrf();

    $action = (string) ($_POST['action'] ?? '');

    if (($action === 'send_user_message' || $action === 'send_suggestion') && !$isMaster) {
        $recipientScope = trim((string) ($_POST['recipient_scope'] ?? 'administrator'));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));

        if ($body === '') {
            flash('error', 'Escreva a mensagem antes de enviar.');
            redirect('messages.php');
        }

        if ($recipientScope === 'xerox') {
            if (!$xeroxEnabled) {
                flash('error', 'O setor Xerox não está habilitado neste momento.');
                redirect('messages.php');
            }

            if ($subject === '') {
                $subject = 'Mensagem para o Xerox';
            }

            $sent = messages_send_to_xerox_team($userId, $subject, $body);
            $successMessage = 'Mensagem enviada para o setor Xerox.';
            $errorMessage = 'Nenhum usuário Xerox disponível para receber a mensagem.';
        } else {
            if ($subject === '') {
                $subject = 'Mensagem para o administrador';
            }

            $sent = messages_send_suggestion($userId, $subject, $body);
            $successMessage = 'Mensagem enviada para o administrador.';
            $errorMessage = 'Nenhum administrador disponível para receber a mensagem.';
        }

        flash($sent > 0 ? 'success' : 'error', $sent > 0 ? $successMessage : $errorMessage);
        redirect('messages.php');
    }

    if ($action === 'delete_message') {
        $messageId = (int) ($_POST['message_id'] ?? 0);

        if ($messageId > 0 && messages_delete_for_recipient($messageId, $userId)) {
            flash('success', 'Mensagem excluída da sua caixa de entrada.');
        } else {
            flash('error', 'Não foi possível excluir a mensagem.');
        }

        redirect('messages.php');
    }

    if ($action === 'send_broadcast' && $isMaster) {
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));

        if ($body === '') {
            flash('error', 'Escreva o aviso antes de enviar.');
            redirect('messages.php');
        }

        if ($subject === '') {
            $subject = 'Houve atualização no sistema';
        }

        $sent = messages_send_broadcast($userId, $subject, $body);
        flash($sent > 0 ? 'success' : 'error', $sent > 0 ? 'Aviso enviado para todos os usuários.' : 'Nenhum usuário disponível para receber o aviso.');
        redirect('messages.php');
    }

    if ($action === 'send_direct' && $isMaster) {
        $recipientUserId = (int) ($_POST['recipient_user_id'] ?? 0);
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));

        if ($recipientUserId <= 0 || $body === '') {
            flash('error', 'Escolha um usuário e escreva a mensagem antes de enviar.');
            redirect('messages.php');
        }

        if ($subject === '') {
            $subject = 'Mensagem do administrador';
        }

        $sent = messages_send_direct($userId, $recipientUserId, $subject, $body);
        flash(
            $sent ? 'success' : 'error',
            $sent ? 'Mensagem enviada para o usuário selecionado.' : 'Não foi possível enviar a mensagem agora.'
        );
        redirect('messages.php');
    }

    if ($action === 'reply_message' && $canReplyInbox) {
        $messageId = (int) ($_POST['message_id'] ?? 0);
        $body = trim((string) ($_POST['body'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $originalMessage = $messageId > 0 ? messages_find_for_recipient($messageId, $userId) : null;

        if (!$originalMessage || (int) ($originalMessage['sender_user_id'] ?? 0) <= 0) {
            flash('error', 'Mensagem original não encontrada para resposta.');
            redirect('messages.php');
        }

        if ($body === '') {
            flash('error', 'Escreva a resposta antes de enviar.');
            redirect('messages.php');
        }

        $replyDefaultSubject = $isMaster ? 'Resposta do administrador' : 'Resposta do Xerox';
        $subject = $subject !== '' ? $subject : $replyDefaultSubject;

        if (!messages_send_reply($userId, (int) $originalMessage['sender_user_id'], $subject, $body, $messageId)) {
            flash('error', 'Não foi possível enviar a resposta agora.');
        } else {
            messages_mark_as_read($messageId, $userId);
            flash('success', 'Resposta enviada com sucesso.');
        }

        redirect('messages.php');
    }

    if ($action === 'mark_read') {
        $messageId = (int) ($_POST['message_id'] ?? 0);

        if ($messageId > 0) {
            messages_mark_as_read($messageId, $userId);
        }

        redirect('messages.php');
    }
}

$inboxMessages = messages_inbox_for_user($userId, 40);
$recentBroadcasts = $isMaster ? messages_recent_broadcast_groups($userId, 12) : [];
$userDirectory = $isMaster ? messages_user_directory($userId) : [];
$destinationSummary = $xeroxEnabled ? 'Administrador e Xerox' : 'Administrador';
$panelLabel = $isMaster ? 'Administrador' : ($isXerox ? 'Xerox' : 'Usuário');

render_header(
    'Mensagens',
    $isMaster
        ? 'Receba mensagens dos usuários, responda e envie avisos para todos.'
        : 'Envie mensagens para o administrador ou para o Xerox e acompanhe os retornos.'
);
?>

<section class="messages-layout">
    <section class="simple-metric-grid">
        <article class="simple-metric-card">
            <small>Não lidas</small>
            <strong><?= h((string) messages_unread_count($userId)) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Caixa de entrada</small>
            <strong><?= h((string) count($inboxMessages)) ?></strong>
        </article>
        <?php if ($isMaster): ?>
            <article class="simple-metric-card">
                <small>Avisos enviados</small>
                <strong><?= h((string) count($recentBroadcasts)) ?></strong>
            </article>
            <article class="simple-metric-card">
                <small>Painel</small>
                <strong>Administrador</strong>
            </article>
        <?php else: ?>
            <article class="simple-metric-card">
                <small>Canal</small>
                <strong><?= h($panelLabel) ?></strong>
            </article>
            <article class="simple-metric-card">
                <small>Destino</small>
                <strong><?= h($destinationSummary) ?></strong>
            </article>
        <?php endif; ?>
    </section>

    <section class="messages-grid">
        <article class="simple-card">
            <div class="simple-card-head">
                <div>
                    <h2><?= $isMaster ? 'Caixa do administrador' : ($isXerox ? 'Caixa do Xerox' : 'Mensagens recebidas') ?></h2>
                    <p class="helper-text"><?= $isMaster ? 'Mensagens dos usuários, respostas e avisos recebidos neste perfil.' : ($isXerox ? 'Pedidos e recados enviados ao setor Xerox.' : 'Avisos gerais e respostas enviadas para você.') ?></p>
                </div>
            </div>

            <?php if ($inboxMessages === []): ?>
                <div class="empty-state">
                    <h2>Nenhuma mensagem</h2>
                    <p><?= $isMaster ? 'Quando os usuários enviarem mensagens, elas aparecerão aqui.' : ($isXerox ? 'Quando alguém mandar recado ao Xerox, ele aparecerá aqui.' : 'Quando houver aviso ou resposta, a mensagem aparecerá aqui.') ?></p>
                </div>
            <?php else: ?>
                <div class="messages-thread-list">
                    <?php foreach ($inboxMessages as $message): ?>
                        <article class="message-card <?= (string) $message['status'] === 'unread' ? 'is-unread' : '' ?> <?= (string) $message['kind'] === 'broadcast' ? 'is-broadcast' : '' ?>">
                            <div class="message-card-head">
                                <strong><?= h((string) ($message['subject'] !== '' ? $message['subject'] : message_kind_label((string) $message['kind']))) ?></strong>
                                <div class="simple-inline-list">
                                    <span class="badge"><?= h(message_kind_label((string) $message['kind'])) ?></span>
                                    <span class="badge"><?= h((string) ($message['status'] === 'unread' ? 'Não lida' : 'Lida')) ?></span>
                                </div>
                            </div>

                            <div class="message-card-meta">
                                <span><?= h((string) (($message['sender_name'] ?? '') !== '' ? $message['sender_name'] : 'Sistema')) ?></span>
                                <span><?= h(datetime_label($message['created_at'] ?? null)) ?></span>
                            </div>

                            <div class="message-card-copy">
                                <p><?= nl2br(h((string) $message['body'])) ?></p>
                            </div>

                            <div class="message-card-actions">
                                <?php if ((string) $message['status'] === 'unread'): ?>
                                    <form method="post" class="inline-actions">
                                        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="message_id" value="<?= h((string) $message['id']) ?>">
                                        <button class="ghost-button" type="submit">Marcar como lida</button>
                                    </form>
                                <?php endif; ?>

                                <form method="post" class="inline-actions">
                                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                    <input type="hidden" name="action" value="delete_message">
                                    <input type="hidden" name="message_id" value="<?= h((string) $message['id']) ?>">
                                    <button class="button-danger" type="submit">Excluir</button>
                                </form>

                                <?php if ($canReplyInbox && (int) ($message['sender_user_id'] ?? 0) > 0 && (string) $message['kind'] !== 'broadcast'): ?>
                                    <details class="message-reply-box">
                                        <summary>Responder</summary>
                                        <form method="post" class="message-composer">
                                            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                                            <input type="hidden" name="action" value="reply_message">
                                            <input type="hidden" name="message_id" value="<?= h((string) $message['id']) ?>">
                                            <label>Assunto
                                                <input type="text" name="subject" value="<?= h($isMaster ? 'Resposta do administrador' : 'Resposta do Xerox') ?>" maxlength="180">
                                            </label>
                                            <label>Resposta
                                                <textarea name="body" placeholder="Escreva a resposta para o usuário." required></textarea>
                                            </label>
                                            <button class="button-secondary" type="submit">Enviar resposta</button>
                                        </form>
                                    </details>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <section class="simple-stack">
            <article class="simple-card message-compose-card <?= $isMaster ? 'is-broadcast-card' : '' ?>">
                <div class="simple-card-head">
                    <div>
                        <h2><?= $isMaster ? 'Enviar aviso para todos' : 'Enviar mensagem' ?></h2>
                        <p class="helper-text"><?= $isMaster ? 'Esse comunicado aparece para todos os usuários com destaque visual.' : 'Escolha entre o administrador e o setor Xerox quando ele estiver liberado.' ?></p>
                    </div>
                </div>

                <form method="post" class="message-composer">
                    <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="action" value="<?= $isMaster ? 'send_broadcast' : 'send_user_message' ?>">
                    <?php if (!$isMaster): ?>
                        <label>Enviar para
                            <select name="recipient_scope">
                                <option value="administrator">Administrador</option>
                                <?php if ($xeroxEnabled): ?>
                                    <option value="xerox">Setor Xerox</option>
                                <?php endif; ?>
                            </select>
                        </label>
                    <?php endif; ?>
                    <label>Assunto
                        <input type="text" name="subject" maxlength="180" placeholder="<?= $isMaster ? 'Ex.: Houve atualização no sistema' : 'Ex.: Pedido de apoio' ?>">
                    </label>
                    <label>Mensagem
                        <textarea name="body" placeholder="<?= $isMaster ? 'Escreva o aviso que todos os usuários devem ver.' : 'Descreva a mensagem que precisa ser enviada.' ?>" required></textarea>
                    </label>
                    <button class="button" type="submit"><?= $isMaster ? 'Enviar aviso' : 'Enviar mensagem' ?></button>
                </form>
            </article>

            <?php if ($isMaster): ?>
                <article class="simple-card">
                    <div class="simple-card-head">
                        <div>
                            <h2>Enviar para usuário específico</h2>
                            <p class="helper-text">Use este envio quando a mensagem não for um aviso geral.</p>
                        </div>
                    </div>

                    <form method="post" class="message-composer">
                        <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">
                        <input type="hidden" name="action" value="send_direct">
                        <label>Usuário
                            <select name="recipient_user_id" required>
                                <option value="">Selecione um usuário</option>
                                <?php foreach ($userDirectory as $directoryUser): ?>
                                    <option value="<?= h((string) $directoryUser['id']) ?>">
                                        <?= h((string) $directoryUser['name']) ?> · <?= h(role_label((string) $directoryUser['role'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <label>Assunto
                            <input type="text" name="subject" maxlength="180" placeholder="Ex.: Retorno sobre sua sugestão">
                        </label>
                        <label>Mensagem
                            <textarea name="body" placeholder="Escreva a mensagem para o usuário selecionado." required></textarea>
                        </label>
                        <button class="button-secondary" type="submit">Enviar mensagem direta</button>
                    </form>
                </article>

                <article class="simple-card">
                    <div class="simple-card-head">
                        <div>
                            <h2>Avisos enviados</h2>
                            <p class="helper-text">Resumo dos comunicados já disparados para todos os usuários.</p>
                        </div>
                    </div>

                    <?php if ($recentBroadcasts === []): ?>
                        <div class="empty-state">
                            <h2>Nenhum aviso enviado</h2>
                            <p>Os comunicados enviados para todos aparecerão aqui.</p>
                        </div>
                    <?php else: ?>
                        <div class="message-broadcast-list">
                            <?php foreach ($recentBroadcasts as $broadcast): ?>
                                <article class="message-broadcast-item">
                                    <strong><?= h((string) ($broadcast['subject'] !== '' ? $broadcast['subject'] : 'Aviso geral')) ?></strong>
                                    <p><?= h((string) $broadcast['body']) ?></p>
                                    <div class="simple-inline-list">
                                        <span class="badge"><?= h((string) $broadcast['total_recipients']) ?> destinatários</span>
                                        <span class="badge"><?= h(datetime_label($broadcast['created_at'] ?? null)) ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endif; ?>
        </section>
    </section>
</section>

<?php render_footer(); ?>
