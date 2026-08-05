<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('staff');
$pdo = getPDO();

$joueurs = $pdo->query("
    SELECT j.*,
        COALESCE(SUM(s.buts),0) AS buts,
        COALESCE(SUM(s.passes_decisives),0) AS passes,
        COALESCE(SUM(s.minutes_jouees),0) AS minutes,
        COUNT(DISTINCT s.match_id) AS matchs_joues
    FROM joueurs j LEFT JOIN statistiques s ON s.joueur_id=j.id
    GROUP BY j.id ORDER BY j.poste, j.nom
")->fetchAll();

$pageTitle = 'Joueurs';
require_once __DIR__ . '/includes/header.php';
?>
<div class="mb-4"><h4 class="fw-bold mb-0">Fiche joueurs</h4></div>
<div class="card table-card">
    <div class="card-body p-0 table-responsive">
        <table class="table datatable mb-0">
            <thead><tr>
                <th>Joueur</th><th>Poste</th><th>N°</th><th>Statut</th>
                <th class="text-center">Matchs</th><th class="text-center">Buts</th>
                <th class="text-center">Passes D.</th><th class="text-center">Minutes</th>
            </tr></thead>
            <tbody>
            <?php foreach ($joueurs as $j): ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="<?= avatarUrl($j['photo'], $j['prenom'].' '.$j['nom']) ?>" class="avatar-sm">
                        <span class="fw-semibold"><?= e($j['prenom']) ?> <?= e($j['nom']) ?></span>
                    </div>
                </td>
                <td><?= e($j['poste']) ?></td>
                <td><?= $j['numero_maillot']?'#'.$j['numero_maillot']:'—' ?></td>
                <td><?= statusBadge($j['statut']) ?></td>
                <td class="text-center"><?= $j['matchs_joues'] ?></td>
                <td class="text-center fw-bold text-success"><?= $j['buts'] ?></td>
                <td class="text-center"><?= $j['passes'] ?></td>
                <td class="text-center"><?= $j['minutes'] ?>'</td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
