<?php
/**
 * Contrôleur pour la gestion des candidatures et wishlists
 *
 * Implémente les fonctionnalités de candidature aux offres de stage
 * et de gestion de la liste de souhaits avec validation des droits
 * d'accès, journalisation avancée et gestion robuste des erreurs.
 *
 * @version 2.0
 */
class CandidatureController {
    private $candidatureModel;
    private $offreModel;
    private $etudiantModel;
    private $logManager;

    /**
     * Constructeur - Initialise les modèles nécessaires
     */
    public function __construct() {
        // Vérification d'authentification avec stockage de la route demandée
        if (!isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            redirect(url('auth', 'login'));
        }

        // Vérification de rôle souple pour les actions spécifiques
        $restrictedActions = ['mes-candidatures', 'afficher-wishlist', 'postuler'];
        $currentAction = isset($_GET['action']) ? $_GET['action'] : '';

        if (in_array($currentAction, $restrictedActions) && !$this->isUserEtudiant()) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Vous n'avez pas les droits nécessaires pour accéder à cette page."
            ];
            redirect(url());
        }

        // Chargement des modèles nécessaires
        require_once MODELS_PATH . '/Candidature.php';
        require_once MODELS_PATH . '/Offre.php';
        require_once MODELS_PATH . '/Etudiant.php';
        require_once ROOT_PATH . '/includes/LogManager.php';

        $this->candidatureModel = new Candidature();
        $this->offreModel = new Offre();
        $this->etudiantModel = new Etudiant();
        $this->logManager = LogManager::getInstance();
    }

    /**
     * Vérifie si l'utilisateur connecté est un étudiant
     *
     * @return bool
     */
    private function isUserEtudiant() {
        return isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ETUDIANT;
    }

    /**
     * Récupère l'ID étudiant correspondant à l'utilisateur connecté
     *
     * @return int|false ID étudiant ou false en cas d'erreur
     */
    private function getCurrentEtudiantId() {
        // Si déjà en cache dans la session, retourner directement
        if (isset($_SESSION['etudiant_id'])) {
            $this->logManager->info("Utilisation de l'ID étudiant en cache: " . $_SESSION['etudiant_id'], $_SESSION['email']);
            return $_SESSION['etudiant_id'];
        }

        // Sinon, chercher dans la base de données
        $etudiantId = $this->etudiantModel->getEtudiantIdFromUserId($_SESSION['user_id']);

        // Journalisation du résultat
        $this->logManager->info(
            "Récupération ID étudiant pour user_id=" . $_SESSION['user_id'],
            $_SESSION['email'],
            ['resultat' => $etudiantId ? $etudiantId : "non trouvé"]
        );

        // Mettre en cache dans la session pour les futures requêtes
        if ($etudiantId) {
            $_SESSION['etudiant_id'] = $etudiantId;
        } else {
            $this->logManager->warning(
                "ID étudiant non trouvé pour l'utilisateur",
                $_SESSION['email'],
                ['user_id' => $_SESSION['user_id']]
            );
        }

        return $etudiantId;
    }

    /**
     * Affiche les candidatures de l'étudiant connecté
     */
    public function mesCandidatures() {
        // Récupération de l'ID étudiant
        $etudiantId = $this->getCurrentEtudiantId();

        if (!$etudiantId) {
            $this->logManager->error(
                "Erreur d'accès au profil étudiant",
                $_SESSION['email'],
                ['user_id' => $_SESSION['user_id']]
            );

            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Erreur d'accès à votre profil étudiant. Veuillez contacter l'administrateur."
            ];
            redirect(url());
        }

        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Construction des filtres
        $filters = [
            'etudiant_id' => $etudiantId
        ];

        // Filtres de date éventuels
        if (isset($_GET['date_debut']) && !empty($_GET['date_debut'])) {
            $filters['date_debut'] = cleanData($_GET['date_debut']);
        }
        if (isset($_GET['date_fin']) && !empty($_GET['date_fin'])) {
            $filters['date_fin'] = cleanData($_GET['date_fin']);
        }

        // Récupération des candidatures
        $candidatures = $this->candidatureModel->getAll($page, ITEMS_PER_PAGE, $filters);
        $totalCandidatures = $this->candidatureModel->countAll($filters);

        // Journalisation de la consultation
        $this->logManager->info(
            "Consultation des candidatures personnelles",
            $_SESSION['email'],
            [
                'etudiant_id' => $etudiantId,
                'nombre_candidatures' => count($candidatures)
            ]
        );

        // Titre de la page
        $pageTitle = "Mes candidatures";

        // Chargement de la vue
        include VIEWS_PATH . '/candidatures/liste.php';
    }

    /**
     * Formulaire et traitement de candidature et traite l'envoi
     */
    public function postuler() {
        // Logs de débogage
        $this->logManager->info("Début méthode postuler()", $_SESSION['email']);

        // Récupération de l'ID de l'offre
        $offre_id = isset($_GET['offre_id']) ? (int)$_GET['offre_id'] : 0;
        $this->logManager->info("ID offre: " . $offre_id, $_SESSION['email']);

        if ($offre_id <= 0) {
            $this->logManager->warning("ID offre invalide", $_SESSION['email']);
            redirect(url('offres'));
        }

        // Récupération des détails de l'offre
        $offre = $this->offreModel->getById($offre_id);

        if (!$offre) {
            $this->logManager->warning(
                "Offre non trouvée",
                $_SESSION['email'],
                ['offre_id' => $offre_id]
            );
            redirect(url('offres'));
        }

        // Récupération de l'ID étudiant
        $etudiantId = $this->getCurrentEtudiantId();
        $this->logManager->info(
            "ID étudiant récupéré: " . ($etudiantId ? $etudiantId : "non trouvé"),
            $_SESSION['email']
        );

        if (!$etudiantId) {
            $this->logManager->error(
                "Profil étudiant non trouvé",
                $_SESSION['email'],
                ['user_id' => $_SESSION['user_id']]
            );

            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Erreur: Votre profil étudiant n'a pas été trouvé. Veuillez contacter l'administrateur."
            ];
            redirect(url('offres', 'detail', ['id' => $offre_id]));
        }

        // Vérification de candidature existante
        $hasCandidature = $this->candidatureModel->hasCandidature($etudiantId, $offre_id);
        $this->logManager->info(
            "Vérification candidature existante: " . ($hasCandidature ? "oui" : "non"),
            $_SESSION['email']
        );

        if ($hasCandidature) {
            $this->logManager->warning(
                "Candidature déjà existante",
                $_SESSION['email'],
                ['offre_id' => $offre_id, 'etudiant_id' => $etudiantId]
            );

            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'message' => "Vous avez déjà postulé à cette offre."
            ];
            redirect(url('offres', 'detail', ['id' => $offre_id]));
        }

        $errors = [];
        $success = false;
        $lettre_motivation = "";

        // Traitement du formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->logManager->info("Traitement du formulaire POST", $_SESSION['email']);

            // Récupération et nettoyage des données
            $lettre_motivation = isset($_POST['lettre_motivation']) ? cleanData($_POST['lettre_motivation']) : '';

            // Validation des données
            if (empty($lettre_motivation)) {
                $errors[] = "La lettre de motivation est obligatoire.";
                $this->logManager->warning("Erreur: lettre de motivation vide", $_SESSION['email']);
            } elseif (strlen($lettre_motivation) < 10) { // Réduit à 10 pour les tests
                $errors[] = "La lettre de motivation doit contenir au moins 10 caractères.";
                $this->logManager->warning(
                    "Erreur: lettre de motivation trop courte",
                    $_SESSION['email'],
                    ['longueur' => strlen($lettre_motivation)]
                );
            }

            // Validation du fichier CV
            if (!isset($_FILES['cv']) || $_FILES['cv']['error'] != UPLOAD_ERR_OK) {
                $errors[] = "Vous devez téléverser votre CV.";
                $this->logManager->warning(
                    "Erreur: fichier CV manquant ou erreur d'upload",
                    $_SESSION['email'],
                    ['error_code' => isset($_FILES['cv']) ? $_FILES['cv']['error'] : 'non défini']
                );
            } else {
                // Vérification du type de fichier
                $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $fileType = $_FILES['cv']['type'];
                $fileSize = $_FILES['cv']['size'];
                $maxSize = 5 * 1024 * 1024; // 5 Mo

                $this->logManager->info(
                    "CV reçu",
                    $_SESSION['email'],
                    ['type' => $fileType, 'taille' => $fileSize]
                );

                if (!in_array($fileType, $allowedTypes) && !empty($fileType)) {
                    $errors[] = "Le format du fichier n'est pas accepté. Formats acceptés : PDF, DOC, DOCX.";
                    $this->logManager->warning(
                        "Erreur: type de fichier non accepté",
                        $_SESSION['email'],
                        ['type' => $fileType]
                    );
                } elseif ($fileSize > $maxSize) {
                    $errors[] = "Le fichier est trop volumineux. Taille maximale : 5 Mo.";
                    $this->logManager->warning(
                        "Erreur: fichier trop volumineux",
                        $_SESSION['email'],
                        ['taille' => $fileSize, 'max_size' => $maxSize]
                    );
                }
            }

            // Création de la candidature si pas d'erreurs
            if (empty($errors)) {
                $this->logManager->info("Validation réussie, création de la candidature", $_SESSION['email']);

                $data = [
                    'offre_id' => $offre_id,
                    'etudiant_id' => $etudiantId,
                    'lettre_motivation' => $lettre_motivation
                ];

                $result = $this->candidatureModel->create($data, $_FILES);

                if ($result) {
                    $this->logManager->success(
                        "Candidature créée avec succès",
                        $_SESSION['email'],
                        [
                            'candidature_id' => $result,
                            'offre_id' => $offre_id,
                            'offre_titre' => $offre['titre'],
                            'entreprise_nom' => $offre['entreprise_nom']
                        ]
                    );

                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "Votre candidature a été envoyée avec succès."
                    ];
                    redirect(url('candidatures', 'mes-candidatures'));
                } else {
                    $this->logManager->error(
                        "Échec de la création de candidature",
                        $_SESSION['email'],
                        ['offre_id' => $offre_id]
                    );

                    $errors[] = "Une erreur est survenue lors de l'envoi de votre candidature.";
                }
            } else {
                $this->logManager->warning(
                    "Erreurs de validation du formulaire de candidature",
                    $_SESSION['email'],
                    ['errors' => $errors]
                );
            }
        } else {
            $this->logManager->info(
                "Accès au formulaire de candidature",
                $_SESSION['email'],
                [
                    'method' => $_SERVER['REQUEST_METHOD'],
                    'offre_id' => $offre_id,
                    'offre_titre' => $offre['titre'],
                    'entreprise_nom' => $offre['entreprise_nom']
                ]
            );
        }

        // Titre de la page
        $pageTitle = "Postuler à une offre";

        // Chargement de la vue
        include VIEWS_PATH . '/candidatures/postuler.php';

        $this->logManager->info("Fin méthode postuler()", $_SESSION['email']);
    }

    /**
     * Affiche les détails d'une candidature
     */
    public function detail() {
        // Récupération de l'ID de la candidature
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $this->logManager->warning(
                "Tentative d'accès à une candidature avec ID invalide",
                $_SESSION['email']
            );
            redirect(url('candidatures', 'mes-candidatures'));
        }

        // Récupération des détails de la candidature
        $candidature = $this->candidatureModel->getById($id);

        // Récupération de l'ID étudiant associé à l'utilisateur connecté
        $etudiantId = $this->getCurrentEtudiantId();

        // Vérification que la candidature existe et appartient à l'étudiant connecté
        if (!$candidature || $candidature['etudiant_id'] != $etudiantId) {
            $this->logManager->warning(
                "Tentative d'accès à une candidature non autorisée",
                $_SESSION['email'],
                [
                    'candidature_id' => $id,
                    'etudiant_id' => $etudiantId,
                    'candidature_etudiant_id' => $candidature ? $candidature['etudiant_id'] : 'N/A'
                ]
            );
            redirect(url('candidatures', 'mes-candidatures'));
        }

        // Journalisation de la consultation
        $this->logManager->info(
            "Consultation du détail d'une candidature",
            $_SESSION['email'],
            [
                'candidature_id' => $id,
                'offre_id' => $candidature['offre_id'],
                'offre_titre' => $candidature['offre_titre']
            ]
        );

        // Titre de la page
        $pageTitle = "Détail de la candidature";

        // Chargement de la vue
        include VIEWS_PATH . '/candidatures/detail.php';
    }

    /**
     * Supprime une candidature
     */
    public function supprimer() {
        // Récupération de l'ID de la candidature
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            $this->logManager->warning(
                "Tentative de suppression d'une candidature avec ID invalide",
                $_SESSION['email']
            );
            redirect(url('candidatures', 'mes-candidatures'));
        }

        // Récupération des détails de la candidature
        $candidature = $this->candidatureModel->getById($id);

        // Récupération de l'ID étudiant associé à l'utilisateur connecté
        $etudiantId = $this->getCurrentEtudiantId();

        // Vérification que la candidature existe et appartient à l'étudiant connecté
        if (!$candidature || $candidature['etudiant_id'] != $etudiantId) {
            $this->logManager->warning(
                "Tentative de suppression d'une candidature non autorisée",
                $_SESSION['email'],
                [
                    'candidature_id' => $id,
                    'etudiant_id' => $etudiantId,
                    'candidature_etudiant_id' => $candidature ? $candidature['etudiant_id'] : 'N/A'
                ]
            );
            redirect(url('candidatures', 'mes-candidatures'));
        }

        // Confirmation de suppression
        if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
            // Sauvegarde des informations de la candidature pour la journalisation
            $candidatureInfo = [
                'id' => $candidature['id'],
                'offre_id' => $candidature['offre_id'],
                'offre_titre' => $candidature['offre_titre'],
                'entreprise_nom' => $candidature['entreprise_nom']
            ];

            $result = $this->candidatureModel->delete($id);

            if ($result) {
                $this->logManager->success(
                    "Suppression d'une candidature",
                    $_SESSION['email'],
                    $candidatureInfo
                );

                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => "Votre candidature a été supprimée avec succès."
                ];
            } else {
                $this->logManager->error(
                    "Échec de suppression d'une candidature",
                    $_SESSION['email'],
                    $candidatureInfo
                );

                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'message' => "Une erreur est survenue lors de la suppression de votre candidature."
                ];
            }

            redirect(url('candidatures', 'mes-candidatures'));
        }

        // Journalisation de l'accès à la page de confirmation
        $this->logManager->info(
            "Accès à la page de confirmation de suppression d'une candidature",
            $_SESSION['email'],
            [
                'candidature_id' => $id,
                'offre_id' => $candidature['offre_id'],
                'offre_titre' => $candidature['offre_titre']
            ]
        );

        // Titre de la page
        $pageTitle = "Supprimer la candidature";

        // Chargement de la vue de confirmation
        include VIEWS_PATH . '/candidatures/supprimer.php';
    }

    /**
     * Affiche la wishlist de l'étudiant connecté
     */
    public function afficherWishlist() {
        // Récupération de l'ID étudiant
        $etudiantId = $this->getCurrentEtudiantId();

        if (!$etudiantId) {
            $this->logManager->error(
                "Erreur d'accès au profil étudiant pour la wishlist",
                $_SESSION['email'],
                ['user_id' => $_SESSION['user_id']]
            );

            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Erreur d'accès à votre profil étudiant. Veuillez contacter l'administrateur."
            ];
            redirect(url());
        }

        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Ajout logs de débogage
        $this->logManager->info(
            "Récupération wishlist",
            $_SESSION['email'],
            ['etudiant_id' => $etudiantId, 'page' => $page]
        );

        // Récupération de la wishlist
        $wishlist = $this->candidatureModel->getWishlist($etudiantId);

        $this->logManager->info(
            "Nombre d'éléments dans la wishlist",
            $_SESSION['email'],
            ['count' => count($wishlist)]
        );

        $totalWishlist = count($wishlist);

        // Pagination manuelle car getWishlist retourne déjà toutes les entrées
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        $wishlist = array_slice($wishlist, $offset, ITEMS_PER_PAGE);

        // Récupération des offres recommandées
        $recommendedOffers = [];

        // Titre de la page
        $pageTitle = "Ma liste de souhaits";

        // Chargement de la vue
        include VIEWS_PATH . '/candidatures/wishlist.php';
    }

    /**
     * Ajoute une offre à la wishlist
     */
    public function ajouterWishlist() {
        // Récupération de l'ID de l'offre
        $offre_id = isset($_GET['offre_id']) ? (int)$_GET['offre_id'] : 0;

        if ($offre_id <= 0) {
            $this->logManager->warning(
                "Tentative d'ajout à la wishlist avec ID offre invalide",
                $_SESSION['email']
            );
            redirect(url('offres'));
        }

        // Vérification que l'offre existe
        $offre = $this->offreModel->getById($offre_id);
        if (!$offre) {
            $this->logManager->warning(
                "Tentative d'ajout d'une offre inexistante à la wishlist",
                $_SESSION['email'],
                ['offre_id' => $offre_id]
            );
            redirect(url('offres'));
        }

        // Récupération de l'ID étudiant
        $etudiantId = $this->getCurrentEtudiantId();

        if (!$etudiantId) {
            $this->logManager->error(
                "Erreur d'accès au profil étudiant pour l'ajout à la wishlist",
                $_SESSION['email'],
                ['user_id' => $_SESSION['user_id'], 'offre_id' => $offre_id]
            );

            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Erreur d'accès à votre profil étudiant. Veuillez contacter l'administrateur."
            ];
            redirect(url('offres', 'detail', ['id' => $offre_id]));
        }

        // Ajout à la wishlist
        $this->logManager->info(
            "Tentative d'ajout à la wishlist",
            $_SESSION['email'],
            ['etudiant_id' => $etudiantId, 'offre_id' => $offre_id, 'offre_titre' => $offre['titre']]
        );

        $result = $this->candidatureModel->addToWishlist($etudiantId, $offre_id);

        // Journalisation et message de confirmation selon le résultat
        if ($result === 'already_exists') {
            $this->logManager->info(
                "Offre déjà présente dans la wishlist",
                $_SESSION['email'],
                ['offre_id' => $offre_id, 'offre_titre' => $offre['titre']]
            );

            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'message' => "Cette offre est déjà dans vos favoris."
            ];
        } else if ($result) {
            $this->logManager->success(
                "Ajout à la wishlist réussi",
                $_SESSION['email'],
                ['offre_id' => $offre_id, 'offre_titre' => $offre['titre']]
            );

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "L'offre a été ajoutée à vos favoris."
            ];
        } else {
            $this->logManager->error(
                "Échec d'ajout à la wishlist",
                $_SESSION['email'],
                ['offre_id' => $offre_id, 'offre_titre' => $offre['titre']]
            );

            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Une erreur est survenue lors de l'ajout aux favoris."
            ];
        }

        // Redirection vers la page détaillée de l'offre
        redirect(url('offres', 'detail', ['id' => $offre_id]));
    }

    /**
     * Retire une offre de la wishlist
     */
    public function retirerWishlist() {
        // Récupération de l'ID de l'offre
        $offre_id = isset($_GET['offre_id']) ? (int)$_GET['offre_id'] : 0;

        if ($offre_id <= 0) {
            $this->logManager->warning(
                "Tentative de retrait de la wishlist avec ID offre invalide",
                $_SESSION['email']
            );
            redirect(url('candidatures', 'afficher-wishlist'));
        }

        // Récupération de l'ID étudiant
        $etudiantId = $this->getCurrentEtudiantId();

        if (!$etudiantId) {
            $this->logManager->error(
                "Erreur d'accès au profil étudiant pour le retrait de la wishlist",
                $_SESSION['email'],
                ['user_id' => $_SESSION['user_id'], 'offre_id' => $offre_id]
            );

            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Erreur d'accès à votre profil étudiant. Veuillez contacter l'administrateur."
            ];
            redirect(url('candidatures', 'afficher-wishlist'));
        }

        // Récupération du titre de l'offre pour la journalisation
        $offre = $this->offreModel->getById($offre_id);
        $offreTitre = $offre ? $offre['titre'] : "ID: $offre_id";

        // Retrait de la wishlist
        $result = $this->candidatureModel->removeFromWishlist($etudiantId, $offre_id);

        if ($result) {
            $this->logManager->success(
                "Retrait de la wishlist réussi",
                $_SESSION['email'],
                ['offre_id' => $offre_id, 'offre_titre' => $offreTitre]
            );
        } else {
            $this->logManager->warning(
                "Échec du retrait de la wishlist",
                $_SESSION['email'],
                ['offre_id' => $offre_id, 'offre_titre' => $offreTitre]
            );
        }

        // Message de confirmation
        $_SESSION['flash_message'] = [
            'type' => 'success',
            'message' => "L'offre a été retirée de vos favoris."
        ];

        // Déterminer la page de redirection
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'afficher-wishlist') !== false) {
            redirect(url('candidatures', 'afficher-wishlist'));
        } else {
            redirect(url('offres', 'detail', ['id' => $offre_id]));
        }
    }

    /**
     * Répare la wishlist en synchronisant les IDs
     */
    public function repairWishlist() {
        // Vider le cache de session
        if (isset($_SESSION['etudiant_id'])) {
            $oldId = $_SESSION['etudiant_id'];
            unset($_SESSION['etudiant_id']);

            $this->logManager->warning(
                "Suppression de l'ID étudiant en cache pour réparation",
                $_SESSION['email'],
                ['old_etudiant_id' => $oldId]
            );
        }

        // Forcer la récupération de l'ID étudiant
        $etudiantId = $this->getCurrentEtudiantId();

        if ($etudiantId) {
            $this->logManager->success(
                "Synchronisation du profil étudiant réussie",
                $_SESSION['email'],
                ['etudiant_id' => $etudiantId]
            );

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "Synchronisation de votre profil réussie. ID étudiant: $etudiantId"
            ];
        } else {
            $this->logManager->error(
                "Échec de synchronisation du profil étudiant",
                $_SESSION['email'],
                ['user_id' => $_SESSION['user_id']]
            );

            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Impossible de synchroniser votre profil étudiant."
            ];
        }

        redirect(url('candidatures', 'afficher-wishlist'));
    }
}