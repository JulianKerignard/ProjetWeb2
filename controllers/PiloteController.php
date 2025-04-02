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
    private $centreModel;
    private $logManager;

    /**
     * Constructeur - Initialise les modèles nécessaires avec vérification des droits
     */
    public function __construct() {
        // Chargement du modèle Pilote
        require_once MODELS_PATH . '/Pilote.php';
        $this->piloteModel = new Pilote();

        // Chargement du modèle Centre pour les sélections de centres
        require_once MODELS_PATH . '/Centre.php';
        $this->centreModel = new Centre();

        // Chargement du gestionnaire de logs
        require_once ROOT_PATH . '/includes/LogManager.php';
        $this->logManager = LogManager::getInstance();

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

        if (isset($_GET['centre_id']) && !empty($_GET['centre_id'])) {
            $filters['centre_id'] = (int)$_GET['centre_id'];
        }

        // Récupération des centres pour le filtre
        $centres = $this->centreModel->getAllForSelect();

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
            'password' => '',
            'centre_id' => ''
        ];

        // Récupération des centres pour le select
        $centres = $this->centreModel->getAllForSelect();

        $errors = [];
        $success = false;

        // Traitement du formulaire de création
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $pilote = [
                'nom' => isset($_POST['nom']) ? cleanData($_POST['nom']) : '',
                'prenom' => isset($_POST['prenom']) ? cleanData($_POST['prenom']) : '',
                'email' => isset($_POST['email']) ? cleanData($_POST['email']) : '',
                'password' => isset($_POST['password']) ? $_POST['password'] : '',
                'centre_id' => isset($_POST['centre_id']) && !empty($_POST['centre_id']) ? (int)$_POST['centre_id'] : null
            ];

            // Validation des données
            $errors = $this->validatePiloteForm($pilote);

            // Si pas d'erreurs, création du pilote
            if (empty($errors)) {
                $result = $this->piloteModel->create($pilote);

                if ($result) {
                    // Journalisation
                    $this->logManager->success(
                        "Création d'un pilote",
                        $_SESSION['email'],
                        [
                            'pilote_id' => $result,
                            'pilote_nom' => $pilote['nom'],
                            'pilote_centre_id' => $pilote['centre_id']
                        ]
                    );

                    // Redirection vers la liste avec message de succès
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "Le pilote a été créé avec succès."
                    ];
                    redirect(url('pilotes'));
                } else {
                    $errors[] = "Une erreur est survenue lors de la création du pilote.";

                    // Journalisation
                    $this->logManager->error(
                        "Échec de création d'un pilote",
                        $_SESSION['email'],
                        [
                            'pilote_nom' => $pilote['nom'],
                            'pilote_email' => $pilote['email']
                        ]
                    );
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

        // Récupération des centres pour le select
        $centres = $this->centreModel->getAllForSelect();

        $errors = [];
        $success = false;

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $updatedPilote = [
                'nom' => isset($_POST['nom']) ? cleanData($_POST['nom']) : '',
                'prenom' => isset($_POST['prenom']) ? cleanData($_POST['prenom']) : '',
                'email' => isset($_POST['email']) ? cleanData($_POST['email']) : '',
                'password' => isset($_POST['password']) ? $_POST['password'] : '',
                'centre_id' => isset($_POST['centre_id']) && !empty($_POST['centre_id']) ? (int)$_POST['centre_id'] : null
            ];

            // Validation des données
            $errors = $this->validatePiloteForm($updatedPilote, true);

            // Si pas d'erreurs, mise à jour du pilote
            if (empty($errors)) {
                $result = $this->piloteModel->update($id, $updatedPilote);

                if ($result) {
                    $success = true;

                    // Journalisation
                    $this->logManager->success(
                        "Modification d'un pilote",
                        $_SESSION['email'],
                        [
                            'pilote_id' => $id,
                            'pilote_nom' => $updatedPilote['nom'],
                            'pilote_centre_id' => $updatedPilote['centre_id']
                        ]
                    );

                    // Rafraîchissement des données du pilote
                    $pilote = $this->piloteModel->getById($id);
                } else {
                    $errors[] = "Une erreur est survenue lors de la mise à jour du pilote.";

                    // Journalisation
                    $this->logManager->error(
                        "Échec de modification d'un pilote",
                        $_SESSION['email'],
                        [
                            'pilote_id' => $id,
                            'pilote_nom' => $updatedPilote['nom']
                        ]
                    );
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
                // Journalisation
                $this->logManager->success(
                    "Suppression d'un pilote",
                    $_SESSION['email'],
                    [
                        'pilote_id' => $id,
                        'pilote_nom' => $pilote['nom'],
                        'pilote_prenom' => $pilote['prenom']
                    ]
                );

                // Redirection vers la liste avec message de succès
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => "Le pilote a été supprimé avec succès."
                ];
            } else {
                // Journalisation
                $this->logManager->error(
                    "Échec de suppression d'un pilote",
                    $_SESSION['email'],
                    [
                        'pilote_id' => $id,
                        'pilote_nom' => $pilote['nom']
                    ]
                );

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

        // Journalisation
        $this->logManager->info(
            "Consultation des détails d'un pilote",
            $_SESSION['email'],
            [
                'pilote_id' => $id,
                'pilote_nom' => $pilote['nom']
            ]
        );

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
     * Affiche les étudiants assignés à un pilote
     */
    public function etudiants() {
        // Récupération de l'ID du pilote
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('pilotes'));
        }

        // Récupération des détails du pilote
        $pilote = $this->piloteModel->getById($id);

        if (!$pilote) {
            // Journalisation de l'erreur d'accès à un pilote inexistant
            $this->logManager->warning(
                "Tentative d'accès à un pilote inexistant",
                $_SESSION['email'],
                ['pilote_id' => $id]
            );

            // Redirection vers la liste si pilote non trouvé
            redirect(url('pilotes'));
        }

        // Vérification des droits d'accès pour les pilotes
        if (!isAdmin() && isPilote() && $_SESSION['user_id'] != $pilote['user_id']) {
            // Journalisation
            $this->logManager->warning(
                "Tentative d'accès non autorisé aux étudiants d'un autre pilote",
                $_SESSION['email'],
                [
                    'pilote_id' => $id,
                    'pilote_nom' => $pilote['nom']
                ]
            );

            // Redirection avec message d'erreur
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Vous n'avez pas l'autorisation d'accéder aux étudiants de ce pilote."
            ];

            redirect(url('pilotes'));
        }

        // Récupération des étudiants assignés au pilote
        $etudiants = $this->piloteModel->getEtudiantsAssignes($id);

        // Journalisation de la consultation
        $this->logManager->info(
            "Consultation des étudiants assignés à un pilote",
            $_SESSION['email'],
            [
                'pilote_id' => $id,
                'pilote_nom' => $pilote['nom'],
                'nb_etudiants' => count($etudiants)
            ]
        );

        // Définir le titre de la page
        $pageTitle = "Étudiants assignés à " . $pilote['prenom'] . ' ' . $pilote['nom'];

        // Chargement de la vue
        include VIEWS_PATH . '/pilotes/etudiants.php';
    }

    /**
     * Formulaire d'attribution d'étudiants à un pilote
     */
    public function attribuerEtudiants() {
        // Vérification des droits d'accès (admin uniquement)
        if (!isAdmin()) {
            // Journalisation de la tentative d'accès non autorisé
            $this->logManager->warning(
                "Tentative d'attribution d'étudiants sans autorisation",
                $_SESSION['email']
            );

            // Redirection vers la liste si droits insuffisants
            redirect(url('pilotes'));
        }

        // Récupération de l'ID du pilote
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste si ID invalide
            redirect(url('pilotes'));
        }

        // Récupération des détails du pilote
        $pilote = $this->piloteModel->getById($id);

        if (!$pilote) {
            // Journalisation de l'erreur d'accès à un pilote inexistant
            $this->logManager->warning(
                "Tentative d'attribution d'étudiants à un pilote inexistant",
                $_SESSION['email'],
                ['pilote_id' => $id]
            );

            // Redirection vers la liste si pilote non trouvé
            redirect(url('pilotes'));
        }

        // Récupération des étudiants déjà assignés
        $etudiantsAssignes = $this->piloteModel->getEtudiantsAssignes($id);
        $etudiantsAssignesIds = array_column($etudiantsAssignes, 'id');

        // Récupération de tous les étudiants disponibles (du même centre que le pilote)
        $tousLesEtudiants = $this->piloteModel->getEtudiantsDisponibles($id);

        $errors = [];
        $success = false;

        // Traitement de l'attribution d'étudiants
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['etudiant_ids']) && is_array($_POST['etudiant_ids'])) {
                $etudiantIds = array_map('intval', $_POST['etudiant_ids']);

                // Supprimer les attributions existantes
                foreach ($etudiantsAssignesIds as $etudiantId) {
                    if (!in_array($etudiantId, $etudiantIds)) {
                        $this->piloteModel->retirerEtudiant($id, $etudiantId);
                    }
                }

                // Ajouter les nouvelles attributions
                $success = true;
                $errorCount = 0;
                $invalidCentre = 0;

                foreach ($etudiantIds as $etudiantId) {
                    $result = $this->piloteModel->assignerEtudiant($id, $etudiantId);
                    if ($result === 'not_same_centre') {
                        $invalidCentre++;
                    } else if (!$result) {
                        $errorCount++;
                        $success = false;
                        $errors[] = "Erreur lors de l'attribution de l'étudiant #" . $etudiantId;
                    }
                }

                if ($invalidCentre > 0) {
                    $errors[] = "{$invalidCentre} étudiant(s) n'ont pas pu être attribués car ils appartiennent à un centre différent du pilote.";
                }

                if ($success && $errorCount == 0) {
                    // Journalisation du succès
                    $this->logManager->success(
                        "Attribution d'étudiants à un pilote",
                        $_SESSION['email'],
                        [
                            'pilote_id' => $id,
                            'pilote_nom' => $pilote['nom'],
                            'nb_etudiants' => count($etudiantIds)
                        ]
                    );

                    // Message de succès
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "Les étudiants ont été attribués avec succès." .
                            ($invalidCentre > 0 ? " ({$invalidCentre} avec centre incompatible ignorés)" : "")
                    ];

                    // Redirection vers la liste des étudiants du pilote
                    redirect(url('pilotes', 'etudiants', ['id' => $id]));
                } else {
                    // Journalisation de l'échec
                    $this->logManager->error(
                        "Échec d'attribution d'étudiants à un pilote",
                        $_SESSION['email'],
                        [
                            'pilote_id' => $id,
                            'pilote_nom' => $pilote['nom'],
                            'errors' => $errors
                        ]
                    );
                }
            } else {
                // Aucun étudiant sélectionné, supprimer toutes les attributions
                foreach ($etudiantsAssignesIds as $etudiantId) {
                    $this->piloteModel->retirerEtudiant($id, $etudiantId);
                }

                // Message de succès
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => "Toutes les attributions d'étudiants ont été supprimées."
                ];

                // Redirection vers la liste des étudiants du pilote
                redirect(url('pilotes', 'etudiants', ['id' => $id]));
            }
        }

        // Définir le titre de la page
        $pageTitle = "Attribuer des étudiants à " . $pilote['prenom'] . ' ' . $pilote['nom'];

        // Chargement de la vue
        include VIEWS_PATH . '/pilotes/attribuer_etudiants.php';
    }

    /**
     * Supprime l'attribution d'un étudiant à un pilote
     */
    public function retirerEtudiant() {
        // Vérification des droits d'accès (admin uniquement)
        if (!isAdmin()) {
            // Journalisation de la tentative d'accès non autorisé
            $this->logManager->warning(
                "Tentative de retrait d'étudiant sans autorisation",
                $_SESSION['email']
            );

            // Redirection vers la liste si droits insuffisants
            redirect(url('pilotes'));
        }

        // Récupération des IDs
        $piloteId = isset($_GET['pilote_id']) ? (int)$_GET['pilote_id'] : 0;
        $etudiantId = isset($_GET['etudiant_id']) ? (int)$_GET['etudiant_id'] : 0;

        if ($piloteId <= 0 || $etudiantId <= 0) {
            // Redirection vers la liste si IDs invalides
            redirect(url('pilotes'));
        }

        // Suppression de l'attribution
        $result = $this->piloteModel->retirerEtudiant($piloteId, $etudiantId);

        if ($result) {
            // Journalisation du succès
            $this->logManager->success(
                "Retrait de l'attribution d'un étudiant à un pilote",
                $_SESSION['email'],
                [
                    'pilote_id' => $piloteId,
                    'etudiant_id' => $etudiantId
                ]
            );

            // Message de succès
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "L'étudiant a été retiré avec succès."
            ];
        } else {
            // Journalisation de l'échec
            $this->logManager->error(
                "Échec du retrait de l'attribution d'un étudiant à un pilote",
                $_SESSION['email'],
                [
                    'pilote_id' => $piloteId,
                    'etudiant_id' => $etudiantId
                ]
            );

            // Message d'erreur
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Une erreur est survenue lors du retrait de l'étudiant."
            ];
        }

        // Redirection vers la liste des étudiants du pilote
        redirect(url('pilotes', 'etudiants', ['id' => $piloteId]));
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

        // Validation du centre (optionnel)
        if (isset($data['centre_id']) && !empty($data['centre_id'])) {
            if (!is_numeric($data['centre_id'])) {
                $errors[] = "Le centre sélectionné n'est pas valide.";
            } else {
                // Vérifier que le centre existe
                $centre = $this->centreModel->getById($data['centre_id']);
                if (!$centre) {
                    $errors[] = "Le centre sélectionné n'existe pas.";
                }
            }
        }

        return $errors;
    }
}