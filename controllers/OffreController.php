<?php
/**
 * Contrôleur pour la gestion des offres de stage
 * Implémente les fonctionnalités CRUD et métier avec système de journalisation avancé
 *
 * @version 2.0
 */
class OffreController {
    private $offreModel;
    private $entrepriseModel;
    private $logManager;

    /**
     * Constructeur - Initialise les modèles nécessaires et le gestionnaire de logs
     */
    public function __construct() {
        // Chargement des modèles requis
        require_once MODELS_PATH . '/Offre.php';
        require_once MODELS_PATH . '/Entreprise.php';
        require_once ROOT_PATH . '/includes/LogManager.php';

        $this->offreModel = new Offre();
        $this->entrepriseModel = new Entreprise();
        $this->logManager = LogManager::getInstance();

        // Vérification d'authentification pour certaines actions
        $publicActions = ['index', 'rechercher', 'detail'];
        $action = isset($_GET['action']) ? $_GET['action'] : 'index';

        if (!in_array($action, $publicActions) && !isLoggedIn()) {
            // Journalisation de la tentative d'accès non authentifiée
            $this->logManager->warning(
                "Tentative d'accès non authentifiée à une action protégée: offres/{$action}",
                null,
                ['ip' => $_SERVER['REMOTE_ADDR']]
            );

            // Redirection vers la page de connexion si non authentifié
            redirect(url('auth', 'login'));
        }
    }

    /**
     * Action par défaut - Liste des offres
     */
    public function index() {
        // Acquisition des critères de filtrage depuis l'URL
        $filters = $this->getFiltersFromRequest();

        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Initialiser des variables par défaut en cas d'échec de connexion
        $totalOffres = 0;
        $offres = [];
        $competences = [];
        $entreprises = [];
        $dbError = false;

        try {
            // Récupération des offres paginées
            $offres = $this->offreModel->getAll($page, ITEMS_PER_PAGE, $filters);

            // Comptage du nombre total d'offres pour la pagination
            $totalOffres = $this->offreModel->countAll($filters);

            // Récupération des compétences pour le filtre
            $competences = $this->offreModel->getAllCompetences();

            // Récupération des entreprises pour le filtre
            $entreprises = $this->entrepriseModel->getAllForFilter();

            // Journalisation de la consultation des offres
            if (isLoggedIn()) {
                $this->logManager->info(
                    "Consultation des offres de stage",
                    $_SESSION['email'],
                    [
                        'page' => $page,
                        'filters' => $filters,
                        'results_count' => count($offres)
                    ]
                );
            }
        } catch (Exception $e) {
            // En cas d'erreur de connexion ou autre erreur
            error_log("Erreur dans OffreController::index() - " . $e->getMessage());
            $dbError = true;

            // Journalisation de l'erreur
            $this->logManager->error(
                "Erreur lors de la consultation des offres",
                isset($_SESSION['email']) ? $_SESSION['email'] : null,
                ['error' => $e->getMessage()]
            );
        }

        // Définir le titre de la page
        $pageTitle = "Offres de stage";

        // Passer l'état d'erreur à la vue
        $dbError = ($offres === [] && $totalOffres === 0 && $competences === [] && $entreprises === []) || $dbError;

        // Chargement de la vue
        include VIEWS_PATH . '/offres/index.php';
    }

    /**
     * Recherche d'offres selon critères
     * Cette méthode est un alias de index() pour maintenir la cohérence des routes
     */
    public function rechercher() {
        // Journalisation de la recherche avancée
        if (isLoggedIn()) {
            $this->logManager->info(
                "Recherche avancée d'offres",
                $_SESSION['email'],
                ['filters' => $_GET]
            );
        }

        // Réutiliser la logique existante de index() qui gère déjà les filtres
        $this->index();
    }

    /**
     * Affiche les détails d'une offre
     */
    public function detail() {
        // Récupération de l'ID de l'offre
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('offres'));
        }

        // Récupération des détails de l'offre
        $offre = $this->offreModel->getById($id);

        if (!$offre) {
            // Journalisation de l'erreur d'accès à une offre inexistante
            if (isLoggedIn()) {
                $this->logManager->warning(
                    "Tentative d'accès à une offre inexistante",
                    $_SESSION['email'],
                    ['offre_id' => $id]
                );
            }

            // Redirection vers la liste si offre non trouvée
            redirect(url('offres'));
        }

        // Vérification si l'offre est dans la wishlist de l'utilisateur connecté
        $inWishlist = false;

        if (isLoggedIn() && $_SESSION['role'] === ROLE_ETUDIANT) {
            // Modèle de candidature pour vérifier la wishlist
            require_once MODELS_PATH . '/Candidature.php';
            require_once MODELS_PATH . '/Etudiant.php';
            $candidatureModel = new Candidature();
            $etudiantModel = new Etudiant();

            // Obtenir l'ID étudiant de manière fiable
            $etudiantId = null;

            // Utiliser l'ID étudiant de la session si disponible
            if (isset($_SESSION['etudiant_id'])) {
                $etudiantId = $_SESSION['etudiant_id'];
            } else {
                // Sinon, tenter de le récupérer depuis la base de données
                $etudiantId = $etudiantModel->getEtudiantIdFromUserId($_SESSION['user_id']);

                // Mettre en cache si trouvé
                if ($etudiantId) {
                    $_SESSION['etudiant_id'] = $etudiantId;
                }
            }

            // Vérifier si l'offre est dans la wishlist
            if ($etudiantId) {
                $inWishlist = $candidatureModel->isInWishlist($etudiantId, $id);
            }
        }

        // Journalisation de la consultation détaillée
        if (isLoggedIn()) {
            $this->logManager->info(
                "Consultation du détail d'une offre",
                $_SESSION['email'],
                [
                    'offre_id' => $id,
                    'offre_titre' => $offre['titre'],
                    'entreprise_id' => $offre['entreprise_id'],
                    'entreprise_nom' => $offre['entreprise_nom']
                ]
            );
        }

        // Définir le titre de la page
        $pageTitle = "Détail de l'offre: " . $offre['titre'];

        // Chargement de la vue
        include VIEWS_PATH . '/offres/detail.php';
    }

    /**
     * Formulaire et traitement de création d'offre
     */
    public function creer() {
        // Vérification des droits d'accès
        if (!checkAccess('offre_creer')) {
            // Journalisation de la tentative d'accès non autorisé
            $this->logManager->warning(
                "Tentative de création d'offre sans autorisation",
                isset($_SESSION['email']) ? $_SESSION['email'] : null
            );

            // Redirection vers la liste si droits insuffisants
            redirect(url('offres'));
        }

        // Récupération des entreprises pour le formulaire
        $entreprises = $this->entrepriseModel->getAllForSelect();

        // Récupération des compétences pour le formulaire
        $competences = $this->offreModel->getAllCompetences();

        // Initialisation des variables pour le formulaire
        $offre = [
            'titre' => '',
            'description' => '',
            'entreprise_id' => '',
            'remuneration' => '',
            'date_debut' => '',
            'date_fin' => '',
            'competences' => []
        ];

        $errors = [];
        $success = false;

        // Traitement du formulaire de création
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation et nettoyage des données
            $offre = $this->validateOffreData($_POST);
            $errors = $this->validateOffreForm($offre);

            // Si pas d'erreurs, création de l'offre
            if (empty($errors)) {
                $result = $this->offreModel->create($offre);

                if ($result) {
                    // Journalisation de la création réussie
                    $this->logManager->success(
                        "Création d'une offre de stage",
                        $_SESSION['email'],
                        [
                            'offre_id' => $result,
                            'offre_titre' => $offre['titre'],
                            'entreprise_id' => $offre['entreprise_id'],
                            'date_debut' => $offre['date_debut'],
                            'date_fin' => $offre['date_fin'],
                            'competences' => implode(',', $offre['competences'])
                        ]
                    );

                    // Redirection vers la page de détail de l'offre créée
                    redirect(url('offres', 'detail', ['id' => $result]));
                } else {
                    $errors[] = "Une erreur est survenue lors de la création de l'offre.";

                    // Journalisation de l'échec
                    $this->logManager->error(
                        "Échec de création d'une offre de stage",
                        $_SESSION['email'],
                        [
                            'offre_titre' => $offre['titre'],
                            'entreprise_id' => $offre['entreprise_id']
                        ]
                    );
                }
            } else {
                // Journalisation des erreurs de validation
                $this->logManager->warning(
                    "Erreurs de validation lors de la création d'une offre",
                    $_SESSION['email'],
                    [
                        'errors' => $errors,
                        'offre_titre' => $offre['titre']
                    ]
                );
            }
        }

        // Définir le titre de la page
        $pageTitle = "Créer une offre de stage";

        // Chargement de la vue
        include VIEWS_PATH . '/offres/form.php';
    }

    /**
     * Formulaire et traitement de modification d'offre
     */
    public function modifier() {
        // Vérification des droits d'accès
        if (!checkAccess('offre_modifier')) {
            // Journalisation de la tentative d'accès non autorisé
            $this->logManager->warning(
                "Tentative de modification d'offre sans autorisation",
                isset($_SESSION['email']) ? $_SESSION['email'] : null
            );

            // Redirection vers la liste si droits insuffisants
            redirect(url('offres'));
        }

        // Récupération de l'ID de l'offre
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('offres'));
        }

        // Récupération des détails de l'offre
        $offre = $this->offreModel->getById($id);

        if (!$offre) {
            // Journalisation de la tentative de modification d'une offre inexistante
            $this->logManager->warning(
                "Tentative de modification d'une offre inexistante",
                $_SESSION['email'],
                ['offre_id' => $id]
            );

            // Redirection vers la liste si offre non trouvée
            redirect(url('offres'));
        }

        // Extraction des IDs de compétences pour le formulaire
        $offre['competences'] = array_map(function($comp) {
            return $comp['id'];
        }, $offre['competences']);

        // Récupération des entreprises pour le formulaire
        $entreprises = $this->entrepriseModel->getAllForSelect();

        // Récupération des compétences pour le formulaire
        $competences = $this->offreModel->getAllCompetences();

        $errors = [];
        $success = false;

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation et nettoyage des données
            $updatedOffre = $this->validateOffreData($_POST);
            $errors = $this->validateOffreForm($updatedOffre);

            // Si pas d'erreurs, mise à jour de l'offre
            if (empty($errors)) {
                $result = $this->offreModel->update($id, $updatedOffre);

                if ($result) {
                    $success = true;

                    // Journalisation de la modification réussie
                    $this->logManager->success(
                        "Modification d'une offre de stage",
                        $_SESSION['email'],
                        [
                            'offre_id' => $id,
                            'offre_titre' => $updatedOffre['titre'],
                            'entreprise_id' => $updatedOffre['entreprise_id'],
                            'date_debut' => $updatedOffre['date_debut'],
                            'date_fin' => $updatedOffre['date_fin'],
                            'competences' => implode(',', $updatedOffre['competences'])
                        ]
                    );

                    // Rafraîchissement des données de l'offre
                    $offre = $this->offreModel->getById($id);
                    $offre['competences'] = array_map(function($comp) {
                        return $comp['id'];
                    }, $offre['competences']);
                } else {
                    $errors[] = "Une erreur est survenue lors de la mise à jour de l'offre.";

                    // Journalisation de l'échec
                    $this->logManager->error(
                        "Échec de modification d'une offre de stage",
                        $_SESSION['email'],
                        [
                            'offre_id' => $id,
                            'offre_titre' => $updatedOffre['titre']
                        ]
                    );
                }
            } else {
                // Journalisation des erreurs de validation
                $this->logManager->warning(
                    "Erreurs de validation lors de la modification d'une offre",
                    $_SESSION['email'],
                    [
                        'errors' => $errors,
                        'offre_id' => $id,
                        'offre_titre' => $updatedOffre['titre']
                    ]
                );
            }
        } else {
            // Journalisation de l'accès au formulaire de modification
            $this->logManager->info(
                "Accès au formulaire de modification d'une offre",
                $_SESSION['email'],
                [
                    'offre_id' => $id,
                    'offre_titre' => $offre['titre']
                ]
            );
        }

        // Définir le titre de la page
        $pageTitle = "Modifier l'offre de stage";

        // Chargement de la vue
        include VIEWS_PATH . '/offres/form.php';
    }

    /**
     * Suppression d'une offre
     */
    public function supprimer() {
        // Vérification des droits d'accès
        if (!checkAccess('offre_supprimer')) {
            // Journalisation de la tentative d'accès non autorisé
            $this->logManager->warning(
                "Tentative de suppression d'offre sans autorisation",
                isset($_SESSION['email']) ? $_SESSION['email'] : null
            );

            // Redirection vers la liste si droits insuffisants
            redirect(url('offres'));
        }

        // Récupération de l'ID de l'offre
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('offres'));
        }

        // Vérification de l'existence de l'offre
        $offre = $this->offreModel->getById($id);

        if (!$offre) {
            // Journalisation de la tentative de suppression d'une offre inexistante
            $this->logManager->warning(
                "Tentative de suppression d'une offre inexistante",
                $_SESSION['email'],
                ['offre_id' => $id]
            );

            // Redirection vers la liste si offre non trouvée
            redirect(url('offres'));
        }

        // Confirmation de suppression
        if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
            // Sauvegarde des informations de l'offre pour la journalisation
            $offreInfo = [
                'id' => $offre['id'],
                'titre' => $offre['titre'],
                'entreprise_id' => $offre['entreprise_id'],
                'entreprise_nom' => $offre['entreprise_nom']
            ];

            $result = $this->offreModel->delete($id);

            if ($result) {
                // Journalisation de la suppression réussie
                $this->logManager->success(
                    "Suppression d'une offre de stage",
                    $_SESSION['email'],
                    $offreInfo
                );

                // Redirection vers la liste avec message de succès
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => "L'offre a été supprimée avec succès."
                ];
            } else {
                // Journalisation de l'échec
                $this->logManager->error(
                    "Échec de suppression d'une offre de stage",
                    $_SESSION['email'],
                    $offreInfo
                );

                // Redirection vers la liste avec message d'erreur
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'message' => "Une erreur est survenue lors de la suppression de l'offre."
                ];
            }

            redirect(url('offres'));
        } else {
            // Journalisation de l'accès à la page de confirmation
            $this->logManager->info(
                "Accès à la page de confirmation de suppression d'une offre",
                $_SESSION['email'],
                [
                    'offre_id' => $id,
                    'offre_titre' => $offre['titre']
                ]
            );
        }

        // Définir le titre de la page
        $pageTitle = "Supprimer l'offre de stage";

        // Chargement de la vue de confirmation
        include VIEWS_PATH . '/offres/supprimer.php';
    }

    /**
     * Affichage des statistiques des offres
     */
    public function statistiques() {
        // Vérification des droits d'accès (admin ou pilote)
        if (!isAdmin() && !isPilote()) {
            // Journalisation de la tentative d'accès non autorisé
            $this->logManager->warning(
                "Tentative d'accès aux statistiques des offres sans autorisation",
                isset($_SESSION['email']) ? $_SESSION['email'] : null
            );

            // Redirection vers la liste si droits insuffisants
            redirect(url('offres'));
        }

        // Récupération des statistiques
        $statistics = $this->offreModel->getStatistics();

        // Journalisation de la consultation des statistiques
        $this->logManager->info(
            "Consultation des statistiques des offres",
            $_SESSION['email']
        );

        // Définir le titre de la page
        $pageTitle = "Statistiques des offres de stage";

        // Chargement de la vue
        include VIEWS_PATH . '/offres/statistiques.php';
    }

    /**
     * Extraction et préparation des filtres depuis la requête
     *
     * @return array Filtres validés et nettoyés
     */
    private function getFiltersFromRequest() {
        $filters = [];

        // Filtrage par titre
        if (isset($_GET['titre']) && !empty($_GET['titre'])) {
            $filters['titre'] = cleanData($_GET['titre']);
        }

        // Filtrage par entreprise
        if (isset($_GET['entreprise_id']) && !empty($_GET['entreprise_id'])) {
            $filters['entreprise_id'] = (int)$_GET['entreprise_id'];
        }

        // Filtrage par compétence
        if (isset($_GET['competence_id']) && !empty($_GET['competence_id'])) {
            $filters['competence_id'] = (int)$_GET['competence_id'];
        }

        // Filtrage par date de début
        if (isset($_GET['date_debut']) && !empty($_GET['date_debut'])) {
            $filters['date_debut'] = cleanData($_GET['date_debut']);
        }

        // Filtrage par date de fin
        if (isset($_GET['date_fin']) && !empty($_GET['date_fin'])) {
            $filters['date_fin'] = cleanData($_GET['date_fin']);
        }

        // Tri des résultats
        if (isset($_GET['order_by']) && !empty($_GET['order_by'])) {
            $allowedOrderBy = ['o.titre', 'e.nom', 'o.date_debut', 'o.remuneration', 'o.created_at'];
            if (in_array($_GET['order_by'], $allowedOrderBy)) {
                $filters['order_by'] = $_GET['order_by'];
            }
        }

        // Direction du tri
        if (isset($_GET['order_dir']) && in_array(strtoupper($_GET['order_dir']), ['ASC', 'DESC'])) {
            $filters['order_dir'] = strtoupper($_GET['order_dir']);
        }

        return $filters;
    }

    /**
     * Validation et nettoyage des données du formulaire
     *
     * @param array $data Données brutes du formulaire
     * @return array Données validées et nettoyées
     */
    private function validateOffreData($data) {
        $offre = [];

        // Validation du titre
        $offre['titre'] = isset($data['titre']) ? cleanData($data['titre']) : '';

        // Validation de la description (autoriser les balises HTML de base)
        $offre['description'] = isset($data['description']) ?
            htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8', false) : '';

        // Validation de l'entreprise
        $offre['entreprise_id'] = isset($data['entreprise_id']) ? (int)$data['entreprise_id'] : 0;

        // Validation de la rémunération
        $offre['remuneration'] = isset($data['remuneration']) ?
            (float)str_replace(',', '.', $data['remuneration']) : 0;

        // Validation des dates
        $offre['date_debut'] = isset($data['date_debut']) ? cleanData($data['date_debut']) : '';
        $offre['date_fin'] = isset($data['date_fin']) ? cleanData($data['date_fin']) : '';

        // Validation des compétences
        $offre['competences'] = isset($data['competences']) && is_array($data['competences']) ?
            array_map('intval', $data['competences']) : [];

        return $offre;
    }

    /**
     * Validation des contraintes métier du formulaire
     *
     * @param array $offre Données de l'offre à valider
     * @return array Liste des erreurs de validation
     */
    private function validateOffreForm($offre) {
        $errors = [];

        // Validation du titre
        if (empty($offre['titre'])) {
            $errors[] = "Le titre de l'offre est obligatoire.";
        } elseif (strlen($offre['titre']) < 5 || strlen($offre['titre']) > 100) {
            $errors[] = "Le titre doit contenir entre 5 et 100 caractères.";
        }

        // Validation de la description
        if (empty($offre['description'])) {
            $errors[] = "La description de l'offre est obligatoire.";
        } elseif (strlen($offre['description']) < 50) {
            $errors[] = "La description doit contenir au moins 50 caractères.";
        }

        // Validation de l'entreprise
        if (empty($offre['entreprise_id'])) {
            $errors[] = "Vous devez sélectionner une entreprise.";
        } else {
            // Vérification que l'entreprise existe
            $entreprise = $this->entrepriseModel->getById($offre['entreprise_id']);
            if (!$entreprise) {
                $errors[] = "L'entreprise sélectionnée n'existe pas.";
            }
        }

        // Validation de la rémunération
        if ($offre['remuneration'] < 0) {
            $errors[] = "La rémunération ne peut pas être négative.";
        }

        // Validation des dates
        if (empty($offre['date_debut'])) {
            $errors[] = "La date de début est obligatoire.";
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $offre['date_debut'])) {
            $errors[] = "La date de début doit être au format YYYY-MM-DD.";
        }

        if (empty($offre['date_fin'])) {
            $errors[] = "La date de fin est obligatoire.";
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $offre['date_fin'])) {
            $errors[] = "La date de fin doit être au format YYYY-MM-DD.";
        }

        // Vérification que la date de fin est postérieure à la date de début
        if (!empty($offre['date_debut']) && !empty($offre['date_fin'])) {
            $debut = new DateTime($offre['date_debut']);
            $fin = new DateTime($offre['date_fin']);

            if ($fin <= $debut) {
                $errors[] = "La date de fin doit être postérieure à la date de début.";
            }
        }

        // Validation des compétences
        if (empty($offre['competences'])) {
            $errors[] = "Vous devez sélectionner au moins une compétence.";
        }

        return $errors;
    }
}