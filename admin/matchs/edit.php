<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM matchs WHERE id=?");
$stmt->execute([$id]);
$match = $stmt->fetch();
if (!$match) { setFlash('error','Match introuvable.'); redirect('/admin/matchs/index.php'); }

$competitions = $pdo->query("SELECT * FROM competitions ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $adversaire = trim($_POST['adversaire'] ?? '');
    $date_match = $_POST['date_match'] ?? '';
    $errors = [];
    if (!$adversaire) $errors[] = "L'adversaire est requis.";
    if (!$date_match) $errors[] = "La date est requise.";

    if (!$errors) {
        $stmt = $pdo->prepare("
            UPDATE matchs SET competition_id=?, date_match=?, heure_match=?, stade=?, adversaire=?,
                domicile_exterieur=?, score_equipe=?, score_adverse=?, statut=?, notes=?
            WHERE id=?
        ");
        $stmt->execute([
            (int)($_POST['competition_id'] ?? 0) ?: null,
            $date_match,
            $_POST['heure_match'] ?: null,
            trim($_POST['stade'] ?? '') ?: null,
            $adversaire,
            $_POST['domicile_exterieur'] ?? 'Domicile',
            (int)($_POST['score_equipe'] ?? 0),
            (int)($_POST['score_adverse'] ?? 0),
            $_POST['statut'] ?? 'Programmé',
            trim($_POST['notes'] ?? '') ?: null,
            $id
        ]);
        setFlash('success', "Match mis à jour.");
        redirect('/admin/matchs/index.php');
    }
    $match = array_merge($match, $_POST);
}

$pageTitle = 'Modifier le match';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= BASE_URL ?>/admin/matchs/index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">Modifier — vs <?= e($match['adversaire']) ?></h4>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST">
            <?= csrfField() ?>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Adversaire <span class="text-danger">*</span></label>
                    <input type="text" name="adversaire" class="form-control" value="<?= e($match['adversaire']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_match" class="form-control" value="<?= e($match['date_match']) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Heure</label>
                    <input type="time" name="heure_match" class="form-control" value="<?= e(substr($match['heure_match'] ?? '',0,5)) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Stade / Lieu</label>
                    <input type="text" name="stade" class="form-control" value="<?= e($match['stade'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Domicile / Extérieur</label>
                    <select name="domicile_exterieur" class="form-select">
                        <option value="Domicile"  <?= $match['domicile_exterieur']==='Domicile' ?'selected':'' ?>>Domicile</option>
                        <option value="Extérieur" <?= $match['domicile_exterieur']==='Extérieur'?'selected':'' ?>>Extérieur</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Compétition</label>
                    <select name="competition_id" class="form-select">
                        <option value="">— Aucune —</option>
                        <?php foreach ($competitions as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $match['competition_id']==$c['id']?'selected':'' ?>><?= e($c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <?php foreach (['Programmé','En cours','Terminé','Annulé','Reporté'] as $s): ?>
                            <option value="<?= $s ?>" <?= $match['statut']===$s?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <hr class="my-1">
                    <label class="form-label fw-semibold">Résultat du match</label>
                    <div class="d-flex align-items-center gap-3">
                        <div style="width:160px">
                            <label class="form-label text-muted small mb-1">Score AS Étoile de Dakar</label>
                            <input type="number" name="score_equipe" class="form-control form-control-lg text-center fw-bold" min="0" max="30" value="<?= e($match['score_equipe'] ?? 0) ?>" placeholder="0">
                        </div>
                        <div class="fw-bold fs-3 text-muted mt-3">—</div>
                        <div style="width:160px">
                            <label class="form-label text-muted small mb-1">Score <?= e($match['adversaire']) ?></label>
                            <input type="number" name="score_adverse" class="form-control form-control-lg text-center fw-bold" min="0" max="30" value="<?= e($match['score_adverse'] ?? 0) ?>" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"><?= e($match['notes'] ?? '') ?></textarea>
                </div>
            </div>
            <hr class="my-4">
            <div class="d-flex gap-2 justify-content-end">
                <a href="<?= BASE_URL ?>/admin/matchs/index.php" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-success"><i class="bi bi-check2 me-1"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
