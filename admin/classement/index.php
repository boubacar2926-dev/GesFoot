<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('admin');
$pdo = getPDO();

$competitions = $pdo->query("SELECT * FROM competitions WHERE type='Championnat' ORDER BY nom")->fetchAll();
$compId = (int)($_GET['competition_id'] ?? ($competitions[0]['id'] ?? 0));

// Calcul du classement à partir des matchs terminés
$classement = [];
if ($compId) {
    $matchs = $pdo->prepare("
        SELECT * FROM matchs
        WHERE competition_id = ? AND statut = 'Terminé'
        ORDER BY date_match
    ");
    $matchs->execute([$compId]);
    $matchs = $matchs->fetchAll();

    // Notre équipe vs adversaires
    $equipeNom = $pdo->query("SELECT nom FROM clubs LIMIT 1")->fetchColumn() ?: 'Notre équipe';

    $table = [];
    foreach ($matchs as $m) {
        // Notre équipe
        if (!isset($table[$equipeNom])) {
            $table[$equipeNom] = ['equipe'=>$equipeNom,'j'=>0,'g'=>0,'n'=>0,'p'=>0,'bp'=>0,'bc'=>0,'pts'=>0];
        }
        // Adversaire
        $adv = $m['adversaire'];
        if (!isset($table[$adv])) {
            $table[$adv] = ['equipe'=>$adv,'j'=>0,'g'=>0,'n'=>0,'p'=>0,'bp'=>0,'bc'=>0,'pts'=>0];
        }

        $se = (int)$m['score_equipe'];
        $sa = (int)$m['score_adverse'];

        // Notre équipe
        $table[$equipeNom]['j']++;
        $table[$equipeNom]['bp'] += $se;
        $table[$equipeNom]['bc'] += $sa;
        // Adversaire
        $table[$adv]['j']++;
        $table[$adv]['bp'] += $sa;
        $table[$adv]['bc'] += $se;

        if ($se > $sa) {
            $table[$equipeNom]['g']++; $table[$equipeNom]['pts'] += 3;
            $table[$adv]['p']++;
        } elseif ($se < $sa) {
            $table[$adv]['g']++; $table[$adv]['pts'] += 3;
            $table[$equipeNom]['p']++;
        } else {
            $table[$equipeNom]['n']++; $table[$equipeNom]['pts']++;
            $table[$adv]['n']++;       $table[$adv]['pts']++;
        }
    }

    // Tri: pts DESC, diff DESC, bp DESC
    usort($table, fn($a,$b) => $b['pts'] <=> $a['pts']
        ?: ($b['bp']-$b['bc']) <=> ($a['bp']-$a['bc'])
        ?: $b['bp'] <=> $a['bp']);
    $classement = array_values($table);
}

$pageTitle = 'Classement';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Classement</h4>
</div>

<?php if ($competitions): ?>
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Compétition</label>
                <select name="competition_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($competitions as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $compId===$c['id']?'selected':'' ?>>
                            <?= e($c['nom']) ?> <?= $c['saison'] ? '('.$c['saison'].')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card table-card">
    <div class="card-header">
        <i class="bi bi-award me-2 text-warning"></i>
        <?php if ($compId): ?>
            <?= e($competitions[array_search($compId, array_column($competitions,'id'))]['nom'] ?? '') ?>
        <?php endif; ?>
    </div>
    <div class="card-body p-0 table-responsive">
        <?php if ($classement): ?>
        <table class="table mb-0">
            <thead><tr>
                <th style="width:40px">#</th>
                <th>Équipe</th>
                <th class="text-center">J</th>
                <th class="text-center text-success">G</th>
                <th class="text-center text-warning">N</th>
                <th class="text-center text-danger">P</th>
                <th class="text-center">BP</th>
                <th class="text-center">BC</th>
                <th class="text-center">Diff</th>
                <th class="text-center fw-bold">Pts</th>
            </tr></thead>
            <tbody>
            <?php $equipeNom = $pdo->query("SELECT nom FROM clubs LIMIT 1")->fetchColumn() ?: 'Notre équipe'; ?>
            <?php foreach ($classement as $i => $row): ?>
            <?php $isOurs = $row['equipe'] === $equipeNom; ?>
            <tr class="<?= $isOurs ? 'table-success' : '' ?>">
                <td class="text-center fw-bold">
                    <?php if ($i === 0): ?>
                        <i class="bi bi-trophy-fill text-warning"></i>
                    <?php elseif ($i === 1): ?>
                        <i class="bi bi-trophy-fill text-secondary"></i>
                    <?php elseif ($i === 2): ?>
                        <i class="bi bi-trophy-fill" style="color:#cd7f32"></i>
                    <?php else: ?>
                        <?= $i+1 ?>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="fw-semibold<?= $isOurs ? ' text-success' : '' ?>">
                        <?= e($row['equipe']) ?>
                        <?php if ($isOurs): ?><span class="badge bg-success ms-2">Nous</span><?php endif; ?>
                    </span>
                </td>
                <td class="text-center"><?= $row['j'] ?></td>
                <td class="text-center text-success fw-semibold"><?= $row['g'] ?></td>
                <td class="text-center text-warning fw-semibold"><?= $row['n'] ?></td>
                <td class="text-center text-danger fw-semibold"><?= $row['p'] ?></td>
                <td class="text-center"><?= $row['bp'] ?></td>
                <td class="text-center"><?= $row['bc'] ?></td>
                <td class="text-center">
                    <?php $diff = $row['bp'] - $row['bc']; ?>
                    <span class="<?= $diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted') ?>">
                        <?= $diff > 0 ? '+' : '' ?><?= $diff ?>
                    </span>
                </td>
                <td class="text-center">
                    <span class="badge bg-dark fs-6"><?= $row['pts'] ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-award fs-1 d-block mb-3"></i>
                <?= $compId ? 'Aucun match terminé pour cette compétition.' : 'Sélectionnez une compétition.' ?>
            </div>
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

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
