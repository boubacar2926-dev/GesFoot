<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('coach');
$pdo = getPDO();

$matchs = $pdo->query("
    SELECT m.*, c.nom AS competition,
        (SELECT COUNT(*) FROM convocations cv WHERE cv.match_id=m.id) AS a_convocation
    FROM matchs m
    LEFT JOIN competitions c ON c.id=m.competition_id
    ORDER BY m.date_match DESC
")->fetchAll();

$pageTitle = 'Matchs';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Calendrier des matchs</h4>
</div>

<div class="card table-card">
    <div class="card-body p-0 table-responsive">
        <table class="table datatable mb-0">
            <thead><tr>
                <th>Date</th><th>Adversaire</th><th>Lieu</th><th>Compétition</th>
                <th class="text-center">Score</th><th>Statut</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($matchs as $m): ?>
            <tr>
                <td>
                    <div class="fw-semibold"><?= formatDate($m['date_match']) ?></div>
                    <?php if ($m['heure_match']): ?><small class="text-muted"><?= formatTime($m['heure_match']) ?></small><?php endif; ?>
                </td>
                <td class="fw-semibold">vs <?= e($m['adversaire']) ?></td>
                <td><span class="badge bg-light text-dark"><?= e($m['domicile_exterieur']) ?></span></td>
                <td class="text-muted small"><?= e($m['competition'] ?? '—') ?></td>
                <td class="text-center fw-bold">
                    <?= $m['statut']==='Terminé' ? $m['score_equipe'].'—'.$m['score_adverse'] : '—' ?>
                </td>
                <td><?= statusBadge($m['statut']) ?></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="<?= BASE_URL ?>/coach/convocations/add.php?match_id=<?= $m['id'] ?>"
                           class="btn btn-icon btn-sm btn-outline-<?= $m['a_convocation']?'success':'warning' ?>"
                           title="<?= $m['a_convocation']?'Voir convocation':'Créer convocation' ?>" data-bs-toggle="tooltip">
                            <i class="bi bi-clipboard2-<?= $m['a_convocation']?'check':'plus' ?>"></i>
                        </a>
                        <a href="<?= BASE_URL ?>/coach/compositions/index.php?match_id=<?= $m['id'] ?>"
                           class="btn btn-icon btn-sm btn-outline-primary" title="Composition" data-bs-toggle="tooltip">
                            <i class="bi bi-layout-text-sidebar"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$matchs): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Aucun match</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
