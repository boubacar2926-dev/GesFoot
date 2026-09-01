<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$id]);
$targetUser = $stmt->fetch();
if (!$targetUser) { setFlash('error', 'Utilisateur introuvable.'); redirect('/admin/users/index.php'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    // Sécurité mode démo : Empêcher la modification de l'admin principal
    if ($targetUser['email'] === 'admin@club.fr' || $id === 1) {
        setFlash('error', "Action impossible : Le compte administrateur de démonstration ne peut pas être modifié.");
        redirect('/admin/users/index.php');
    }

    $nom    = trim($_POST['nom']    ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $tel    = trim($_POST['telephone'] ?? '');
    $role   = in_array($_POST['role']   ?? '', ['admin','coach','staff','joueur']) ? $_POST['role'] : 'joueur';
    $statut = in_array($_POST['statut'] ?? '', ['actif','inactif'])               ? $_POST['statut'] : 'actif';
    $pass   = $_POST['password'] ?? '';
    $errors = [];

    if (!$nom)    $errors[] = 'Le nom est requis.';
    if (!$prenom) $errors[] = 'Le prénom est requis.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invalide.';

    if (!$errors) {
        $check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ? AND id != ?");
        $check->execute([$email, $id]);
        if ($check->fetch()) $errors[] = 'Cet email est déjà utilisé par un autre compte.';
    }

    $photo = $targetUser['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $newPhoto = uploadPhoto($_FILES['photo'], 'photos');
        if ($newPhoto) $photo = $newPhoto;
        else $errors[] = 'Photo invalide (JPG/PNG, max 2 Mo).';
    }

    if (!$errors) {
        if ($pass && strlen($pass) >= 6) {
            $hash = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?,prenom=?,email=?,telephone=?,role=?,statut=?,photo=?,mot_de_passe=? WHERE id=?");
            $stmt->execute([$nom,$prenom,$email,$tel,$role,$statut,$photo,$hash,$id]);
        } else {
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?,prenom=?,email=?,telephone=?,role=?,statut=?,photo=? WHERE id=?");
            $stmt->execute([$nom,$prenom,$email,$tel,$role,$statut,$photo,$id]);
        }
        setFlash('success', "Utilisateur mis à jour.");
        redirect('/admin/users/index.php');
    }
    // Repopuler le formulaire en cas d'erreur
    $targetUser = array_merge($targetUser, compact('nom','prenom','email','tel','role','statut'));
}

$pageTitle = 'Modifier l\'utilisateur';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= BASE_URL ?>/admin/users/index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold mb-0">Modifier l'utilisateur</h4>
        <p class="text-muted mb-0 small"><?= e($targetUser['prenom']) ?> <?= e($targetUser['nom']) ?></p>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrfField() ?>
            <div class="row g-4">
                <div class="col-12 text-center">
                    <img id="photoPreview" src="<?= avatarUrl($targetUser['photo'], $targetUser['prenom'].' '.$targetUser['nom']) ?>"
                         class="avatar-lg border mb-2" style="border-radius:12px!important">
                    <div>
                        <label class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-camera"></i> Changer la photo
                            <input type="file" name="photo" class="d-none" accept="image/*" data-preview="photoPreview">
                        </label>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" class="form-control" value="<?= e($targetUser['prenom']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control" value="<?= e($targetUser['nom']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= e($targetUser['email']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Téléphone</label>
                    <input type="tel" name="telephone" class="form-control" value="<?= e($targetUser['telephone'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Rôle</label>
                    <select name="role" class="form-select">
                        <?php foreach (['admin'=>'Administrateur','coach'=>'Coach','staff'=>'Staff Technique','joueur'=>'Joueur'] as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $targetUser['role'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <option value="actif"   <?= $targetUser['statut']==='actif'   ? 'selected' : '' ?>>Actif</option>
                        <option value="inactif" <?= $targetUser['statut']==='inactif' ? 'selected' : '' ?>>Inactif</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-control" placeholder="Laisser vide pour ne pas changer">
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