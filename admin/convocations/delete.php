<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/admin/convocations/index.php'); }
csrfVerify();
$pdo = getPDO();
$id  = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT id FROM convocations WHERE id=?");
$stmt->execute([$id]);
if (!$stmt->fetch()) { setFlash('error','Convocation introuvable.'); redirect('/admin/convocations/index.php'); }
$pdo->prepare("DELETE FROM convocations WHERE id=?")->execute([$id]);
setFlash('success','Convocation supprimée.');
redirect('/admin/convocations/index.php');
