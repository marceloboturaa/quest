<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if (current_user() !== null) {
    redirect('dashboard.php');
}

render_header(
    'Quest',
    '',
    false,
    false
);
?>

<section class="home-minimal">
    <div class="home-minimal-card">
        <div class="home-minimal-scene is-revealing">
            <div class="construction-orbit construction-orbit-left"></div>
            <div class="construction-orbit construction-orbit-right"></div>
            <a class="construction-core construction-core-link" href="login.php" aria-label="Entrar no Quest">
                <span class="construction-core-ring"></span>
                <span class="construction-core-ring construction-core-ring-delay"></span>
                <span class="construction-core-icon">Q</span>
            </a>
            <div class="construction-question construction-question-one">
                <strong>Questão 01</strong>
                <span>Matemática</span>
            </div>
            <div class="construction-question construction-question-two">
                <strong>Questão 02</strong>
                <span>Linguagens</span>
            </div>
            <div class="construction-question construction-question-three">
                <strong>Questão 03</strong>
                <span>Ciências</span>
            </div>
            <div class="construction-chip construction-chip-one">HTML</div>
            <div class="construction-chip construction-chip-two">CSS</div>
            <div class="construction-chip construction-chip-three">JS</div>
        </div>
        <h1>Estamos construindo.</h1>
        <p class="home-minimal-status">
            Ajustando <span data-construction-word>detalhes</span><span class="home-minimal-dots" data-construction-dots>...</span>
        </p>
        <div class="home-minimal-progress" aria-hidden="true">
            <span class="home-minimal-progress-bar"></span>
        </div>
        <div class="home-minimal-signal" aria-hidden="true">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="home-actions form-actions">
            <a class="button" href="login.php">Entrar</a>
            <a class="ghost-button" href="register.php">Criar conta</a>
        </div>
    </div>
</section>

<?php render_footer(false); ?>
