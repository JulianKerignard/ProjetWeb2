<?php
/**
 * Contrôleur pour la gestion des étudiants
 *
 * Implémente les fonctionnalités CRUD et métier pour les étudiants
 * avec validation des droits d'accès et gestion robuste des erreurs.
 *
 * @version 1.1
 */
class EtudiantController {
    private $etudiantModel;
    private $candidatureModel;
    private $centreModel;
    private $logManager;

    /**
     * Constructeur - Initialise les modèles nécessaires avec vérification des droits
     */
    public function __construct() {
        // Chargement des modèles nécessaires
        require_once MODELS_PATH . '/Etudiant.php';
        $this->etudiantModel = new Etudiant();

        // Chargement du modèle Centre pour la gestion des centres
        require_once MODELS_PATH . '/Centre.php';
        $this->centreModel = new Centre();

        // Chargement du gestionnaire de logs
        require_once ROOT_PATH . '/includes/LogManager.php';
        $this->logManager = LogManager::getInstance();

        // Chargement optionnel du modèle Candidature pour les statistiques
        if (in_array($_GET['action'] ?? 'index', ['statistiques', 'detail'])) {
            require_once MODELS_PATH . '/Candidature.php';
            $this->candidatureModel = new Candidature();
        }

        // Vérification d'authentification pour toutes les actions sauf index
        $publicActions = ['index'];
        $action = isset($_GET['action']) ? $_GET['action'] : 'index';

        if (!in_array($action, $publicActions) && !isLoggedIn()) {
            // Redirection vers la page de connexion si non authentifié
            redirect(url('auth', 'login'));
        }

        // Vérification des droits d'accès pour les actions administratives
        $adminActions = ['creer', 'modifier', 'supprimer'];
        if (in_array($action, $adminActions) && !isAdmin() && !isPilote()) {
            // Redirection vers la liste en cas de droits insuffisants
            redirect(url('etudiants'));
        }
    }

    /**
     * Action par défaut - Liste des étudiants
     */
    public function index() {
        // Vérification des droits d'accès (admin ou pilote)
        if (!isAdmin() && !isPilote()) {
            // Redirection vers l'accueil
            redirect(url());
        }

        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Initialisation des filtres
        $filters = [];

        if (isset($_GET['nom']) && !empty($_GET['nom'])) {
            $filters['nom'] = cleanData($_GET['nom']);
        }

        if (isset($_GET['prenom']) && !empty($_GET['prenom'])) {
            $filters['prenom'] = cleanData($_GET['prenom']);
        }

        if (isset($_GET['email']) && !empty($_GET['email'])) {
            $filters['email'] = cleanData($_GET['email']);
        }

        if (isset($_GET['centre_id']) && !empty($_GET['centre_id'])) {
            $filters['centre_id'] = (int)$_GET['centre_id'];
        }

        if (isset($_GET['with_candidatures']) && $_GET['with_candidatures'] == '1') {
            $filters['with_candidatures'] = true;
        }

        // Restriction pour les pilotes - voir uniquement les étudiants de leur centre
        if (isPilote() && !isAdmin()) {
            $piloteModel = new Pilote();
            $pilote = $piloteModel->getByUserId($_SESSION['user_id']);
            if ($pilote && $pilote['centre_id']) {
                $filters['pilote_centre_id'] = $pilote['centre_id'];
            }
        }

        // Récupération des centres pour le filtre
        $centres = $this->centreModel->getAllForSelect();

        // Récupération des étudiants paginés
        $etudiants = $this->etudiantModel->getAll($page, ITEMS_PER_PAGE, $filters);

        // Comptage du nombre total d'étudiants pour la pagination
        $totalEtudiants = $this->etudiantModel->countAll($filters);

        // Journaliser l'accès à la liste des étudiants
        $this->logManager->info(
            "Consultation de la liste des étudiants",
            $_SESSION['email'],
            [
                'page' => $page,
                'filters' => $filters,
                'results_count' => count($etudiants)
            ]
        );

        // Définir le titre de la page
        $pageTitle = "Liste des étudiants";

        // Chargement de la vue
        include VIEWS_PATH . '/etudiants/index.php';
    }

    /**
     * Recherche d'étudiants selon critères
     */
    public function rechercher() {
        // Action index utilisée avec des filtres
        $this->index();
    }

    /**
     * Affiche les détails d'un étudiant avec ses candidatures
     */
    public function detail() {
        // Vérification des droits d'accès (admin, pilote ou l'étudiant lui-même)
        if (!isAdmin() && !isPilote() && (!isLoggedIn() || $_SESSION['role'] !== ROLE_ETUDIANT || $_SESSION['etudiant_id'] != $_GET['id'])) {
            // Redirection vers la page d'accueil
            redirect(url());
        }

        // Récupération de l'ID de l'étudiant
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('etudiants'));
        }

        // Récupération des détails de l'étudiant
        $etudiant = $this->etudiantModel->getById($id);

        if (!$etudiant) {
            // Redirection vers la liste si étudiant non trouvé
            redirect(url('etudiants'));
        }

        // Si pilote, vérifier l'accès au centre
        if (isPilote() && !isAdmin()) {
            $piloteModel = new Pilote();
            $pilote = $piloteModel->getByUserId($_SESSION['user_id']);

            if ($pilote && $pilote['centre_id'] && $etudiant['centre_id'] != $pilote['centre_id']) {
                // Redirection si l'étudiant n'est pas du même centre que le pilote
                $this->logManager->warning(
                    "Tentative d'accès aux détails d'un étudiant d'un autre centre",
                    $_SESSION['email'],
                    [
                        'etudiant_id' => $id,
                        'etudiant_centre_id' => $etudiant['centre_id'],
                        'pilote_centre_id' => $pilote['centre_id']
                    ]
                );

                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'message' => "Vous n'avez pas l'autorisation d'accéder aux détails de cet étudiant."
                ];

                redirect(url('etudiants'));
            }
        }

        // Journaliser l'accès aux détails de l'étudiant
        $this->logManager->info(
            "Consultation des détails d'un étudiant",
            $_SESSION['email'],
            [
                'etudiant_id' => $id,
                'etudiant_nom' => $etudiant['nom'] . ' ' . $etudiant['prenom']
            ]
        );

        // Définir le titre de la page
        $pageTitle = "Profil de l'étudiant: " . $etudiant['prenom'] . ' ' . $etudiant['nom'];

        // Chargement de la vue
        include VIEWS_PATH . '/etudiants/detail.php';
    }

    /**
     * Formulaire et traitement de création d'étudiant
     */
    public function creer() {
        // Initialisation des variables pour le formulaire
        $etudiant = [
            'nom' => '',
            'prenom' => '',
            'email' => '',
            'password' => '',
            'centre_id' => ''
        ];

        $errors = [];
        $success = false;

        // Récupération des centres pour le select
        $centres = $this->centreModel->getAllForSelect();

        // Traitement du formulaire de création
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $etudiant = [
                'nom' => isset($_POST['nom']) ? cleanData($_POST['nom']) : '',
                'prenom' => isset($_POST['prenom']) ? cleanData($_POST['prenom']) : '',
                'email' => isset($_POST['email']) ? cleanData($_POST['email']) : '',
                'password' => isset($_POST['password']) ? $_POST['password'] : '',
                'centre_id' => isset($_POST['centre_id']) && !empty($_POST['centre_id']) ? (int)$_POST['centre_id'] : 1 // Centre 1 par défaut
            ];

            // Validation des données
            $errors = $this->validateEtudiantForm($etudiant);

            // Si pas d'erreurs, création de l'étudiant
            if (empty($errors)) {
                $result = $this->etudiantModel->create($etudiant);

                if ($result) {
                    // Journaliser le succès
                    $this->logManager->success(
                        "Création d'un étudiant",
                        $_SESSION['email'],
                        [
                            'etudiant_id' => $result,
                            'etudiant_nom' => $etudiant['nom'] . ' ' . $etudiant['prenom'],
                            'etudiant_centre_id' => $etudiant['centre_id']
                        ]
                    );

                    // Redirection vers la liste avec message de succès
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "L'étudiant a été créé avec succès."
                    ];
                    redirect(url('etudiants'));
                } else {
                    $errors[] = "Une erreur est survenue lors de la création de l'étudiant.";

                    // Journaliser l'échec
                    $this->logManager->error(
                        "Échec de création d'un étudiant",
                        $_SESSION['email'],
                        [
                            'etudiant_nom' => $etudiant['nom'] . ' ' . $etudiant['prenom'],
                            'errors' => $errors
                        ]
                    );
                }
            }
        }

        // Définir le titre de la page
        $pageTitle = "Ajouter un étudiant";

        // Chargement de la vue
        include VIEWS_PATH . '/etudiants/form.php';
    }

    /**
     * Formulaire et traitement de modification