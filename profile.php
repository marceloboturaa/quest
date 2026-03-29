<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/includes/exam_metadata.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];

$profileFields = [
    'preferred_teacher_name' => 'Nome do professor',
    'preferred_discipline' => 'Disciplina padrão',
    'preferred_component_name' => 'Componente curricular',
    'preferred_class_name' => 'Turma mais usada',
    'preferred_year_reference' => 'Ano / série',
    'preferred_exam_label' => 'Título padrão da avaliação',
    'preferred_school_name' => 'Nome da escola',
    'preferred_school_subtitle' => 'Subtítulo da escola',
    'preferred_header_logo_left' => 'Logo principal',
    'preferred_header_logo_right' => 'Logo secundária',
];

if (is_post()) {
    abort_if_invalid_csrf();

    $payload = [];

    foreach (array_keys($profileFields) as $field) {
        $payload[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    $statement = db()->prepare(
        'UPDATE users SET
            preferred_teacher_name = :preferred_teacher_name,
            preferred_discipline = :preferred_discipline,
            preferred_component_name = :preferred_component_name,
            preferred_class_name = :preferred_class_name,
            preferred_year_reference = :preferred_year_reference,
            preferred_exam_label = :preferred_exam_label,
            preferred_school_name = :preferred_school_name,
            preferred_school_subtitle = :preferred_school_subtitle,
            preferred_header_logo_left = :preferred_header_logo_left,
            preferred_header_logo_right = :preferred_header_logo_right,
            updated_at = NOW()
         WHERE id = :id'
    );
    $statement->execute($payload + ['id' => $userId]);

    flash('success', 'Painel do usuário atualizado. Os novos padrões já entram na criação das provas.');
    redirect('profile.php');
}

$user = current_user();
$examDefaults = exam_user_profile_defaults($user);

render_header(
    'Meu painel',
    'Preencha seus dados padrão para acelerar a criação das provas.'
);
?>

<section class="profile-workspace">
    <aside class="profile-sidebar">
        <article class="simple-card profile-account-card">
            <span class="profile-card-kicker">Conta</span>
            <h2><?= h((string) $user['name']) ?></h2>
            <p><?= h((string) $user['email']) ?></p>
            <div class="simple-inline-list">
                <span class="badge"><?= h(role_label((string) $user['role'])) ?></span>
                <span class="badge">Painel ativo</span>
            </div>
            <div class="profile-sidebar-actions">
                <a class="button-secondary" href="exam-create.php">Nova prova</a>
                <a class="ghost-button" href="messages.php">Mensagens</a>
            </div>
        </article>

        <article class="simple-card profile-summary-card-clean">
            <span class="profile-card-kicker">Na nova prova</span>
            <div class="profile-summary-list">
                <div class="profile-summary-row">
                    <small>Professor</small>
                    <strong><?= h($examDefaults['teacher_name'] !== '' ? $examDefaults['teacher_name'] : (string) $user['name']) ?></strong>
                </div>
                <div class="profile-summary-row">
                    <small>Escola</small>
                    <strong><?= h($examDefaults['school_name']) ?></strong>
                </div>
                <div class="profile-summary-row">
                    <small>Cabeçalho</small>
                    <strong><?= h($examDefaults['header_logo_left'] !== '' ? 'Logo principal definida' : 'Sem logo definida') ?></strong>
                </div>
            </div>
        </article>

        <article class="simple-card profile-tip-card">
            <span class="profile-card-kicker">Uso rápido</span>
            <p>Preencha escola, professor e logo principal. Isso já elimina boa parte do retrabalho na montagem das provas.</p>
        </article>
    </aside>

    <section class="profile-main">
        <article class="simple-card profile-form-shell">
            <div class="simple-card-head">
                <div>
                    <span class="profile-card-kicker">Configuração pessoal</span>
                    <h2>Dados padrão da prova</h2>
                    <p class="helper-text">Ajuste somente o que costuma se repetir. O restante você altera normalmente dentro da prova.</p>
                </div>
            </div>

            <form method="post" class="simple-stack">
                <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">

                <section class="profile-form-section">
                    <div class="profile-form-section-head">
                        <h3>Professor e turma</h3>
                        <p>Informações mais usadas no dia a dia.</p>
                    </div>
                    <div class="simple-filter-grid profile-form-grid">
                        <label><span>Nome do professor</span>
                            <input type="text" name="preferred_teacher_name" value="<?= h((string) ($user['preferred_teacher_name'] ?? '')) ?>" placeholder="<?= h((string) $user['name']) ?>">
                        </label>
                        <label><span>Disciplina padrão</span>
                            <input type="text" name="preferred_discipline" value="<?= h((string) ($user['preferred_discipline'] ?? '')) ?>" placeholder="Ex.: Matemática">
                        </label>
                        <label><span>Componente curricular</span>
                            <input type="text" name="preferred_component_name" value="<?= h((string) ($user['preferred_component_name'] ?? '')) ?>" placeholder="Ex.: Educação Financeira">
                        </label>
                        <label><span>Turma mais usada</span>
                            <input type="text" name="preferred_class_name" value="<?= h((string) ($user['preferred_class_name'] ?? '')) ?>" placeholder="Ex.: 6A">
                        </label>
                        <label><span>Ano / série</span>
                            <input type="text" name="preferred_year_reference" value="<?= h((string) ($user['preferred_year_reference'] ?? '')) ?>" placeholder="Ex.: 6º ano">
                        </label>
                        <label><span>Título padrão da avaliação</span>
                            <input type="text" name="preferred_exam_label" value="<?= h((string) ($user['preferred_exam_label'] ?? '')) ?>" placeholder="Ex.: AVALIAÇÃO TRIMESTRAL">
                        </label>
                    </div>
                </section>

                <section class="profile-form-section">
                    <div class="profile-form-section-head">
                        <h3>Escola e cabeçalho</h3>
                        <p>Esses dados alimentam o topo da prova automaticamente.</p>
                    </div>
                    <div class="simple-filter-grid profile-form-grid">
                        <label class="profile-form-grid-span-2"><span>Nome da escola</span>
                            <input type="text" name="preferred_school_name" value="<?= h((string) ($user['preferred_school_name'] ?? '')) ?>" placeholder="<?= h(EXAM_DEFAULT_SCHOOL_NAME) ?>">
                        </label>
                        <label class="profile-form-grid-span-2"><span>Subtítulo da escola</span>
                            <input type="text" name="preferred_school_subtitle" value="<?= h((string) ($user['preferred_school_subtitle'] ?? '')) ?>" placeholder="<?= h(EXAM_DEFAULT_SCHOOL_SUBTITLE) ?>">
                        </label>
                        <label class="profile-form-grid-span-2"><span>Logo principal</span>
                            <input type="text" name="preferred_header_logo_left" value="<?= h((string) ($user['preferred_header_logo_left'] ?? '')) ?>" placeholder="<?= h(EXAM_DEFAULT_LOGO_URL) ?>">
                        </label>
                        <label class="profile-form-grid-span-2"><span>Logo secundária</span>
                            <input type="text" name="preferred_header_logo_right" value="<?= h((string) ($user['preferred_header_logo_right'] ?? '')) ?>" placeholder="Opcional">
                        </label>
                    </div>
                </section>

                <div class="simple-action-row profile-form-actions">
                    <button class="button" type="submit">Salvar painel</button>
                    <a class="ghost-button" href="exam-create.php">Abrir criação de prova</a>
                </div>
            </form>
        </article>
    </section>
</section>

<?php render_footer(); ?>
