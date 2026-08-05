<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();

$search  = trim($_GET['q']    ?? '');
$role    = $_GET['role']      ?? '';
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where  = ['1=1'];
$params = [];
if ($search) {
    $where[] = '(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR CONCAT(prenom, " ", nom) LIKE ?)';
    $params  = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}
if ($role) {
    $where[] = 'role = ?';
    $params[] = $role;
}

$whereStr = implode(' AND ', $where);

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE $whereStr");
$totalStmt->execute($params);
$total      = (int)$totalStmt->fetchColumn();
$totalPages = max(1, (int)ceil($total / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

$usersStmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE $whereStr ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$usersStmt->execute($params);
$users = $usersStmt->fetchAll();

// Données pour l'autocomplete
$suggestions = $pdo->query("SELECT prenom, nom, role FROM utilisateurs ORDER BY nom")->fetchAll();

$pageTitle = 'Gestion des utilisateurs';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Utilisateurs</h4>
        <p class="text-muted mb-0"><?= $total ?> utilisateur(s)</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/users/add.php" class="btn btn-success">
        <i class="bi bi-person-plus-fill me-1"></i> Ajouter
    </a>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form class="row g-3 align-items-end" method="GET" id="filterForm">
            <div class="col-md-6" style="position:relative">
                <input type="text" name="q" id="searchInput" class="form-control"
                       placeholder="Rechercher par nom, prénom ou email..."
                       value="<?= e($search) ?>" autocomplete="off">
                <div id="dropdownSuggestions"
                     style="display:none;position:absolute;top:calc(100% + 2px);left:0;right:0;
                            background:#fff;border:1px solid #dee2e6;border-radius:8px;
                            box-shadow:0 4px 16px rgba(0,0,0,.1);z-index:1050;overflow:hidden;
                            max-height:280px;overflow-y:auto">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select">
                    <option value="">Tous les rôles</option>
                    <?php foreach (['admin','coach','staff','joueur'] as $r): ?>
                        <option value="<?= $r ?>" <?= $role === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
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

<!-- Table -->
<div class="card table-card">
    <div class="card-body p-0 table-responsive">
        <table class="table datatable">
            <thead><tr>
                <th>#</th>
                <th>Utilisateur</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Inscrit le</th>
                <th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if ($users): ?>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td class="text-muted"><?= $u['id'] ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= avatarUrl($u['photo'], $u['prenom'].' '.$u['nom']) ?>" class="avatar" alt="">
                            <div>
                                <div class="fw-semibold"><?= e($u['prenom']) ?> <?= e($u['nom']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['telephone'] ?: '—') ?></td>
                    <td><?= roleBadge($u['role']) ?></td>
                    <td><?= statusBadge($u['statut']) ?></td>
                    <td class="text-muted small"><?= formatDate($u['created_at']) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= BASE_URL ?>/admin/users/edit.php?id=<?= $u['id'] ?>"
                               class="btn btn-icon btn-sm btn-outline-primary" title="Modifier" data-bs-toggle="tooltip">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="<?= BASE_URL ?>/admin/users/delete.php" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button type="submit" class="btn btn-icon btn-sm btn-outline-danger"
                                        data-confirm="Supprimer cet utilisateur ?" title="Supprimer" data-bs-toggle="tooltip">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-2 d-block mb-2"></i>Aucun utilisateur trouvé
                </td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1):
    $qp = array_filter(['q'=>$search,'role'=>$role]);
?>
<nav class="d-flex align-items-center justify-content-between mt-3">
    <small class="text-muted">Page <?= $page ?> / <?= $totalPages ?> — <?= $total ?> utilisateur(s)</small>
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

    const data = <?= json_encode(array_map(fn($u) => [
        'label' => $u['prenom'] . ' ' . $u['nom'],
        'role'  => $u['role'],
    ], $suggestions), JSON_HEX_TAG | JSON_HEX_AMP) ?>;

    const colors = { 'admin':'#dc2626', 'coach':'#1d4ed8', 'staff':'#a16207', 'joueur':'#15803d' };
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
            dot.style.cssText = `width:8px;height:8px;border-radius:50%;background:${colors[d.role]||'#ccc'};flex-shrink:0`;

            const roleEl = document.createElement('span');
            roleEl.style.cssText = `min-width:55px;font-size:.72rem;font-weight:600;color:${colors[d.role]||'#374151'}`;
            roleEl.textContent = d.role;

            const nameEl = document.createElement('span');
            nameEl.className = 'flex-grow-1 small';
            nameEl.innerHTML = d.label.replace(re, '<strong>$1</strong>');

            div.appendChild(dot);
            div.appendChild(roleEl);
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
        if (e.key === 'ArrowDown')    { e.preventDefault(); highlight(Math.min(active+1, items.length-1)); }
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
