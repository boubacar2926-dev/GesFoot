<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/admin/matchs/index.php'); }
csrfVerify();
$pdo = getPDO();
$id  = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT id FROM matchs WHERE id=?");
$stmt->execute([$id]);
if (!$stmt->fetch()) { setFlash('error','Match introuvable.'); redirect('/admin/matchs/index.php'); }
$pdo->prepare("DELETE FROM matchs WHERE id=?")->execute([$id]);
setFlash('success','Match et données associées supprimés.');
redirect('/admin/matchs/index.php');
