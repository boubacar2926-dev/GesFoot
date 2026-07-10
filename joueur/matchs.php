<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('joueur');
$pdo = getPDO();

$matchs = $pdo->query("
    SELECT m.*, c.nom AS competition FROM matchs m
    LEFT JOIN competitions c ON c.id=m.competition_id
    ORDER BY m.date_match DESC
")->fetchAll();

$pageTitle = 'Matchs';
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4"><h4 class="fw-bold mb-0">Calendrier et résultats</h4></div>

<div class="card table-card">
    <div class="card-body p-0 table-responsive">
        <table class="table datatable mb-0">
            <thead><tr>
                <th>Date</th><th>Adversaire</th><th>Lieu</th><th>Compétition</th>
                <th class="text-center">Score</th><th>Résultat</th><th>Statut</th>
            </tr></thead>
            <tbody>
            <?php foreach ($matchs as $m):
                $res = '—'; $rCls = '';
                if ($m['statut']==='Terminé') {
                    if ($m['score_equipe'] > $m['score_adverse'])      { $res='Victoire'; $rCls='text-success fw-bold'; }
                    elseif ($m['score_equipe'] < $m['score_adverse'])  { $res='Défaite';  $rCls='text-danger fw-bold'; }
                    else                                               { $res='Nul';      $rCls='text-warning fw-bold'; }
                }
            ?>
            <tr>
                <td>
                    <div><?= formatDate($m['date_match']) ?></div>
                    <?php if ($m['heure_match']): ?><small class="text-muted"><?= formatTime($m['heure_match']) ?></small><?php endif; ?>
                </td>
                <td class="fw-semibold">vs <?= e($m['adversaire']) ?></td>
                <td><span class="badge bg-light text-dark"><?= e($m['domicile_exterieur']) ?></span></td>
                <td class="text-muted small"><?= e($m['competition'] ?? '—') ?></td>
                <td class="text-center fw-bold"><?= $m['statut']==='Terminé' ? $m['score_equipe'].'—'.$m['score_adverse'] : '—' ?></td>
                <td class="<?= $rCls ?>"><?= $res ?></td>
                <td><?= statusBadge($m['statut']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$matchs): ?><tr><td colspan="7" class="text-center py-5 text-muted">Aucun match enregistré</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
