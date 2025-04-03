<?php
/**
 * Bootstrap principal de l'application
 * Orchestration du chargement des composants système avec initialisation séquentielle
 *
 * @version 2.1.0
 * @package LeBonPlan\Core
 */

// Protection contre l'accès direct au fichier
defined('SECURE_ACCESS') or define('SECURE_ACCESS', true);

// Définition du chemin racine absolu
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(dirname(__FILE__)));
}

// Initialisation du gestionnaire d'erreurs personnalisé avec support de journalisation
require_once ROOT_PATH . '/config/error_handler.php';

// Chargement des configurations principales
require_once ROOT_PATH . '/config/config.php';

// Chargement des fonctions utilitaires globales
require_once ROOT_PATH . '/includes/functions.php';

// Initialisation des constantes de rôle pour le système d'ACL
if (!defined('ROLE_ADMIN')) define('ROLE_ADMIN', 'admin');
if (!defined('ROLE_PILOTE')) define('ROLE_PILOTE', 'pilote');
if (!defined('ROLE_ETUDIANT')) define('ROLE_ETUDIANT', 'etudiant');

// Démarrage ou restauration de la session
if (session_status() === PHP_SESSION_NONE) {
    // Configuration avancée de session pour sécurité et performances
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);

    if (ENVIRONMENT === 'production') {
        ini_set('session.cookie_secure', 1);
    }

    // Optimisation du garbage collector en production
    if (ENVIRONMENT === 'production') {
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 1000);
    }

    session_name(SESSION_NAME);
    session_start();
}

// Paramètres d'en-tête pour sécurité et optimisation
if (ENVIRONMENT === 'production') {
    // Headers de sécurité en production
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');

    // Désactiver les rapports d'erreurs affichés
    ini_set('display_errors', 0);
} else {
    // En dev, on active tous les rapports d'erreurs
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Création du répertoire de cache si nécessaire
$cachePath = ROOT_PATH . '/cache';
if (!is_dir($cachePath)) {
    mkdir($cachePath, 0755, true);
}

// Vérification et création des répertoires critiques
$requiredDirectories = [
    ROOT_PATH . '/logs',
    ROOT_PATH . '/public/uploads',
    ROOT_PATH . '/public/uploads/cv',
    ROOT_PATH . '/cache',
    ROOT_PATH . '/cache/templates'
];

foreach ($requiredDirectories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Instanciation du gestionnaire de layout avec synchronisation des z-index
class LayoutManager {
    private static $instance = null;
    private $viewsPath;
    private $hasWidgetZone = false;
    private $contentType = 'default';
    private $extraSpacing = 0;

    private function __construct() {
        $this->viewsPath = ROOT_PATH . '/views';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setContentType($type) {
        $this->contentType = $type;

        // Ajustement automatique d'espacement selon le type de contenu
        if ($type === 'detail-view') {
            $this->extraSpacing = 100; // 100px d'espacement supplémentaire pour les vues détaillées
        }

        return $this;
    }

    public function setWidgetZone($hasWidgetZone) {
        $this->hasWidgetZone = $hasWidgetZone;

        // Espacement supplémentaire si des widgets sont présents
        if ($hasWidgetZone) {
            $this->extraSpacing += 50;
        }

        return $this;
    }

    public function getExtraSpacing() {
        return $this->extraSpacing;
    }

    public function renderView($viewPath, $data = []) {
        // Extraction des variables pour les rendre disponibles dans la vue
        extract($data);

        // Inclusion du template principal avec les ajustements
        include $this->viewsPath . '/' . $viewPath . '.php';

        // Si c'est une vue détaillée avec zone de widgets, ajouter l'élément d'espacement
        if ($this->hasWidgetZone && $this->contentType === 'detail-view') {
            echo '<div class="clearfix" style="margin-bottom: ' . $this->extraSpacing . 'px;"></div>';
        }
    }
}

// Enregistrement du gestionnaire de layout dans le contexte global
$GLOBALS['layoutManager'] = LayoutManager::getInstance();

// Fonction d'accès rapide au gestionnaire de layout
function getLayoutManager() {
    return $GLOBALS['layoutManager'];
}

// Installation du gestionnaire d'exceptions global
function exceptionHandler($exception) {
    // Journalisation de l'exception
    error_log(date('Y-m-d H:i:s') . " - Exception non capturée: " . $exception->getMessage() .
        " dans " . $exception->getFile() . " à la ligne " . $exception->getLine() . "\n" .
        $exception->getTraceAsString());

    // Affichage adapté selon l'environnement
    if (defined('ENVIRONMENT') && ENVIRONMENT !== 'development') {
        // Production: page d'erreur formattée
        $errorMessage = "Une erreur système est survenue. Référence: " . uniqid('ERR-');

        if (defined('ROOT_PATH') && file_exists(ROOT_PATH . '/views/500.php')) {
            $pageTitle = "Erreur interne";
            include ROOT_PATH . '/views/500.php';
        } else {
            // Fallback minimal
            echo '<div style="text-align:center;margin-top:50px;font-family:sans-serif">';
            echo '<h1>Une erreur est survenue</h1>';
            echo '<p>Nous nous excusons pour ce désagrément. L\'équipe technique a été informée du problème.</p>';
            echo '<p><a href="' . (defined('URL_ROOT') ? URL_ROOT : '/') . '">Retour à l\'accueil</a></p>';
            echo '</div>';
        }
    } else {
        // Développement: détails complets
        echo '<div style="border:1px solid #dc3545; padding:15px; margin:15px; background-color:#f8d7da; color:#721c24; font-family:monospace;">';
        echo '<h2>Exception non capturée</h2>';
        echo '<p><strong>Message:</strong> ' . $exception->getMessage() . '</p>';
        echo '<p><strong>Fichier:</strong> ' . $exception->getFile() . '</p>';
        echo '<p><strong>Ligne:</strong> ' . $exception->getLine() . '</p>';
        echo '<h3>Stack Trace:</h3>';
        echo '<pre>' . $exception->getTraceAsString() . '</pre>';
        echo '</div>';
    }

    exit(1);
}

// Enregistrement du gestionnaire d'exceptions
set_exception_handler('exceptionHandler');

// Optimisation des performances
if (ENVIRONMENT === 'production') {
    // Compression de sortie en production
    if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
        ini_set('zlib.output_compression', 'On');
        ini_set('zlib.output_compression_level', 5);
    }

    // Cache d'opcode en production si disponible
    if (function_exists('opcache_get_status')) {
        // OPcache est disponible et actif
        if (opcache_get_status() !== false) {
            ini_set('opcache.revalidate_freq', 60);
            ini_set('opcache.validate_timestamps', 0);
        }
    }
}

// Enregistrement des hooks d'arrêt pour nettoyage des ressources
register_shutdown_function(function() {
    // Fermeture des connexions persistantes éventuelles
    if (class_exists('Database') && method_exists('Database', 'closeAllConnections')) {
        Database::closeAllConnections();
    }

    // Nettoyage des fichiers temporaires si nécessaire
    $tempDir = ROOT_PATH . '/tmp';
    if (is_dir($tempDir)) {
        $files = glob($tempDir . '/*.tmp');
        $now = time();

        foreach ($files as $file) {
            if (filemtime($file) < ($now - 3600)) {
                @unlink($file);
            }
        }
    }
});

// Indicateur de fin de chargement du bootstrap
define('BOOTSTRAP_LOADED', true);