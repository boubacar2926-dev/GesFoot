<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('admin');
$pdo = getPDO();

$convocations = $pdo->query("
    SELECT cv.*, m.date_match, m.adversaire, m.domicile_exterieur,
           COUNT(cj.id) AS nb_joueurs
    FROM convocations cv
    JOIN matchs m ON m.id = cv.match_id
    LEFT JOIN convocation_joueur cj ON cj.convocation_id = cv.id
    GROUP BY cv.id
    ORDER BY m.date_match DESC
")->fetchAll();

$pageTitle = 'Convocations';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Convocations</h4>
    <a href="<?= BASE_URL ?>/admin/convocations/add.php" class="btn btn-success">
        <i class="bi bi-clipboard2-plus me-1"></i> Créer
    </a>
</div>

<div class="card table-card">
    <div class="card-body p-0 table-responsive">
        <table class="table datatable">
            <thead><tr>
                <th>Match</th><th>Date match</th><th>Lieu RDV</th><th>Heure RDV</th>
                <th>Joueurs</th><th>Statut</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if ($convocations): ?>
                <?php foreach ($convocations as $cv): ?>
                <tr>
                    <td>
                        <div class="fw-semibold">vs <?= e($cv['adversaire']) ?></div>
                        <small class="text-muted"><?= e($cv['domicile_exterieur']) ?></small>
                    </td>
                    <td><?= formatDate($cv['date_match']) ?></td>
                    <td class="text-muted small"><?= e($cv['lieu_rdv'] ?: '—') ?></td>
                    <td class="text-muted small"><?= $cv['heure_rdv'] ? formatTime($cv['heure_rdv']) : '—' ?></td>
                    <td><span class="badge bg-primary rounded-pill"><?= $cv['nb_joueurs'] ?> joueur(s)</span></td>
                    <td><?= statusBadge($cv['statut']) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= BASE_URL ?>/admin/convocations/view.php?id=<?= $cv['id'] ?>"
                               class="btn btn-icon btn-sm btn-outline-info" title="Voir" data-bs-toggle="tooltip">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/convocations/add.php?id=<?= $cv['id'] ?>"
                               class="btn btn-icon btn-sm btn-outline-primary" title="Modifier" data-bs-toggle="tooltip">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/convocations/delete.php" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $cv['id'] ?>">
                                <button type="submit" class="btn btn-icon btn-sm btn-outline-danger"
                                        data-confirm="Supprimer cette convocation ?"
                                        title="Supprimer" data-bs-toggle="tooltip">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center py-5 text-muted">
                    <i class="bi bi-clipboard2-x fs-2 d-block mb-2"></i>Aucune convocation
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
