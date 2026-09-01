<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('admin');
$pdo = getPDO();

$allowedTypes = ['joueurs', 'matchs', 'statistiques', 'performances'];
$type = in_array($_GET['type'] ?? '', $allowedTypes, true) ? $_GET['type'] : 'joueurs';

// Données selon le type de rapport
$data = [];
switch ($type) {
    case 'joueurs':
        $data = $pdo->query("
            SELECT j.*,
                COALESCE(SUM(s.buts),0) AS total_buts,
                COALESCE(SUM(s.passes_decisives),0) AS total_passes,
                COALESCE(SUM(s.minutes_jouees),0) AS total_minutes,
                COUNT(DISTINCT s.match_id) AS matchs_joues
            FROM joueurs j
            LEFT JOIN statistiques s ON s.joueur_id=j.id
            GROUP BY j.id ORDER BY j.poste, j.nom
        ")->fetchAll();
        break;

    case 'matchs':
        $data = $pdo->query("
            SELECT m.*, c.nom AS competition,
                COUNT(DISTINCT s.joueur_id) AS joueurs_stats
            FROM matchs m
            LEFT JOIN competitions c ON c.id=m.competition_id
            LEFT JOIN statistiques s ON s.match_id=m.id
            GROUP BY m.id ORDER BY m.date_match DESC
        ")->fetchAll();
        break;

    case 'statistiques':
        $data = $pdo->query("
            SELECT j.nom, j.prenom, j.poste, j.numero_maillot,
                COALESCE(SUM(s.buts),0)              AS buts,
                COALESCE(SUM(s.passes_decisives),0)  AS passes,
                COALESCE(SUM(s.cartons_jaunes),0)    AS cj,
                COALESCE(SUM(s.cartons_rouges),0)    AS cr,
                COALESCE(SUM(s.minutes_jouees),0)    AS minutes,
                COUNT(DISTINCT s.match_id)            AS matchs,
                ROUND(AVG(s.note_match),1)           AS note_moy
            FROM joueurs j
            LEFT JOIN statistiques s ON s.joueur_id=j.id
            GROUP BY j.id
            ORDER BY buts DESC, passes DESC
        ")->fetchAll();
        break;

    case 'performances':
        $data = $pdo->query("
            SELECT m.date_match, m.adversaire, m.score_equipe, m.score_adverse,
                m.domicile_exterieur, c.nom AS competition,
                COUNT(cv.id) AS nb_convoqués,
                SUM(s.buts) AS buts_match
            FROM matchs m
            LEFT JOIN competitions c ON c.id=m.competition_id
            LEFT JOIN convocations cv ON cv.match_id=m.id
            LEFT JOIN statistiques s ON s.match_id=m.id
            WHERE m.statut='Terminé'
            GROUP BY m.id ORDER BY m.date_match DESC
        ")->fetchAll();
        break;
}

$pageTitle = 'Rapports';
require_once ROOT_PATH . '/admin/includes/header.php';

$types = [
    'joueurs'       => ['Joueurs',         'bi-person-badge'],
    'matchs'        => ['Matchs',          'bi-calendar-event'],
    'statistiques'  => ['Statistiques',    'bi-bar-chart-line'],
    'performances'  => ['Performances',    'bi-graph-up'],
];
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Rapports</h4>
    <a href="<?= BASE_URL ?>/admin/rapports/export_pdf.php?type=<?= urlencode($type) ?>"
       class="btn btn-danger" target="_blank">
        <i class="bi bi-file-earmark-pdf me-1"></i> Exporter en PDF
    </a>
</div>

<!-- Onglets -->
<ul class="nav nav-tabs mb-4">
    <?php foreach ($types as $key => [$label, $icon]): ?>
    <li class="nav-item">
        <a class="nav-link <?= $type===$key?'active':'' ?>"
           href="?type=<?= $key ?>">
            <i class="bi <?= $icon ?> me-1"></i> <?= $label ?>
        </a>
    </li>
    <?php endforeach; ?>
</ul>

<div class="card table-card" id="printArea">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi <?= $types[$type][1] ?> me-2"></i>Rapport — <?= $types[$type][0] ?></span>
        <small class="text-muted">Généré le <?= date('d/m/Y à H:i') ?></small>
    </div>
    <div class="card-body p-0 table-responsive">

    <?php if ($type === 'joueurs'): ?>
        <table class="table mb-0 datatable">
            <thead><tr>
                <th>Joueur</th><th>Poste</th><th>N°</th><th>Statut</th>
                <th class="text-center">Matchs</th><th class="text-center">Buts</th>
                <th class="text-center">Passes D.</th><th class="text-center">Minutes</th>
            </tr></thead>
            <tbody>
            <?php foreach ($data as $j): ?>
            <tr>
                <td><span class="fw-semibold"><?= e($j['prenom']) ?> <?= e($j['nom']) ?></span></td>
                <td><?= e($j['poste']) ?></td>
                <td><?= $j['numero_maillot'] ? '#'.$j['numero_maillot'] : '—' ?></td>
                <td><?= statusBadge($j['statut']) ?></td>
                <td class="text-center"><?= $j['matchs_joues'] ?></td>
                <td class="text-center fw-bold text-success"><?= $j['total_buts'] ?></td>
                <td class="text-center"><?= $j['total_passes'] ?></td>
                <td class="text-center"><?= $j['total_minutes'] ?>'</td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$data): ?><tr><td colspan="8" class="text-center py-4 text-muted">Aucun joueur</td></tr><?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($type === 'matchs'): ?>
        <table class="table mb-0 datatable">
            <thead><tr>
                <th>Date</th><th>Adversaire</th><th>Lieu</th><th>Compétition</th>
                <th class="text-center">Score</th><th>Résultat</th><th>Statut</th>
            </tr></thead>
            <tbody>
            <?php foreach ($data as $m):
                $res = '—';
                $cls = '';
                if ($m['statut'] === 'Terminé') {
                    if ($m['score_equipe'] > $m['score_adverse'])     { $res = 'Victoire'; $cls = 'text-success'; }
                    elseif ($m['score_equipe'] < $m['score_adverse']) { $res = 'Défaite';  $cls = 'text-danger'; }
                    else                                               { $res = 'Nul';      $cls = 'text-warning'; }
                }
            ?>
            <tr>
                <td><?= formatDate($m['date_match']) ?></td>
                <td class="fw-semibold">vs <?= e($m['adversaire']) ?></td>
                <td><?= e($m['domicile_exterieur']) ?></td>
                <td class="text-muted small"><?= e($m['competition'] ?? '—') ?></td>
                <td class="text-center fw-bold"><?= $m['statut']==='Terminé' ? $m['score_equipe'].'—'.$m['score_adverse'] : '—' ?></td>
                <td class="<?= $cls ?> fw-semibold"><?= $res ?></td>
                <td><?= statusBadge($m['statut']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$data): ?><tr><td colspan="7" class="text-center py-4 text-muted">Aucun match</td></tr><?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($type === 'statistiques'): ?>
        <table class="table mb-0 datatable">
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
            <?php foreach ($data as $j): ?>
            <tr>
                <td class="fw-semibold"><?= e($j['prenom']) ?> <?= e($j['nom']) ?><?= $j['numero_maillot'] ? ' <small class="text-muted">#'.$j['numero_maillot'].'</small>' : '' ?></td>
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
            <?php if (!$data): ?><tr><td colspan="9" class="text-center py-4 text-muted">Aucune statistique</td></tr><?php endif; ?>
            </tbody>
        </table>

    <?php elseif ($type === 'performances'): ?>
        <table class="table mb-0 datatable">
            <thead><tr>
                <th>Date</th><th>Adversaire</th><th>Compétition</th>
                <th class="text-center">Score</th><th>Résultat</th>
                <th class="text-center">Buts marqués</th><th class="text-center">Convoqués</th>
            </tr></thead>
            <tbody>
            <?php foreach ($data as $m):
                $res = 'Nul'; $cls = 'text-warning';
                if ($m['score_equipe'] > $m['score_adverse'])     { $res = 'Victoire'; $cls = 'text-success'; }
                elseif ($m['score_equipe'] < $m['score_adverse']) { $res = 'Défaite';  $cls = 'text-danger'; }
            ?>
            <tr>
                <td><?= formatDate($m['date_match']) ?></td>
                <td class="fw-semibold">vs <?= e($m['adversaire']) ?></td>
                <td class="text-muted small"><?= e($m['competition'] ?? '—') ?></td>
                <td class="text-center fw-bold"><?= $m['score_equipe'] ?>—<?= $m['score_adverse'] ?></td>
                <td class="<?= $cls ?> fw-semibold"><?= $res ?></td>
                <td class="text-center"><?= $m['buts_match'] ?? 0 ?></td>
                <td class="text-center"><?= $m['nb_convoqués'] ?? '—' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$data): ?><tr><td colspan="7" class="text-center py-4 text-muted">Aucune donnée</td></tr><?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    </div>
</div>

<style>
@media print {
    .sidebar, .topbar, .btn, .nav-tabs, form { display: none !important; }
    .main-wrapper { margin-left: 0 !important; }
    .page-content { padding: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
}
</style>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
