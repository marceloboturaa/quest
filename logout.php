<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

logout_user();
session_start();
flash('success', 'Sessao encerrada.');
redirect('login.php');
