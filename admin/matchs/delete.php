<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('/admin/matchs/index.php'); }
csrfVerify();
$pdo = getPDO();
$id  = (int)($_POST['id'] ?? 0);
$stmt = $pdo->prepare("SELECT id, adversaire, date_match FROM matchs WHERE id=?");
$stmt->execute([$id]);
$matchData = $stmt->fetch();
if (!$matchData) { setFlash('error','Match introuvable.'); redirect('/admin/matchs/index.php'); }
$matchLabel = 'vs ' . $matchData['adversaire'] . ' — ' . formatDate($matchData['date_match']);
logMatchHistory($pdo, $id, $matchLabel, 'suppression');
$pdo->prepare("DELETE FROM matchs WHERE id=?")->execute([$id]);
setFlash('success','Match et données associées supprimés.');
redirect('/admin/matchs/index.php');
