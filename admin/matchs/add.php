<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();
$competitions = $pdo->query("SELECT * FROM competitions ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $adversaire   = trim($_POST['adversaire']           ?? '');
    $date_match   = $_POST['date_match']                 ?? '';
    $heure_match  = $_POST['heure_match']                ?? null;
    $stade        = trim($_POST['stade']                 ?? '');
    $lieu         = $_POST['domicile_exterieur']         ?? 'Domicile';
    $comp_id      = (int)($_POST['competition_id']       ?? 0) ?: null;
    $score_eq     = (int)($_POST['score_equipe']         ?? 0);
    $score_adv    = (int)($_POST['score_adverse']        ?? 0);
    $statut       = $_POST['statut']                     ?? 'Programmé';
    $notes        = trim($_POST['notes']                 ?? '');
    $errors       = [];

    if (!$adversaire) $errors[] = "L'adversaire est requis.";
    if (!$date_match) $errors[] = "La date est requise.";

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO matchs
                (competition_id, date_match, heure_match, stade, adversaire,
                 domicile_exterieur, score_equipe, score_adverse, statut, notes)
            VALUES (?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([$comp_id, $date_match, $heure_match ?: null, $stade ?: null,
            $adversaire, $lieu, $score_eq, $score_adv, $statut, $notes ?: null]);
        $newId = (int)$pdo->lastInsertId();
        logMatchHistory($pdo, $newId, 'vs ' . $adversaire . ' — ' . formatDate($date_match), 'creation');
        setFlash('success', "Match contre $adversaire enregistré.");
        redirect('/admin/matchs/index.php');
    }
}

$pageTitle = 'Ajouter un match';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= BASE_URL ?>/admin/matchs/index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">Ajouter un match</h4>
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
                    <input type="text" name="adversaire" class="form-control" value="<?= e($_POST['adversaire'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_match" class="form-control" value="<?= e($_POST['date_match'] ?? '') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Heure</label>
                    <input type="time" name="heure_match" class="form-control" value="<?= e($_POST['heure_match'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Stade / Lieu</label>
                    <input type="text" name="stade" class="form-control" value="<?= e($_POST['stade'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Domicile / Extérieur</label>
                    <select name="domicile_exterieur" class="form-select">
                        <option value="Domicile" <?= ($_POST['domicile_exterieur'] ?? 'Domicile')==='Domicile'?'selected':'' ?>>Domicile</option>
                        <option value="Extérieur" <?= ($_POST['domicile_exterieur'] ?? '')==='Extérieur'?'selected':'' ?>>Extérieur</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Compétition</label>
                    <select name="competition_id" class="form-select">
                        <option value="">— Aucune —</option>
                        <?php foreach ($competitions as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($_POST['competition_id'] ?? '')==$c['id']?'selected':'' ?>><?= e($c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <?php foreach (['Programmé','En cours','Terminé','Annulé','Reporté'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($_POST['statut'] ?? 'Programmé')===$s?'selected':'' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Score équipe</label>
                    <input type="number" name="score_equipe" class="form-control" min="0" value="<?= e($_POST['score_equipe'] ?? '0') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Score adversaire</label>
                    <input type="number" name="score_adverse" class="form-control" min="0" value="<?= e($_POST['score_adverse'] ?? '0') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3"><?= e($_POST['notes'] ?? '') ?></textarea>
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
