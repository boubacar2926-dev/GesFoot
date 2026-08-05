<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('admin');
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    if ($_POST['action'] === 'add') {
        $nom    = trim($_POST['nom']    ?? '');
        $type   = $_POST['type']   ?? 'Championnat';
        $saison = trim($_POST['saison'] ?? '');
        if ($nom) {
            $pdo->prepare("INSERT INTO competitions (nom, type, saison) VALUES (?,?,?)")->execute([$nom,$type,$saison?:null]);
            setFlash('success',"Compétition $nom ajoutée.");
        }
    } elseif ($_POST['action'] === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM competitions WHERE id=?")->execute([$id]);
        setFlash('success','Compétition supprimée.');
    }
    redirect('/admin/competitions/index.php');
}

$competitions = $pdo->query("
    SELECT c.*, COUNT(m.id) AS nb_matchs
    FROM competitions c
    LEFT JOIN matchs m ON m.competition_id = c.id
    GROUP BY c.id ORDER BY c.nom
")->fetchAll();

$pageTitle = 'Compétitions';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><i class="bi bi-plus-circle me-2 text-success"></i>Ajouter une compétition</div>
            <div class="card-body">
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" placeholder="Ex: Championnat Régional" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <?php foreach (['Championnat','Coupe','Amical','Tournoi'] as $t): ?>
                                <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Saison</label>
                        <input type="text" name="saison" class="form-control" placeholder="Ex: 2025-2026">
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-plus me-1"></i> Ajouter</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card table-card">
            <div class="card-header"><i class="bi bi-trophy me-2 text-warning"></i>Liste des compétitions</div>
            <div class="card-body p-0 table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Nom</th><th>Type</th><th>Saison</th><th>Matchs</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php if ($competitions): ?>
                        <?php foreach ($competitions as $c): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($c['nom']) ?></td>
                            <td><span class="badge bg-primary"><?= e($c['type']) ?></span></td>
                            <td class="text-muted"><?= e($c['saison'] ?: '—') ?></td>
                            <td><span class="badge bg-light text-dark"><?= $c['nb_matchs'] ?></span></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-icon btn-sm btn-outline-danger"
                                            onclick="return confirm('Supprimer cette compétition ?')" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Aucune compétition</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
