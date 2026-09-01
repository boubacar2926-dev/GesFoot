<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/functions.php';

startSession();
if (isLoggedIn()) redirect('/index.php');

$message = '';
$type    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfVerify();
    $email = trim($_POST['email'] ?? '');

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Veuillez entrer une adresse email valide.';
        $type    = 'danger';
    } else {
        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT id, nom, prenom FROM utilisateurs WHERE email = ? AND statut = 'actif'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Sécurité mode démo : le compte administrateur principal ne peut pas être
        // réinitialisé depuis cette page publique (même protection que admin/users/edit.php).
        if ($user && (int)$user['id'] === 1) {
            $user = null;
        }

        if ($user) {
            $tempPass = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $hash     = password_hash($tempPass, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?")
                ->execute([$hash, $user['id']]);

            $host    = $_SERVER['HTTP_HOST'] ?? '';
            $isLocal = in_array($host, ['localhost', '127.0.0.1', '::1'], true)
                    || str_starts_with($host, '127.')
                    || str_starts_with($host, '192.168.');

            if ($isLocal) {
                // Environnement local : log fichier, jamais affiché dans le navigateur
                $logDir = ROOT_PATH . '/logs';
                if (!is_dir($logDir)) mkdir($logDir, 0750, true);
                $line = sprintf("[%s] Reset %s (%s %s) — MDP temporaire : %s\n",
                    date('H:i:s'), $email, $user['prenom'], $user['nom'], $tempPass);
                file_put_contents($logDir . '/reset_' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
            } else {
                // Production : envoi par email
                $subject = 'Réinitialisation de votre mot de passe — AS Étoile de Dakar';
                $body    = "Bonjour {$user['prenom']} {$user['nom']},\n\n"
                         . "Votre mot de passe temporaire est : $tempPass\n\n"
                         . "Connectez-vous puis changez-le immédiatement depuis votre profil.\n\n"
                         . "AS Étoile de Dakar";
                $headers = "From: no-reply@etoiledeakar.sn\r\nContent-Type: text/plain; charset=UTF-8";
                @mail($email, $subject, $body, $headers);
            }
        }

        // Toujours le même message — empêche l'énumération d'emails
        $message = 'Si cet email est enregistré, un mot de passe temporaire vous a été envoyé.';
        $type    = 'success';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mot de passe oublié — Football Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">

<div class="login-card">
    <div class="login-header">
        <div class="mb-3">
            <span style="background:rgba(255,255,255,.15);display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;border-radius:16px;">
                <i class="bi bi-key-fill fs-2 text-warning"></i>
            </span>
        </div>
        <h4 class="fw-bold mb-1">Mot de passe oublié</h4>
        <p class="opacity-75 mb-0 small">Réinitialisation de votre accès</p>
    </div>

    <div class="login-body">

        <?php if ($message): ?>
            <div class="alert alert-<?= $type ?>">
                <?= e($message) ?>
            </div>
            <?php if ($type === 'success'): ?>
                <a href="<?= BASE_URL ?>/auth/login.php" class="btn btn-success w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Se connecter
                </a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/forgot.php" class="btn btn-outline-secondary w-100 mt-2">Réessayer</a>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-muted small mb-4">Entrez votre adresse email. Un mot de passe temporaire sera généré immédiatement.</p>

            <form method="POST" novalidate>
                <?= csrfField() ?>
                <div class="mb-4">
                    <label class="form-label">Adresse email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control border-start-0"
                               placeholder="vous@club.fr" required autofocus>
                    </div>
                </div>
                <button type="submit" class="btn btn-success w-100 py-2 fw-bold">
                    <i class="bi bi-arrow-clockwise me-1"></i> Générer un mot de passe
                </button>
            </form>
        <?php endif; ?>

        <div class="text-center mt-4 pt-3 border-top">
            <a href="<?= BASE_URL ?>/auth/login.php" class="text-muted small">
                <i class="bi bi-arrow-left"></i> Retour à la connexion
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
