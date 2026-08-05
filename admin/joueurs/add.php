<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();

// Utilisateurs disponibles pour lien
$utilisateurs = $pdo->query("SELECT id, prenom, nom, email FROM utilisateurs WHERE role='joueur' ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $fields = ['nom','prenom','date_naissance','nationalite','poste','statut'];
    $data   = [];
    foreach ($fields as $f) $data[$f] = trim($_POST[$f] ?? '');

    $data['utilisateur_id']  = (int)($_POST['utilisateur_id']  ?? 0) ?: null;
    $data['numero_maillot']  = (int)($_POST['numero_maillot']  ?? 0) ?: null;
    $data['taille']          = (float)($_POST['taille'] ?? 0) ?: null;
    $data['poids']           = (float)($_POST['poids']  ?? 0) ?: null;
    $data['date_inscription']= $_POST['date_inscription'] ?? date('Y-m-d');

    $errors = [];
    if (!$data['nom'])    $errors[] = 'Le nom est requis.';
    if (!$data['prenom']) $errors[] = 'Le prénom est requis.';
    if (!$data['poste'])  $errors[] = 'Le poste est requis.';

    $photo = null;
    if (!empty($_FILES['photo']['name'])) {
        $photo = uploadPhoto($_FILES['photo'], 'joueurs');
        if (!$photo) $errors[] = 'Photo invalide (JPG/PNG, max 2 Mo).';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO joueurs
                (utilisateur_id, club_id, nom, prenom, date_naissance, nationalite,
                 taille, poids, poste, numero_maillot, photo, date_inscription, statut)
            VALUES (?,1,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $data['utilisateur_id'], $data['nom'], $data['prenom'],
            $data['date_naissance'] ?: null, $data['nationalite'] ?: null,
            $data['taille'], $data['poids'], $data['poste'],
            $data['numero_maillot'], $photo, $data['date_inscription'], $data['statut']
        ]);
        setFlash('success', "Joueur {$data['prenom']} {$data['nom']} ajouté.");
        redirect('/admin/joueurs/index.php');
    }
}

$pageTitle = 'Ajouter un joueur';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= BASE_URL ?>/admin/joueurs/index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">Ajouter un joueur</h4>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="row g-4">
                <!-- Photo -->
                <div class="col-12 text-center">
                    <img id="photoPreview" src="https://ui-avatars.com/api/?name=J&background=2d6a4f&color=fff&size=100"
                         style="width:100px;height:100px;border-radius:12px;object-fit:cover;border:2px solid #e2e8f0">
                    <div class="mt-2">
                        <label class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-camera"></i> Photo
                            <input type="file" name="photo" class="d-none" accept="image/*" data-preview="photoPreview">
                        </label>
                    </div>
                </div>

                <div class="col-12"><h6 class="fw-bold text-success">Identité</h6><hr class="mt-1"></div>

                <div class="col-md-6">
                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" class="form-control" value="<?= e($_POST['prenom'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control" value="<?= e($_POST['nom'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control" value="<?= e($_POST['date_naissance'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nationalité</label>
                    <input type="text" name="nationalite" class="form-control" value="<?= e($_POST['nationalite'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date d'inscription</label>
                    <input type="date" name="date_inscription" class="form-control" value="<?= e($_POST['date_inscription'] ?? date('Y-m-d')) ?>">
                </div>

                <div class="col-12"><h6 class="fw-bold text-success">Caractéristiques</h6><hr class="mt-1"></div>

                <div class="col-md-3">
                    <label class="form-label">Poste <span class="text-danger">*</span></label>
                    <select name="poste" class="form-select" required>
                        <option value="">— Choisir —</option>
                        <?php foreach (['Gardien','Défenseur','Milieu','Attaquant'] as $p): ?>
                            <option value="<?= $p ?>" <?= ($_POST['poste'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° de maillot</label>
                    <input type="number" name="numero_maillot" class="form-control" min="1" max="99" value="<?= e($_POST['numero_maillot'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Taille (cm)</label>
                    <input type="number" name="taille" class="form-control" step="0.1" min="100" max="220" value="<?= e($_POST['taille'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Poids (kg)</label>
                    <input type="number" name="poids" class="form-control" step="0.1" min="40" max="150" value="<?= e($_POST['poids'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <?php foreach (['actif'=>'Actif','blessé'=>'Blessé','suspendu'=>'Suspendu','inactif'=>'Inactif'] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= ($_POST['statut'] ?? 'actif') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Lier à un compte utilisateur</label>
                    <select name="utilisateur_id" class="form-select">
                        <option value="">— Aucun —</option>
                        <?php foreach ($utilisateurs as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= ($_POST['utilisateur_id'] ?? '') == $u['id'] ? 'selected' : '' ?>>
                                <?= e($u['prenom'].' '.$u['nom']) ?> (<?= e($u['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex gap-2 justify-content-end">
                <a href="<?= BASE_URL ?>/admin/joueurs/index.php" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-success"><i class="bi bi-check2 me-1"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
