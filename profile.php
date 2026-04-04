<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();

$user = current_user();
$userId = (int) $user['id'];

$profileFields = [
    'preferred_teacher_name' => 'Nome do professor',
    'preferred_discipline' => 'Disciplina padrão',
    'preferred_component_name' => 'Componente curricular',
    'preferred_class_name' => 'Turma mais usada',
    'preferred_year_reference' => 'Ano / série',
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
            preferred_school_name = :preferred_school_name,
            preferred_school_subtitle = :preferred_school_subtitle,
            preferred_header_logo_left = :preferred_header_logo_left,
            preferred_header_logo_right = :preferred_header_logo_right,
            updated_at = NOW()
         WHERE id = :id'
    );
    $statement->execute($payload + ['id' => $userId]);

    flash('success', 'Painel do usuário atualizado. Os novos padrões já serão usados nas páginas do sistema.');
    redirect('profile.php');
}

$user = current_user();
render_header(
    'Meu painel',
    'Preencha seus dados padrão para deixar o painel mais rápido no dia a dia.'
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
                <a class="ghost-button" href="messages.php">Mensagens</a>
            </div>
        </article>

        <article class="simple-card profile-summary-card-clean">
            <span class="profile-card-kicker">Preferências</span>
            <div class="profile-summary-list">
                <div class="profile-summary-row">
                    <small>Professor</small>
                    <strong><?= h((string) ($user['preferred_teacher_name'] ?? $user['name'])) ?></strong>
                </div>
                <div class="profile-summary-row">
                    <small>Escola</small>
                    <strong><?= h((string) ($user['preferred_school_name'] ?? '')) ?></strong>
                </div>
                <div class="profile-summary-row">
                    <small>Logo principal</small>
                    <strong><?= h(!empty($user['preferred_header_logo_left']) ? 'Definida' : 'Não definida') ?></strong>
                </div>
            </div>
        </article>

        <article class="simple-card profile-tip-card">
            <span class="profile-card-kicker">Uso rápido</span>
            <p>Preencha os campos mais usados para reduzir retrabalho nas páginas do sistema.</p>
        </article>
    </aside>

    <section class="profile-main">
        <article class="simple-card profile-form-shell">
            <div class="simple-card-head">
                    <div>
                    <span class="profile-card-kicker">Configuração pessoal</span>
                    <h2>Dados padrão do painel</h2>
                    <p class="helper-text">Preencha somente o que costuma se repetir no uso do sistema.</p>
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
                    </div>
                </section>

                <section class="profile-form-section">
                    <div class="profile-form-section-head">
                        <h3>Escola e cabeçalho</h3>
                        <p>Esses dados alimentam os campos reutilizáveis do sistema automaticamente.</p>
                    </div>
                    <div class="simple-filter-grid profile-form-grid">
                        <label class="profile-form-grid-span-2"><span>Nome da escola</span>
                            <input type="text" name="preferred_school_name" value="<?= h((string) ($user['preferred_school_name'] ?? '')) ?>" placeholder="COLÉGIO ESTADUAL CÍVICO-MILITAR TANCREDO DE ALMEIDA NEVES">
                        </label>
                        <label class="profile-form-grid-span-2"><span>Subtítulo da escola</span>
                            <input type="text" name="preferred_school_subtitle" value="<?= h((string) ($user['preferred_school_subtitle'] ?? '')) ?>" placeholder="ENSINO FUNDAMENTAL, MÉDIO E PROFISSIONALIZANTE">
                        </label>
                        <label class="profile-form-grid-span-2"><span>Logo principal</span>
                            <input type="text" name="preferred_header_logo_left" value="<?= h((string) ($user['preferred_header_logo_left'] ?? '')) ?>" placeholder="https://cdn.worldvectorlogo.com/logos/colegio-estadual-c-vico-militar-tancredo-de-almeida-neves.svg">
                        </label>
                        <label class="profile-form-grid-span-2"><span>Logo secundária</span>
                            <input type="text" name="preferred_header_logo_right" value="<?= h((string) ($user['preferred_header_logo_right'] ?? '')) ?>" placeholder="Opcional">
                        </label>
                    </div>
                </section>

                <div class="simple-action-row profile-form-actions">
                    <button class="button" type="submit">Salvar painel</button>
                </div>
            </form>
        </article>
    </section>
</section>

<?php render_footer(); ?>
