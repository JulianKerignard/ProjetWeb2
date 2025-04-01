<?php
/**
 * Contrôleur pour la gestion des entreprises
 * Implémente les fonctionnalités CRUD et métier
 */
class EntrepriseController {
    private $entrepriseModel;

    /**
     * Constructeur - Initialise les modèles nécessaires
     */
    public function __construct() {
        // Chargement des modèles requis
        require_once MODELS_PATH . '/Entreprise.php';
        $this->entrepriseModel = new Entreprise();

        // Vérification d'authentification pour certaines actions
        $publicActions = ['index', 'detail'];
        $action = isset($_GET['action']) ? $_GET['action'] : 'index';

        if (!in_array($action, $publicActions) && !isLoggedIn()) {
            // Redirection vers la page de connexion si non authentifié
            redirect(url('auth', 'login'));
        }
    }

    /**
     * Action par défaut - Liste des entreprises
     */
    public function index() {
        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Initialisation des filtres
        $filters = [];

        if (isset($_GET['nom']) && !empty($_GET['nom'])) {
            $filters['nom'] = cleanData($_GET['nom']);
        }

        // Vérifier si le modèle a une erreur de connexion
        $dbError = $this->entrepriseModel->hasError();

        // Récupération des entreprises paginées
        $entreprises = $this->entrepriseModel->getAll($page, ITEMS_PER_PAGE, $filters);

        // Comptage du nombre total d'entreprises pour la pagination
        $totalEntreprises = $this->entrepriseModel->countAll($filters);

        // Définir le titre de la page
        $pageTitle = "Liste des entreprises";

        // Chargement de la vue
        include VIEWS_PATH . '/entreprises/index.php';
    }

    /**
     * Affiche les détails d'une entreprise
     */
    public function detail() {
        // Récupération de l'ID de l'entreprise
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('entreprises'));
        }

        // Récupération des détails de l'entreprise
        $entreprise = $this->entrepriseModel->getById($id);

        if (!$entreprise) {
            // Redirection vers la liste si entreprise non trouvée
            redirect(url('entreprises'));
        }

        // Définir le titre de la page
        $pageTitle = "Détail de l'entreprise: " . $entreprise['nom'];

        // Chargement de la vue
        include VIEWS_PATH . '/entreprises/detail.php';
    }

    /**
     * Formulaire et traitement de création d'entreprise
     */
    public function creer() {
        // Vérification des droits d'accès
        if (!checkAccess('entreprise_creer')) {
            // Redirection vers la liste si droits insuffisants
            redirect(url('entreprises'));
        }

        // Initialisation des variables pour le formulaire
        $entreprise = [
            'nom' => '',
            'description' => '',
            'email' => '',
            'telephone' => ''
        ];

        $errors = [];
        $success = false;

        // Traitement du formulaire de création
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation et nettoyage des données
            $entreprise = $this->validateEntrepriseData($_POST);
            $errors = $this->validateEntrepriseForm($entreprise);

            // Si pas d'erreurs, création de l'entreprise
            if (empty($errors)) {
                $result = $this->entrepriseModel->create($entreprise);

                if ($result) {
                    // Redirection vers la page de détail de l'entreprise créée
                    redirect(url('entreprises', 'detail', ['id' => $result]));
                } else {
                    $errors[] = "Une erreur est survenue lors de la création de l'entreprise.";
                }
            }
        }

        // Définir le titre de la page
        $pageTitle = "Ajouter une entreprise";

        // Chargement de la vue
        include VIEWS_PATH . '/entreprises/form.php';
    }

    /**
     * Formulaire et traitement de modification d'entreprise
     */
    public function modifier() {
        // Vérification des droits d'accès
        if (!checkAccess('entreprise_modifier')) {
            // Redirection vers la liste si droits insuffisants
            redirect(url('entreprises'));
        }

        // Récupération de l'ID de l'entreprise
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('entreprises'));
        }

        // Récupération des détails de l'entreprise
        $entreprise = $this->entrepriseModel->getById($id);

        if (!$entreprise) {
            // Redirection vers la liste si entreprise non trouvée
            redirect(url('entreprises'));
        }

        $errors = [];
        $success = false;

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation et nettoyage des données
            $updatedEntreprise = $this->validateEntrepriseData($_POST);
            $errors = $this->validateEntrepriseForm($updatedEntreprise);

            // Si pas d'erreurs, mise à jour de l'entreprise
            if (empty($errors)) {
                $result = $this->entrepriseModel->update($id, $updatedEntreprise);

                if ($result) {
                    $success = true;
                    // Rafraîchissement des données de l'entreprise
                    $entreprise = $this->entrepriseModel->getById($id);
                } else {
                    $errors[] = "Une erreur est survenue lors de la mise à jour de l'entreprise.";
                }
            }
        }

        // Définir le titre de la page
        $pageTitle = "Modifier l'entreprise";

        // Chargement de la vue
        include VIEWS_PATH . '/entreprises/form.php';
    }

    /**
     * Suppression d'une entreprise
     */
    public function supprimer() {
        // Vérification des droits d'accès
        if (!checkAccess('entreprise_supprimer')) {
            // Redirection vers la liste si droits insuffisants
            redirect(url('entreprises'));
        }

        // Récupération de l'ID de l'entreprise
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('entreprises'));
        }

        // Vérification de l'existence de l'entreprise
        $entreprise = $this->entrepriseModel->getById($id);

        if (!$entreprise) {
            // Redirection vers la liste si entreprise non trouvée
            redirect(url('entreprises'));
        }

        $errors = [];

        // Confirmation de suppression
        if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
            $result = $this->entrepriseModel->delete($id);

            if ($result) {
                // Redirection vers la liste avec message de succès
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => "L'entreprise a été supprimée avec succès."
                ];
                redirect(url('entreprises'));
            } else {
                // Impossible de supprimer car des offres sont liées
                $errors[] = "Impossible de supprimer cette entreprise car des offres de stage y sont associées.";
            }
        }

        // Définir le titre de la page
        $pageTitle = "Supprimer l'entreprise";

        // Chargement de la vue
        include VIEWS_PATH . '/entreprises/supprimer.php';
    }

    /**
     * Formulaire et traitement d'évaluation d'entreprise
     */
    public function evaluer() {
        // Vérification de l'authentification et des droits
        if (!isLoggedIn() || !checkAccess('entreprise_evaluer')) {
            redirect(url('auth', 'login'));
        }

        // Récupération de l'ID de l'entreprise
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            redirect(url('entreprises'));
        }

        // Récupération des détails de l'entreprise
        $entreprise = $this->entrepriseModel->getById($id);

        if (!$entreprise) {
            redirect(url('entreprises'));
        }

        $errors = [];
        $success = false;

        // Traitement du formulaire d'évaluation
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $evaluation = [
                'entreprise_id' => $id,
                'etudiant_id' => $_SESSION['user_id'],
                'note' => isset($_POST['note']) ? (int)$_POST['note'] : 0,
                'commentaire' => isset($_POST['commentaire']) ? cleanData($_POST['commentaire']) : ''
            ];

            // Validation des données
            if ($evaluation['note'] < 1 || $evaluation['note'] > 5) {
                $errors[] = "La note doit être comprise entre 1 et 5.";
            }

            if (empty($evaluation['commentaire'])) {
                $errors[] = "Le commentaire est obligatoire.";
            }

            // Si pas d'erreurs, ajout de l'évaluation
            if (empty($errors)) {
                $result = $this->entrepriseModel->addEvaluation($evaluation);

                if ($result) {
                    $success = true;
                    // Rafraîchir l'entreprise avec la nouvelle évaluation
                    $entreprise = $this->entrepriseModel->getById($id);
                } else {
                    $errors[] = "Une erreur est survenue lors de l'ajout de l'évaluation.";
                }
            }
        }

        // Définir le titre de la page
        $pageTitle = "Évaluer l'entreprise: " . $entreprise['nom'];

        // Chargement de la vue
        include VIEWS_PATH . '/entreprises/evaluer.php';
    }

    /**
     * Recherche d'entreprises avec filtres
     */
    public function rechercher() {
        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Initialisation des filtres
        $filters = [];

        if (isset($_GET['nom']) && !empty($_GET['nom'])) {
            $filters['nom'] = cleanData($_GET['nom']);
        }

        if (isset($_GET['with_offres']) && $_GET['with_offres'] == '1') {
            $filters['with_offres'] = true;
        }

        // Tri des résultats
        if (isset($_GET['order_by']) && !empty($_GET['order_by'])) {
            $allowedOrderBy = ['e.nom', 'nb_offres', 'moyenne_evaluations'];
            if (in_array($_GET['order_by'], $allowedOrderBy)) {
                $filters['order_by'] = $_GET['order_by'];
            }
        }

        if (isset($_GET['order_dir']) && in_array(strtoupper($_GET['order_dir']), ['ASC', 'DESC'])) {
            $filters['order_dir'] = strtoupper($_GET['order_dir']);
        }

        // Récupération des entreprises filtrées
        $entreprises = $this->entrepriseModel->getAll($page, ITEMS_PER_PAGE, $filters);

        // Comptage du nombre total d'entreprises
        $totalEntreprises = $this->entrepriseModel->countAll($filters);

        // Définir le titre de la page
        $pageTitle = "Recherche d'entreprises";

        // Chargement de la vue
        include VIEWS_PATH . '/entreprises/index.php';
    }

    /**
     * Validation et nettoyage des données du formulaire
     *
     * @param array $data Données brutes du formulaire
     * @return array Données validées et nettoyées
     */
    private function validateEntrepriseData($data) {
        $entreprise = [];

        // Validation du nom
        $entreprise['nom'] = isset($data['nom']) ? cleanData($data['nom']) : '';

        // Validation de la description
        $entreprise['description'] = isset($data['description']) ?
            htmlspecialchars($data['description'], ENT_QUOTES, 'UTF-8', false) : '';

        // Validation de l'email
        $entreprise['email'] = isset($data['email']) ? cleanData($data['email']) : '';

        // Validation du téléphone
        $entreprise['telephone'] = isset($data['telephone']) ? cleanData($data['telephone']) : '';

        return $entreprise;
    }

    /**
     * Validation des contraintes métier du formulaire
     *
     * @param array $entreprise Données de l'entreprise à valider
     * @return array Liste des erreurs de validation
     */
    private function validateEntrepriseForm($entreprise) {
        $errors = [];

        // Validation du nom
        if (empty($entreprise['nom'])) {
            $errors[] = "Le nom de l'entreprise est obligatoire.";
        } elseif (strlen($entreprise['nom']) < 2 || strlen($entreprise['nom']) > 100) {
            $errors[] = "Le nom doit contenir entre 2 et 100 caractères.";
        }

        // Validation de l'email
        if (!empty($entreprise['email']) && !filter_var($entreprise['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email n'est pas valide.";
        }

        // Validation du téléphone
        if (!empty($entreprise['telephone']) && !preg_match('/^[0-9+\(\)\s.-]{6,20}$/', $entreprise['telephone'])) {
            $errors[] = "Le numéro de téléphone n'est pas valide.";
        }

        return $errors;
    }
}