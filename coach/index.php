<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('coach');
$pdo = getPDO();

// Stats rapides
$stats = [
    'joueurs'     => $pdo->query("SELECT COUNT(*) FROM joueurs WHERE statut='actif'")->fetchColumn(),
    'matchs'      => $pdo->query("SELECT COUNT(*) FROM matchs WHERE statut='Programmé' AND date_match >= CURDATE()")->fetchColumn(),
    'convocations'=> $pdo->query("SELECT COUNT(*) FROM convocations WHERE statut='Publiée'")->fetchColumn(),
];

// Prochains matchs
$prochains = $pdo->query("
    SELECT m.*, c.nom AS competition,
        (SELECT COUNT(*) FROM convocations cv WHERE cv.match_id=m.id AND cv.statut='Publiée') AS a_convocation
    FROM matchs m
    LEFT JOIN competitions c ON c.id=m.competition_id
    WHERE m.statut='Programmé' AND m.date_match >= CURDATE()
    ORDER BY m.date_match ASC LIMIT 5
")->fetchAll();

// Derniers résultats
$resultats = $pdo->query("
    SELECT m.*, c.nom AS competition
    FROM matchs m
    LEFT JOIN competitions c ON c.id=m.competition_id
    WHERE m.statut='Terminé'
    ORDER BY m.date_match DESC LIMIT 5
")->fetchAll();

// Joueurs indisponibles
$indisponibles = $pdo->query("
    SELECT * FROM joueurs WHERE statut IN ('blessé','suspendu') ORDER BY statut, nom
")->fetchAll();

// Top performeurs
$topBut = $pdo->query("
    SELECT j.prenom, j.nom, j.poste, j.photo, SUM(s.buts) AS buts
    FROM statistiques s JOIN joueurs j ON j.id=s.joueur_id
    GROUP BY j.id ORDER BY buts DESC LIMIT 5
")->fetchAll();

$pageTitle = 'Tableau de bord Coach';
require_once __DIR__ . '/includes/header.php';
?>

<!-- STATS -->
<div class="row g-4 mb-4">
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4;color:#166534"><i class="bi bi-people-fill"></i></div>
            <div><div class="stat-value"><?= $stats['joueurs'] ?></div><div class="stat-label">Joueurs disponibles</div></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#475569"><i class="bi bi-calendar-event-fill"></i></div>
            <div><div class="stat-value"><?= $stats['matchs'] ?></div><div class="stat-label">Matchs à venir</div></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4;color:#166534"><i class="bi bi-clipboard2-check-fill"></i></div>
            <div><div class="stat-value"><?= $stats['convocations'] ?></div><div class="stat-label">Convocations publiées</div></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Prochains matchs -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-clock text-warning me-2"></i>Prochains matchs</span>
                <a href="<?= BASE_URL ?>/coach/matchs.php" class="btn btn-sm btn-outline-success">Voir tout</a>
            </div>
            <div class="card-body p-0 table-responsive">
                <?php if ($prochains): ?>
                <table class="table mb-0">
                    <thead><tr><th>Date</th><th>Adversaire</th><th>Lieu</th><th>Convocation</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($prochains as $m): ?>
                    <tr>
                        <td>
                            <div class="fw-semibold"><?= formatDate($m['date_match']) ?></div>
                            <?php if ($m['heure_match']): ?><small class="text-muted"><?= formatTime($m['heure_match']) ?></small><?php endif; ?>
                        </td>
                        <td class="fw-semibold">vs <?= e($m['adversaire']) ?></td>
                        <td><span class="badge bg-light text-dark"><?= e($m['domicile_exterieur']) ?></span></td>
                        <td>
                            <?php if ($m['a_convocation']): ?>
                                <span class="badge bg-success"><i class="bi bi-check me-1"></i>Publiée</span>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/coach/convocations/add.php?match_id=<?= $m['id'] ?>"
                                   class="badge bg-warning text-dark text-decoration-none">
                                    <i class="bi bi-plus me-1"></i>Créer
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/coach/compositions/index.php?match_id=<?= $m['id'] ?>"
                               class="btn btn-sm btn-outline-primary btn-icon" title="Composition" data-bs-toggle="tooltip">
                                <i class="bi bi-layout-text-sidebar"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="text-center py-5 text-muted"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Aucun match à venir</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <!-- Derniers résultats -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-trophy me-2 text-success"></i>Derniers résultats</div>
            <div class="card-body p-0">
                <?php if ($resultats): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($resultats as $m):
                        if ($m['score_equipe'] > $m['score_adverse'])     { $r='V'; $c='success'; }
                        elseif ($m['score_equipe'] < $m['score_adverse']) { $r='D'; $c='danger'; }
                        else                                               { $r='N'; $c='warning'; }
                    ?>
                    <li class="list-group-item px-3 py-2 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="badge bg-<?= $c ?> me-2"><?= $r ?></span>
                            <span class="fw-semibold small">vs <?= e($m['adversaire']) ?></span>
                            <div class="text-muted" style="font-size:.75rem"><?= formatDate($m['date_match']) ?></div>
                        </div>
                        <span class="fw-bold"><?= $m['score_equipe'] ?>—<?= $m['score_adverse'] ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                    <div class="text-center py-3 text-muted small">Aucun résultat</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Indisponibles -->
        <?php if ($indisponibles): ?>
        <div class="card">
            <div class="card-header"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Joueurs indisponibles</div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php foreach ($indisponibles as $j): ?>
                    <li class="list-group-item px-3 py-2 d-flex align-items-center gap-2">
                        <img src="<?= avatarUrl($j['photo'], $j['prenom'].' '.$j['nom']) ?>" class="avatar-sm">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small"><?= e($j['prenom']) ?> <?= e($j['nom']) ?></div>
                            <small class="text-muted"><?= e($j['poste']) ?></small>
                        </div>
                        <?= statusBadge($j['statut']) ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
