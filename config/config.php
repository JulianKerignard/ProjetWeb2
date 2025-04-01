<?php
/**
 * Configuration générale de l'application avec détection d'environnement
 */

// Définition de l'environnement de développement
define('ENVIRONMENT', 'development');

// Détection dynamique de l'environnement d'exécution
$isPhpStormServer = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '63342') !== false;
$serverScriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
$projectFolder = '/ProjetWeb2'; // Dossier du projet

// Détection du chemin racine du projet
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Configuration de l'URL de base selon l'environnement
if ($isPhpStormServer) {
    // Configuration pour serveur intégré PhpStorm
    define('USING_APACHE', false);
    define('URL_ROOT', 'http://' . $_SERVER['HTTP_HOST'] . substr($serverScriptName, 0, strpos($serverScriptName, '/index.php')));
} else {
    // Configuration pour serveur Apache standard
    define('USING_APACHE', true);
    define('URL_ROOT', 'http://localhost' . $projectFolder);
}

// Définition des chemins applicatifs
define('VIEWS_PATH', ROOT_PATH . '/views');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Configuration de l'application
define('SITE_NAME', 'Stages Web4All');

// Définition des rôles utilisateurs
define('ROLE_ADMIN', 'admin');
define('ROLE_PILOTE', 'pilote');
define('ROLE_ETUDIANT', 'etudiant');

// Configuration des uploads
define('UPLOAD_DIR', PUBLIC_PATH . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 Mo
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx']);

// Configuration de pagination
define('ITEMS_PER_PAGE', 10);

// Journalisation des informations de configuration (en développement uniquement)
if (ENVIRONMENT === 'development') {
    error_log('Environment: ' . ($isPhpStormServer ? 'PhpStorm Server' : 'Apache Server'));
    error_log('URL_ROOT: ' . URL_ROOT);
    error_log('ROOT_PATH: ' . ROOT_PATH);
}