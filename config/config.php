<?php
/**
 * Configuration globale de l'application
 * Ce fichier définit toutes les constantes et paramètres essentiels au fonctionnement
 * de l'application de gestion des stages.
 */

// Détection de l'environnement d'exécution
// Options: 'development', 'testing', 'production'
define('ENVIRONMENT', 'development');

// Configuration des erreurs selon l'environnement
switch (ENVIRONMENT) {
    case 'development':
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        break;
    case 'testing':
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
        ini_set('display_errors', 1);
        break;
    case 'production':
        error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
        ini_set('display_errors', 0);
        break;
    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'Environnement d\'application non valide.';
        exit(1);
}

// Informations générales de l'application
define('SITE_NAME', 'LeBonPlan');
define('SITE_VERSION', '1.0.0');
define('SITE_AUTHOR', 'Web4All');

// Configuration des chemins absolus
define('ROOT_PATH', dirname(__DIR__)); // Chemin racine de l'application
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Création des répertoires nécessaires s'ils n'existent pas
$required_dirs = [LOGS_PATH, UPLOADS_PATH];
foreach ($required_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// URL de base de l'application (pour les liens)
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
$base_url .= "://" . $_SERVER['HTTP_HOST'];
$base_url .= dirname($_SERVER['SCRIPT_NAME']);
define('URL_ROOT', rtrim($base_url, '/'));

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_stages');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', 3306);
define('DB_PREFIX', ''); // Préfixe de tables optionnel

// Configuration de la session
define('SESSION_NAME', 'STAGEID');
define('SESSION_LIFETIME', 7200); // 2 heures en secondes
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', '');
define('SESSION_SECURE', false);
define('SESSION_HTTPONLY', true);

// Configuration de la pagination
define('ITEMS_PER_PAGE', 10);
define('MAX_PAGINATION_LINKS', 5);

// Configuration des téléchargements
define('UPLOAD_DIR', ROOT_PATH . '/public/uploads/');
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 Mo en octets

// Définition des rôles utilisateurs - CRITIQUE pour l'application
define('ROLE_ADMIN', 'admin');
define('ROLE_PILOTE', 'pilote');
define('ROLE_ETUDIANT', 'etudiant'); // Doit correspondre exactement à la valeur en session

// Configuration des emails
define('MAIL_FROM', 'noreply@example.com');
define('MAIL_FROM_NAME', 'Gestion des Stages CESI');
define('MAIL_ADMIN', 'admin@example.com');

// Configuration de sécurité
define('HASH_COST', 10); // Coût de hachage pour les mots de passe (bcrypt)
define('TOKEN_LIFETIME', 3600); // Durée de vie des tokens (1 heure)
define('CSRF_TOKEN_NAME', 'csrf_token');

// Configuration des images
define('MAX_IMAGE_WIDTH', 1200);
define('MAX_IMAGE_HEIGHT', 1200);
define('IMAGE_QUALITY', 80);

// Fonctions utilitaires de configuration
function get_config($key, $default = null) {
    return defined($key) ? constant($key) : $default;
}

// Déterminer si l'application est en mode maintenance
define('MAINTENANCE_MODE', false);