<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';
requireRole('joueur');
$pdo  = getPDO();
$user = currentUser();

$joueurStmt = $pdo->prepare("SELECT * FROM joueurs WHERE utilisateur_id=? LIMIT 1");
$joueurStmt->execute([$user['id']]);
$joueur = $joueurStmt->fetch();

$pageTitle = 'Mon profil';
require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4"><h4 class="fw-bold mb-0">Mon profil</h4></div>

<?php if ($joueur): ?>
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card text-center">
            <div style="background:linear-gradient(135deg,#1b4332,#2d6a4f);padding:30px;border-radius:12px 12px 0 0">
                <img src="<?= avatarUrl($joueur['photo'], $joueur['prenom'].' '.$joueur['nom']) ?>"
                     style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.4)">
                <div class="mt-3 text-white">
                    <h5 class="fw-bold mb-1"><?= e($joueur['prenom']) ?> <?= e($joueur['nom']) ?></h5>
                    <?php if ($joueur['numero_maillot']): ?>
                        <span class="badge bg-light text-dark fs-6">#<?= e($joueur['numero_maillot']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <?= statusBadge($joueur['statut']) ?> <span class="badge bg-secondary"><?= e($joueur['poste']) ?></span>
                <div class="divider"></div>
                <ul class="list-unstyled text-start small">
                    <?php if ($joueur['date_naissance']): ?>
                    <li class="mb-2"><i class="bi bi-calendar3 text-muted me-2"></i><strong>Naissance :</strong> <?= formatDate($joueur['date_naissance']) ?> (<?= age($joueur['date_naissance']) ?>)</li>
                    <?php endif; ?>
                    <?php if ($joueur['nationalite']): ?>
                    <li class="mb-2"><i class="bi bi-flag text-muted me-2"></i><strong>Nationalité :</strong> <?= e($joueur['nationalite']) ?></li>
                    <?php endif; ?>
                    <?php if ($joueur['taille']): ?>
                    <li class="mb-2"><i class="bi bi-rulers text-muted me-2"></i><strong>Taille :</strong> <?= $joueur['taille'] ?> cm</li>
                    <?php endif; ?>
                    <?php if ($joueur['poids']): ?>
                    <li class="mb-2"><i class="bi bi-speedometer2 text-muted me-2"></i><strong>Poids :</strong> <?= $joueur['poids'] ?> kg</li>
                    <?php endif; ?>
                    <li class="mb-2"><i class="bi bi-envelope text-muted me-2"></i><?= e($user['email']) ?></li>
                    <li><i class="bi bi-calendar-check text-muted me-2"></i><strong>Inscrit :</strong> <?= formatDate($joueur['date_inscription']) ?></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Informations compte -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-person-gear me-2"></i>Informations de compte</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Prénom</label>
                        <div class="fw-semibold"><?= e($user['prenom']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Nom</label>
                        <div class="fw-semibold"><?= e($user['nom']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Email</label>
                        <div><?= e($user['email']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Rôle</label>
                        <div><?= roleBadge($user['role']) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Caractéristiques sportives -->
        <div class="card">
            <div class="card-header"><i class="bi bi-trophy me-2"></i>Caractéristiques sportives</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-light rounded-3">
                            <div class="fw-bold fs-4 text-success"><?= e($joueur['poste']) ?></div>
                            <div class="text-muted small">Poste</div>
                        </div>
                    </div>
                    <?php if ($joueur['numero_maillot']): ?>
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-light rounded-3">
                            <div class="fw-bold fs-4">#<?= e($joueur['numero_maillot']) ?></div>
                            <div class="text-muted small">Numéro de maillot</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($joueur['taille']): ?>
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-light rounded-3">
                            <div class="fw-bold fs-4"><?= $joueur['taille'] ?> cm</div>
                            <div class="text-muted small">Taille</div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($joueur['poids']): ?>
                    <div class="col-md-4 text-center">
                        <div class="p-3 bg-light rounded-3">
                            <div class="fw-bold fs-4"><?= $joueur['poids'] ?> kg</div>
                            <div class="text-muted small">Poids</div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Votre compte n'est pas encore lié à une fiche joueur. Contactez l'administrateur.
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
