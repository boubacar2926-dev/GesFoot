<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();

// Crée la table si elle n'existe pas encore (aucune action n'a été logguée)
$pdo->exec("CREATE TABLE IF NOT EXISTS historique_matchs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_id INT NOT NULL,
    match_label VARCHAR(255) NOT NULL,
    utilisateur_id INT NOT NULL,
    action ENUM('creation','modification','suppression') NOT NULL,
    details TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_match (match_id),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;
$total   = (int)$pdo->query("SELECT COUNT(*) FROM historique_matchs")->fetchColumn();

$stmt = $pdo->prepare("
    SELECT h.*, u.nom AS u_nom, u.prenom AS u_prenom
    FROM historique_matchs h
    LEFT JOIN utilisateurs u ON u.id = h.utilisateur_id
    ORDER BY h.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([$perPage, $offset]);
$lignes = $stmt->fetchAll();

$pageTitle = 'Historique des matchs';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Historique des modifications</h4>
    <span class="text-muted small"><?= $total ?> entrée<?= $total > 1 ? 's' : '' ?></span>
</div>

<div class="card table-card">
    <div class="card-header"><i class="bi bi-clock-history me-2"></i>Journal des actions sur les matchs</div>
    <div class="card-body p-0 table-responsive">
        <?php if ($lignes): ?>
        <table class="table mb-0">
            <thead><tr>
                <th style="width:110px">Date</th>
                <th>Utilisateur</th>
                <th>Match</th>
                <th class="text-center" style="width:120px">Action</th>
                <th>Détails</th>
            </tr></thead>
            <tbody>
            <?php foreach ($lignes as $row):
                $actionMap = [
                    'creation'     => ['success', 'Création'],
                    'modification' => ['primary', 'Modification'],
                    'suppression'  => ['danger',  'Suppression'],
                ];
                [$cls, $lbl] = $actionMap[$row['action']] ?? ['secondary', $row['action']];
            ?>
            <tr>
                <td class="small text-nowrap">
                    <?= date('d/m/Y', strtotime($row['created_at'])) ?>
                    <br><span class="text-muted"><?= date('H:i', strtotime($row['created_at'])) ?></span>
                </td>
                <td class="small">
                    <?php if ($row['u_nom']): ?>
                        <?= e($row['u_prenom'] . ' ' . $row['u_nom']) ?>
                    <?php else: ?>
                        <span class="text-muted fst-italic">Utilisateur supprimé</span>
                    <?php endif; ?>
                </td>
                <td class="fw-semibold small"><?= e($row['match_label']) ?></td>
                <td class="text-center">
                    <span class="badge bg-<?= $cls ?>"><?= $lbl ?></span>
                </td>
                <td class="small">
                    <?php if ($row['details']): ?>
                        <?php $diff = json_decode($row['details'], true) ?? []; ?>
                        <?php foreach ($diff as $champ => $vals): ?>
                        <div class="mb-1">
                            <span class="text-muted"><?= e($champ) ?> :</span>
                            <span class="text-danger"><?= e((string)($vals['avant'] ?? '')) ?></span>
                            <i class="bi bi-arrow-right text-muted mx-1" style="font-size:.7rem"></i>
                            <span class="text-success"><?= e((string)($vals['apres'] ?? '')) ?></span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-clock-history fs-1 d-block mb-3"></i>
            Aucun historique pour l'instant.
        </div>
        <?php endif; ?>
    </div>
    <?php if ($total > $perPage): ?>
    <div class="card-footer d-flex justify-content-end">
        <?= paginate($total, $perPage, $page, BASE_URL . '/admin/historique/index.php?') ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
