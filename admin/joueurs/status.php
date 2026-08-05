<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
csrfVerify();

$id     = (int)($_POST['id']     ?? 0);
$statut = $_POST['statut']       ?? '';
$valid  = ['actif','blessé','suspendu','inactif'];

if ($id && in_array($statut, $valid)) {
    $pdo = getPDO();
    $pdo->prepare("UPDATE joueurs SET statut=? WHERE id=?")->execute([$statut, $id]);
    setFlash('success', 'Statut mis à jour.');
}

redirect('/admin/joueurs/index.php');
