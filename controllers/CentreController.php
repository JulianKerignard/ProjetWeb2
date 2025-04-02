<?php
/**
 * Contrôleur pour la gestion des centres
 *
 * @version 1.0
 */
class CentreController {
    private $centreModel;
    private $logManager;

    /**
     * Constructeur - Initialise les modèles nécessaires
     */
    public function __construct() {
        // Chargement des modèles requis
        require_once MODELS_PATH . '/Centre.php';
        require_once ROOT_PATH . '/includes/LogManager.php';

        $this->centreModel = new Centre();
        $this->logManager = LogManager::getInstance();

        // Vérification d'authentification pour toutes les actions
        if (!isLoggedIn()) {
            // Journalisation de la tentative d'accès non authentifiée
            $this->logManager->warning(
                "Tentative d'accès non authentifiée à une action protégée: centres",
                null,
                ['ip' => $_SERVER['REMOTE_ADDR']]
            );

            // Redirection vers la page de connexion
            redirect(url('auth', 'login'));
        }

        // Vérification des droits d'accès (admin ou pilote uniquement)
        if (!isAdmin() && !isPilote()) {
            // Journalisation de la tentative d'accès non autorisé
            $this->logManager->warning(
                "Tentative d'accès non autorisé à la gestion des centres",
                $_SESSION['email'],
                ['role' => $_SESSION['role']]
            );

            // Redirection vers l'accueil
            redirect(url());
        }
    }

    /**
     * Action par défaut - Liste des centres
     */
    public function index() {
        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Initialisation des filtres
        $filters = [];

        if (isset($_GET['nom']) && !empty($_GET['nom'])) {
            $filters['nom'] = cleanData($_GET['nom']);
        }

        if (isset($_GET['code']) && !empty($_GET['code'])) {
            $filters['code'] = cleanData($_GET['code']);
        }

        // Récupération des centres paginés
        $centres = $this->centreModel->getAll($page, ITEMS_PER_PAGE, $filters);

        // Comptage du nombre total de centres pour la pagination
        $totalCentres = $this->centreModel->countAll($filters);

        // Journalisation de la consultation des centres
        $this->logManager->info(
            "Consultation de la liste des centres",
            $_SESSION['email'],
            [
                'page' => $page,
                'filters' => $filters,
                'results_count' => count($centres)
            ]
        );

        // Définir le titre de la page
        $pageTitle = "Liste des centres";

        // Chargement de la vue
        include VIEWS_PATH . '/centres/index.php';
    }

    /**
     * Affiche les détails d'un centre
     */
    public function detail() {
        // Récupération de l'ID du centre
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('centres'));
        }

        // Récupération des détails du centre
        $centre = $this->centreModel->getById($id);

        if (!$centre) {
            // Journalisation de l'erreur d'accès à un centre inexistant
            $this->logManager->warning(
                "Tentative d'accès à un centre inexistant",
                $_SESSION['email'],
                ['centre_id' => $id]
            );

            // Redirection vers la liste si centre non trouvé
            redirect(url('centres'));
        }

        // Journalisation de la consultation détaillée
        $this->logManager->info(
            "Consultation du détail d'un centre",
            $_SESSION['email'],
            [
                'centre_id' => $id,
                'centre_nom' => $centre['nom']
            ]
        );

        // Définir le titre de la page
        $pageTitle = "Détail du centre: " . $centre['nom'];

        // Chargement de la vue
        include VIEWS_PATH . '/centres/detail.php';
    }

    /**
     * Formulaire et traitement de création de centre
     */
    public function creer() {
        // Vérification des droits d'accès (admin uniquement)
        if (!isAdmin()) {
            // Journalisation de la tentative d'accès non autorisé
            $this->logManager->warning(
                "Tentative de création de centre sans autorisation",
                $_SESSION['email']
            );

            // Redirection vers la liste si droits insuffisants
            redirect(url('centres'));
        }

        // Initialisation des variables pour le formulaire
        $centre = [
            'nom' => '',
            'code' => '',
            'adresse' => ''
        ];

        $errors = [];
        $success = false;

        // Traitement du formulaire de création
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation et nettoyage des données
            $centre = $this->validateCentreData($_POST);
            $errors = $this->validateCentreForm($centre);

            // Si pas d'erreurs, création du centre
            if (empty($errors)) {
                $result = $this->centreModel->create($centre);

                if ($result) {
                    // Journalisation de la création réussie
                    $this->logManager->success(
                        "Création d'un centre",
                        $_SESSION['email'],
                        [
                            'centre_id' => $result,
                            'centre_nom' => $centre['nom'],
                            'centre_code' => $centre['code']
                        ]
                    );

                    // Redirection vers la page de détail du centre créé
                    redirect(url('centres', 'detail', ['id' => $result]));
                } else {
                    $errors[] = "Une erreur est survenue lors de la création du centre.";

                    // Journalisation de l'échec
                    $this->logManager->error(
                        "Échec de création d'un centre",
                        $_SESSION['email'],
                        [
                            'centre_nom' => $centre['nom'],
                            'centre_code' => $centre['code']
                        ]
                    );
                }
            } else {
                // Journalisation des erreurs de validation
                $this->logManager->warning(
                    "Erreurs de validation lors de la création d'un centre",
                    $_SESSION['email'],
                    [
                        'errors' => $errors,
                        'centre_nom' => $centre['nom']
                    ]
                );
            }
        }

        // Définir le titre de la page
        $pageTitle = "Ajouter un centre";

        // Chargement de la vue
        include VIEWS_PATH . '/centres/form.php';
    }

    /**
     * Formulaire et traitement de modification de centre
     */
    public function modifier() {
        // Vérification des droits d'accès (admin uniquement)
        if (!isAdmin()) {
            // Journalisation de la tentative d'accès non autorisé
            $this->logManager->warning(
                "Tentative de modification de centre sans autorisation",
                $_SESSION['email']
            );

            // Redirection vers la liste si droits insuffisants
            redirect(url('centres'));
        }

        // Récupération de l'ID du centre
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('centres'));
        }

        // Récupération des détails du centre
        $centre = $this->centreModel->getById($id);

        if (!$centre) {
            // Journalisation de la tentative de modification d'un centre inexistant
            $this->logManager->warning(
                "Tentative de modification d'un centre inexistant",
                $_SESSION['email'],
                ['centre_id' => $id]
            );

            // Redirection vers la liste si centre non trouvé
            redirect(url('centres'));
        }

        $errors = [];
        $success = false;

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validation et nettoyage des données
            $updatedCentre = $this->validateCentreData($_POST);
            $errors = $this->validateCentreForm($updatedCentre);

            // Si pas d'erreurs, mise à jour du centre
            if (empty($errors)) {
                $result = $this->centreModel->update($id, $updatedCentre);

                if ($result) {
                    $success = true;

                    // Journalisation de la modification réussie
                    $this->logManager->success(
                        "Modification d'un centre",
                        $_SESSION['email'],
                        [
                            'centre_id' => $id,
                            'centre_nom' => $updatedCentre['nom'],
                            'centre_code' => $updatedCentre['code']
                        ]
                    );

                    // Rafraîchissement des données du centre
                    $centre = $this->centreModel->getById($id);
                } else {
                    $errors[] = "Une erreur est survenue lors de la mise à jour du centre.";

                    // Journalisation de l'échec
                    $this->logManager->error(
                        "Échec de modification d'un centre",
                        $_SESSION['email'],
                        [
                            'centre_id' => $id,
                            'centre_nom' => $updatedCentre['nom']
                        ]
                    );
                }
            } else {
                // Journalisation des erreurs de validation
                $this->logManager->warning(
                    "Erreurs de validation lors de la modification d'un centre",
                    $_SESSION['email'],
                    [
                        'errors' => $errors,
                        'centre_id' => $id,
                        'centre_nom' => $updatedCentre['nom']
                    ]
                );
            }
        }

        // Définir le titre de la page
        $pageTitle = "Modifier le centre";

        // Chargement de la vue
        include VIEWS_PATH . '/centres/form.php';
    }

    /**
     * Suppression d'un centre
     */
    public function supprimer() {
        // Vérification des droits d'accès (admin uniquement)
        if (!isAdmin()) {
            // Journalisation de la tentative d'accès non autorisé
            $this->logManager->warning(
                "Tentative de suppression de centre sans autorisation",
                $_SESSION['email']
            );

            // Redirection vers la liste si droits insuffisants
            redirect(url('centres'));
        }

        // Récupération de l'ID du centre
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('centres'));
        }

        // Vérification de l'existence du centre
        $centre = $this->centreModel->getById($id);

        if (!$centre) {
            // Journalisation de la tentative de suppression d'un centre inexistant
            $this->logManager->warning(
                "Tentative de suppression d'un centre inexistant",
                $_SESSION['email'],
                ['centre_id' => $id]
            );

            // Redirection vers la liste si centre non trouvé
            redirect(url('centres'));
        }

        // Confirmation de suppression
        if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
            // Sauvegarde des informations du centre pour la journalisation
            $centreInfo = [
                'id' => $centre['id'],
                'nom' => $centre['nom'],
                'code' => $centre['code']
            ];

            $result = $this->centreModel->delete($id);

            if ($result) {
                // Journalisation de la suppression réussie
                $this->logManager->success(
                    "Suppression d'un centre",
                    $_SESSION['email'],
                    $centreInfo
                );

                // Redirection vers la liste avec message de succès
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => "Le centre a été supprimé avec succès."
                ];
            } else {
                // Journalisation de l'échec
                $this->logManager->error(
                    "Échec de suppression d'un centre",
                    $_SESSION['email'],
                    $centreInfo
                );

                // Redirection vers la liste avec message d'erreur
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'message' => "Une erreur est survenue lors de la suppression du centre."
                ];
            }

            redirect(url('centres'));
        }

        // Définir le titre de la page
        $pageTitle = "Supprimer le centre";

        // Chargement de la vue
        include VIEWS_PATH . '/centres/supprimer.php';
    }

    /**
     * Validation et nettoyage des données du formulaire
     *
     * @param array $data Données brutes du formulaire
     * @return array Données validées et nettoyées
     */
    private function validateCentreData($data) {
        $centre = [];

        // Validation du nom
        $centre['nom'] = isset($data['nom']) ? cleanData($data['nom']) : '';

        // Validation du code
        $centre['code'] = isset($data['code']) ? strtoupper(cleanData($data['code'])) : '';

        // Validation de l'adresse
        $centre['adresse'] = isset($data['adresse']) ? cleanData($data['adresse']) : '';

        return $centre;
    }

    /**
     * Validation des contraintes métier du formulaire
     *
     * @param array $centre Données du centre à valider
     * @return array Liste des erreurs de validation
     */
    private function validateCentreForm($centre) {
        $errors = [];

        // Validation du nom
        if (empty($centre['nom'])) {
            $errors[] = "Le nom du centre est obligatoire.";
        } elseif (strlen($centre['nom']) < 2 || strlen($centre['nom']) > 100) {
            $errors[] = "Le nom doit contenir entre 2 et 100 caractères.";
        }

        // Validation du code
        if (empty($centre['code'])) {
            $errors[] = "Le code du centre est obligatoire.";
        } elseif (strlen($centre['code']) < 2 || strlen($centre['code']) > 20) {
            $errors[] = "Le code doit contenir entre 2 et 20 caractères.";
        }

        return $errors;
    }
}