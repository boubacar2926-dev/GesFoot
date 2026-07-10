<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('staff');
$pdo = getPDO();

$matchId   = (int)($_GET['match_id'] ?? 0);
$matchs    = $pdo->query("SELECT m.*, c.nom AS competition FROM matchs m LEFT JOIN competitions c ON c.id=m.competition_id WHERE m.statut IN ('Terminé','En cours') ORDER BY m.date_match DESC")->fetchAll();
$matchInfo = null;
$stats     = [];

if ($matchId) {
    $stmt = $pdo->prepare("SELECT m.*, c.nom AS competition FROM matchs m LEFT JOIN competitions c ON c.id=m.competition_id WHERE m.id=?");
    $stmt->execute([$matchId]);
    $matchInfo = $stmt->fetch();

    $stmt = $pdo->prepare("
        SELECT j.id, j.nom, j.prenom, j.poste, j.numero_maillot, j.photo,
               s.minutes_jouees, s.buts, s.passes_decisives,
               s.cartons_jaunes, s.cartons_rouges, s.note_match
        FROM joueurs j
        LEFT JOIN statistiques s ON s.joueur_id=j.id AND s.match_id=?
        WHERE j.statut='actif'
        ORDER BY j.poste, j.nom
    ");
    $stmt->execute([$matchId]);
    $stats = $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $mid  = (int)($_POST['match_id'] ?? 0);
    $rows = $_POST['stats'] ?? [];
    $ins  = $pdo->prepare("
        INSERT INTO statistiques (joueur_id, match_id, minutes_jouees, buts, passes_decisives, cartons_jaunes, cartons_rouges, note_match)
        VALUES (?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE
            minutes_jouees=VALUES(minutes_jouees), buts=VALUES(buts),
            passes_decisives=VALUES(passes_decisives), cartons_jaunes=VALUES(cartons_jaunes),
            cartons_rouges=VALUES(cartons_rouges), note_match=VALUES(note_match)
    ");
    foreach ($rows as $jid => $r) {
        $mins = (int)($r['minutes'] ?? 0);
        $note = ($r['note'] ?? '') !== '' ? (float)$r['note'] : null;
        if ($mins === 0 && !($r['buts']??0) && !($r['passes']??0) && !($r['cj']??0) && !($r['cr']??0) && $note === null) continue;
        $ins->execute([(int)$jid, $mid, $mins, (int)($r['buts']??0), (int)($r['passes']??0),
            (int)($r['cj']??0), (int)($r['cr']??0), ($r['note']??'')!==''?(float)$r['note']:null]);
    }
    setFlash('success','Statistiques enregistrées.');
    redirect('/staff/statistiques/index.php?match_id='.$mid);
}

$pageTitle = 'Saisie des statistiques';
require_once ROOT_PATH . '/staff/includes/header.php';
?>

<div class="mb-4">
    <h4 class="fw-bold mb-0">Saisie des statistiques</h4>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET">
            <select name="match_id" class="form-select" style="max-width:500px" onchange="this.form.submit()">
                <option value="">— Sélectionner un match —</option>
                <?php foreach ($matchs as $m): ?>
                    <option value="<?= $m['id'] ?>" <?= $matchId===$m['id']?'selected':'' ?>>
                        <?= formatDate($m['date_match']) ?> — vs <?= e($m['adversaire']) ?>
                        <?= $m['statut']==='Terminé'?'✅':'' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if ($matchId && $matchInfo): ?>
<!-- Info match -->
<div class="alert alert-info d-flex align-items-center gap-3 mb-4">
    <i class="bi bi-info-circle-fill fs-4"></i>
    <div>
        <strong>vs <?= e($matchInfo['adversaire']) ?></strong> —
        <?= formatDate($matchInfo['date_match']) ?> —
        Score : <strong><?= (int)$matchInfo['score_equipe'] ?>—<?= (int)$matchInfo['score_adverse'] ?></strong>
        <?= $matchInfo['competition'] ? '— '.e($matchInfo['competition']) : '' ?>
        <?= statusBadge($matchInfo['statut']) ?>
    </div>
</div>

<form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="match_id" value="<?= $matchId ?>">
    <div class="card table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table mb-0" style="min-width:700px">
                <thead><tr>
                    <th>Joueur</th>
                    <th class="text-center" style="width:80px">Min.</th>
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
                                <small class="text-muted"><?= e($j['poste']) ?><?= $j['numero_maillot']?' #'.$j['numero_maillot']:'' ?></small>
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
                </tbody>
            </table>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-end mt-3">
        <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-check2 me-1"></i> Enregistrer</button>
    </div>
</form>

<?php else: ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-bar-chart-line fs-1 d-block mb-3"></i>Sélectionnez un match pour saisir les statistiques.
</div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/staff/includes/footer.php'; ?>
