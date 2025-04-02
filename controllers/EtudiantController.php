<?php
/**
 * Contrôleur pour la gestion des étudiants
 *
 * Implémente les fonctionnalités CRUD et métier pour les étudiants
 * avec validation des droits d'accès et gestion robuste des erreurs.
 *
 * @version 1.0
 */
class EtudiantController {
    private $etudiantModel;
    private $candidatureModel;

    /**
     * Constructeur - Initialise les modèles nécessaires avec vérification des droits
     */
    public function __construct() {
        // Chargement des modèles nécessaires
        require_once MODELS_PATH . '/Etudiant.php';
        $this->etudiantModel = new Etudiant();

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

        if (isset($_GET['with_candidatures']) && $_GET['with_candidatures'] == '1') {
            $filters['with_candidatures'] = true;
        }

        // Récupération des étudiants paginés
        $etudiants = $this->etudiantModel->getAll($page, ITEMS_PER_PAGE, $filters);

        // Comptage du nombre total d'étudiants pour la pagination
        $totalEtudiants = $this->etudiantModel->countAll($filters);

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
        // Vérification des droits d'accès (admin ou pilote)
        if (!isAdmin() && !isPilote()) {
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
            'password' => ''
        ];

        $errors = [];
        $success = false;

        // Traitement du formulaire de création
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $etudiant = [
                'nom' => isset($_POST['nom']) ? cleanData($_POST['nom']) : '',
                'prenom' => isset($_POST['prenom']) ? cleanData($_POST['prenom']) : '',
                'email' => isset($_POST['email']) ? cleanData($_POST['email']) : '',
                'password' => isset($_POST['password']) ? $_POST['password'] : ''
            ];

            // Validation des données
            $errors = $this->validateEtudiantForm($etudiant);

            // Si pas d'erreurs, création de l'étudiant
            if (empty($errors)) {
                $result = $this->etudiantModel->create($etudiant);

                if ($result) {
                    // Redirection vers la liste avec message de succès
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "L'étudiant a été créé avec succès."
                    ];
                    redirect(url('etudiants'));
                } else {
                    $errors[] = "Une erreur est survenue lors de la création de l'étudiant.";
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

        $errors = [];
        $success = false;

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $updatedEtudiant = [
                'nom' => isset($_POST['nom']) ? cleanData($_POST['nom']) : '',
                'prenom' => isset($_POST['prenom']) ? cleanData($_POST['prenom']) : '',
                'email' => isset($_POST['email']) ? cleanData($_POST['email']) : '',
                'password' => isset($_POST['password']) ? $_POST['password'] : ''
            ];

            // Validation des données (mode édition)
            $errors = $this->validateEtudiantForm($updatedEtudiant, true);

            // Si pas d'erreurs, mise à jour de l'étudiant
            if (empty($errors)) {
                $result = $this->etudiantModel->update($id, $updatedEtudiant);

                if ($result) {
                    $success = true;
                    // Rafraîchissement des données de l'étudiant
                    $etudiant = $this->etudiantModel->getById($id);
                } else {
                    $errors[] = "Une erreur est survenue lors de la mise à jour de l'étudiant.";
                }
            }
        }

        // Définir le titre de la page
        $pageTitle = "Modifier l'étudiant";

        // Chargement de la vue
        include VIEWS_PATH . '/etudiants/form.php';
    }

    /**
     * Suppression d'un étudiant et de ses données associées
     */
    public function supprimer() {
        // Récupération de l'ID de l'étudiant
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('etudiants'));
        }

        // Vérification de l'existence de l'étudiant
        $etudiant = $this->etudiantModel->getById($id);

        if (!$etudiant) {
            // Redirection vers la liste si étudiant non trouvé
            redirect(url('etudiants'));
        }

        // Confirmation de suppression
        if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
            $result = $this->etudiantModel->delete($id);

            if ($result) {
                // Redirection vers la liste avec message de succès
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => "L'étudiant a été supprimé avec succès."
                ];
            } else {
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

        // Chargement de la vue de confirmation
        include VIEWS_PATH . '/etudiants/supprimer.php';
    }

    /**
     * Affichage des statistiques d'un étudiant
     */
    public function statistiques() {
        // Vérification des droits d'accès (admin ou pilote)
        if (!isAdmin() && !isPilote()) {
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

        // Récupération des statistiques de l'étudiant
        // - Nombre de candidatures
        // - Répartition par statut
        // - Offres en wishlist
        $statistiques = [
            'nb_candidatures' => $etudiant['nb_candidatures'],
            'nb_wishlist' => $etudiant['nb_wishlist'],
            'candidatures' => $etudiant['candidatures'],
            'wishlist' => $etudiant['wishlist']
        ];

        // Définir le titre de la page
        $pageTitle = "Statistiques de " . $etudiant['prenom'] . ' ' . $etudiant['nom'];

        // Chargement de la vue
        include VIEWS_PATH . '/etudiants/statistiques.php';
    }

    /**
     * Validation des données du formulaire étudiant
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

        return $errors;
    }
}