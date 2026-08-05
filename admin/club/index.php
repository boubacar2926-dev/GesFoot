<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('admin');
$pdo   = getPDO();
$club  = $pdo->query("SELECT * FROM clubs LIMIT 1")->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $nom      = trim($_POST['nom']      ?? '');
    $ville    = trim($_POST['ville']    ?? '');
    $adresse  = trim($_POST['adresse']  ?? '');
    $tel      = trim($_POST['telephone'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $site     = trim($_POST['site_web'] ?? '');
    $annee    = (int)($_POST['annee_fondation'] ?? 0) ?: null;
    $errors   = [];

    if (!$nom) $errors[] = 'Le nom du club est requis.';

    $logo = $club['logo'] ?? null;
    if (!empty($_FILES['logo']['name'])) {
        $newLogo = uploadPhoto($_FILES['logo'], 'club');
        if ($newLogo) $logo = $newLogo;
        else $errors[] = 'Logo invalide (JPG/PNG, max 2 Mo).';
    }

    if (!$errors) {
        if ($club) {
            $pdo->prepare("UPDATE clubs SET nom=?,ville=?,adresse=?,telephone=?,email=?,site_web=?,annee_fondation=?,logo=? WHERE id=?")
                ->execute([$nom,$ville,$adresse,$tel,$email,$site,$annee,$logo,$club['id']]);
        } else {
            $pdo->prepare("INSERT INTO clubs (nom,ville,adresse,telephone,email,site_web,annee_fondation,logo) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$nom,$ville,$adresse,$tel,$email,$site,$annee,$logo]);
        }
        setFlash('success','Informations du club mises à jour.');
        redirect('/admin/club/index.php');
    }
    $club = array_merge($club ?? [], compact('nom','ville','adresse','tel','email','site','annee'));
}

$pageTitle = 'Mon Club';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="row g-4">
    <div class="col-lg-3 text-center">
        <div class="card p-4">
            <?php if (!empty($club['logo'])): ?>
                <img src="<?= avatarUrl($club['logo'], $club['nom']) ?>" style="width:120px;height:120px;border-radius:12px;object-fit:contain;margin:auto">
            <?php else: ?>
                <div style="width:120px;height:120px;background:#f1f5f9;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:auto">
                    <i class="bi bi-building fs-1 text-muted"></i>
                </div>
            <?php endif; ?>
            <h5 class="fw-bold mt-3"><?= e($club['nom'] ?? 'Mon Club FC') ?></h5>
            <?php if (!empty($club['ville'])): ?>
                <p class="text-muted mb-0"><i class="bi bi-geo-alt"></i> <?= e($club['ville']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-9">
        <div class="card">
            <div class="card-header"><i class="bi bi-pencil-square me-2"></i>Modifier les informations</div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nom du club <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control" value="<?= e($club['nom'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Année de fondation</label>
                            <input type="number" name="annee_fondation" class="form-control" min="1800" max="2100" value="<?= e($club['annee_fondation'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ville</label>
                            <input type="text" name="ville" class="form-control" value="<?= e($club['ville'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" name="telephone" class="form-control" value="<?= e($club['telephone'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="adresse" class="form-control" value="<?= e($club['adresse'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= e($club['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Site web</label>
                            <input type="text" name="site_web" class="form-control" placeholder="www.monclub.sn" value="<?= e($club['site_web'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Logo du club</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            <div class="form-text">Format JPG ou PNG, max 2 Mo.</div>
                        </div>
                    </div>
                    <hr class="my-4">
                    <button type="submit" class="btn btn-success"><i class="bi bi-check2 me-1"></i> Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
