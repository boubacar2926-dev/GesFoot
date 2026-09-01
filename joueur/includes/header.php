<?php
$pageTitle = $pageTitle ?? 'Espace Joueur';
$user      = currentUser();
$baseUrl   = BASE_URL;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> — Football Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= $baseUrl ?>/assets/css/style.css?v=<?= assetVersion('assets/css/style.css') ?>" rel="stylesheet">
    <?= $extraHead ?? '' ?>
</head>
<body>
<div class="sidebar-overlay d-none" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <a href="<?= $baseUrl ?>/joueur/index.php" class="sidebar-brand">
        <div class="brand-icon" style="background:#1e3a5f"><i class="bi bi-person-fill"></i></div>
        <div><div class="brand-text">Football Club</div><div class="brand-sub">Espace Joueur</div></div>
    </a>
    <nav class="mt-2 pb-4">
        <div class="sidebar-section">Mon espace</div>
        <a href="<?= $baseUrl ?>/joueur/index.php"><i class="bi bi-speedometer2"></i> Mon tableau de bord</a>
        <a href="<?= $baseUrl ?>/joueur/profil.php"><i class="bi bi-person-circle"></i> Mon profil</a>
        <a href="<?= $baseUrl ?>/joueur/statistiques.php"><i class="bi bi-bar-chart-line"></i> Mes statistiques</a>
        <div class="sidebar-section">Équipe</div>
        <a href="<?= $baseUrl ?>/joueur/convocations.php"><i class="bi bi-clipboard2-check"></i> Mes convocations</a>
        <a href="<?= $baseUrl ?>/joueur/matchs.php"><i class="bi bi-calendar-event"></i> Matchs à venir</a>
        <a href="<?= $baseUrl ?>/joueur/classement.php"><i class="bi bi-award"></i> Classement</a>
    </nav>
</aside>

<div class="main-wrapper">
    <header class="topbar">
        <button class="btn btn-sm btn-light d-lg-none me-2" id="sidebarToggler"><i class="bi bi-list fs-5"></i></button>
        <span class="page-title"><?= e($pageTitle) ?></span>
        <div class="topbar-right">
            <div class="dropdown">
                <div class="user-chip dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?= avatarUrl($user['photo'] ?? null, ($user['prenom'] ?? '?').' '.($user['nom'] ?? '')) ?>" class="avatar-sm">
                    <span class="d-none d-md-inline"><?= e(($user['prenom'] ?? '').' '.($user['nom'] ?? '')) ?></span>
                    <?= roleBadge('joueur') ?>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li><h6 class="dropdown-header"><?= e($user['email'] ?? '') ?></h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>
    <main class="page-content">
        <?= renderFlash() ?>
