<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();

// Crée la table si elle n'existe pas encore
$pdo->exec("CREATE TABLE IF NOT EXISTS demandes_contact (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom_club    VARCHAR(255) NOT NULL,
    nom_contact VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL,
    telephone   VARCHAR(50)  NULL,
    offre       VARCHAR(100) NULL,
    message     TEXT         NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
if (!$pdo->query("SHOW COLUMNS FROM demandes_contact LIKE 'offre'")->fetchColumn()) {
    $pdo->exec("ALTER TABLE demandes_contact ADD COLUMN offre VARCHAR(100) NULL AFTER telephone");
}

$perPage  = 20;
$page     = max(1, (int)($_GET['page'] ?? 1));
$offset   = ($page - 1) * $perPage;
$total    = (int)$pdo->query("SELECT COUNT(*) FROM demandes_contact")->fetchColumn();

$stmt = $pdo->prepare("SELECT * FROM demandes_contact ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$perPage, $offset]);
$demandes = $stmt->fetchAll();

$pageTitle = 'Demandes de contact';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h4 class="fw-bold mb-0">Demandes de contact</h4>
    <span class="text-muted small"><?= $total ?> demande<?= $total > 1 ? 's' : '' ?> reçue<?= $total > 1 ? 's' : '' ?></span>
</div>

<div class="card table-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-envelope-paper me-2"></i>Clubs intéressés par la plateforme</span>
        <a href="<?= BASE_URL ?>/vitrine.php" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-box-arrow-up-right me-1"></i>Voir la vitrine
        </a>
    </div>
    <div class="card-body p-0 table-responsive">
        <?php if ($demandes): ?>
        <table class="table mb-0">
            <thead><tr>
                <th style="width:100px">Date</th>
                <th>Club</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Offre</th>
                <th>Message</th>
            </tr></thead>
            <tbody>
            <?php foreach ($demandes as $d): ?>
            <tr>
                <td class="small text-nowrap">
                    <?= date('d/m/Y', strtotime($d['created_at'])) ?>
                    <br><span class="text-muted"><?= date('H:i', strtotime($d['created_at'])) ?></span>
                </td>
                <td class="fw-semibold"><?= e($d['nom_club']) ?></td>
                <td><?= e($d['nom_contact']) ?></td>
                <td>
                    <a href="mailto:<?= e($d['email']) ?>" class="text-decoration-none small">
                        <?= e($d['email']) ?>
                    </a>
                </td>
                <td class="text-muted small"><?= $d['telephone'] ? e($d['telephone']) : '—' ?></td>
                <td class="text-muted small"><?= !empty($d['offre']) ? e($d['offre']) : '—' ?></td>
                <td class="small" style="max-width:260px">
                    <?php if ($d['message']): ?>
                        <span title="<?= e($d['message']) ?>">
                            <?= e(mb_substr($d['message'], 0, 100)) ?><?= mb_strlen($d['message']) > 100 ? '…' : '' ?>
                        </span>
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
            <i class="bi bi-envelope-paper fs-1 d-block mb-3"></i>
            Aucune demande de contact reçue pour l'instant.<br>
            <a href="<?= BASE_URL ?>/vitrine.php" target="_blank" class="btn btn-outline-success btn-sm mt-3">
                <i class="bi bi-box-arrow-up-right me-1"></i>Voir la page vitrine
            </a>
        </div>
        <?php endif; ?>
    </div>
    <?php if ($total > $perPage): ?>
    <div class="card-footer d-flex justify-content-end">
        <?= paginate($total, $perPage, $page, BASE_URL . '/admin/contacts/index.php?') ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
