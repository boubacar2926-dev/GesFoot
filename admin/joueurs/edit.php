<?php
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

requireRole('admin');
$pdo = getPDO();
$id  = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM joueurs WHERE id = ?");
$stmt->execute([$id]);
$joueur = $stmt->fetch();
if (!$joueur) { setFlash('error', 'Joueur introuvable.'); redirect('/admin/joueurs/index.php'); }

$utilisateurs = $pdo->query("SELECT id, prenom, nom, email FROM utilisateurs WHERE role='joueur' ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $nom              = trim($_POST['nom']    ?? '');
    $prenom           = trim($_POST['prenom'] ?? '');
    $date_naissance   = $_POST['date_naissance']   ?? null;
    $nationalite      = trim($_POST['nationalite'] ?? '');
    $taille           = (float)($_POST['taille'] ?? 0) ?: null;
    $poids            = (float)($_POST['poids']  ?? 0) ?: null;
    $poste            = $_POST['poste']  ?? '';
    $numero_maillot   = (int)($_POST['numero_maillot'] ?? 0) ?: null;
    $statut           = $_POST['statut'] ?? 'actif';
    $utilisateur_id   = (int)($_POST['utilisateur_id'] ?? 0) ?: null;
    $date_inscription = $_POST['date_inscription'] ?? $joueur['date_inscription'];
    $errors           = [];

    if (!$nom)    $errors[] = 'Le nom est requis.';
    if (!$prenom) $errors[] = 'Le prénom est requis.';
    if (!$poste)  $errors[] = 'Le poste est requis.';

    $photo = $joueur['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $newPhoto = uploadPhoto($_FILES['photo'], 'joueurs');
        if ($newPhoto) $photo = $newPhoto;
        else $errors[] = 'Photo invalide (JPG/PNG, max 2 Mo).';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            UPDATE joueurs SET
                utilisateur_id=?, nom=?, prenom=?, date_naissance=?, nationalite=?,
                taille=?, poids=?, poste=?, numero_maillot=?, photo=?,
                date_inscription=?, statut=?
            WHERE id=?
        ");
        $stmt->execute([
            $utilisateur_id, $nom, $prenom, $date_naissance ?: null, $nationalite ?: null,
            $taille, $poids, $poste, $numero_maillot, $photo,
            $date_inscription, $statut, $id
        ]);
        setFlash('success', "Joueur $prenom $nom mis à jour.");
        redirect('/admin/joueurs/index.php');
    }
    $joueur = array_merge($joueur, compact('nom','prenom','date_naissance','nationalite','taille','poids','poste','numero_maillot','statut','utilisateur_id','date_inscription'));
}

$pageTitle = 'Modifier le joueur';
require_once ROOT_PATH . '/admin/includes/header.php';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= BASE_URL ?>/admin/joueurs/index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h4 class="fw-bold mb-0">Modifier — <?= e($joueur['prenom']) ?> <?= e($joueur['nom']) ?></h4>
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
                    <img id="photoPreview" src="<?= avatarUrl($joueur['photo'], $joueur['prenom'].' '.$joueur['nom']) ?>"
                         style="width:100px;height:100px;border-radius:12px;object-fit:cover;border:2px solid #e2e8f0">
                    <div class="mt-2">
                        <label class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-camera"></i> Changer la photo
                            <input type="file" name="photo" class="d-none" accept="image/*" data-preview="photoPreview">
                        </label>
                    </div>
                </div>

                <div class="col-12"><h6 class="fw-bold text-success">Identité</h6><hr class="mt-1"></div>
                <div class="col-md-6">
                    <label class="form-label">Prénom <span class="text-danger">*</span></label>
                    <input type="text" name="prenom" class="form-control" value="<?= e($joueur['prenom']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nom <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control" value="<?= e($joueur['nom']) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control" value="<?= e($joueur['date_naissance'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nationalité</label>
                    <input type="text" name="nationalite" class="form-control" value="<?= e($joueur['nationalite'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date d'inscription</label>
                    <input type="date" name="date_inscription" class="form-control" value="<?= e($joueur['date_inscription'] ?? date('Y-m-d')) ?>">
                </div>

                <div class="col-12"><h6 class="fw-bold text-success">Caractéristiques</h6><hr class="mt-1"></div>
                <div class="col-md-3">
                    <label class="form-label">Poste <span class="text-danger">*</span></label>
                    <select name="poste" class="form-select" required>
                        <?php foreach (['Gardien','Défenseur','Milieu','Attaquant'] as $p): ?>
                            <option value="<?= $p ?>" <?= $joueur['poste']===$p?'selected':'' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">N° maillot</label>
                    <input type="number" name="numero_maillot" class="form-control" min="1" max="99" value="<?= e($joueur['numero_maillot'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Taille (cm)</label>
                    <input type="number" name="taille" class="form-control" step="0.1" value="<?= e($joueur['taille'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Poids (kg)</label>
                    <input type="number" name="poids" class="form-control" step="0.1" value="<?= e($joueur['poids'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Statut</label>
                    <select name="statut" class="form-select">
                        <?php foreach (['actif'=>'Actif','blessé'=>'Blessé','suspendu'=>'Suspendu','inactif'=>'Inactif'] as $val => $lbl): ?>
                            <option value="<?= $val ?>" <?= $joueur['statut']===$val?'selected':'' ?>><?= $lbl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Compte utilisateur lié</label>
                    <select name="utilisateur_id" class="form-select">
                        <option value="">— Aucun —</option>
                        <?php foreach ($utilisateurs as $u): ?>
                            <option value="<?= $u['id'] ?>" <?= $joueur['utilisateur_id']==$u['id']?'selected':'' ?>>
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
