<?php
/**
 * Contrôleur pour la gestion des candidatures et wishlists
 *
 * Implémente les fonctionnalités de candidature aux offres de stage
 * et de gestion de la liste de souhaits avec validation des droits
 * d'accès et gestion robuste des erreurs.
 *
 * @version 1.3
 */
class CandidatureController {
    private $candidatureModel;
    private $offreModel;
    private $etudiantModel;

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

        $this->candidatureModel = new Candidature();
        $this->offreModel = new Offre();
        $this->etudiantModel = new Etudiant();
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
            error_log("ETUDIANT DEBUG: Utilisation de l'ID étudiant en cache: " . $_SESSION['etudiant_id']);
            return $_SESSION['etudiant_id'];
        }

        // Sinon, chercher dans la base de données
        $etudiantId = $this->etudiantModel->getEtudiantIdFromUserId($_SESSION['user_id']);

        // Ajouter ces logs
        error_log("ETUDIANT DEBUG: Récupération ID étudiant pour user_id=" . $_SESSION['user_id'] .
            ", résultat: " . ($etudiantId ? $etudiantId : "non trouvé"));

        // Mettre en cache dans la session pour les futures requêtes
        if ($etudiantId) {
            $_SESSION['etudiant_id'] = $etudiantId;
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

        // Titre de la page
        $pageTitle = "Mes candidatures";

        // Chargement de la vue
        include VIEWS_PATH . '/candidatures/liste.php';
    }

    /**
     * Affiche le formulaire de candidature et traite l'envoi
     */
    public function postuler() {
        // Logs de débogage
        error_log("----------- DÉBUT MÉTHODE POSTULER() -----------");

        // Récupération de l'ID de l'offre
        $offre_id = isset($_GET['offre_id']) ? (int)$_GET['offre_id'] : 0;
        error_log("offre_id = $offre_id");

        if ($offre_id <= 0) {
            error_log("ID offre invalide, redirection...");
            redirect(url('offres'));
        }

        // Récupération des détails de l'offre
        $offre = $this->offreModel->getById($offre_id);
        error_log("Offre récupérée: " . ($offre ? "oui" : "non"));

        if (!$offre) {
            error_log("Offre non trouvée, redirection...");
            redirect(url('offres'));
        }

        // Récupération de l'ID étudiant
        $etudiantId = $this->getCurrentEtudiantId();
        error_log("ID étudiant récupéré: " . ($etudiantId ? $etudiantId : "non trouvé"));

        if (!$etudiantId) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Erreur: Votre profil étudiant n'a pas été trouvé. Veuillez contacter l'administrateur."
            ];
            error_log("Profil étudiant non trouvé pour l'utilisateur " . $_SESSION['user_id']);
            redirect(url('offres', 'detail', ['id' => $offre_id]));
        }

        // Vérification de candidature existante
        $hasCandidature = $this->candidatureModel->hasCandidature($etudiantId, $offre_id);
        error_log("hasCandidature = " . ($hasCandidature ? "true" : "false"));

        if ($hasCandidature) {
            error_log("Candidature existante, redirection...");
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
            error_log("Traitement du formulaire POST reçu");

            // Récupération et nettoyage des données
            $lettre_motivation = isset($_POST['lettre_motivation']) ? cleanData($_POST['lettre_motivation']) : '';

            // Validation des données
            if (empty($lettre_motivation)) {
                $errors[] = "La lettre de motivation est obligatoire.";
                error_log("Erreur: lettre de motivation vide");
            } elseif (strlen($lettre_motivation) < 10) { // Réduit à 10 pour les tests
                $errors[] = "La lettre de motivation doit contenir au moins 10 caractères.";
                error_log("Erreur: lettre de motivation trop courte (" . strlen($lettre_motivation) . " caractères)");
            }

            // Validation du fichier CV
            if (!isset($_FILES['cv']) || $_FILES['cv']['error'] != UPLOAD_ERR_OK) {
                $errors[] = "Vous devez téléverser votre CV.";
                error_log("Erreur: fichier CV manquant ou erreur d'upload");
            } else {
                // Vérification du type de fichier
                $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $fileType = $_FILES['cv']['type'];
                $fileSize = $_FILES['cv']['size'];
                $maxSize = 5 * 1024 * 1024; // 5 Mo

                error_log("CV reçu: type=$fileType, taille=$fileSize");

                if (!in_array($fileType, $allowedTypes) && !empty($fileType)) {
                    $errors[] = "Le format du fichier n'est pas accepté. Formats acceptés : PDF, DOC, DOCX.";
                    error_log("Erreur: type de fichier non accepté ($fileType)");
                } elseif ($fileSize > $maxSize) {
                    $errors[] = "Le fichier est trop volumineux. Taille maximale : 5 Mo.";
                    error_log("Erreur: fichier trop volumineux ($fileSize octets)");
                }
            }

            // Création de la candidature si pas d'erreurs
            if (empty($errors)) {
                error_log("Validation réussie, création de la candidature");

                $data = [
                    'offre_id' => $offre_id,
                    'etudiant_id' => $etudiantId,
                    'lettre_motivation' => $lettre_motivation
                ];

                $result = $this->candidatureModel->create($data, $_FILES);

                if ($result) {
                    error_log("Candidature créée avec succès (ID: $result)");
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "Votre candidature a été envoyée avec succès."
                    ];
                    redirect(url('candidatures', 'mes-candidatures'));
                } else {
                    error_log("Erreur lors de la création de la candidature");
                    $errors[] = "Une erreur est survenue lors de l'envoi de votre candidature.";
                }
            }
        } else {
            error_log("Méthode: " . $_SERVER['REQUEST_METHOD'] . ", Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'non défini'));
        }

        // Titre de la page
        $pageTitle = "Postuler à une offre";

        // Chargement de la vue
        include VIEWS_PATH . '/candidatures/postuler.php';

        error_log("----------- FIN MÉTHODE POSTULER() -----------");
    }

    /**
     * Affiche les détails d'une candidature
     */
    public function detail() {
        // Récupération de l'ID de la candidature
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            redirect(url('candidatures', 'mes-candidatures'));
        }

        // Récupération des détails de la candidature
        $candidature = $this->candidatureModel->getById($id);

        // Récupération de l'ID étudiant associé à l'utilisateur connecté
        $etudiantId = $this->getCurrentEtudiantId();

        // Vérification que la candidature existe et appartient à l'étudiant connecté
        if (!$candidature || $candidature['etudiant_id'] != $etudiantId) {
            redirect(url('candidatures', 'mes-candidatures'));
        }

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
            redirect(url('candidatures', 'mes-candidatures'));
        }

        // Récupération des détails de la candidature
        $candidature = $this->candidatureModel->getById($id);

        // Récupération de l'ID étudiant associé à l'utilisateur connecté
        $etudiantId = $this->getCurrentEtudiantId();

        // Vérification que la candidature existe et appartient à l'étudiant connecté
        if (!$candidature || $candidature['etudiant_id'] != $etudiantId) {
            redirect(url('candidatures', 'mes-candidatures'));
        }

        // Confirmation de suppression
        if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
            $result = $this->candidatureModel->delete($id);

            if ($result) {
                $_SESSION['flash_message'] = [
                    'type' => 'success',
                    'message' => "Votre candidature a été supprimée avec succès."
                ];
            } else {
                $_SESSION['flash_message'] = [
                    'type' => 'danger',
                    'message' => "Une erreur est survenue lors de la suppression de votre candidature."
                ];
            }

            redirect(url('candidatures', 'mes-candidatures'));
        }

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
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Erreur d'accès à votre profil étudiant. Veuillez contacter l'administrateur."
            ];
            redirect(url());
        }

        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Ajout logs
        error_log("WISHLIST DEBUG: Récupération wishlist pour etudiant_id=$etudiantId");

        // Récupération de la wishlist
        $wishlist = $this->candidatureModel->getWishlist($etudiantId);

        error_log("WISHLIST DEBUG: Nombre d'éléments trouvés: " . count($wishlist));

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
            redirect(url('offres'));
        }

        // Vérification que l'offre existe
        $offre = $this->offreModel->getById($offre_id);
        if (!$offre) {
            redirect(url('offres'));
        }

        // Récupération de l'ID étudiant
        $etudiantId = $this->getCurrentEtudiantId();

        if (!$etudiantId) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Erreur d'accès à votre profil étudiant. Veuillez contacter l'administrateur."
            ];
            redirect(url('offres', 'detail', ['id' => $offre_id]));
        }

        // Ajouter logs
        error_log("WISHLIST DEBUG: Ajout offre_id=$offre_id pour etudiant_id=$etudiantId");

        // Ajout à la wishlist
        $result = $this->candidatureModel->addToWishlist($etudiantId, $offre_id);

        // Ajouter log résultat
        error_log("WISHLIST DEBUG: Résultat de l'ajout: " . var_export($result, true));

        // Message de confirmation
        if ($result === 'already_exists') {
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'message' => "Cette offre est déjà dans vos favoris."
            ];
        } else if ($result) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "L'offre a été ajoutée à vos favoris."
            ];
        } else {
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
            redirect(url('candidatures', 'afficher-wishlist'));
        }

        // Récupération de l'ID étudiant
        $etudiantId = $this->getCurrentEtudiantId();

        if (!$etudiantId) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Erreur d'accès à votre profil étudiant. Veuillez contacter l'administrateur."
            ];
            redirect(url('candidatures', 'afficher-wishlist'));
        }

        // Retrait de la wishlist
        $this->candidatureModel->removeFromWishlist($etudiantId, $offre_id);

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
            unset($_SESSION['etudiant_id']);
        }

        // Forcer la récupération de l'ID étudiant
        $etudiantId = $this->getCurrentEtudiantId();

        if ($etudiantId) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "Synchronisation de votre profil réussie. ID étudiant: $etudiantId"
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Impossible de synchroniser votre profil étudiant."
            ];
        }

        redirect(url('candidatures', 'afficher-wishlist'));
    }
}