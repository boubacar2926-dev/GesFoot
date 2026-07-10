<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('joueur');
$pdo  = getPDO();
$user = currentUser();

$joueurStmt = $pdo->prepare("SELECT * FROM joueurs WHERE utilisateur_id=? LIMIT 1");
$joueurStmt->execute([$user['id']]);
$joueur = $joueurStmt->fetch();

$stats   = [];
$cumuls  = [];
$matchsJoues = [];

if ($joueur) {
    $cumulsStmt = $pdo->prepare("
        SELECT COALESCE(SUM(buts),0) AS buts, COALESCE(SUM(passes_decisives),0) AS passes,
               COALESCE(SUM(minutes_jouees),0) AS minutes, COALESCE(SUM(cartons_jaunes),0) AS cj,
               COALESCE(SUM(cartons_rouges),0) AS cr, COUNT(DISTINCT match_id) AS matchs,
               ROUND(AVG(note_match),1) AS note_moy
        FROM statistiques WHERE joueur_id=?
    ");
    $cumulsStmt->execute([$joueur['id']]);
    $cumuls = $cumulsStmt->fetch();

    $histStmt = $pdo->prepare("
        SELECT m.date_match, m.adversaire, m.score_equipe, m.score_adverse, m.domicile_exterieur,
               c.nom AS competition,
               s.minutes_jouees, s.buts, s.passes_decisives, s.cartons_jaunes, s.cartons_rouges, s.note_match
        FROM statistiques s
        JOIN matchs m ON m.id=s.match_id
        LEFT JOIN competitions c ON c.id=m.competition_id
        WHERE s.joueur_id=? ORDER BY m.date_match DESC
    ");
    $histStmt->execute([$joueur['id']]);
    $matchsJoues = $histStmt->fetchAll();
}

$pageTitle = 'Mes statistiques';
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4"><h4 class="fw-bold mb-0">Mes statistiques</h4></div>

<?php if ($joueur): ?>

<!-- Cumuls -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['bi-calendar-check','Matchs joués',   $cumuls['matchs']   ?? 0, '#f1f5f9','#475569'],
        ['bi-bullseye',      'Buts',            $cumuls['buts']     ?? 0, '#f0fdf4','#166534'],
        ['bi-send',          'Passes D.',       $cumuls['passes']   ?? 0, '#f0fdf4','#166534'],
        ['bi-clock',         'Minutes',         ($cumuls['minutes'] ?? 0)."'", '#f1f5f9','#475569'],
        ['bi-card-text',     'Cartons J.',      $cumuls['cj']       ?? 0, '#fef9c3','#854d0e'],
        ['bi-card-text',     'Cartons R.',      $cumuls['cr']       ?? 0, '#fef2f2','#b91c1c'],
    ];
    foreach ($cards as [$icon,$label,$val,$bg,$color]):
    ?>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="stat-card">
            <div class="stat-icon" style="background:<?= $bg ?>;color:<?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
            <div><div class="stat-value" style="font-size:1.4rem"><?= $val ?></div><div class="stat-label"><?= $label ?></div></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Note moyenne -->
<?php if ($cumuls['note_moy']): ?>
<div class="alert alert-<?= $cumuls['note_moy']>=7?'success':($cumuls['note_moy']>=5?'warning':'danger') ?> mb-4">
    <i class="bi bi-star-fill me-2"></i>
    Note moyenne sur tous vos matchs : <strong class="fs-5"><?= $cumuls['note_moy'] ?>/10</strong>
</div>
<?php endif; ?>

<!-- Historique -->
<div class="card table-card">
    <div class="card-header"><i class="bi bi-list-ul me-2"></i>Historique match par match</div>
    <div class="card-body p-0 table-responsive">
        <?php if ($matchsJoues): ?>
        <table class="table datatable mb-0">
            <thead><tr>
                <th>Date</th><th>Adversaire</th><th>Compétition</th>
                <th class="text-center">Score</th><th class="text-center">Min</th>
                <th class="text-center">Buts</th><th class="text-center">Passes D.</th>
                <th class="text-center">CJ</th><th class="text-center">CR</th><th class="text-center">Note</th>
            </tr></thead>
            <tbody>
            <?php foreach ($matchsJoues as $m):
                $rCls = '';
                if ($m['score_equipe'] > $m['score_adverse'])      $rCls = 'text-success';
                elseif ($m['score_equipe'] < $m['score_adverse'])  $rCls = 'text-danger';
                else                                               $rCls = 'text-warning';
            ?>
            <tr>
                <td><?= formatDate($m['date_match']) ?></td>
                <td class="fw-semibold">vs <?= e($m['adversaire']) ?><br><small class="text-muted"><?= e($m['domicile_exterieur']) ?></small></td>
                <td class="text-muted small"><?= e($m['competition'] ?? '—') ?></td>
                <td class="text-center fw-bold <?= $rCls ?>"><?= $m['score_equipe'] ?>—<?= $m['score_adverse'] ?></td>
                <td class="text-center"><?= $m['minutes_jouees'] ?>'</td>
                <td class="text-center fw-bold text-success"><?= $m['buts'] ?: '—' ?></td>
                <td class="text-center"><?= $m['passes_decisives'] ?: '—' ?></td>
                <td class="text-center"><?= $m['cartons_jaunes'] ? '<span class="badge bg-warning text-dark">'.$m['cartons_jaunes'].'</span>' : '—' ?></td>
                <td class="text-center"><?= $m['cartons_rouges'] ? '<span class="badge bg-danger">'.$m['cartons_rouges'].'</span>' : '—' ?></td>
                <td class="text-center">
                    <?php if ($m['note_match']): ?>
                    <span class="badge <?= $m['note_match']>=7?'bg-success':($m['note_match']>=5?'bg-warning text-dark':'bg-danger') ?>">
                        <?= $m['note_match'] ?>
                    </span>
                    <?php else: ?>—<?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="text-center py-5 text-muted"><i class="bi bi-bar-chart fs-2 d-block mb-2"></i>Aucune statistique disponible</div>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<div class="alert alert-warning"><i class="bi bi-exclamation-triangle-fill me-2"></i>Votre compte n'est pas lié à une fiche joueur.</div>
<?php endif; ?>

<?php if ($matchsJoues && count($matchsJoues) > 1): ?>
<div class="row g-4 mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-graph-up me-2"></i>Évolution de mes performances</div>
            <div class="card-body">
                <div style="position:relative;height:250px;">
                    <canvas id="chartMesPerf"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
(function() {
    const matches = <?= json_encode(array_map(fn($m) => formatDate($m['date_match']).' vs '.$m['adversaire'], array_reverse($matchsJoues)), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const notes   = <?= json_encode(array_map(fn($m) => $m['note_match'] !== null ? (float)$m['note_match'] : null, array_reverse($matchsJoues)), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const buts    = <?= json_encode(array_map(fn($m) => (int)$m['buts'], array_reverse($matchsJoues)), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    new Chart(document.getElementById('chartMesPerf'), {
        type: 'line',
        data: {
            labels: matches,
            datasets: [
                {
                    label: 'Note /10',
                    data: notes,
                    borderColor: '#2d6a4f',
                    backgroundColor: 'rgba(45,106,79,.1)',
                    tension: 0.3, fill: true, yAxisID: 'y',
                },
                {
                    label: 'Buts',
                    data: buts,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220,38,38,.15)',
                    type: 'bar', yAxisID: 'y2',
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
