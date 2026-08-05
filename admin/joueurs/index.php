<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();

$search  = trim($_GET['q']      ?? '');
$poste   = $_GET['poste']       ?? '';
$statut  = $_GET['statut']      ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where  = ['1=1'];
$params = [];
if ($search) {
    $where[]  = '(j.nom LIKE ? OR j.prenom LIKE ? OR j.nationalite LIKE ? OR CONCAT(j.prenom, " ", j.nom) LIKE ?)';
    $params   = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($poste)  { $where[] = 'j.poste = ?';  $params[] = $poste; }
if ($statut) { $where[] = 'j.statut = ?'; $params[] = $statut; }

$whereStr = implode(' AND ', $where);

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM joueurs j WHERE $whereStr");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$stmt = $pdo->prepare("
    SELECT j.*, u.email
    FROM joueurs j
    LEFT JOIN utilisateurs u ON u.id = j.utilisateur_id
    WHERE $whereStr
    ORDER BY j.poste, j.nom
    LIMIT $perPage OFFSET $offset
");
$stmt->execute($params);
$joueurs = $stmt->fetchAll();

// Tous les joueurs pour les suggestions
$suggestions = $pdo->query("SELECT prenom, nom, poste FROM joueurs ORDER BY nom")->fetchAll();

$pageTitle = 'Gestion des joueurs';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Joueurs</h4>
        <p class="text-muted mb-0"><?= $total ?> joueur(s)</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/joueurs/add.php" class="btn btn-success">
        <i class="bi bi-person-plus-fill me-1"></i> Ajouter
    </a>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form class="row g-3 align-items-end" method="GET" id="filterForm">
            <div class="col-md-5" style="position:relative">
                <input type="text" name="q" id="searchInput" class="form-control"
                       placeholder="Recherche par nom, nationalité..."
                       value="<?= e($search) ?>" autocomplete="off">
                <div id="dropdownSuggestions"
                     style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;
                            background:#fff;border:1px solid #dee2e6;border-radius:8px;
                            box-shadow:0 4px 16px rgba(0,0,0,.1);z-index:1050;overflow:hidden;max-height:280px;overflow-y:auto">
                </div>
            </div>
            <div class="col-md-2">
                <select name="poste" class="form-select">
                    <option value="">Tous postes</option>
                    <?php foreach (['Gardien','Défenseur','Milieu','Attaquant'] as $p): ?>
                        <option value="<?= $p ?>" <?= $poste===$p?'selected':'' ?>><?= $p ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="statut" class="form-select">
                    <option value="">Tous statuts</option>
                    <?php foreach (['actif','blessé','suspendu','inactif'] as $s): ?>
                        <option value="<?= $s ?>" <?= $statut===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-success flex-grow-1"><i class="bi bi-search"></i> Filtrer</button>
                <a href="?" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card table-card">
    <div class="card-body p-0 table-responsive">
        <table class="table datatable">
            <thead><tr>
                <th>#</th><th>Joueur</th><th>Poste</th><th>N°</th>
                <th>Nationalité</th><th>Âge</th><th>Taille</th><th>Poids</th>
                <th>Statut</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if ($joueurs): ?>
                <?php foreach ($joueurs as $j): ?>
                <tr>
                    <td class="text-muted"><?= $j['id'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= avatarUrl($j['photo'], $j['prenom'].' '.$j['nom']) ?>" class="avatar" alt="">
                            <div>
                                <div class="fw-semibold"><?= e($j['prenom']) ?> <?= e($j['nom']) ?></div>
                                <?php if ($j['email']): ?><small class="text-muted"><?= e($j['email']) ?></small><?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><?= e($j['poste']) ?></td>
                    <td><?= $j['numero_maillot'] ? '<span class="badge bg-dark">'.$j['numero_maillot'].'</span>' : '—' ?></td>
                    <td><?= e($j['nationalite'] ?: '—') ?></td>
                    <td><?= age($j['date_naissance']) ?></td>
                    <td><?= $j['taille'] ? $j['taille'].' cm' : '—' ?></td>
                    <td><?= $j['poids']  ? $j['poids'].' kg'  : '—' ?></td>
                    <td><?= statusBadge($j['statut']) ?></td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="<?= BASE_URL ?>/admin/joueurs/view.php?id=<?= $j['id'] ?>"
                               class="btn btn-icon btn-sm btn-outline-info" title="Voir profil" data-bs-toggle="tooltip">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/joueurs/edit.php?id=<?= $j['id'] ?>"
                               class="btn btn-icon btn-sm btn-outline-primary" title="Modifier" data-bs-toggle="tooltip">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <div class="dropdown">
                                <button class="btn btn-icon btn-sm btn-outline-secondary dropdown-toggle" title="Changer statut" data-bs-toggle="dropdown">
                                    <i class="bi bi-toggle-on"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width:150px">
                                    <?php foreach (['actif'=>['success','Actif'],'blessé'=>['warning','Blessé'],'suspendu'=>['danger','Suspendu'],'inactif'=>['secondary','Inactif']] as $val=>[$cls,$lbl]): ?>
                                    <li>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/joueurs/status.php" class="d-inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                            <input type="hidden" name="statut" value="<?= $val ?>">
                                            <button type="submit" class="dropdown-item <?= $j['statut']===$val?'fw-bold':'' ?>">
                                                <span class="badge bg-<?= $cls ?> me-1"> </span><?= $lbl ?>
                                            </button>
                                        </form>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <form method="POST" action="<?= BASE_URL ?>/admin/joueurs/delete.php" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                <button type="submit" class="btn btn-icon btn-sm btn-outline-danger"
                                        data-confirm="Supprimer ce joueur ?" title="Supprimer" data-bs-toggle="tooltip">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="10" class="text-center py-5 text-muted">
                    <i class="bi bi-person-x fs-2 d-block mb-2"></i>Aucun joueur trouvé
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1):
    $qp = array_filter(['q'=>$search,'poste'=>$poste,'statut'=>$statut]);
?>
<nav class="d-flex align-items-center justify-content-between mt-3">
    <small class="text-muted">Page <?= $page ?> / <?= $totalPages ?> — <?= $total ?> joueur(s)</small>
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

<script>
(function () {
    const input    = document.getElementById('searchInput');
    const dropdown = document.getElementById('dropdownSuggestions');
    const form     = document.getElementById('filterForm');

    const data = <?= json_encode(array_map(fn($j) => [
        'label' => $j['prenom'] . ' ' . $j['nom'],
        'poste' => $j['poste'],
    ], $suggestions), JSON_HEX_TAG | JSON_HEX_AMP) ?>;

    const colors = { 'Gardien':'#6b7280','Défenseur':'#1d4ed8','Milieu':'#15803d','Attaquant':'#dc2626' };
    let active = -1;

    function render(q) {
        const term = q.trim().toLowerCase();
        if (!term) { close(); return; }

        const hits = data.filter(d => d.label.toLowerCase().includes(term)).slice(0, 8);
        if (!hits.length) { close(); return; }

        const re = new RegExp('(' + term.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')', 'gi');

        dropdown.innerHTML = '';
        hits.forEach((d, i) => {
            const div = document.createElement('div');
            div.className = 'ac-item d-flex align-items-center gap-2 px-3 py-2';
            div.style.cssText = 'cursor:pointer;border-bottom:1px solid #f1f5f9';

            const dot = document.createElement('span');
            dot.style.cssText = `width:8px;height:8px;border-radius:50%;background:${colors[d.poste]||'#ccc'};flex-shrink:0`;

            const posteEl = document.createElement('span');
            posteEl.style.cssText = `min-width:80px;font-size:.72rem;font-weight:600;color:${colors[d.poste]||'#374151'}`;
            posteEl.textContent = d.poste;

            const nameEl = document.createElement('span');
            nameEl.className = 'flex-grow-1 small';
            nameEl.innerHTML = d.label.replace(re, '<strong>$1</strong>');

            div.appendChild(dot);
            div.appendChild(posteEl);
            div.appendChild(nameEl);
            div.addEventListener('mousedown', e => {
                e.preventDefault();
                input.value = d.label;
                close();
                form.submit();
            });
            div.addEventListener('mouseenter', () => highlight(i));
            dropdown.appendChild(div);
        });

        dropdown.style.display = 'block';
        active = -1;
    }

    function highlight(i) {
        dropdown.querySelectorAll('.ac-item').forEach((el, j) => {
            el.style.background = j === i ? '#f0fdf4' : '';
        });
        active = i;
    }

    function close() {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        active = -1;
    }

    input.addEventListener('input', () => render(input.value));

    input.addEventListener('keydown', e => {
        const items = dropdown.querySelectorAll('.ac-item');
        if (e.key === 'ArrowDown')  { e.preventDefault(); highlight(Math.min(active+1, items.length-1)); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); highlight(Math.max(active-1, 0)); }
        else if (e.key === 'Enter' && active >= 0) {
            e.preventDefault();
            input.value = items[active].dataset.label;
            close(); form.submit();
        } else if (e.key === 'Escape') close();
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) close();
    });
}());
</script>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
