<?php
define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

startSession();

// 1. Si l'utilisateur N'EST PAS connecté -> Redirection directe vers le site vitrine
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/vitrine.php');
    exit;
}

// 2. Si l'utilisateur EST connecté -> Redirection vers son espace dédié
$role = currentRole();
$destinations = [
    'admin'  => '/admin/index.php',
    'coach'  => '/coach/index.php',
    'staff'  => '/staff/index.php',
    'joueur' => '/joueur/index.php',
];

$target = $destinations[$role] ?? '/auth/login.php';
header('Location: ' . BASE_URL . $target);
exit;