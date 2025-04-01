<?php
// bootstrap.php - Système d'amorçage central
if (!defined('APP_INITIALIZED')) {
    define('APP_INITIALIZED', true);

    // Initialisation session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Detection du mode d'exécution (direct IDE vs routeur HTTP)
    $isDirectAccess = !isset($GLOBALS['ROUTER_INITIALIZED']);

    // Configuration chemins absolus
    if (!defined('ROOT_PATH')) {
        define('ROOT_PATH', realpath(dirname(__FILE__)));
    }

    // Chargement des dépendances essentielles
    require_once ROOT_PATH . '/config/config.php';
    require_once ROOT_PATH . '/includes/functions.php';

    // Journal de diagnostic en environnement de développement
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        error_log('Bootstrap initialized | Mode: ' . ($isDirectAccess ? 'Direct IDE' : 'Router') .
            ' | Path: ' . ROOT_PATH);
    }

    // Configuration diagnostique
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Détection d'environnement PhpStorm
    $isPhpStormServer = isset($_SERVER['SERVER_SOFTWARE']) &&
        strpos($_SERVER['SERVER_SOFTWARE'], 'Development Server') !== false;

    if ($isPhpStormServer) {
        define('IS_PHPSTORM_SERVER', true);
    }
}