<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

startSession();

if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();

    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email && $pass) {
        $pdo = getPDO();

        // Créer la table de suivi si elle n'existe pas encore
        $pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id           INT AUTO_INCREMENT PRIMARY KEY,
            email        VARCHAR(255) NOT NULL,
            attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email_time (email, attempted_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Compter les tentatives échouées sur les 15 dernières minutes
        $stmtCount = $pdo->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE email = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)"
        );
        $stmtCount->execute([$email]);
        $attempts = (int)$stmtCount->fetchColumn();

        if ($attempts >= 5) {
            $error = 'Trop de tentatives échouées. Compte temporairement bloqué pendant 15 minutes.';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND statut = 'actif' LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($pass, $user['mot_de_passe'])) {
                // Connexion réussie : supprimer l'historique des tentatives
                $pdo->prepare("DELETE FROM login_attempts WHERE email = ?")->execute([$email]);
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['user']    = [
                    'id'     => $user['id'],
                    'nom'    => $user['nom'],
                    'prenom' => $user['prenom'],
                    'email'  => $user['email'],
                    'role'   => $user['role'],
                    'photo'  => $user['photo'],
                ];
                redirect('/index.php');
            } else {
                // Échec : enregistrer la tentative
                $pdo->prepare("INSERT INTO login_attempts (email) VALUES (?)")->execute([$email]);
                $remaining = 4 - $attempts; // après cet échec, il reste $remaining tentatives
                if ($remaining <= 0) {
                    $error = 'Trop de tentatives échouées. Compte temporairement bloqué pendant 15 minutes.';
                } else {
                    $warn  = $remaining <= 2 ? " ($remaining tentative(s) restante(s) avant blocage)" : '';
                    $error = 'Email ou mot de passe incorrect.' . $warn;
                }
            }
        }
    } else {
        $error = 'Veuillez remplir tous les champs.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — Football Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">

<div class="login-card">
    <div class="login-header">
        <div class="mb-3">
            <span style="background:rgba(255,255,255,.15);display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:16px;">
                <i class="bi bi-trophy-fill fs-2 text-warning"></i>
            </span>
        </div>
        <h4 class="fw-bold mb-1">Football Club</h4>
        <p class="opacity-75 mb-0 small">Plateforme de gestion d'équipe</p>
    </div>

    <div class="login-body">
        <h5 class="fw-bold mb-1">Connexion</h5>
        <p class="text-muted small mb-4">Entrez vos identifiants pour accéder à votre espace.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
                <i class="bi bi-x-circle-fill"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <?= csrfField() ?>

            <div class="mb-3">
                <label class="form-label">Adresse email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                    <input type="email" name="email" class="form-control border-start-0"
                           placeholder="vous@club.fr"
                           value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" name="password" id="passwordInput"
                           class="form-control border-start-0 border-end-0"
                           placeholder="••••••••" required>
                    <button type="button" class="input-group-text bg-light border-start-0" id="togglePwd">
                        <i class="bi bi-eye text-muted" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
            </button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <a href="<?= BASE_URL ?>/auth/forgot.php" class="text-muted small">
                <i class="bi bi-key"></i> Mot de passe oublié ?
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('togglePwd')?.addEventListener('click', () => {
        const inp  = document.getElementById('passwordInput');
        const icon = document.getElementById('eyeIcon');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.className = 'bi bi-eye-slash text-muted';
        } else {
            inp.type = 'password';
            icon.className = 'bi bi-eye text-muted';
        }
    });
</script>
</body>
</html>
