<?php
/**
 * Script d'installation - à exécuter UNE SEULE FOIS
 * Accès : http://localhost/projet_fin/install.php
 * SUPPRIMER ce fichier après installation !
 */

$host     = 'localhost';
$user     = 'root';
$pass     = '';
$dbName   = 'football_club';
$charset  = 'utf8mb4';

$errors   = [];
$success  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminNom    = trim($_POST['admin_nom']    ?? 'Administrateur');
    $adminPrenom = trim($_POST['admin_prenom'] ?? 'Système');
    $adminEmail  = trim($_POST['admin_email']  ?? 'admin@club.fr');
    $adminPass   = $_POST['admin_pass']         ?? '';
    $clubNom     = trim($_POST['club_nom']      ?? 'Mon Club FC');
    $clubVille   = trim($_POST['club_ville']    ?? '');

    if (strlen($adminPass) < 6) {
        $errors[] = 'Le mot de passe admin doit contenir au moins 6 caractères.';
    }

    if (empty($errors)) {
        try {
            // Connexion sans sélectionner de DB
            $pdo = new PDO(
                "mysql:host=$host;charset=$charset",
                $user, $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Créer la base
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbName`");
            $success[] = "Base de données <strong>$dbName</strong> créée.";

            // Exécuter le schéma (sans les INSERT de données)
            $sql = "
            CREATE TABLE IF NOT EXISTS clubs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nom VARCHAR(150) NOT NULL,
                logo VARCHAR(255),
                adresse TEXT,
                ville VARCHAR(100),
                telephone VARCHAR(20),
                email VARCHAR(150),
                site_web VARCHAR(255),
                annee_fondation YEAR,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS utilisateurs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nom VARCHAR(100) NOT NULL,
                prenom VARCHAR(100) NOT NULL,
                email VARCHAR(150) UNIQUE NOT NULL,
                telephone VARCHAR(20),
                mot_de_passe VARCHAR(255) NOT NULL,
                role ENUM('admin','coach','staff','joueur') NOT NULL DEFAULT 'joueur',
                statut ENUM('actif','inactif') NOT NULL DEFAULT 'actif',
                photo VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS joueurs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                utilisateur_id INT,
                club_id INT DEFAULT 1,
                nom VARCHAR(100) NOT NULL,
                prenom VARCHAR(100) NOT NULL,
                date_naissance DATE,
                nationalite VARCHAR(100),
                taille DECIMAL(4,1),
                poids DECIMAL(5,1),
                poste ENUM('Gardien','Défenseur','Milieu','Attaquant') NOT NULL,
                numero_maillot TINYINT UNSIGNED,
                photo VARCHAR(255),
                date_inscription DATE DEFAULT (CURRENT_DATE),
                statut ENUM('actif','blessé','suspendu','inactif') NOT NULL DEFAULT 'actif',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
                FOREIGN KEY (club_id)        REFERENCES clubs(id)        ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS competitions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                nom VARCHAR(150) NOT NULL,
                type ENUM('Championnat','Coupe','Amical','Tournoi') DEFAULT 'Championnat',
                saison VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS matchs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                competition_id INT,
                date_match DATE NOT NULL,
                heure_match TIME,
                stade VARCHAR(200),
                adversaire VARCHAR(150) NOT NULL,
                domicile_exterieur ENUM('Domicile','Extérieur') DEFAULT 'Domicile',
                score_equipe TINYINT UNSIGNED DEFAULT 0,
                score_adverse TINYINT UNSIGNED DEFAULT 0,
                statut ENUM('Programmé','En cours','Terminé','Annulé','Reporté') DEFAULT 'Programmé',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS convocations (
                id INT PRIMARY KEY AUTO_INCREMENT,
                match_id INT NOT NULL,
                date_convocation DATE,
                lieu_rdv VARCHAR(200),
                heure_rdv TIME,
                notes TEXT,
                statut ENUM('Brouillon','Publiée','Annulée') DEFAULT 'Brouillon',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (match_id) REFERENCES matchs(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS convocation_joueur (
                id INT PRIMARY KEY AUTO_INCREMENT,
                convocation_id INT NOT NULL,
                joueur_id INT NOT NULL,
                statut ENUM('Convoqué','Absent','Blessé') DEFAULT 'Convoqué',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_conv_joueur (convocation_id, joueur_id),
                FOREIGN KEY (convocation_id) REFERENCES convocations(id) ON DELETE CASCADE,
                FOREIGN KEY (joueur_id)      REFERENCES joueurs(id)      ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS compositions (
                id INT PRIMARY KEY AUTO_INCREMENT,
                match_id INT NOT NULL,
                joueur_id INT NOT NULL,
                type_joueur ENUM('Titulaire','Remplaçant') NOT NULL DEFAULT 'Titulaire',
                poste_match VARCHAR(50),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_comp (match_id, joueur_id),
                FOREIGN KEY (match_id)  REFERENCES matchs(id)  ON DELETE CASCADE,
                FOREIGN KEY (joueur_id) REFERENCES joueurs(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

            CREATE TABLE IF NOT EXISTS statistiques (
                id INT PRIMARY KEY AUTO_INCREMENT,
                joueur_id INT NOT NULL,
                match_id INT NOT NULL,
                minutes_jouees SMALLINT UNSIGNED DEFAULT 0,
                buts TINYINT UNSIGNED DEFAULT 0,
                passes_decisives TINYINT UNSIGNED DEFAULT 0,
                cartons_jaunes TINYINT UNSIGNED DEFAULT 0,
                cartons_rouges TINYINT UNSIGNED DEFAULT 0,
                note_match DECIMAL(3,1),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY uk_stat (joueur_id, match_id),
                FOREIGN KEY (joueur_id) REFERENCES joueurs(id) ON DELETE CASCADE,
                FOREIGN KEY (match_id)  REFERENCES matchs(id)  ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ";

            // Exécuter chaque CREATE séparément
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                if ($stmt) $pdo->exec($stmt);
            }
            $success[] = 'Tables créées avec succès.';

            // Insérer le club
            $stmt = $pdo->prepare("INSERT INTO clubs (nom, ville) VALUES (?, ?) ON DUPLICATE KEY UPDATE nom=nom");
            $stmt->execute([$clubNom, $clubVille]);
            $success[] = "Club <strong>$clubNom</strong> enregistré.";

            // Insérer l'admin
            $hash = password_hash($adminPass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
                VALUES (?, ?, ?, ?, 'admin')
                ON DUPLICATE KEY UPDATE mot_de_passe = VALUES(mot_de_passe)
            ");
            $stmt->execute([$adminNom, $adminPrenom, $adminEmail, $hash]);
            $success[] = "Compte administrateur <strong>$adminEmail</strong> créé.";

            // Insérer compétitions par défaut
            $pdo->exec("INSERT IGNORE INTO competitions (nom, type, saison) VALUES
                ('Championnat Régional', 'Championnat', '2025-2026'),
                ('Coupe Régionale',      'Coupe',       '2025-2026')
            ");
            $success[] = 'Données initiales insérées.';

            // Créer le dossier uploads
            $uploadPath = __DIR__ . '/assets/uploads';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            $success[] = 'Dossier uploads créé.';

        } catch (PDOException $e) {
            $errors[] = 'Erreur base de données : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installation - Football Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f0f4f8; }
        .install-card { max-width: 600px; margin: 60px auto; }
        .logo-header { background: linear-gradient(135deg, #1a3a2a, #2d6a4f); color: #fff; padding: 30px; border-radius: 12px 12px 0 0; text-align: center; }
    </style>
</head>
<body>
<div class="install-card">
    <div class="card shadow-lg border-0">
        <div class="logo-header">
            <i class="bi bi-shield-fill-check fs-1"></i>
            <h2 class="mt-2 mb-0">Installation</h2>
            <p class="mb-0 opacity-75">Plateforme Football Club</p>
        </div>
        <div class="card-body p-4">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?= $e ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <h5><i class="bi bi-check-circle-fill"></i> Installation réussie !</h5>
                    <ul class="mb-2">
                        <?php foreach ($success as $s): ?>
                            <li><?= $s ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <strong>Supprimez ce fichier</strong> (<code>install.php</code>) avant de passer en production !
                </div>
                <a href="auth/login.php" class="btn btn-success w-100">
                    <i class="bi bi-box-arrow-in-right"></i> Aller à la connexion
                </a>
            <?php else: ?>
                <p class="text-muted mb-4">Ce script va créer la base de données, les tables et le compte administrateur.</p>

                <form method="POST">
                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-building"></i> Informations du Club</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-8">
                            <label class="form-label">Nom du club <span class="text-danger">*</span></label>
                            <input type="text" name="club_nom" class="form-control" value="Mon Club FC" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label">Ville</label>
                            <input type="text" name="club_ville" class="form-control" placeholder="Paris">
                        </div>
                    </div>

                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-person-badge"></i> Compte Administrateur</h6>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Nom</label>
                            <input type="text" name="admin_nom" class="form-control" value="Administrateur">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Prénom</label>
                            <input type="text" name="admin_prenom" class="form-control" value="Système">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="admin_email" class="form-control" value="admin@club.fr" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Mot de passe <span class="text-danger">*</span></label>
                            <input type="password" name="admin_pass" class="form-control" placeholder="Min. 6 caractères" required>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-rocket-takeoff"></i> Installer la plateforme
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
