<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('coach');
$pdo = getPDO();
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT cv.*, m.date_match, m.heure_match, m.adversaire, m.stade, m.domicile_exterieur, c.nom AS competition
    FROM convocations cv JOIN matchs m ON m.id=cv.match_id LEFT JOIN competitions c ON c.id=m.competition_id
    WHERE cv.id=?
");
$stmt->execute([$id]);
$conv = $stmt->fetch();
if (!$conv) { setFlash('error','Convocation introuvable.'); redirect('/coach/convocations/index.php'); }

$joueurs = $pdo->prepare("
    SELECT j.*, cj.statut AS conv_statut FROM convocation_joueur cj
    JOIN joueurs j ON j.id=cj.joueur_id WHERE cj.convocation_id=? ORDER BY j.poste, j.nom
");
$joueurs->execute([$id]);
$joueurs = $joueurs->fetchAll();

$pageTitle = 'Convocation — vs '.$conv['adversaire'];
require_once ROOT_PATH . '/coach/includes/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= BASE_URL ?>/coach/convocations/index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">Convocation</h4>
    <div class="ms-auto d-flex gap-2">
        <a href="<?= BASE_URL ?>/coach/convocations/add.php?id=<?= $conv['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Modifier</a>
        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer me-1"></i>Imprimer</button>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-calendar-event me-2"></i>Détails</div>
            <div class="card-body">
                <h5 class="fw-bold mb-3">vs <?= e($conv['adversaire']) ?> <?= statusBadge($conv['statut']) ?></h5>
                <ul class="list-unstyled small">
                    <li class="mb-2"><i class="bi bi-calendar3 text-muted me-2"></i><?= formatDate($conv['date_match']) ?> <?= $conv['heure_match'] ? 'à '.formatTime($conv['heure_match']) : '' ?></li>
                    <?php if ($conv['stade']): ?><li class="mb-2"><i class="bi bi-geo-alt text-muted me-2"></i><?= e($conv['stade']) ?></li><?php endif; ?>
                    <li class="mb-2"><i class="bi bi-house text-muted me-2"></i><?= e($conv['domicile_exterieur']) ?></li>
                    <?php if ($conv['competition']): ?><li class="mb-2"><i class="bi bi-trophy text-muted me-2"></i><?= e($conv['competition']) ?></li><?php endif; ?>
                </ul>
                <?php if ($conv['lieu_rdv'] || $conv['heure_rdv']): ?>
                <div class="divider"></div>
                <h6 class="fw-bold">RDV</h6>
                <ul class="list-unstyled small">
                    <?php if ($conv['lieu_rdv']): ?><li class="mb-1"><i class="bi bi-pin-map text-muted me-2"></i><?= e($conv['lieu_rdv']) ?></li><?php endif; ?>
                    <?php if ($conv['heure_rdv']): ?><li class="mb-1"><i class="bi bi-clock text-muted me-2"></i><?= formatTime($conv['heure_rdv']) ?></li><?php endif; ?>
                </ul>
                <?php endif; ?>
                <?php if ($conv['notes']): ?>
                <div class="divider"></div>
                <p class="small text-muted mb-0"><?= nl2br(e($conv['notes'])) ?></p>
                <?php endif; ?>
                <div class="mt-3 pt-2 border-top">
                    <span class="badge bg-primary fs-6"><?= count($joueurs) ?> convoqué(s)</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><i class="bi bi-people me-2"></i>Joueurs convoqués</div>
            <div class="card-body p-0 table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>#</th><th>Joueur</th><th>Poste</th><th>N°</th></tr></thead>
                    <tbody>
                    <?php foreach ($joueurs as $i => $j): ?>
                    <tr>
                        <td class="text-muted"><?= $i+1 ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?= avatarUrl($j['photo'], $j['prenom'].' '.$j['nom']) ?>" class="avatar-sm">
                                <span class="fw-semibold"><?= e($j['prenom']) ?> <?= e($j['nom']) ?></span>
                            </div>
                        </td>
                        <td><?= e($j['poste']) ?></td>
                        <td><?= $j['numero_maillot'] ? '#'.$j['numero_maillot'] : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$joueurs): ?><tr><td colspan="4" class="text-center py-4 text-muted">Aucun joueur convoqué</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/coach/includes/footer.php'; ?>
