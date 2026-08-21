<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/users/index.php');
}

csrfVerify();
$pdo = getPDO();
$id  = (int)($_POST['id'] ?? 0);

// Vérification : propre compte
if ($id === (int)(currentUser()['id'] ?? 0)) {
    setFlash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
    redirect('/admin/users/index.php');
}

// Sécurité mode démo : Empêcher la suppression de l'admin principal
if ($id === 1) {
    setFlash('error', 'Action impossible : Le compte administrateur de démonstration ne peut pas être supprimé.');
    redirect('/admin/users/index.php');
}

$stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    setFlash('error', 'Utilisateur introuvable.');
    redirect('/admin/users/index.php');
}

$pdo->prepare("DELETE FROM utilisateurs WHERE id = ?")->execute([$id]);
setFlash('success', 'Utilisateur supprimé.');
redirect('/admin/users/index.php');