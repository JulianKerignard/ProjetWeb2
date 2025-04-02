<?php
/**
 * Configuration du gestionnaire d'erreurs personnalisé
 * Ce fichier permet de gérer les erreurs de façon élégante
 */

// Désactiver l'affichage des erreurs par défaut (à commenter en développement)
// ini_set('display_errors', 0);

// Activer la journalisation des erreurs
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/logs/error.log');

// Vérifier si le dossier de logs existe, sinon le créer
if (!file_exists(ROOT_PATH . '/logs')) {
    mkdir(ROOT_PATH . '/logs', 0755, true);
}

// Gestionnaire d'erreurs personnalisé
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Ne pas journaliser les erreurs désactivées par error_reporting
    if (!(error_reporting() & $errno)) {
        return false;
    }

    // Construire le message d'erreur
    $error_message = date('Y-m-d H:i:s') . " - Erreur: [$errno] $errstr dans $errfile à la ligne $errline\n";

    // Journaliser l'erreur
    error_log($error_message);

    // En mode développement, afficher l'erreur
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo "<div style='border:1px solid #dc3545; padding:10px; margin:10px; background-color:#f8d7da; color:#721c24;'>";
        echo "<h3>Erreur détectée:</h3>";
        echo "<p><strong>Type:</strong> " . getErrorType($errno) . "</p>";
        echo "<p><strong>Message:</strong> $errstr</p>";
        echo "<p><strong>Fichier:</strong> $errfile</p>";
        echo "<p><strong>Ligne:</strong> $errline</p>";
        echo "</div>";
    }

    // Pour les erreurs fatales, terminer le script
    if ($errno == E_ERROR || $errno == E_USER_ERROR) {
        exit(1);
    }

    // Permet au gestionnaire d'erreurs PHP standard de s'exécuter
    return false;
}

// Fonction pour obtenir le type d'erreur en texte
function getErrorType($errno) {
    switch ($errno) {
        case E_ERROR:
        case E_USER_ERROR:
            return 'Erreur fatale';
        case E_WARNING:
        case E_USER_WARNING:
            return 'Avertissement';
        case E_NOTICE:
        case E_USER_NOTICE:
            return 'Notice';
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            return 'Déprécié';
        default:
            return 'Erreur inconnue';
    }
}

// Définir le gestionnaire d'erreurs personnalisé
set_error_handler("customErrorHandler");

// Gestionnaire d'exceptions global
function exceptionHandler($exception) {
    // Journaliser l'exception
    error_log(date('Y-m-d H:i:s') . " - Exception non capturée: " . $exception->getMessage() .
        " dans " . $exception->getFile() . " à la ligne " . $exception->getLine() . "\n" .
        $exception->getTraceAsString());

    // Rediriger vers une page d'erreur élégante en production
    if (defined('ENVIRONMENT') && ENVIRONMENT !== 'development') {
        if (defined('ROOT_PATH') && file_exists(ROOT_PATH . '/views/500.php')) {
            include ROOT_PATH . '/views/500.php';
        } else {
            echo "<h1>Une erreur est survenue</h1>";
            echo "<p>Nous nous excusons pour ce désagrément. L'équipe technique a été informée du problème.</p>";
        }
    } else {
        // En mode développement, afficher les détails de l'exception
        echo "<div style='border:1px solid #dc3545; padding:10px; margin:10px; background-color:#f8d7da; color:#721c24;'>";
        echo "<h2>Exception non capturée</h2>";
        echo "<p><strong>Message:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>Fichier:</strong> " . $exception->getFile() . "</p>";
        echo "<p><strong>Ligne:</strong> " . $exception->getLine() . "</p>";
        echo "<h3>Stack Trace:</h3>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    }

    exit(1);
}

// Définir le gestionnaire d'exceptions
set_exception_handler("exceptionHandler");