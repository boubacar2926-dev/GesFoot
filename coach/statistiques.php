<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('coach');
$pdo = getPDO();

$stats = $pdo->query("
    SELECT j.nom, j.prenom, j.poste, j.numero_maillot, j.photo,
        COALESCE(SUM(s.buts),0) AS buts,
        COALESCE(SUM(s.passes_decisives),0) AS passes,
        COALESCE(SUM(s.cartons_jaunes),0) AS cj,
        COALESCE(SUM(s.cartons_rouges),0) AS cr,
        COALESCE(SUM(s.minutes_jouees),0) AS minutes,
        COUNT(DISTINCT s.match_id) AS matchs,
        ROUND(AVG(s.note_match),1) AS note_moy
    FROM joueurs j
    LEFT JOIN statistiques s ON s.joueur_id=j.id
    WHERE j.statut='actif'
    GROUP BY j.id
    ORDER BY buts DESC, passes DESC
")->fetchAll();

$pageTitle = 'Statistiques';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Statistiques de l'effectif</h4>
</div>

<div class="card table-card">
    <div class="card-body p-0 table-responsive">
        <table class="table datatable mb-0">
            <thead><tr>
                <th>Joueur</th><th>Poste</th>
                <th class="text-center">Matchs</th>
                <th class="text-center">Buts</th>
                <th class="text-center">Passes D.</th>
                <th class="text-center text-warning">CJ</th>
                <th class="text-center text-danger">CR</th>
                <th class="text-center">Minutes</th>
                <th class="text-center">Note moy.</th>
            </tr></thead>
            <tbody>
            <?php foreach ($stats as $j): ?>
            <tr>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <img src="<?= avatarUrl($j['photo'], $j['prenom'].' '.$j['nom']) ?>" class="avatar-sm">
                        <div>
                            <div class="fw-semibold small"><?= e($j['prenom']) ?> <?= e($j['nom']) ?></div>
                            <?php if ($j['numero_maillot']): ?><small class="text-muted">#<?= $j['numero_maillot'] ?></small><?php endif; ?>
                        </div>
                    </div>
                </td>
                <td><?= e($j['poste']) ?></td>
                <td class="text-center"><?= $j['matchs'] ?></td>
                <td class="text-center fw-bold text-success"><?= $j['buts'] ?></td>
                <td class="text-center"><?= $j['passes'] ?></td>
                <td class="text-center"><span class="badge bg-warning text-dark"><?= $j['cj'] ?></span></td>
                <td class="text-center"><span class="badge bg-danger"><?= $j['cr'] ?></span></td>
                <td class="text-center"><?= $j['minutes'] ?>'</td>
                <td class="text-center"><?= $j['note_moy'] ?? '—' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$stats): ?>
            <tr><td colspan="9" class="text-center py-5 text-muted"><i class="bi bi-bar-chart fs-2 d-block mb-2"></i>Aucune statistique disponible</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
