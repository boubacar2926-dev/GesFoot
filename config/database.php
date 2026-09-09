<?php
// ============================================================
// Configuration globale de l'application (Multi-environnement)
// ============================================================

// Détection automatique de l'environnement (Local vs Render)
$isRender = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'onrender.com') !== false;

if ($isRender) {
    // Configuration pour Render / Aiven (Production)
    define('DB_HOST',     getenv('DB_HOST')     ?: 'votre-hote-aiven.aivencloud.com'); // Mettez l'hôte Aiven si pas en variable d'env
    define('DB_PORT',     getenv('DB_PORT')     ?: '12345');                        // Port Aiven
    define('DB_NAME',     getenv('DB_NAME')     ?: 'defaultdb');                    // Nom de la bdd Aiven
    define('DB_USER',     getenv('DB_USER')     ?: 'avnadmin');                     // User Aiven
    define('DB_PASS',     getenv('DB_PASS')     ?: 'votre-mot-de-passe');           // Password Aiven
    
    // URL dynamique pour Render
    define('BASE_URL',    'https://' . $_SERVER['HTTP_HOST']);
} else {
    // Configuration pour XAMPP (Local)
    define('DB_HOST',     'localhost');
    define('DB_PORT',     '3307');
    define('DB_NAME',     'football_club');
    define('DB_USER',     'root');
    define('DB_PASS',     '');
    
    // URL locale
    define('BASE_URL',    'http://localhost/GesFoot');
}

define('DB_CHARSET', 'utf8mb4');

defined('ROOT_PATH') || define('ROOT_PATH', realpath(__DIR__ . '/..'));
define('UPLOAD_PATH', ROOT_PATH . '/assets/uploads');
define('UPLOAD_URL',  BASE_URL  . '/assets/uploads');

// Singleton PDO
function getPDO(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                // Certains hébergeurs MySQL managés (ex. Aiven) n'appliquent pas de façon
                // fiable le paramètre "charset" du DSN pour les requêtes préparées natives ;
                // on le force explicitement pour éviter les erreurs 1366 sur les accents.
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . DB_CHARSET,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('<h3>Erreur de connexion à la base de données.</h3><p>' . htmlspecialchars($e->getMessage()) . '</p>');
        }
    }
    return $pdo;
}