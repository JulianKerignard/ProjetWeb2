<?php
/**
 * Point d'entrée principal de l'application
 * Gère le routage des requêtes vers les contrôleurs appropriés
 */

// Démarrage de la session (UNE SEULE FOIS)
session_start();

// Debug pour les problèmes de session
error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("========= DÉBUT DE LA REQUÊTE =========");
error_log("Session actuelle: " . print_r($_SESSION, true));
error_log("URL: " . $_SERVER['REQUEST_URI']);

// Charger les fichiers de configuration
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// CORRECTION: Définir le niveau d'erreur pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Système de routage simple
$page = isset($_GET['page']) ? $_GET['page'] : 'accueil';
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

// Router vers le contrôleur approprié
switch ($page) {
    case 'accueil':
        // Page d'accueil
        include ROOT_PATH . '/views/accueil.php';
        break;
    case 'mentions-legales':
        // Page mentions légales
        include ROOT_PATH . '/views/legal/mentions-legales.php';
        break;
    case 'auth':
        require_once 'controllers/AuthController.php';
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

    case 'entreprises':
        require_once 'controllers/EntrepriseController.php';
        $controller = new EntrepriseController();

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
            case 'evaluer':
                $controller->evaluer();
                break;
            case 'supprimer':
                $controller->supprimer();
                break;
            case 'detail':
                $controller->detail();
                break;
            default:
                $controller->index();
                break;
        }
        break;

    case 'offres':
        require_once 'controllers/OffreController.php';
        $controller = new OffreController();

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
            case 'detail':
                $controller->detail();
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

    case 'profile':
        require_once 'controllers/ProfileController.php';
        $controller = new ProfileController();

        switch ($action) {
            case 'edit':
                $controller->edit();
                break;
            default:
                $controller->index();
                break;
        }
        break;

    case 'admin':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();

        switch ($action) {
            case 'stats':
                $controller->stats();
                break;
            case 'permissions':
                $controller->permissions();
                break;
            case 'logs':
                $controller->logs();
                break;
            case 'maintenance':
                $controller->maintenance();
                break;
            case 'add-test-logs':
                $controller->addTestLogs();
                break;
            default:
                $controller->index();
                break;
        }
        break;

    case 'pilotes':
        require_once 'controllers/PiloteController.php';
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
            case 'detail':
                $controller->detail();
                break;
            default:
                $controller->index();
                break;
        }
        break;

    case 'etudiants':
        require_once 'controllers/EtudiantController.php';
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
            case 'detail':
                $controller->detail();
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
        require_once 'controllers/CandidatureController.php';
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
            case 'detail':
                $controller->detail();
                break;
            case 'supprimer':
                $controller->supprimer();
                break;
            default:
                $controller->mesCandidatures();
                break;
        }
        break;

    default:
        // Page 404
        include ROOT_PATH . '/views/404.php';
        break;
}