<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('staff');
$pdo = getPDO();

// Matchs terminés sans statistiques saisies
$sansStat = $pdo->query("
    SELECT m.*, c.nom AS competition,
        (SELECT COUNT(DISTINCT s.joueur_id) FROM statistiques s WHERE s.match_id=m.id) AS nb_stats
    FROM matchs m
    LEFT JOIN competitions c ON c.id=m.competition_id
    WHERE m.statut='Terminé'
    ORDER BY m.date_match DESC LIMIT 10
")->fetchAll();

// Dernières stats saisies
$dernieresSaisies = $pdo->query("
    SELECT s.updated_at, j.prenom, j.nom, m.adversaire, m.date_match,
           s.buts, s.passes_decisives, s.minutes_jouees
    FROM statistiques s
    JOIN joueurs j ON j.id=s.joueur_id
    JOIN matchs m ON m.id=s.match_id
    ORDER BY s.updated_at DESC LIMIT 8
")->fetchAll();

// Totaux globaux
$totaux = $pdo->query("
    SELECT SUM(buts) AS buts, SUM(passes_decisives) AS passes,
           SUM(minutes_jouees) AS minutes, COUNT(*) AS lignes
    FROM statistiques
")->fetch();

$pageTitle = 'Tableau de bord Staff';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Stats cards -->
<div class="row g-4 mb-4">
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4;color:#166534"><i class="bi bi-bullseye"></i></div>
            <div><div class="stat-value"><?= $totaux['buts'] ?? 0 ?></div><div class="stat-label">Buts saisis</div></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4;color:#166534"><i class="bi bi-send"></i></div>
            <div><div class="stat-value"><?= $totaux['passes'] ?? 0 ?></div><div class="stat-label">Passes décisives</div></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#475569"><i class="bi bi-clock"></i></div>
            <div><div class="stat-value"><?= number_format($totaux['minutes'] ?? 0) ?>'</div><div class="stat-label">Minutes enregistrées</div></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Matchs à renseigner -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-clipboard-plus me-2 text-warning"></i>Matchs terminés — état des stats</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Date</th><th>Adversaire</th><th>Score</th><th>Stats</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($sansStat as $m): ?>
                    <tr>
                        <td class="small"><?= formatDate($m['date_match']) ?></td>
                        <td class="fw-semibold">vs <?= e($m['adversaire']) ?></td>
                        <td class="fw-bold"><?= $m['score_equipe'] ?>—<?= $m['score_adverse'] ?></td>
                        <td>
                            <?php if ($m['nb_stats'] > 0): ?>
                                <span class="badge bg-success"><i class="bi bi-check"></i> <?= $m['nb_stats'] ?> joueurs</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-exclamation"></i> À saisir</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/staff/statistiques/index.php?match_id=<?= $m['id'] ?>"
                               class="btn btn-sm btn-<?= $m['nb_stats']?'outline-success':'success' ?>">
                                <i class="bi bi-pencil me-1"></i><?= $m['nb_stats']?'Modifier':'Saisir' ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$sansStat): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Aucun match terminé</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Dernières saisies -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-clock-history me-2"></i>Dernières saisies</div>
            <div class="card-body p-0">
                <?php if ($dernieresSaisies): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($dernieresSaisies as $s): ?>
                    <li class="list-group-item px-3 py-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="fw-semibold small"><?= e($s['prenom']) ?> <?= e($s['nom']) ?></div>
                                <small class="text-muted">vs <?= e($s['adversaire']) ?> · <?= formatDate($s['date_match']) ?></small>
                            </div>
                            <div class="text-end small">
                                <?php if ($s['buts']): ?><span class="badge bg-success me-1"><?= $s['buts'] ?> but<?= $s['buts']>1?'s':'' ?></span><?php endif; ?>
                                <span class="text-muted"><?= $s['minutes_jouees'] ?>'</span>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                    <div class="text-center py-5 text-muted small">Aucune saisie récente</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
