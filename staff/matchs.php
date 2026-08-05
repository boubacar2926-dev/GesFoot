<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('staff');
$pdo = getPDO();

$matchs = $pdo->query("
    SELECT m.*, c.nom AS competition,
        (SELECT COUNT(DISTINCT s.joueur_id) FROM statistiques s WHERE s.match_id=m.id) AS nb_stats
    FROM matchs m LEFT JOIN competitions c ON c.id=m.competition_id
    ORDER BY m.date_match DESC
")->fetchAll();

$pageTitle = 'Matchs';
require_once __DIR__ . '/includes/header.php';
?>
<div class="mb-4"><h4 class="fw-bold mb-0">Liste des matchs</h4></div>
<div class="card table-card">
    <div class="card-body p-0 table-responsive">
        <table class="table datatable mb-0">
            <thead><tr>
                <th>Date</th><th>Adversaire</th><th>Score</th><th>Statut</th><th>Stats saisies</th><th>Action</th>
            </tr></thead>
            <tbody>
            <?php foreach ($matchs as $m): ?>
            <tr>
                <td><?= formatDate($m['date_match']) ?></td>
                <td class="fw-semibold">vs <?= e($m['adversaire']) ?></td>
                <td class="fw-bold"><?= $m['statut']==='Terminé'?$m['score_equipe'].'—'.$m['score_adverse']:'—' ?></td>
                <td><?= statusBadge($m['statut']) ?></td>
                <td>
                    <?php if ($m['nb_stats'] > 0): ?>
                        <span class="badge bg-success"><?= $m['nb_stats'] ?> joueurs</span>
                    <?php elseif ($m['statut']==='Terminé'): ?>
                        <span class="badge bg-warning text-dark">À saisir</span>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($m['statut']==='Terminé'): ?>
                    <a href="<?= BASE_URL ?>/staff/statistiques/index.php?match_id=<?= $m['id'] ?>"
                       class="btn btn-sm btn-<?= $m['nb_stats']?'outline-success':'success' ?>">
                        <i class="bi bi-pencil me-1"></i><?= $m['nb_stats']?'Modifier':'Saisir stats' ?>
                    </a>
                    <?php else: ?>
                        <span class="text-muted small">Match non terminé</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$matchs): ?><tr><td colspan="6" class="text-center py-5 text-muted">Aucun match</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
