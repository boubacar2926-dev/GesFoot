<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('joueur');
$pdo  = getPDO();
$user = currentUser();

$joueurStmt = $pdo->prepare("SELECT id FROM joueurs WHERE utilisateur_id=? LIMIT 1");
$joueurStmt->execute([$user['id']]);
$joueur = $joueurStmt->fetch();

$convocations = [];
if ($joueur) {
    $stmt = $pdo->prepare("
        SELECT cv.*, m.date_match, m.heure_match, m.adversaire, m.stade, m.domicile_exterieur,
               c.nom AS competition, cj.statut AS conv_statut
        FROM convocation_joueur cj
        JOIN convocations cv ON cv.id=cj.convocation_id
        JOIN matchs m ON m.id=cv.match_id
        LEFT JOIN competitions c ON c.id=m.competition_id
        WHERE cj.joueur_id=? AND cv.statut != 'Brouillon'
        ORDER BY m.date_match DESC
    ");
    $stmt->execute([$joueur['id']]);
    $convocations = $stmt->fetchAll();
}

$pageTitle = 'Mes convocations';
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4"><h4 class="fw-bold mb-0">Mes convocations</h4></div>

<?php if ($convocations): ?>
<div class="row g-4">
    <?php foreach ($convocations as $cv):
        $isPast = strtotime($cv['date_match']) < time();
    ?>
    <div class="col-md-6 col-xl-4">
        <div class="card h-100 <?= $isPast ? 'opacity-75' : '' ?>">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="fw-bold">vs <?= e($cv['adversaire']) ?></span>
                <?= statusBadge($cv['conv_statut']) ?>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small mb-0">
                    <li class="mb-2">
                        <i class="bi bi-calendar3 text-muted me-2"></i>
                        <strong><?= formatDate($cv['date_match']) ?></strong>
                        <?= $cv['heure_match'] ? ' à '.formatTime($cv['heure_match']) : '' ?>
                    </li>
                    <?php if ($cv['domicile_exterieur']): ?>
                    <li class="mb-2"><i class="bi bi-house text-muted me-2"></i><?= e($cv['domicile_exterieur']) ?></li>
                    <?php endif; ?>
                    <?php if ($cv['stade']): ?>
                    <li class="mb-2"><i class="bi bi-geo-alt text-muted me-2"></i><?= e($cv['stade']) ?></li>
                    <?php endif; ?>
                    <?php if ($cv['competition']): ?>
                    <li class="mb-2"><i class="bi bi-trophy text-muted me-2"></i><?= e($cv['competition']) ?></li>
                    <?php endif; ?>
                    <?php if ($cv['lieu_rdv'] || $cv['heure_rdv']): ?>
                    <li class="mt-3 p-2 bg-info bg-opacity-10 rounded-2">
                        <div class="fw-semibold text-info mb-1"><i class="bi bi-pin-map me-1"></i>Point de rassemblement</div>
                        <?= $cv['lieu_rdv'] ? '<div>'.e($cv['lieu_rdv']).'</div>' : '' ?>
                        <?= $cv['heure_rdv'] ? '<div><strong>'.formatTime($cv['heure_rdv']).'</strong></div>' : '' ?>
                    </li>
                    <?php endif; ?>
                    <?php if ($cv['notes']): ?>
                    <li class="mt-2 text-muted fst-italic"><?= nl2br(e($cv['notes'])) ?></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="card-footer bg-transparent">
                <small class="text-muted">
                    Convocation <?= statusBadge($cv['statut']) ?>
                </small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-clipboard2-x fs-1 d-block mb-3"></i>
    <?= $joueur ? 'Vous n\'avez pas encore été convoqué.' : 'Votre compte n\'est pas lié à une fiche joueur.' ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
