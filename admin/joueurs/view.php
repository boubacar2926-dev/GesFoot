<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT j.*, u.email FROM joueurs j LEFT JOIN utilisateurs u ON u.id=j.utilisateur_id WHERE j.id=?");
$stmt->execute([$id]);
$j = $stmt->fetch();
if (!$j) { setFlash('error','Joueur introuvable.'); redirect('/admin/joueurs/index.php'); }

// Statistiques cumulées
$statsStmt = $pdo->prepare("
    SELECT
        COUNT(DISTINCT s.match_id) AS matchs_joues,
        SUM(s.buts) AS buts,
        SUM(s.passes_decisives) AS passes,
        SUM(s.cartons_jaunes) AS cj,
        SUM(s.cartons_rouges) AS cr,
        SUM(s.minutes_jouees) AS minutes,
        AVG(s.note_match) AS note_moy
    FROM statistiques s WHERE s.joueur_id = ?
");
$statsStmt->execute([$id]);
$stats = $statsStmt->fetch();

// Derniers matchs du joueur
$matchsStmt = $pdo->prepare("
    SELECT m.date_match, m.adversaire, m.score_equipe, m.score_adverse, m.statut,
           s.buts, s.passes_decisives, s.cartons_jaunes, s.cartons_rouges, s.minutes_jouees, s.note_match
    FROM statistiques s
    JOIN matchs m ON m.id = s.match_id
    WHERE s.joueur_id = ?
    ORDER BY m.date_match DESC LIMIT 10
");
$matchsStmt->execute([$id]);
$matchsJoueur = $matchsStmt->fetchAll();

$pageTitle = 'Profil joueur — ' . $j['prenom'] . ' ' . $j['nom'];
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= BASE_URL ?>/admin/joueurs/index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">Profil Joueur</h4>
    <a href="<?= BASE_URL ?>/admin/joueurs/edit.php?id=<?= $j['id'] ?>" class="btn btn-sm btn-outline-primary ms-auto">
        <i class="bi bi-pencil me-1"></i> Modifier
    </a>
</div>

<div class="row g-4">
    <!-- Carte profil -->
    <div class="col-lg-4">
        <div class="card text-center">
            <div style="background:#1b4332;padding:30px;border-radius:8px 8px 0 0">
                <img src="<?= avatarUrl($j['photo'], $j['prenom'].' '.$j['nom']) ?>"
                     style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.4)">
                <div class="mt-3 text-white">
                    <h5 class="fw-bold mb-1"><?= e($j['prenom']) ?> <?= e($j['nom']) ?></h5>
                    <?php if ($j['numero_maillot']): ?>
                        <span class="badge bg-light text-dark fs-6">#<?= $j['numero_maillot'] ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-2"><?= statusBadge($j['statut']) ?> <span class="badge bg-secondary"><?= e($j['poste']) ?></span></div>
                <div class="divider"></div>
                <ul class="list-unstyled text-start small">
                    <?php if ($j['date_naissance']): ?>
                    <li class="mb-2"><i class="bi bi-calendar3 text-muted me-2"></i><strong>Naissance :</strong> <?= formatDate($j['date_naissance']) ?> (<?= age($j['date_naissance']) ?>)</li>
                    <?php endif; ?>
                    <?php if ($j['nationalite']): ?>
                    <li class="mb-2"><i class="bi bi-flag text-muted me-2"></i><strong>Nationalité :</strong> <?= e($j['nationalite']) ?></li>
                    <?php endif; ?>
                    <?php if ($j['taille']): ?>
                    <li class="mb-2"><i class="bi bi-rulers text-muted me-2"></i><strong>Taille :</strong> <?= $j['taille'] ?> cm</li>
                    <?php endif; ?>
                    <?php if ($j['poids']): ?>
                    <li class="mb-2"><i class="bi bi-speedometer2 text-muted me-2"></i><strong>Poids :</strong> <?= $j['poids'] ?> kg</li>
                    <?php endif; ?>
                    <?php if ($j['email']): ?>
                    <li class="mb-2"><i class="bi bi-envelope text-muted me-2"></i><?= e($j['email']) ?></li>
                    <?php endif; ?>
                    <li><i class="bi bi-calendar-check text-muted me-2"></i><strong>Inscrit :</strong> <?= formatDate($j['date_inscription']) ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Stats + matchs -->
    <div class="col-lg-8">
        <!-- Stats cards -->
        <div class="row g-3 mb-4">
            <?php
            $statsCards = [
                ['bi-calendar-check','Matchs joués', $stats['matchs_joues'] ?? 0, '#f1f5f9','#475569'],
                ['bi-bullseye','Buts',                $stats['buts']         ?? 0, '#f0fdf4','#166534'],
                ['bi-send','Passes D.',               $stats['passes']       ?? 0, '#f0fdf4','#166534'],
                ['bi-card-text','Cartons J.',         $stats['cj']           ?? 0, '#fef9c3','#854d0e'],
                ['bi-card-text','Cartons R.',         $stats['cr']           ?? 0, '#fef2f2','#b91c1c'],
                ['bi-clock','Minutes',                $stats['minutes']      ?? 0, '#f1f5f9','#475569'],
            ];
            foreach ($statsCards as [$icon, $label, $val, $bg, $color]):
            ?>
            <div class="col-6 col-md-4">
                <div class="stat-card">
                    <div class="stat-icon" style="background:<?= $bg ?>;color:<?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
                    <div>
                        <div class="stat-value"><?= $val ?></div>
                        <div class="stat-label"><?= $label ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Historique matchs -->
        <div class="card table-card">
            <div class="card-header"><i class="bi bi-list-ul me-2"></i>Historique des matchs</div>
            <div class="card-body p-0 table-responsive">
                <?php if ($matchsJoueur): ?>
                <table class="table mb-0">
                    <thead><tr>
                        <th>Date</th><th>Adversaire</th><th>Score</th>
                        <th>Min</th><th>Buts</th><th>Passes</th><th>CJ</th><th>CR</th><th>Note</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($matchsJoueur as $m): ?>
                    <tr>
                        <td class="small"><?= formatDate($m['date_match']) ?></td>
                        <td><?= e($m['adversaire']) ?></td>
                        <td class="fw-bold"><?= $m['score_equipe'] ?>—<?= $m['score_adverse'] ?></td>
                        <td><?= $m['minutes_jouees'] ?>'</td>
                        <td><?= $m['buts'] ?: '—' ?></td>
                        <td><?= $m['passes_decisives'] ?: '—' ?></td>
                        <td><?= $m['cartons_jaunes'] ? '<span class="badge bg-warning text-dark">'.$m['cartons_jaunes'].'</span>' : '—' ?></td>
                        <td><?= $m['cartons_rouges'] ? '<span class="badge bg-danger">'.$m['cartons_rouges'].'</span>' : '—' ?></td>
                        <td><?= $m['note_match'] ? number_format($m['note_match'],1) : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="text-center py-5 text-muted"><i class="bi bi-bar-chart fs-2 d-block mb-2"></i>Aucune statistique</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($matchsJoueur && count($matchsJoueur) > 1): ?>
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-graph-up me-2"></i>Évolution des performances</div>
            <div class="card-body">
                <div style="position:relative;height:250px;">
                    <canvas id="chartPerf"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function() {
    const matches   = <?= json_encode(array_map(fn($m) => formatDate($m['date_match']).' vs '.$m['adversaire'], array_reverse($matchsJoueur)), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const notes     = <?= json_encode(array_map(fn($m) => $m['note_match'] !== null ? (float)$m['note_match'] : null, array_reverse($matchsJoueur)), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const buts      = <?= json_encode(array_map(fn($m) => (int)$m['buts'], array_reverse($matchsJoueur)), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    new Chart(document.getElementById('chartPerf'), {
        type: 'line',
        data: {
            labels: matches,
            datasets: [
                {
                    label: 'Note /10',
                    data: notes,
                    borderColor: '#f4a261',
                    backgroundColor: 'rgba(244,162,97,.15)',
                    tension: 0.3,
                    fill: true,
                    yAxisID: 'y',
                },
                {
                    label: 'Buts',
                    data: buts,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220,38,38,.15)',
                    tension: 0.3,
                    fill: false,
                    yAxisID: 'y2',
                    type: 'bar',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y:  { beginAtZero: true, max: 10, title: { display: true, text: 'Note' } },
                y2: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Buts' } }
            }
        }
    });
}());
</script>
<?php endif; ?>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
