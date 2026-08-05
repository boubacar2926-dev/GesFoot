<?php
define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

startSession();

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit;
}

$role = currentRole();
$destinations = [
    'admin'  => '/admin/index.php',
    'coach'  => '/coach/index.php',
    'staff'  => '/staff/index.php',
    'joueur' => '/joueur/index.php',
];

header('Location: ' . BASE_URL . ($destinations[$role] ?? '/auth/login.php'));
exit;
