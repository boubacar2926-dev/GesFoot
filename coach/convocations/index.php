<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('coach');
$pdo = getPDO();

$convocations = $pdo->query("
    SELECT cv.*, m.date_match, m.adversaire, m.domicile_exterieur,
           COUNT(cj.id) AS nb_joueurs
    FROM convocations cv
    JOIN matchs m ON m.id=cv.match_id
    LEFT JOIN convocation_joueur cj ON cj.convocation_id=cv.id
    GROUP BY cv.id ORDER BY m.date_match DESC
")->fetchAll();

$pageTitle = 'Convocations';
require_once ROOT_PATH . '/coach/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Convocations</h4>
    <a href="<?= BASE_URL ?>/coach/convocations/add.php" class="btn btn-success">
        <i class="bi bi-clipboard2-plus me-1"></i> Créer
    </a>
</div>

<div class="card table-card">
    <div class="card-body p-0 table-responsive">
        <table class="table datatable mb-0">
            <thead><tr>
                <th>Match</th><th>Date match</th><th>RDV</th><th>Joueurs</th><th>Statut</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($convocations as $cv): ?>
            <tr>
                <td class="fw-semibold">vs <?= e($cv['adversaire']) ?><br><small class="text-muted"><?= e($cv['domicile_exterieur']) ?></small></td>
                <td><?= formatDate($cv['date_match']) ?></td>
                <td class="small text-muted">
                    <?= $cv['lieu_rdv'] ? e($cv['lieu_rdv']) : '—' ?>
                    <?= $cv['heure_rdv'] ? '<br>'.formatTime($cv['heure_rdv']) : '' ?>
                </td>
                <td><span class="badge bg-primary"><?= $cv['nb_joueurs'] ?></span></td>
                <td><?= statusBadge($cv['statut']) ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="<?= BASE_URL ?>/coach/convocations/view.php?id=<?= $cv['id'] ?>"
                           class="btn btn-icon btn-sm btn-outline-info" title="Voir" data-bs-toggle="tooltip">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/coach/convocations/add.php?id=<?= $cv['id'] ?>"
                           class="btn btn-icon btn-sm btn-outline-primary" title="Modifier" data-bs-toggle="tooltip">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$convocations): ?>
            <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-clipboard2-x fs-2 d-block mb-2"></i>Aucune convocation</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once ROOT_PATH . '/coach/includes/footer.php'; ?>
