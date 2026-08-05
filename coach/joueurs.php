<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('coach');
$pdo = getPDO();

$joueurs = $pdo->query("
    SELECT j.*,
        COALESCE(SUM(s.buts),0) AS buts,
        COALESCE(SUM(s.passes_decisives),0) AS passes,
        COUNT(DISTINCT s.match_id) AS matchs_joues
    FROM joueurs j
    LEFT JOIN statistiques s ON s.joueur_id=j.id
    GROUP BY j.id ORDER BY j.poste, j.nom
")->fetchAll();

$pageTitle = 'Effectif';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Effectif</h4>
    <span class="badge bg-success fs-6"><?= count($joueurs) ?> joueurs</span>
</div>

<?php
$postes = ['Gardien','Défenseur','Milieu','Attaquant'];
$byPoste = [];
foreach ($joueurs as $j) $byPoste[$j['poste']][] = $j;
foreach ($postes as $poste):
    if (empty($byPoste[$poste])) continue;
?>
<div class="section-title mt-4"><?= $poste ?>s</div>
<div class="row g-3 mb-2">
    <?php foreach ($byPoste[$poste] as $j): ?>
    <div class="col-sm-6 col-lg-4 col-xl-3">
        <div class="player-card">
            <div class="player-header">
                <img src="<?= avatarUrl($j['photo'], $j['prenom'].' '.$j['nom']) ?>" alt="">
                <div class="fw-bold mt-2"><?= e($j['prenom']) ?> <?= e($j['nom']) ?></div>
                <?php if ($j['numero_maillot']): ?>
                    <span class="player-num">#<?= $j['numero_maillot'] ?></span>
                <?php endif; ?>
            </div>
            <div class="p-3">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small"><?= age($j['date_naissance']) ?></span>
                    <?= statusBadge($j['statut']) ?>
                </div>
                <div class="row g-1 text-center">
                    <div class="col-4">
                        <div class="fw-bold"><?= $j['matchs_joues'] ?></div>
                        <div class="text-muted" style="font-size:.7rem">Matchs</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold text-success"><?= $j['buts'] ?></div>
                        <div class="text-muted" style="font-size:.7rem">Buts</div>
                    </div>
                    <div class="col-4">
                        <div class="fw-bold"><?= $j['passes'] ?></div>
                        <div class="text-muted" style="font-size:.7rem">Passes D.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php if (!$joueurs): ?>
<div class="text-center py-5 text-muted"><i class="bi bi-people fs-1 d-block mb-3"></i>Aucun joueur enregistré</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
