<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('admin');
$pdo = getPDO();

$matchId = (int)($_GET['match_id'] ?? 0);

$matchs = $pdo->query("
    SELECT m.*, c.nom AS competition FROM matchs m
    LEFT JOIN competitions c ON c.id=m.competition_id
    WHERE m.statut IN ('Terminé','En cours')
    ORDER BY m.date_match DESC
")->fetchAll();

$stats = [];
$matchInfo = null;
if ($matchId) {
    $stmt = $pdo->prepare("SELECT m.*, c.nom AS competition FROM matchs m LEFT JOIN competitions c ON c.id=m.competition_id WHERE m.id=?");
    $stmt->execute([$matchId]);
    $matchInfo = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT j.id, j.nom, j.prenom, j.poste, j.numero_maillot, j.photo,
               s.id AS stat_id, s.minutes_jouees, s.buts, s.passes_decisives,
               s.cartons_jaunes, s.cartons_rouges, s.note_match
        FROM joueurs j
        LEFT JOIN statistiques s ON s.joueur_id = j.id AND s.match_id = ?
        WHERE j.statut = 'actif'
        ORDER BY j.poste, j.nom
    ");
    $stmt->execute([$matchId]);
    $stats = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $mid   = (int)($_POST['match_id'] ?? 0);
    $rows  = $_POST['stats'] ?? [];

    $ins = $pdo->prepare("
        INSERT INTO statistiques
            (joueur_id, match_id, minutes_jouees, buts, passes_decisives, cartons_jaunes, cartons_rouges, note_match)
        VALUES (?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE
            minutes_jouees=VALUES(minutes_jouees), buts=VALUES(buts),
            passes_decisives=VALUES(passes_decisives), cartons_jaunes=VALUES(cartons_jaunes),
            cartons_rouges=VALUES(cartons_rouges), note_match=VALUES(note_match)
    ");
    foreach ($rows as $jid => $r) {
        $jid  = (int)$jid;
        $mins = (int)($r['minutes'] ?? 0);
        $note = ($r['note'] ?? '') !== '' ? (float)$r['note'] : null;
        if ($mins === 0 && !($r['buts']??0) && !($r['passes']??0) && !($r['cj']??0) && !($r['cr']??0) && $note === null) continue;
        $ins->execute([
            $jid, $mid,
            $mins,
            (int)($r['buts']    ?? 0),
            (int)($r['passes']  ?? 0),
            (int)($r['cj']      ?? 0),
            (int)($r['cr']      ?? 0),
            ($r['note'] ?? '') !== '' ? (float)$r['note'] : null,
        ]);
    }
    setFlash('success', 'Statistiques enregistrées.');
    redirect('/admin/statistiques/index.php?match_id=' . $mid);
}

// Top buteurs saison (pour graphique)
$topButeurs = $pdo->query("
    SELECT j.prenom, j.nom, SUM(s.buts) AS buts, SUM(s.passes_decisives) AS passes
    FROM statistiques s JOIN joueurs j ON j.id=s.joueur_id
    GROUP BY j.id HAVING buts > 0
    ORDER BY buts DESC LIMIT 8
")->fetchAll();

// Stats match sélectionné (pour graphique)
$statsChart = [];
if ($matchId && $stats) {
    foreach ($stats as $s) {
        if (($s['minutes_jouees'] ?? 0) > 0 || ($s['buts'] ?? 0) > 0 || ($s['note_match'] ?? 0) > 0) {
            $statsChart[] = $s;
        }
    }
}

$pageTitle = 'Statistiques';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Statistiques par match</h4>
</div>

<!-- Sélection match -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-9">
                <label class="form-label fw-semibold">Sélectionner un match</label>
                <select name="match_id" class="form-select" onchange="this.form.submit()">
                    <option value="">— Choisir un match —</option>
                    <?php foreach ($matchs as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= $matchId===$m['id']?'selected':'' ?>>
                            <?= formatDate($m['date_match']) ?> — vs <?= e($m['adversaire']) ?>
                            <?= $m['competition'] ? ' ('.$m['competition'].')' : '' ?>
                            — <?= e($m['statut']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($matchId && $matchInfo): ?>
<!-- Résumé match -->
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-4 flex-wrap">
            <div>
                <div class="text-muted small">Match</div>
                <div class="fw-bold fs-5">vs <?= e($matchInfo['adversaire']) ?></div>
            </div>
            <div>
                <div class="text-muted small">Date</div>
                <div class="fw-semibold"><?= formatDate($matchInfo['date_match']) ?></div>
            </div>
            <?php if ($matchInfo['competition']): ?>
            <div>
                <div class="text-muted small">Compétition</div>
                <div class="fw-semibold"><?= e($matchInfo['competition']) ?></div>
            </div>
            <?php endif; ?>
            <div>
                <div class="text-muted small">Score</div>
                <div class="fw-bold fs-5 <?= $matchInfo['score_equipe'] > $matchInfo['score_adverse'] ? 'text-success' : ($matchInfo['score_equipe'] < $matchInfo['score_adverse'] ? 'text-danger' : 'text-warning') ?>">
                    <?= $matchInfo['score_equipe'] ?> — <?= $matchInfo['score_adverse'] ?>
                </div>
            </div>
            <div class="ms-auto"><?= statusBadge($matchInfo['statut']) ?></div>
        </div>
    </div>
</div>

<form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="match_id" value="<?= $matchId ?>">
    <div class="card table-card">
        <div class="card-header"><i class="bi bi-bar-chart-line me-2"></i>Saisie des statistiques</div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table mb-0" style="min-width:750px">
                <thead><tr>
                    <th>Joueur</th>
                    <th class="text-center" style="width:80px">Min. joués</th>
                    <th class="text-center" style="width:70px">Buts</th>
                    <th class="text-center" style="width:80px">Passes D.</th>
                    <th class="text-center" style="width:70px"><span class="text-warning">CJ</span></th>
                    <th class="text-center" style="width:70px"><span class="text-danger">CR</span></th>
                    <th class="text-center" style="width:90px">Note /10</th>
                </tr></thead>
                <tbody>
                <?php foreach ($stats as $j): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= avatarUrl($j['photo'], $j['prenom'].' '.$j['nom']) ?>" class="avatar-sm">
                            <div>
                                <div class="fw-semibold small"><?= e($j['prenom']) ?> <?= e($j['nom']) ?></div>
                                <small class="text-muted"><?= e($j['poste']) ?><?= $j['numero_maillot'] ? ' #'.$j['numero_maillot'] : '' ?></small>
                            </div>
                        </div>
                    </td>
                    <td><input type="number" name="stats[<?= $j['id'] ?>][minutes]" class="form-control form-control-sm text-center" min="0" max="120" value="<?= $j['minutes_jouees'] ?? '' ?>" placeholder="0"></td>
                    <td><input type="number" name="stats[<?= $j['id'] ?>][buts]"    class="form-control form-control-sm text-center" min="0" max="20"  value="<?= $j['buts'] ?? '' ?>" placeholder="0"></td>
                    <td><input type="number" name="stats[<?= $j['id'] ?>][passes]"  class="form-control form-control-sm text-center" min="0" max="20"  value="<?= $j['passes_decisives'] ?? '' ?>" placeholder="0"></td>
                    <td><input type="number" name="stats[<?= $j['id'] ?>][cj]"      class="form-control form-control-sm text-center" min="0" max="2"   value="<?= $j['cartons_jaunes'] ?? '' ?>" placeholder="0"></td>
                    <td><input type="number" name="stats[<?= $j['id'] ?>][cr]"      class="form-control form-control-sm text-center" min="0" max="1"   value="<?= $j['cartons_rouges'] ?? '' ?>" placeholder="0"></td>
                    <td><input type="number" name="stats[<?= $j['id'] ?>][note]"    class="form-control form-control-sm text-center" min="0" max="10" step="0.5" value="<?= $j['note_match'] ?? '' ?>" placeholder="—"></td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$stats): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">Aucun joueur actif</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <?php if ($stats): ?>
    <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-success"><i class="bi bi-check2 me-1"></i> Enregistrer les statistiques</button>
    </div>
    <?php endif; ?>
</form>

<?php else: ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-bar-chart-line fs-1 d-block mb-3"></i>
    Sélectionnez un match pour saisir les statistiques.
</div>
<?php endif; ?>

<?php if ($topButeurs || $statsChart): ?>
<div class="row g-4 mt-1">

    <?php if ($statsChart): ?>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-stopwatch me-2"></i>Minutes jouées — ce match</div>
            <div class="card-body">
                <div style="position:relative;height:300px;">
                    <canvas id="chartMinutes"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-star me-2"></i>Notes — ce match</div>
            <div class="card-body">
                <div style="position:relative;height:300px;">
                    <canvas id="chartNotes"></canvas>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($topButeurs): ?>
    <div class="col-lg-<?= $statsChart ? '12' : '8 offset-lg-2' ?>">
        <div class="card">
            <div class="card-header"><i class="bi bi-bullseye me-2 text-danger"></i>Top buteurs — saison</div>
            <div class="card-body">
                <div style="position:relative;height:<?= $statsChart ? '250px' : '300px' ?>;">
                    <canvas id="chartButeurs"></canvas>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
<?php if ($statsChart): ?>
const labelsMatch = <?= json_encode(array_map(fn($s) => $s['prenom'].' '.$s['nom'], $statsChart), JSON_HEX_TAG | JSON_HEX_AMP) ?>;
new Chart(document.getElementById('chartMinutes'), {
    type: 'bar',
    data: {
        labels: labelsMatch,
        datasets: [{
            label: 'Minutes',
            data: <?= json_encode(array_map(fn($s) => (int)($s['minutes_jouees']??0), $statsChart)) ?>,
            backgroundColor: '#2d6a4f',
            borderRadius: 4,
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 120 } } }
});
new Chart(document.getElementById('chartNotes'), {
    type: 'bar',
    data: {
        labels: labelsMatch,
        datasets: [{
            label: 'Note /10',
            data: <?= json_encode(array_map(fn($s) => $s['note_match'] !== null ? (float)$s['note_match'] : null, $statsChart)) ?>,
            backgroundColor: '#f4a261',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, max: 10 } }
    }
});
<?php endif; ?>

<?php if ($topButeurs): ?>
new Chart(document.getElementById('chartButeurs'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($b) => $b['prenom'].' '.$b['nom'], $topButeurs), JSON_HEX_TAG | JSON_HEX_AMP) ?>,
        datasets: [
            {
                label: 'Buts',
                data: <?= json_encode(array_map(fn($b) => (int)$b['buts'], $topButeurs)) ?>,
                backgroundColor: '#dc2626',
                borderRadius: 4,
            },
            {
                label: 'Passes D.',
                data: <?= json_encode(array_map(fn($b) => (int)$b['passes'], $topButeurs)) ?>,
                backgroundColor: '#2d6a4f',
                borderRadius: 4,
            }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
});
<?php endif; ?>
</script>
<?php endif; ?>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
