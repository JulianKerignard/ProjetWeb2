<?php
/**
 * Point d'entrée principal avec dispatcher MVC optimisé
 * Compatible avec environnement de développement PhpStorm
 */

// Activation du reporting d'erreurs en mode développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialisation de la session
session_start();

// Initialisation du mécanisme de routage
try {
    // Chargement de la configuration d'environnement
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/includes/functions.php';

    // Récupération des paramètres de routage
    $page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_SANITIZE_SPECIAL_CHARS) : 'accueil';
    $action = isset($_GET['action']) ? filter_var($_GET['action'], FILTER_SANITIZE_SPECIAL_CHARS) : 'index';

    // Journalisation de la requête en mode développement
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        devLog("Request: page={$page}, action={$action}", 'request');
        devLog("Server: " . json_encode($_SERVER), 'server');
    }

    // Définition de la structure de routage MVC
    $routes = [
        'accueil' => [
            'controller' => null, // Page statique
            'view' => 'views/accueil.php'
        ],
        'auth' => [
            'controller' => 'AuthController',
            'actions' => ['login', 'logout', 'register', 'index']
        ],
        'entreprises' => [
            'controller' => 'EntrepriseController',
            'actions' => ['index', 'rechercher', 'creer', 'modifier', 'evaluer', 'supprimer']
        ],
        'offres' => [
            'controller' => 'OffreController',
            'actions' => ['index', 'rechercher', 'creer', 'modifier', 'supprimer', 'statistiques']
        ],
        'pilotes' => [
            'controller' => 'PiloteController',
            'actions' => ['index', 'rechercher', 'creer', 'modifier', 'supprimer']
        ],
        'etudiants' => [
            'controller' => 'EtudiantController',
            'actions' => ['index', 'rechercher', 'creer', 'modifier', 'supprimer', 'statistiques']
        ],
        'candidatures' => [
            'controller' => 'CandidatureController',
            'actions' => ['index', 'ajouter-wishlist', 'retirer-wishlist', 'afficher-wishlist', 'postuler', 'mes-candidatures']
        ]
    ];

    // Traitement de la requête selon le routage MVC
    if (!isset($routes[$page])) {
        // Route non trouvée -> page 404
        header("HTTP/1.0 404 Not Found");
        viewInclude('views/404.php', ['pageTitle' => 'Page non trouvée']);
    }
    elseif ($routes[$page]['controller'] === null) {
        // Page statique sans contrôleur
        viewInclude($routes[$page]['view'], ['pageTitle' => ucfirst($page)]);
    }
    else {
        // Résolution de contrôleur dynamique
        $controllerName = $routes[$page]['controller'];
        $controllerFile = __DIR__ . "/controllers/{$controllerName}.php";

        // Vérification de l'existence du contrôleur
        if (!file_exists($controllerFile)) {
            throw new Exception("Contrôleur non trouvé: {$controllerName}.php");
        }

        // Chargement et instanciation du contrôleur
        require_once $controllerFile;
        $controller = new $controllerName();

        // Vérification et exécution de l'action
        if (!in_array($action, $routes[$page]['actions'])) {
            $action = 'index'; // Action par défaut si invalide
        }

        // Exécution de l'action du contrôleur
        if (!method_exists($controller, $action)) {
            throw new Exception("Action non supportée: {$action} dans {$controllerName}");
        }

        $controller->$action();
    }
}
catch (PDOException $e) {
    // Gestion des erreurs de base de données
    devLog("Erreur de base de données: " . $e->getMessage(), 'error');

    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo "<div style='background-color: #FFEBEE; color: #C62828; padding: 20px; margin: 20px; border-radius: 5px;'>";
        echo "<h2>Erreur de base de données</h2>";
        echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Fichier:</strong> " . $e->getFile() . " (ligne " . $e->getLine() . ")</p>";
        echo "</div>";
    } else {
        viewInclude('views/500.php', ['pageTitle' => 'Erreur serveur', 'errorMessage' => 'Un problème est survenu avec la base de données.']);
    }
}
catch (Exception $e) {
    // Gestion des autres exceptions
    devLog("Exception: " . $e->getMessage(), 'error');

    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo "<div style='background-color: #FFF8E1; color: #F57F17; padding: 20px; margin: 20px; border-radius: 5px;'>";
        echo "<h2>Exception non gérée</h2>";
        echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Fichier:</strong> " . $e->getFile() . " (ligne " . $e->getLine() . ")</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        viewInclude('views/500.php', ['pageTitle' => 'Erreur serveur', 'errorMessage' => 'Une erreur inattendue est survenue.']);
    }
}