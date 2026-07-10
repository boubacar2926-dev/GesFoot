<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');

$pdo = getPDO();

// Stats globales
$stats = [
    'utilisateurs' => $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn(),
    'joueurs'      => $pdo->query("SELECT COUNT(*) FROM joueurs WHERE statut='actif'")->fetchColumn(),
    'matchs'       => $pdo->query("SELECT COUNT(*) FROM matchs")->fetchColumn(),
    'convocations' => $pdo->query("SELECT COUNT(*) FROM convocations WHERE statut='Publiée'")->fetchColumn(),
    'blesses'      => $pdo->query("SELECT COUNT(*) FROM joueurs WHERE statut IN ('blessé','suspendu')")->fetchColumn(),
    'victoires'    => $pdo->query("SELECT COUNT(*) FROM matchs WHERE statut='Terminé' AND score_equipe > score_adverse")->fetchColumn(),
    'matchs_mois'  => $pdo->query("SELECT COUNT(*) FROM matchs WHERE MONTH(date_match)=MONTH(CURDATE()) AND YEAR(date_match)=YEAR(CURDATE())")->fetchColumn(),
];

// Derniers matchs
$derniers_matchs = $pdo->query("
    SELECT m.*, c.nom AS competition
    FROM matchs m
    LEFT JOIN competitions c ON c.id = m.competition_id
    ORDER BY m.date_match DESC LIMIT 5
")->fetchAll();

// Prochain match
$prochains = $pdo->query("
    SELECT m.*, c.nom AS competition
    FROM matchs m
    LEFT JOIN competitions c ON c.id = m.competition_id
    WHERE m.statut = 'Programmé' AND m.date_match >= CURDATE()
    ORDER BY m.date_match ASC LIMIT 1
")->fetchAll();

// Top buteurs
$buteurs = $pdo->query("
    SELECT j.nom, j.prenom, j.poste, j.photo, SUM(s.buts) AS total_buts
    FROM statistiques s
    JOIN joueurs j ON j.id = s.joueur_id
    GROUP BY j.id
    ORDER BY total_buts DESC LIMIT 5
")->fetchAll();

// Répartition joueurs par poste
$postes = $pdo->query("
    SELECT poste, COUNT(*) AS nb FROM joueurs WHERE statut='actif' GROUP BY poste
")->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Tableau de bord';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ====== STAT CARDS ====== -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#475569"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="stat-value"><?= $stats['utilisateurs'] ?></div>
                <div class="stat-label">Utilisateurs actifs</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4;color:#166534"><i class="bi bi-person-badge-fill"></i></div>
            <div>
                <div class="stat-value"><?= $stats['joueurs'] ?></div>
                <div class="stat-label">Joueurs actifs</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f1f5f9;color:#475569"><i class="bi bi-calendar-event-fill"></i></div>
            <div>
                <div class="stat-value"><?= $stats['matchs'] ?></div>
                <div class="stat-label">Matchs enregistrés</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#f0fdf4;color:#166534"><i class="bi bi-clipboard2-check-fill"></i></div>
            <div>
                <div class="stat-value"><?= $stats['convocations'] ?></div>
                <div class="stat-label">Convocations publiées</div>
            </div>
        </div>
    </div>
</div>

<!-- Ligne stats secondaires -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm">
            <div style="width:40px;height:40px;background:#fef2f2;color:#b91c1c;border-radius:8px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-bandaid-fill"></i>
            </div>
            <div>
                <div class="fw-bold fs-5"><?= $stats['blesses'] ?></div>
                <div class="text-muted small">Blessés / Suspendus</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm">
            <div style="width:40px;height:40px;background:#f0fdf4;color:#166534;border-radius:8px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-trophy-fill"></i>
            </div>
            <div>
                <div class="fw-bold fs-5"><?= $stats['victoires'] ?></div>
                <div class="text-muted small">Victoires cette saison</div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="d-flex align-items-center gap-3 p-3 bg-white rounded-3 shadow-sm">
            <div style="width:40px;height:40px;background:#f1f5f9;color:#475569;border-radius:8px;display:flex;align-items:center;justify-content:center">
                <i class="bi bi-calendar-month"></i>
            </div>
            <div>
                <div class="fw-bold fs-5"><?= $stats['matchs_mois'] ?></div>
                <div class="text-muted small">Matchs ce mois</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Derniers matchs -->
    <div class="col-lg-7">
        <div class="card table-card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-calendar-event text-success me-2"></i>Derniers matchs</span>
                <a href="<?= BASE_URL ?>/admin/matchs/index.php" class="btn btn-sm btn-outline-success">Voir tout</a>
            </div>
            <div class="card-body p-0 table-responsive">
                <?php if ($derniers_matchs): ?>
                <table class="table mb-0">
                    <thead><tr>
                        <th>Date</th><th>Adversaire</th><th>Score</th><th>Lieu</th><th>Statut</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($derniers_matchs as $m): ?>
                        <tr>
                            <td><?= formatDate($m['date_match']) ?></td>
                            <td class="fw-semibold"><?= e($m['adversaire']) ?></td>
                            <td>
                                <?php if ($m['statut'] === 'Terminé'): ?>
                                    <span class="fw-bold"><?= $m['score_equipe'] ?> — <?= $m['score_adverse'] ?></span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-dark"><?= e($m['domicile_exterieur']) ?></span></td>
                            <td><?= statusBadge($m['statut']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="text-center py-5 text-muted"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Aucun match enregistré</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Prochains matchs + Top buteurs -->
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-clock text-warning me-2"></i>Prochain match</div>
            <?php if ($prochains): $m = $prochains[0]; ?>
            <div class="card-body text-center py-3">
                <div class="text-muted small mb-1">
                    <?= $m['competition'] ? e($m['competition']) : 'Match amical' ?>
                </div>
                <div class="fw-bold fs-5 mb-2">AS Étoile de Dakar <span class="text-muted fw-normal mx-2">vs</span> <?= e($m['adversaire']) ?></div>
                <div class="d-flex justify-content-center gap-3 text-muted small mb-3">
                    <span><i class="bi bi-calendar3 me-1"></i><?= formatDate($m['date_match']) ?><?= $m['heure_match'] ? ' · '.formatTime($m['heure_match']) : '' ?></span>
                    <span><i class="bi bi-geo-alt me-1"></i><?= e($m['domicile_exterieur']) ?></span>
                    <?php if ($m['stade']): ?>
                    <span><i class="bi bi-building me-1"></i><?= e($m['stade']) ?></span>
                    <?php endif; ?>
                </div>
                <a href="<?= BASE_URL ?>/admin/convocations/add.php?match_id=<?= $m['id'] ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-clipboard2-plus me-1"></i>Convoquer
                </a>
            </div>
            <?php else: ?>
                <div class="card-body text-center py-4 text-muted small">Aucun match à venir</div>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header"><i class="bi bi-bullseye text-danger me-2"></i>Top buteurs</div>
            <div class="card-body p-0">
                <?php if ($buteurs): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($buteurs as $i => $b): ?>
                    <li class="list-group-item px-3 py-2 d-flex align-items-center gap-3">
                        <span class="fw-bold text-muted" style="width:18px"><?= $i+1 ?></span>
                        <img src="<?= avatarUrl($b['photo'], $b['prenom'].' '.$b['nom']) ?>" class="avatar-sm" alt="">
                        <div class="flex-grow-1">
                            <div class="fw-semibold small"><?= e($b['prenom']) ?> <?= e($b['nom']) ?></div>
                            <div class="text-muted" style="font-size:.75rem"><?= e($b['poste']) ?></div>
                        </div>
                        <span class="badge bg-danger rounded-pill"><?= $b['total_buts'] ?> but<?= $b['total_buts'] > 1 ? 's' : '' ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                    <div class="text-center py-4 text-muted small">Aucune statistique disponible</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Répartition effectif -->
<?php if (array_sum($postes) > 0): ?>
<div class="row g-4 mt-0">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-diagram-3 text-primary me-2"></i>Répartition de l'effectif par poste</div>
            <div class="card-body">
                <div class="row g-3">
                    <?php
                    $postesConfig = [
                        'Gardien'   => ['bg-secondary', 'bi-shield-fill'],
                        'Défenseur' => ['bg-primary',   'bi-shield-fill-check'],
                        'Milieu'    => ['bg-success',   'bi-arrows-move'],
                        'Attaquant' => ['bg-danger',    'bi-bullseye'],
                    ];
                    $total = array_sum($postes);
                    foreach ($postesConfig as $poste => [$color, $icon]):
                        $nb  = $postes[$poste] ?? 0;
                        $pct = $total > 0 ? round($nb / $total * 100) : 0;
                    ?>
                    <div class="col-6 col-md-3">
                        <div class="text-center p-3 rounded-3 bg-light">
                            <i class="bi <?= $icon ?> fs-2 <?= str_replace('bg-','text-',$color) ?>"></i>
                            <div class="fw-bold fs-3 mt-1"><?= $nb ?></div>
                            <div class="text-muted small"><?= $poste ?>s</div>
                            <div class="progress mt-2" style="height:4px">
                                <div class="progress-bar <?= $color ?>" style="width:<?= $pct ?>%"></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
