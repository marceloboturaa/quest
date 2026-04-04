<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

require_login();

$user = current_user();

if (is_post()) {
    abort_if_invalid_csrf();

    $filters = study_filters_from_request($_POST);
    remember_input([
        'discipline_id' => (string) $filters['discipline_id'],
        'subject_id' => (string) $filters['subject_id'],
        'difficulty' => (string) $filters['difficulty'],
    ]);

    $state = study_start_session($filters);

    if (($state['queue'] ?? []) === []) {
        flash('error', 'Não encontramos questões públicas com esses filtros.');
        redirect('study.php');
    }

    forget_old_input();
    flash('success', 'Modo estudo iniciado.');
    redirect('question.php');
}

$selectedDisciplineId = (int) old('discipline_id', '0');
$disciplines = study_disciplines();
$subjects = study_subjects($selectedDisciplineId > 0 ? $selectedDisciplineId : null);
$progress = study_progress();

render_header(
    'Modo Estudo',
    'Escolha disciplina, assunto e dificuldade para começar a resolver questões com correção automática.'
);
?>

<section class="simple-stack">
    <article class="simple-card">
        <div class="simple-card-head">
            <h2>Como funciona</h2>
        </div>
        <p class="metric-copy">
            O sistema usa a mesma base de usuários do projeto. Você cadastra como aluno, inicia um treino e cada resposta é gravada no banco com correção automática.
        </p>
        <div class="simple-inline-list">
            <span class="badge">Filtro por disciplina</span>
            <span class="badge">Filtro por assunto</span>
            <span class="badge">Filtro por dificuldade</span>
            <span class="badge">Histórico salvo</span>
        </div>
    </article>

    <article class="simple-card">
        <div class="simple-card-head">
            <h2>Iniciar estudo</h2>
        </div>

        <form method="post" class="form-grid">
            <input type="hidden" name="_token" value="<?= h(csrf_token()) ?>">

            <div class="form-grid two-columns">
                <label>
                    Disciplina
                    <select name="discipline_id">
                        <option value="0">Todas as disciplinas</option>
                        <?php foreach ($disciplines as $discipline): ?>
                            <option value="<?= h((string) $discipline['id']) ?>" <?= old('discipline_id') === (string) $discipline['id'] ? 'selected' : '' ?>>
                                <?= h((string) $discipline['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Assunto
                    <select name="subject_id">
                        <option value="0">Todos os assuntos</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= h((string) $subject['id']) ?>" <?= old('subject_id') === (string) $subject['id'] ? 'selected' : '' ?>>
                                <?= h((string) $subject['discipline_name']) ?> · <?= h((string) $subject['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <label>
                Dificuldade
                <select name="difficulty">
                    <option value="">Todas as dificuldades</option>
                    <option value="facil" <?= old('difficulty') === 'facil' ? 'selected' : '' ?>>Fácil</option>
                    <option value="medio" <?= old('difficulty') === 'medio' ? 'selected' : '' ?>>Média</option>
                    <option value="dificil" <?= old('difficulty') === 'dificil' ? 'selected' : '' ?>>Difícil</option>
                </select>
            </label>

            <div class="form-actions">
                <button class="button" type="submit">Iniciar estudo</button>
                <a class="ghost-button" href="dashboard.php">Voltar ao dashboard</a>
            </div>
        </form>
    </article>

    <section class="simple-metric-grid">
        <article class="simple-metric-card">
            <small>Questões na fila</small>
            <strong><?= h((string) $progress['total']) ?></strong>
        </article>
        <article class="simple-metric-card">
            <small>Seu progresso</small>
            <strong><?= h((string) $progress['percent']) ?>%</strong>
        </article>
        <article class="simple-metric-card">
            <small>Usuário ativo</small>
            <strong><?= h((string) $user['name']) ?></strong>
        </article>
    </section>
</section>

<?php forget_old_input(); ?>
<?php render_footer(); ?>
