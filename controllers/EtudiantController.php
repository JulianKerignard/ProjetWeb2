<?php
/**
 * Contrôleur pour la gestion des étudiants
 *
 * Implémente les fonctionnalités CRUD et métier pour les étudiants
 * avec validation des droits d'accès et gestion robuste des erreurs.
 * Architecture MVC avec séparation stricte des responsabilités.
 *
 * @version 1.2
 * @author Web4All
 */
class EtudiantController {
    /** @var Etudiant Instance du modèle Etudiant */
    private $etudiantModel;

    /** @var Candidature Instance du modèle Candidature */
    private $candidatureModel;

    /** @var Centre Instance du modèle Centre */
    private $centreModel;

    /** @var Pilote Instance du modèle Pilote */
    private $piloteModel;

    /** @var LogManager Instance du gestionnaire de logs */
    private $logManager;

    /**
     * Constructeur - Initialise les modèles nécessaires avec vérification des droits
     * Utilise l'injection de dépendances pour améliorer la testabilité
     */
    public function __construct() {
        // Chargement des modèles nécessaires
        require_once MODELS_PATH . '/Etudiant.php';
        $this->etudiantModel = new Etudiant();

        // Chargement du modèle Centre pour la gestion des centres
        require_once MODELS_PATH . '/Centre.php';
        $this->centreModel = new Centre();

        // Chargement du modèle Pilote - essentiel pour la vérification des permissions
        require_once MODELS_PATH . '/Pilote.php';
        $this->piloteModel = new Pilote();

        // Chargement du gestionnaire de logs
        require_once ROOT_PATH . '/includes/LogManager.php';
        $this->logManager = LogManager::getInstance();

        // Chargement conditionnel du modèle Candidature pour les statistiques
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
     * Implémente le filtrage et la pagination côté serveur
     */
    public function index() {
        // Vérification des droits d'accès (admin ou pilote)
        if (!isAdmin() && !isPilote()) {
            // Redirection vers l'accueil
            redirect(url());
        }

        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Initialisation des filtres - implémentation d'un filtrage robuste
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
        // Application du principe de séparation des responsabilités
        if (isPilote() && !isAdmin()) {
            $pilote = $this->piloteModel->getByUserId($_SESSION['user_id']);
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

        // Journaliser l'accès à la liste des étudiants - traçabilité des actions
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

        // Chargement de la vue - séparation stricte de la logique de présentation
        include VIEWS_PATH . '/etudiants/index.php';
    }

    /**
     * Recherche d'étudiants selon critères spécifiés
     * Utilise la même implémentation que index() avec des filtres actifs
     */
    public function rechercher() {
        // Action index utilisée avec des filtres
        $this->index();
    }

    /**
     * Affiche les détails d'un étudiant avec ses candidatures
     * Implémente des vérifications d'autorisation avancées
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
            $pilote = $this->piloteModel->getByUserId($_SESSION['user_id']);

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
     * Implémentation du pattern PRG (Post-Redirect-Get) pour éviter les soumissions multiples
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
            // Récupération et nettoyage des données - application des principes de sécurité
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

                    // Redirection vers la liste avec message de succès - pattern PRG
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "L'étudiant a été créé avec succès."
                    ];
                    redirect(url('etudiants'));
                } else {
                    $errors[] = "Une erreur est survenue lors de la création de l'étudiant.";

                    // Journaliser l'échec - amélioration de la traçabilité des erreurs
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
     * Formulaire et traitement de modification d'étudiant
     * Implémente des vérifications avancées sur l'accès aux données
     */
    public function modifier() {
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
            $pilote = $this->piloteModel->getByUserId($_SESSION['user_id']);

            if ($pilote && $pilote['centre_id'] && $etudiant['centre_id'] != $pilote['centre_id']) {
                // Redirection si l'étudiant n'est pas du même centre que le pilote
                $this->logManager->warning(
                    "Tentative de modification d'un étudiant d'un autre centre",
                    $_SESSION['email'],
                    [
                        'etudiant_id' => $id,
                        'etudiant_centre_id' => $etudiant['centre_id'],
                        'pilote_centre_id' => $pilote['centre_id']
                    ]
                );

                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'message' => "Vous n'avez pas l'autorisation de modifier cet étudiant."
                ];

                redirect(url('etudiants'));
            }
        }

        // Récupération des centres pour le select
        $centres = $this->centreModel->getAllForSelect();

        $errors = [];
        $success = false;

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $updatedEtudiant = [
                'nom' => isset($_POST['nom']) ? cleanData($_POST['nom']) : '',
                'prenom' => isset($_POST['prenom']) ? cleanData($_POST['prenom']) : '',
                'email' => isset($_POST['email']) ? cleanData($_POST['email']) : '',
                'password' => isset($_POST['password']) ? $_POST['password'] : '',
                'centre_id' => isset($_POST['centre_id']) && !empty($_POST['centre_id']) ? (int)$_POST['centre_id'] : 1 // Centre 1 par défaut
            ];

            // Validation des données
            $errors = $this->validateEtudiantForm($updatedEtudiant, true);

            // Si pas d'erreurs, mise à jour de l'étudiant
            if (empty($errors)) {
                $result = $this->etudiantModel->update($id, $updatedEtudiant);

                if ($result) {
                    $success = true;

                    // Journaliser le succès
                    $this->logManager->success(
                        "Modification d'un étudiant",
                        $_SESSION['email'],
                        [
                            'etudiant_id' => $id,
                            'etudiant_nom' => $updatedEtudiant['nom'] . ' ' . $updatedEtudiant['prenom'],
                            'etudiant_centre_id' => $updatedEtudiant['centre_id']
                        ]
                    );

                    // Rafraîchissement des données de l'étudiant
                    $etudiant = $this->etudiantModel->getById($id);
                } else {
                    $errors[] = "Une erreur est survenue lors de la mise à jour de l'étudiant.";

                    // Journaliser l'échec
                    $this->logManager->error(
                        "Échec de modification d'un étudiant",
                        $_SESSION['email'],
                        [
                            'etudiant_id' => $id,
                            'etudiant_nom' => $updatedEtudiant['nom'] . ' ' . $updatedEtudiant['prenom'],
                            'errors' => $errors
                        ]
                    );
                }
            }
        }

        // Définir le titre de la page
        $pageTitle = "Modifier l'étudiant";

        // Chargement de la vue
        include VIEWS_PATH . '/etudiants/form.php';
    }

    /**
     * Suppression d'un étudiant
     * Implémente des vérifications de sécurité et une procédure de confirmation
     */
    public function supprimer() {
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
            $pilote = $this->piloteModel->getByUserId($_SESSION['user_id']);

            if ($pilote && $pilote['centre_id'] && $etudiant['centre_id'] != $pilote['centre_id']) {
                // Redirection si l'étudiant n'est pas du même centre que le pilote
                $this->logManager->warning(
                    "Tentative de suppression d'un étudiant d'un autre centre",
                    $_SESSION['email'],
                    [
                        'etudiant_id' => $id,
                        'etudiant_centre_id' => $etudiant['centre_id'],
                        'pilote_centre_id' => $pilote['centre_id']
                    ]
                );

                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'message' => "Vous n'avez pas l'autorisation de supprimer cet étudiant."
                ];

                redirect(url('etudiants'));
            }
        }

        // Confirmation de suppression - double vérification pour éviter suppressions accidentelles
        if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
            $result = $this->etudiantModel->delete($id);

            if ($result) {
                // Journaliser le succès
                $this->logManager->success(
                    "Suppression d'un étudiant",
                    $_SESSION['email'],
                    [
                        'etudiant_id' => $id,
                        'etudiant_nom' => $etudiant['nom'] . ' ' . $etudiant['prenom']
                    ]
                );

                // Redirection vers la liste avec message de succès
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => "L'étudiant a été supprimé avec succès."
                ];
            } else {
                // Journaliser l'échec
                $this->logManager->error(
                    "Échec de suppression d'un étudiant",
                    $_SESSION['email'],
                    [
                        'etudiant_id' => $id,
                        'etudiant_nom' => $etudiant['nom'] . ' ' . $etudiant['prenom']
                    ]
                );

                // Redirection vers la liste avec message d'erreur
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'message' => "Une erreur est survenue lors de la suppression de l'étudiant."
                ];
            }

            redirect(url('etudiants'));
        }

        // Définir le titre de la page
        $pageTitle = "Supprimer l'étudiant";

        // Chargement de la vue
        include VIEWS_PATH . '/etudiants/supprimer.php';
    }

    /**
     * Affiche les statistiques d'un étudiant
     * Utilise l'agrégation de données et implémente des contrôles d'accès avancés
     */
    public function statistiques() {
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
            $pilote = $this->piloteModel->getByUserId($_SESSION['user_id']);

            if ($pilote && $pilote['centre_id'] && $etudiant['centre_id'] != $pilote['centre_id']) {
                // Redirection si l'étudiant n'est pas du même centre que le pilote
                $this->logManager->warning(
                    "Tentative d'accès aux statistiques d'un étudiant d'un autre centre",
                    $_SESSION['email'],
                    [
                        'etudiant_id' => $id,
                        'etudiant_centre_id' => $etudiant['centre_id'],
                        'pilote_centre_id' => $pilote['centre_id']
                    ]
                );

                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'message' => "Vous n'avez pas l'autorisation d'accéder aux statistiques de cet étudiant."
                ];

                redirect(url('etudiants'));
            }
        }

        // Récupération des statistiques - traitement des données pour visualisation
        $statistiques = [
            'nb_candidatures' => $etudiant['nb_candidatures'],
            'nb_wishlist' => $etudiant['nb_wishlist'],
            'candidatures' => $etudiant['candidatures'],
            'wishlist' => $etudiant['wishlist']
        ];

        // Journaliser l'accès aux statistiques
        $this->logManager->info(
            "Consultation des statistiques d'un étudiant",
            $_SESSION['email'],
            [
                'etudiant_id' => $id,
                'etudiant_nom' => $etudiant['nom'] . ' ' . $etudiant['prenom']
            ]
        );

        // Ajouter une classe spécifique pour le conteneur des statistiques
        $containerClass = 'student-stats-container';

        // Définir le titre de la page
        $pageTitle = "Statistiques de " . $etudiant['prenom'] . ' ' . $etudiant['nom'];

        // Chargement de la vue
        include VIEWS_PATH . '/etudiants/statistiques.php';
    }

    /**
     * Validation des données du formulaire étudiant
     * Implémente une validation serveur robuste avec vérifications métier
     *
     * @param array $data Données à valider
     * @param bool $isEdit Mode édition (true) ou création (false)
     * @return array Liste des erreurs de validation
     */
    private function validateEtudiantForm($data, $isEdit = false) {
        $errors = [];

        // Validation du nom
        if (empty($data['nom'])) {
            $errors[] = "Le nom est obligatoire.";
        } elseif (strlen($data['nom']) < 2 || strlen($data['nom']) > 50) {
            $errors[] = "Le nom doit contenir entre 2 et 50 caractères.";
        }

        // Validation du prénom
        if (empty($data['prenom'])) {
            $errors[] = "Le prénom est obligatoire.";
        } elseif (strlen($data['prenom']) < 2 || strlen($data['prenom']) > 50) {
            $errors[] = "Le prénom doit contenir entre 2 et 50 caractères.";
        }

        // Validation de l'email
        if (empty($data['email'])) {
            $errors[] = "L'email est obligatoire.";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide.";
        }

        // Validation du mot de passe (uniquement en création ou si fourni en édition)
        if (!$isEdit || !empty($data['password'])) {
            if (empty($data['password'])) {
                $errors[] = "Le mot de passe est obligatoire.";
            } elseif (strlen($data['password']) < 6) {
                $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
            }
        }

        // Validation du centre (obligatoire)
        if (empty($data['centre_id'])) {
            $errors[] = "Le centre est obligatoire.";
        } else {
            // Vérifier que le centre existe
            $centre = $this->centreModel->getById($data['centre_id']);
            if (!$centre) {
                $errors[] = "Le centre sélectionné n'existe pas.";
            }
        }

        return $errors;
    }
}