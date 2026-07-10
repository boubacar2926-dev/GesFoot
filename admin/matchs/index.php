<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();

$statut  = $_GET['statut'] ?? '';
$comp    = (int)($_GET['competition'] ?? 0);
$search  = trim($_GET['q'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where  = ['1=1'];
$params = [];
if ($statut) { $where[] = 'm.statut = ?'; $params[] = $statut; }
if ($comp)   { $where[] = 'm.competition_id = ?'; $params[] = $comp; }
if ($search) { $where[] = 'm.adversaire LIKE ?'; $params[] = "%$search%"; }

$whereStr = implode(' AND ', $where);

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM matchs m WHERE $whereStr");
$totalStmt->execute($params);
$totalMatchs = (int)$totalStmt->fetchColumn();
$totalPages  = max(1, (int)ceil($totalMatchs / $perPage));
$page        = min($page, $totalPages);
$offset      = ($page - 1) * $perPage;

$matchsStmt = $pdo->prepare("
    SELECT m.*, c.nom AS competition
    FROM matchs m
    LEFT JOIN competitions c ON c.id = m.competition_id
    WHERE $whereStr
    ORDER BY m.date_match DESC
    LIMIT $perPage OFFSET $offset
");
$matchsStmt->execute($params);
$matchs = $matchsStmt->fetchAll();

$competitions = $pdo->query("SELECT * FROM competitions ORDER BY nom")->fetchAll();

$pageTitle = 'Gestion des matchs';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Matchs</h4>
        <p class="text-muted mb-0"><?= $totalMatchs ?> match(s)</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/matchs/add.php" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i> Ajouter
    </a>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-md-4">
                <input type="text" name="q" class="form-control" placeholder="Rechercher par adversaire..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-3">
                <select name="statut" class="form-select">
                    <option value="">Tous les statuts</option>
                    <?php foreach (['Programmé','En cours','Terminé','Annulé','Reporté'] as $s): ?>
                        <option value="<?= $s ?>" <?= $statut===$s?'selected':'' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="competition" class="form-select">
                    <option value="">Toutes compétitions</option>
                    <?php foreach ($competitions as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $comp===$c['id']?'selected':'' ?>><?= e($c['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button class="btn btn-success flex-grow-1"><i class="bi bi-search"></i></button>
                <a href="?" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-body p-0 table-responsive">
        <table class="table datatable">
            <thead><tr>
                <th>Date</th><th>Adversaire</th><th>Lieu</th><th>Compétition</th>
                <th>Score</th><th>Stade</th><th>Statut</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if ($matchs): ?>
                <?php foreach ($matchs as $m): ?>
                <?php
                $resultat = '';
                if ($m['statut'] === 'Terminé') {
                    if ($m['score_equipe'] > $m['score_adverse'])      $resultat = 'text-success';
                    elseif ($m['score_equipe'] < $m['score_adverse'])  $resultat = 'text-danger';
                    else                                                $resultat = 'text-warning';
                }
                ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= formatDate($m['date_match']) ?></div>
                        <?php if ($m['heure_match']): ?><small class="text-muted"><?= formatTime($m['heure_match']) ?></small><?php endif; ?>
                    </td>
                    <td class="fw-semibold">vs <?= e($m['adversaire']) ?></td>
                    <td><span class="badge bg-light text-dark"><?= e($m['domicile_exterieur']) ?></span></td>
                    <td class="text-muted small"><?= e($m['competition'] ?? '—') ?></td>
                    <td>
                        <?php if ($m['statut'] === 'Terminé'): ?>
                            <span class="fw-bold <?= $resultat ?>"><?= $m['score_equipe'] ?> — <?= $m['score_adverse'] ?></span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= e($m['stade'] ?: '—') ?></td>
                    <td><?= statusBadge($m['statut']) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= BASE_URL ?>/admin/matchs/edit.php?id=<?= $m['id'] ?>"
                               class="btn btn-icon btn-sm btn-outline-primary" title="Modifier" data-bs-toggle="tooltip">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/convocations/add.php?match_id=<?= $m['id'] ?>"
                               class="btn btn-icon btn-sm btn-outline-info" title="Convoquer" data-bs-toggle="tooltip">
                                <i class="bi bi-clipboard2-plus"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/matchs/delete.php" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-icon btn-sm btn-outline-danger"
                                        data-confirm="Supprimer ce match et toutes ses données ?"
                                        title="Supprimer" data-bs-toggle="tooltip">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>Aucun match trouvé
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1):
    $qp = array_filter(['q'=>$search,'statut'=>$statut,'competition'=>$comp?:null]);
?>
<nav class="d-flex align-items-center justify-content-between mt-3">
    <small class="text-muted">Page <?= $page ?> / <?= $totalPages ?> — <?= $totalMatchs ?> match(s)</small>
    <ul class="pagination pagination-sm mb-0">
        <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($qp,['page'=>$page-1])) ?>">‹</a></li>
        <?php endif; ?>
        <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
            <li class="page-item <?= $p===$page?'active':'' ?>">
                <a class="page-link" href="?<?= http_build_query(array_merge($qp,['page'=>$p])) ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($qp,['page'=>$page+1])) ?>">›</a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
