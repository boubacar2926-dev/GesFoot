<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $nom      = trim($_POST['nom']      ?? '');
    $prenom   = trim($_POST['prenom']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $tel      = trim($_POST['telephone'] ?? '');
    $role     = $_POST['role']   ?? 'joueur';
    $statut   = $_POST['statut'] ?? 'actif';
    $pass     = $_POST['password'] ?? '';
    $errors   = [];

    if (!$nom)   $errors[] = 'Le nom est requis.';
    if (!$prenom) $errors[] = 'Le prénom est requis.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';
    if (strlen($pass) < 6) $errors[] = 'Mot de passe min. 6 caractères.';
    if (!in_array($role, ['admin','coach','staff','joueur'])) $errors[] = 'Rôle invalide.';

    // Vérif email unique
    if (!$errors) {
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) $errors[] = 'Cet email est déjà utilisé.';
    }

    if (!$errors) {
        $photo = null;
        if (!empty($_FILES['photo']['name'])) {
            $photo = uploadPhoto($_FILES['photo'], 'photos');
            if (!$photo) $errors[] = 'Photo invalide (JPG/PNG, max 2 Mo).';
        }
    }

    if (!$errors) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role, statut, photo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nom, $prenom, $email, $tel, $hash, $role, $statut, $photo]);
        setFlash('success', "Utilisateur $prenom $nom créé avec succès.");
        redirect('/admin/users/index.php');
    }
}

$pageTitle = 'Ajouter un utilisateur';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= BASE_URL ?>/admin/users/index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold mb-0">Ajouter un utilisateur</h4>
        <p class="text-muted mb-0 small">Créer un nouveau compte</p>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="row g-4">
                <!-- Photo -->
                <div class="col-12 text-center">
                    <img id="photoPreview" src="https://ui-avatars.com/api/?name=?&background=2d6a4f&color=fff&size=100"
                         class="avatar-lg border mb-2" style="border-radius:12px!important">
                    <div>
                        <label class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-camera"></i> Choisir une photo
                            <input type="file" name="photo" class="d-none" accept="image/*" data-preview="photoPreview">
                        </label>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" class="form-control" value="<?= e($_POST['prenom'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control" value="<?= e($_POST['nom'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= e($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" value="<?= e($_POST['telephone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Rôle <span class="text-danger">*</span></label>
                    <select name="role" class="form-select" required>
                        <?php foreach (['admin'=>'Administrateur','coach'=>'Coach','staff'=>'Staff Technique','joueur'=>'Joueur'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= ($_POST['role'] ?? 'joueur') === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 caractères" required>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex gap-2 justify-content-end">
                <a href="<?= BASE_URL ?>/admin/users/index.php" class="btn btn-outline-secondary">Annuler</a>
                <button type="submit" class="btn btn-success"><i class="bi bi-check2 me-1"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<?php require_once ROOT_PATH . '/admin/includes/footer.php'; ?>
