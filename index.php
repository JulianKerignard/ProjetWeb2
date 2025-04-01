<?php
/**
 * Point d'entrée principal MVC avec mécanisme de routage
 *
 * Architecture optimisée pour compatibilité IDE PhpStorm et déploiement
 * sur environnements de production Apache/Nginx
 */

// Marqueur d'initialisation via routeur
$GLOBALS['ROUTER_INITIALIZED'] = true;

// Chargement du système d'amorçage
require_once __DIR__ . '/bootstrap.php';

// Journalisation de la requête
error_log('REQUEST: ' . $_SERVER['REQUEST_URI'] . ' | GET: ' . json_encode($_GET));

// Système de routage principal
try {
    // Récupération paramètres de routage
    $page = isset($_GET['page']) ? filter_var($_GET['page'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'accueil';
    $action = isset($_GET['action']) ? filter_var($_GET['action'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : 'index';

    // Dispatching vers le contrôleur approprié
    switch ($page) {
        case 'accueil':
            // Page d'accueil
            include ROOT_PATH . '/views/accueil.php';
            break;

        case 'auth':
            require_once ROOT_PATH . '/controllers/AuthController.php';
            $controller = new AuthController();

            switch ($action) {
                case 'login':
                    $controller->login();
                    break;
                case 'logout':
                    $controller->logout();
                    break;
                case 'register':
                    $controller->register();
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        case 'offres':
            require_once ROOT_PATH . '/controllers/OffreController.php';
            $controller = new OffreController();

            switch ($action) {
                case 'rechercher':
                    $controller->rechercher();
                    break;
                case 'detail':
                    $controller->detail();
                    break;
                case 'creer':
                    $controller->creer();
                    break;
                case 'modifier':
                    $controller->modifier();
                    break;
                case 'supprimer':
                    $controller->supprimer();
                    break;
                case 'statistiques':
                    $controller->statistiques();
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        case 'entreprises':
            require_once ROOT_PATH . '/controllers/EntrepriseController.php';
            $controller = new EntrepriseController();

            switch ($action) {
                case 'rechercher':
                    $controller->rechercher();
                    break;
                case 'detail':
                    $controller->detail();
                    break;
                case 'creer':
                    $controller->creer();
                    break;
                case 'modifier':
                    $controller->modifier();
                    break;
                case 'evaluer':
                    $controller->evaluer();
                    break;
                case 'supprimer':
                    $controller->supprimer();
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        case 'pilotes':
            require_once ROOT_PATH . '/controllers/PiloteController.php';
            $controller = new PiloteController();

            switch ($action) {
                case 'rechercher':
                    $controller->rechercher();
                    break;
                case 'creer':
                    $controller->creer();
                    break;
                case 'modifier':
                    $controller->modifier();
                    break;
                case 'supprimer':
                    $controller->supprimer();
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        case 'etudiants':
            require_once ROOT_PATH . '/controllers/EtudiantController.php';
            $controller = new EtudiantController();

            switch ($action) {
                case 'rechercher':
                    $controller->rechercher();
                    break;
                case 'creer':
                    $controller->creer();
                    break;
                case 'modifier':
                    $controller->modifier();
                    break;
                case 'supprimer':
                    $controller->supprimer();
                    break;
                case 'statistiques':
                    $controller->statistiques();
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        case 'candidatures':
            require_once ROOT_PATH . '/controllers/CandidatureController.php';
            $controller = new CandidatureController();

            switch ($action) {
                case 'ajouter-wishlist':
                    $controller->ajouterWishlist();
                    break;
                case 'retirer-wishlist':
                    $controller->retirerWishlist();
                    break;
                case 'afficher-wishlist':
                    $controller->afficherWishlist();
                    break;
                case 'postuler':
                    $controller->postuler();
                    break;
                case 'mes-candidatures':
                    $controller->mesCandidatures();
                    break;
                default:
                    $controller->index();
                    break;
            }
            break;

        default:
            // Page 404
            header("HTTP/1.0 404 Not Found");
            include ROOT_PATH . '/views/404.php';
            break;
    }
} catch (Exception $e) {
    // Capture et journalisation des exceptions
    error_log('EXCEPTION: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());

    // Affichage si en mode développement
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
        echo '<div style="background-color:#f8d7da; color:#721c24; padding:15px; margin:15px; border:1px solid #f5c6cb; border-radius:5px">';
        echo '<h3>Erreur système</h3>';
        echo '<p><strong>Message:</strong> ' . $e->getMessage() . '</p>';
        echo '<p><strong>Fichier:</strong> ' . $e->getFile() . ' (ligne ' . $e->getLine() . ')</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
        echo '</div>';
    } else {
        // En production, afficher une page d'erreur générique
        include ROOT_PATH . '/views/500.php';
    }
}