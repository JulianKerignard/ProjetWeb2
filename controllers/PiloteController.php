<?php
/**
 * Contrôleur pour la gestion des pilotes de promotion
 *
 * Implémente les fonctionnalités CRUD et métier pour les pilotes
 * avec validation des droits d'accès et gestion des erreurs.
 *
 * @version 1.0
 */
class PiloteController {
    private $piloteModel;

    /**
     * Constructeur - Initialise les modèles nécessaires avec vérification des droits
     */
    public function __construct() {
        // Chargement du modèle Pilote
        require_once MODELS_PATH . '/Pilote.php';
        $this->piloteModel = new Pilote();

        // Vérification d'authentification pour toutes les actions sauf index
        $publicActions = ['index'];
        $action = isset($_GET['action']) ? $_GET['action'] : 'index';

        if (!in_array($action, $publicActions) && !isLoggedIn()) {
            // Redirection vers la page de connexion si non authentifié
            redirect(url('auth', 'login'));
        }

        // Vérification des droits d'accès pour les actions restreintes
        $restrictedActions = ['creer', 'modifier', 'supprimer'];
        if (in_array($action, $restrictedActions) && !isAdmin()) {
            // Redirection vers la liste en cas de droits insuffisants
            redirect(url('pilotes'));
        }
    }

    /**
     * Action par défaut - Liste des pilotes
     */
    public function index() {
        // Vérification des droits d'accès
        if (!isAdmin()) {
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

        // Récupération des pilotes paginés
        $pilotes = $this->piloteModel->getAll($page, ITEMS_PER_PAGE, $filters);

        // Comptage du nombre total de pilotes pour la pagination
        $totalPilotes = $this->piloteModel->countAll($filters);

        // Définir le titre de la page
        $pageTitle = "Liste des pilotes";

        // Chargement de la vue
        include VIEWS_PATH . '/pilotes/index.php';
    }

    /**
     * Formulaire et traitement de création de pilote
     */
    public function creer() {
        // Initialisation des variables pour le formulaire
        $pilote = [
            'nom' => '',
            'prenom' => '',
            'email' => '',
            'password' => ''
        ];

        $errors = [];
        $success = false;

        // Traitement du formulaire de création
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $pilote = [
                'nom' => isset($_POST['nom']) ? cleanData($_POST['nom']) : '',
                'prenom' => isset($_POST['prenom']) ? cleanData($_POST['prenom']) : '',
                'email' => isset($_POST['email']) ? cleanData($_POST['email']) : '',
                'password' => isset($_POST['password']) ? $_POST['password'] : ''
            ];

            // Validation des données
            $errors = $this->validatePiloteForm($pilote);

            // Si pas d'erreurs, création du pilote
            if (empty($errors)) {
                $result = $this->piloteModel->create($pilote);

                if ($result) {
                    // Redirection vers la liste avec message de succès
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "Le pilote a été créé avec succès."
                    ];
                    redirect(url('pilotes'));
                } else {
                    $errors[] = "Une erreur est survenue lors de la création du pilote.";
                }
            }
        }

        // Définir le titre de la page
        $pageTitle = "Ajouter un pilote";

        // Chargement de la vue
        include VIEWS_PATH . '/pilotes/form.php';
    }

    /**
     * Formulaire et traitement de modification de pilote
     */
    public function modifier() {
        // Récupération de l'ID du pilote
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('pilotes'));
        }

        // Récupération des détails du pilote
        $pilote = $this->piloteModel->getById($id);

        if (!$pilote) {
            // Redirection vers la liste si pilote non trouvé
            redirect(url('pilotes'));
        }

        $errors = [];
        $success = false;

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $updatedPilote = [
                'nom' => isset($_POST['nom']) ? cleanData($_POST['nom']) : '',
                'prenom' => isset($_POST['prenom']) ? cleanData($_POST['prenom']) : '',
                'email' => isset($_POST['email']) ? cleanData($_POST['email']) : '',
                'password' => isset($_POST['password']) ? $_POST['password'] : ''
            ];

            // Validation des données
            $errors = $this->validatePiloteForm($updatedPilote, true);

            // Si pas d'erreurs, mise à jour du pilote
            if (empty($errors)) {
                $result = $this->piloteModel->update($id, $updatedPilote);

                if ($result) {
                    $success = true;
                    // Rafraîchissement des données du pilote
                    $pilote = $this->piloteModel->getById($id);
                } else {
                    $errors[] = "Une erreur est survenue lors de la mise à jour du pilote.";
                }
            }
        }

        // Définir le titre de la page
        $pageTitle = "Modifier le pilote";

        // Chargement de la vue
        include VIEWS_PATH . '/pilotes/form.php';
    }

    /**
     * Suppression d'un pilote
     */
    public function supprimer() {
        // Récupération de l'ID du pilote
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('pilotes'));
        }

        // Vérification de l'existence du pilote
        $pilote = $this->piloteModel->getById($id);

        if (!$pilote) {
            // Redirection vers la liste si pilote non trouvé
            redirect(url('pilotes'));
        }

        // Confirmation de suppression
        if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
            $result = $this->piloteModel->delete($id);

            if ($result) {
                // Redirection vers la liste avec message de succès
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => "Le pilote a été supprimé avec succès."
                ];
            } else {
                // Redirection vers la liste avec message d'erreur
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'message' => "Une erreur est survenue lors de la suppression du pilote."
                ];
            }

            redirect(url('pilotes'));
        }

        // Définir le titre de la page
        $pageTitle = "Supprimer le pilote";

        // Chargement de la vue
        include VIEWS_PATH . '/pilotes/supprimer.php';
    }

    /**
     * Affiche les détails d'un pilote
     */
    public function detail() {
        // Récupération de l'ID du pilote
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('pilotes'));
        }

        // Récupération des détails du pilote
        $pilote = $this->piloteModel->getById($id);

        if (!$pilote) {
            // Redirection vers la liste si pilote non trouvé
            redirect(url('pilotes'));
        }

        // Définir le titre de la page
        $pageTitle = "Détail du pilote: " . $pilote['prenom'] . ' ' . $pilote['nom'];

        // Chargement de la vue
        include VIEWS_PATH . '/pilotes/detail.php';
    }

    /**
     * Recherche de pilotes selon critères
     */
    public function rechercher() {
        // Action index utilisée avec des filtres
        $this->index();
    }

    /**
     * Validation des données du formulaire pilote
     *
     * @param array $data Données à valider
     * @param bool $isEdit Mode édition (true) ou création (false)
     * @return array Liste des erreurs de validation
     */
    private function validatePiloteForm($data, $isEdit = false) {
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

        return $errors;
    }
}