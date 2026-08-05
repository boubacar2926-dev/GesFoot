<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('joueur');
$pdo = getPDO();

$competitions = $pdo->query("SELECT * FROM competitions WHERE type='Championnat' ORDER BY nom")->fetchAll();
$compId = (int)($_GET['competition_id'] ?? ($competitions[0]['id'] ?? 0));
$equipeNom = $pdo->query("SELECT nom FROM clubs LIMIT 1")->fetchColumn() ?: 'Notre équipe';

$classement = [];
if ($compId) {
    $matchs = $pdo->prepare("SELECT * FROM matchs WHERE competition_id=? AND statut='Terminé'");
    $matchs->execute([$compId]);
    $matchs = $matchs->fetchAll();
    $table  = [];
    foreach ($matchs as $m) {
        foreach ([$equipeNom, $m['adversaire']] as $eq) {
            if (!isset($table[$eq])) $table[$eq] = ['equipe'=>$eq,'j'=>0,'g'=>0,'n'=>0,'p'=>0,'bp'=>0,'bc'=>0,'pts'=>0];
        }
        $se = (int)$m['score_equipe']; $sa = (int)$m['score_adverse'];
        $table[$equipeNom]['j']++; $table[$equipeNom]['bp']+=$se; $table[$equipeNom]['bc']+=$sa;
        $table[$m['adversaire']]['j']++; $table[$m['adversaire']]['bp']+=$sa; $table[$m['adversaire']]['bc']+=$se;
        if ($se>$sa)      { $table[$equipeNom]['g']++; $table[$equipeNom]['pts']+=3; $table[$m['adversaire']]['p']++; }
        elseif ($se<$sa)  { $table[$m['adversaire']]['g']++; $table[$m['adversaire']]['pts']+=3; $table[$equipeNom]['p']++; }
        else              { $table[$equipeNom]['n']++; $table[$equipeNom]['pts']++; $table[$m['adversaire']]['n']++; $table[$m['adversaire']]['pts']++; }
    }
    usort($table, fn($a,$b) => $b['pts']<=>$a['pts'] ?: ($b['bp']-$b['bc'])<=>($a['bp']-$a['bc']));
    $classement = array_values($table);
}

$pageTitle = 'Classement';
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4"><h4 class="fw-bold mb-0">Classement général</h4></div>

<?php if ($competitions): ?>
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET">
            <select name="competition_id" class="form-select" style="max-width:400px" onchange="this.form.submit()">
                <?php foreach ($competitions as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $compId===$c['id']?'selected':'' ?>><?= e($c['nom']) ?> <?= $c['saison']?'('.$c['saison'].')':'' ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card table-card">
    <div class="card-body p-0 table-responsive">
        <?php if ($classement): ?>
        <table class="table mb-0">
            <thead><tr>
                <th>#</th><th>Équipe</th>
                <th class="text-center">J</th><th class="text-center text-success">G</th>
                <th class="text-center text-warning">N</th><th class="text-center text-danger">P</th>
                <th class="text-center">BP</th><th class="text-center">BC</th>
                <th class="text-center">+/-</th><th class="text-center">Pts</th>
            </tr></thead>
            <tbody>
            <?php foreach ($classement as $i => $row): $isOurs = $row['equipe']===$equipeNom; ?>
            <tr class="<?= $isOurs?'table-success':'' ?>">
                <td class="text-center fw-bold"><?= $i===0?'🥇':($i===1?'🥈':($i===2?'🥉':$i+1)) ?></td>
                <td class="fw-semibold<?= $isOurs?' text-success':'' ?>">
                    <?= e($row['equipe']) ?>
                    <?php if ($isOurs): ?><span class="badge bg-success ms-1">Nous</span><?php endif; ?>
                </td>
                <td class="text-center"><?= $row['j'] ?></td>
                <td class="text-center text-success"><?= $row['g'] ?></td>
                <td class="text-center text-warning"><?= $row['n'] ?></td>
                <td class="text-center text-danger"><?= $row['p'] ?></td>
                <td class="text-center"><?= $row['bp'] ?></td>
                <td class="text-center"><?= $row['bc'] ?></td>
                <td class="text-center <?= ($row['bp']-$row['bc'])>0?'text-success':'text-danger' ?>">
                    <?= ($row['bp']-$row['bc'])>0?'+':'' ?><?= $row['bp']-$row['bc'] ?>
                </td>
                <td class="text-center"><span class="badge bg-dark"><?= $row['pts'] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="text-center py-5 text-muted"><i class="bi bi-award fs-1 d-block mb-3"></i>Aucun résultat disponible.</div>
        <?php endif; ?>
    </div>
</div>

<?php if ($classement): ?>
<div class="mt-3">
    <small class="text-muted">
        <i class="bi bi-info-circle me-1"></i>
        Victoire = 3 pts · Nul = 1 pt · Défaite = 0 pt · J=Joués · G=Gagnés · N=Nuls · P=Perdus · BP=Buts Pour · BC=Buts Contre
    </small>
    <br>
    <small class="text-muted">
        <i class="bi bi-exclamation-circle me-1"></i>
        Ce classement reflète uniquement les confrontations de votre club dans cette compétition — les statistiques des autres équipes ne prennent en compte que leurs matchs contre vous, pas l'ensemble de leurs résultats du championnat.
    </small>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
