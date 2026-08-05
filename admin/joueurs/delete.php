<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/joueurs/index.php');
}

csrfVerify();
$pdo = getPDO();
$id  = (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT id FROM joueurs WHERE id=?");
$stmt->execute([$id]);
if (!$stmt->fetch()) { setFlash('error','Joueur introuvable.'); redirect('/admin/joueurs/index.php'); }

$pdo->prepare("DELETE FROM joueurs WHERE id=?")->execute([$id]);
setFlash('success','Joueur supprimé.');
redirect('/admin/joueurs/index.php');
