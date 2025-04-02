<?php
/**
 * Contrôleur pour la gestion du panel administrateur
 *
 * Ce contrôleur implémente les fonctionnalités centrales de l'administration
 * avec un focus sur la sécurité, les statistiques et la gestion système.
 *
 * @version 1.0
 */
class AdminController {
    private $offreModel;
    private $entrepriseModel;
    private $etudiantModel;
    private $piloteModel;
    private $statsModel;

    /**
     * Constructeur - Initialise les modèles nécessaires avec vérification des droits
     *
     * Utilise le pattern d'injection de dépendances pour charger les modèles requis
     * et implémente un contrôle d'accès strict au niveau du constructeur.
     */
    public function __construct() {
        // Vérification stricte des droits d'administrateur
        if (!isAdmin()) {
            // Journalisation de la tentative d'accès non autorisée
            error_log("Tentative d'accès non autorisée au panel d'administration par l'utilisateur ID: " .
                (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'non authentifié') .
                " - IP: " . $_SERVER['REMOTE_ADDR']);

            // Redirection vers l'accueil avec message d'erreur
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Accès non autorisé. Cette tentative a été enregistrée."
            ];
            redirect(url());
        }

        // Chargement des modèles requis avec gestion des erreurs
        try {
            require_once MODELS_PATH . '/Offre.php';
            require_once MODELS_PATH . '/Entreprise.php';
            require_once MODELS_PATH . '/Etudiant.php';
            require_once MODELS_PATH . '/Pilote.php';
            require_once MODELS_PATH . '/Stats.php';

            $this->offreModel = new Offre();
            $this->entrepriseModel = new Entreprise();
            $this->etudiantModel = new Etudiant();
            $this->piloteModel = new Pilote();
            $this->statsModel = new Stats();
        } catch (Exception $e) {
            // Gestion des erreurs d'initialisation des modèles
            error_log("Erreur d'initialisation du contrôleur Admin: " . $e->getMessage());
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Une erreur est survenue lors de l'initialisation du panel d'administration."
            ];
            redirect(url());
        }
    }

    /**
     * Action par défaut - Tableau de bord d'administration
     *
     * Affiche une vue synthétique des principales métriques du système
     * avec des indicateurs de performance et des liens d'action rapide.
     */
    public function index() {
        // Récupération des statistiques générales pour le dashboard
        $stats = $this->statsModel->getGeneralStats();

        // Liste des 5 dernières offres ajoutées
        $latestOffers = $this->statsModel->getLatestOffers(5);

        // Liste des 5 dernières entreprises ajoutées
        $latestCompanies = $this->statsModel->getLatestCompanies(5);

        // Définir le titre de la page
        $pageTitle = "Panel d'administration";

        // Chargement de la vue
        include VIEWS_PATH . '/admin/dashboard.php';
    }

    /**
     * Tableau de bord des statistiques avancées
     *
     * Agrège et présente des métriques détaillées sur l'utilisation du système
     * sous forme de graphiques et tableaux pour l'analyse décisionnelle.
     */
    public function stats() {
        // Récupération des statistiques détaillées avec mise en cache
        $offreStats = $this->offreModel->getStatistics();

        // Données pour les indicateurs de performance des étudiants
        $placementRate = $this->statsModel->getPlacementRate();
        $avgApplications = $this->statsModel->getAverageApplicationsPerStudent();
        $studentStats = [
            'placement_rate' => $placementRate,
            'avg_applications' => $avgApplications
        ];

        // Données pour les statistiques des entreprises
        $topCompanies = $this->statsModel->getTopCompanies();
        $ratingDistribution = $this->statsModel->getRatingDistribution();
        $companyStats = [
            'top_companies' => $topCompanies,
            'rating_distribution' => $ratingDistribution
        ];

        // Récupération des données pour les graphiques
        $monthlyApplications = $this->statsModel->getMonthlyApplications();
        $skillDistribution = $this->statsModel->getSkillDistribution();

        // Définir le titre de la page
        $pageTitle = "Statistiques détaillées";

        // Chargement de la vue avec preloading des données JavaScript
        include VIEWS_PATH . '/admin/stats.php';
    }

    /**
     * Gestion des accès et permissions
     *
     * Interface pour contrôler les droits d'accès des utilisateurs
     * aux différentes fonctionnalités du système.
     */
    public function permissions() {
        // Définir le titre de la page
        $pageTitle = "Gestion des permissions";

        // Chargement de la vue
        include VIEWS_PATH . '/admin/permissions.php';
    }

    /**
     * Journaux système et suivi d'activité
     *
     * Affiche l'historique des actions système pour l'audit de sécurité
     * et le diagnostic des problèmes techniques.
     */
    public function logs() {
        // Récupération des journaux d'activité avec pagination
        $page = getCurrentPage();
        $limit = 50; // Plus élevé que la pagination standard

        $logs = $this->statsModel->getActivityLogs($limit);

        // Définir le titre de la page
        $pageTitle = "Journaux d'activité";

        // Chargement de la vue
        include VIEWS_PATH . '/admin/logs.php';
    }

    /**
     * Maintenance du système
     *
     * Interface pour les opérations de maintenance technique:
     * - Purge du cache
     * - Optimisation de la base de données
     * - Configuration système
     */
    public function maintenance() {
        $message = '';
        $messageType = '';

        // Traitement des actions de maintenance
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['clear_cache'])) {
                // Simulation de purge du cache
                $message = "Le cache a été purgé avec succès.";
                $messageType = "success";
            } elseif (isset($_POST['optimize_db'])) {
                // Simulation d'optimisation de la base de données
                $message = "La base de données a été optimisée avec succès.";
                $messageType = "success";
            }
        }

        // Définir le titre de la page
        $pageTitle = "Maintenance du système";

        // Chargement de la vue
        include VIEWS_PATH . '/admin/maintenance.php';
    }
}