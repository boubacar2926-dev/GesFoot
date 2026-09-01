<?php
// admin/includes/header.php
// Usage: $pageTitle doit être défini avant l'inclusion
$pageTitle = $pageTitle ?? 'Administration';
$user      = currentUser();
$baseUrl   = BASE_URL;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> — Football Club</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= $baseUrl ?>/assets/css/style.css?v=<?= assetVersion('assets/css/style.css') ?>" rel="stylesheet">

    <?= $extraHead ?? '' ?>
</head>
<body>

<!-- Sidebar Overlay (mobile) -->
<div class="sidebar-overlay d-none" id="sidebarOverlay"></div>

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
    <a href="<?= $baseUrl ?>/admin/index.php" class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-trophy-fill"></i></div>
        <div>
            <div class="brand-text">Football Club</div>
            <div class="brand-sub">Administration</div>
        </div>
    </a>

    <nav class="mt-2 pb-4">
        <div class="sidebar-section">Principal</div>
        <a href="<?= $baseUrl ?>/admin/index.php">
            <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
        <a href="<?= $baseUrl ?>/admin/club/index.php">
            <i class="bi bi-building"></i> Mon Club
        </a>
        <a href="<?= $baseUrl ?>/admin/contacts/index.php">
            <i class="bi bi-envelope-paper"></i> Demandes de contact
        </a>

        <div class="sidebar-section">Gestion</div>
        <a href="<?= $baseUrl ?>/admin/users/index.php">
            <i class="bi bi-people"></i> Utilisateurs
        </a>
        <a href="<?= $baseUrl ?>/admin/joueurs/index.php">
            <i class="bi bi-person-badge"></i> Joueurs
        </a>
        <a href="<?= $baseUrl ?>/admin/competitions/index.php">
            <i class="bi bi-trophy"></i> Compétitions
        </a>
        <a href="<?= $baseUrl ?>/admin/matchs/index.php">
            <i class="bi bi-calendar-event"></i> Matchs
        </a>

        <div class="sidebar-section">Sportif</div>
        <a href="<?= $baseUrl ?>/admin/convocations/index.php">
            <i class="bi bi-clipboard2-check"></i> Convocations
        </a>
        <a href="<?= $baseUrl ?>/admin/compositions/index.php">
            <i class="bi bi-layout-text-sidebar"></i> Compositions
        </a>
        <a href="<?= $baseUrl ?>/admin/statistiques/index.php">
            <i class="bi bi-bar-chart-line"></i> Statistiques
        </a>

        <div class="sidebar-section">Analyse</div>
        <a href="<?= $baseUrl ?>/admin/classement/index.php">
            <i class="bi bi-award"></i> Classement
        </a>
        <a href="<?= $baseUrl ?>/admin/historique/index.php">
            <i class="bi bi-clock-history"></i> Historique
        </a>
        <a href="<?= $baseUrl ?>/admin/rapports/index.php">
            <i class="bi bi-file-earmark-bar-graph"></i> Rapports
        </a>
    </nav>
</aside>

<!-- MAIN WRAPPER -->
<div class="main-wrapper">

    <!-- TOPBAR -->
    <header class="topbar">
        <button class="btn btn-sm btn-light d-lg-none me-2" id="sidebarToggler">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="page-title"><?= e($pageTitle) ?></span>

        <div class="topbar-right">
            <div class="dropdown">
                <div class="user-chip dropdown-toggle" data-bs-toggle="dropdown" id="userMenu">
                    <img src="<?= avatarUrl($user['photo'] ?? null, ($user['prenom'] ?? '?') . ' ' . ($user['nom'] ?? '')) ?>"
                         alt="avatar" class="avatar-sm">
                    <span class="d-none d-md-inline"><?= e(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?></span>
                    <?= roleBadge($user['role'] ?? 'admin') ?>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width:200px">
                    <li><h6 class="dropdown-header"><?= e($user['email'] ?? '') ?></h6></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= $baseUrl ?>/admin/users/edit.php?id=<?= $user['id'] ?? 0 ?>"><i class="bi bi-person me-2"></i>Mon profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= $baseUrl ?>/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- PAGE CONTENT -->
    <main class="page-content">
        <?= renderFlash() ?>
