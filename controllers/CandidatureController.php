<?php
/**
 * Contrôleur pour la gestion des candidatures et wishlists
 *
 * Implémente les fonctionnalités de candidature aux offres de stage
 * et de gestion de la liste de souhaits avec validation des droits
 * d'accès et gestion robuste des erreurs.
 *
 * @version 1.0
 */
class CandidatureController {
    private $candidatureModel;
    private $offreModel;
    private $etudiantModel;

    /**
     * Constructeur - Initialise les modèles nécessaires
     */
    public function __construct() {
        // Vérification d'authentification
        if (!isLoggedIn()) {
            redirect(url('auth', 'login'));
        }

        // Vérification du rôle étudiant
        if ($_SESSION['role'] !== ROLE_ETUDIANT) {
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
     * Affiche les candidatures de l'étudiant connecté
     */
    public function mesCandidatures() {
        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Construction des filtres
        $filters = [
            'etudiant_id' => $_SESSION['user_id']
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
        // Récupération de l'ID de l'offre
        $offre_id = isset($_GET['offre_id']) ? (int)$_GET['offre_id'] : 0;

        if ($offre_id <= 0) {
            // Redirection vers la liste des offres si ID invalide
            redirect(url('offres'));
        }

        // Récupération des détails de l'offre
        $offre = $this->offreModel->getById($offre_id);
        if (!$offre) {
            // Redirection vers la liste des offres si offre non trouvée
            redirect(url('offres'));
        }

        // Vérification si l'étudiant a déjà postulé
        if ($this->candidatureModel->hasCandidature($_SESSION['user_id'], $offre_id)) {
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
            // Récupération et nettoyage des données
            $lettre_motivation = isset($_POST['lettre_motivation']) ? cleanData($_POST['lettre_motivation']) : '';

            // Validation des données
            if (empty($lettre_motivation)) {
                $errors[] = "La lettre de motivation est obligatoire.";
            } elseif (strlen($lettre_motivation) < 100) {
                $errors[] = "La lettre de motivation doit contenir au moins 100 caractères.";
            }

            // Validation du fichier CV
            if (!isset($_FILES['cv']) || $_FILES['cv']['error'] != UPLOAD_ERR_OK) {
                $errors[] = "Vous devez téléverser votre CV.";
            } else {
                // Vérification du type de fichier
                $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $fileType = $_FILES['cv']['type'];
                $fileSize = $_FILES['cv']['size'];
                $maxSize = 5 * 1024 * 1024; // 5 Mo

                if (!in_array($fileType, $allowedTypes)) {
                    $errors[] = "Le format du fichier n'est pas accepté. Formats acceptés : PDF, DOC, DOCX.";
                } elseif ($fileSize > $maxSize) {
                    $errors[] = "Le fichier est trop volumineux. Taille maximale : 5 Mo.";
                }
            }

            // Création de la candidature si pas d'erreurs
            if (empty($errors)) {
                $data = [
                    'offre_id' => $offre_id,
                    'etudiant_id' => $_SESSION['user_id'],
                    'lettre_motivation' => $lettre_motivation
                ];

                $result = $this->candidatureModel->create($data, $_FILES);

                if ($result) {
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "Votre candidature a été envoyée avec succès."
                    ];
                    redirect(url('candidatures', 'mes-candidatures'));
                } else {
                    $errors[] = "Une erreur est survenue lors de l'envoi de votre candidature.";
                }
            }
        }

        // Titre de la page
        $pageTitle = "Postuler à une offre";

        // Chargement de la vue
        include VIEWS_PATH . '/candidatures/postuler.php';
    }

    /**
     * Affiche les détails d'une candidature
     */
    public function detail() {
        // Récupération de l'ID de la candidature
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($id <= 0) {
            // Redirection vers la liste des candidatures si ID invalide
            redirect(url('candidatures', 'mes-candidatures'));
        }

        // Récupération des détails de la candidature
        $candidature = $this->candidatureModel->getById($id);

        // Vérification que la candidature existe et appartient à l'étudiant connecté
        if (!$candidature || $candidature['etudiant_id'] != $_SESSION['user_id']) {
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
            // Redirection vers la liste des candidatures si ID invalide
            redirect(url('candidatures', 'mes-candidatures'));
        }

        // Récupération des détails de la candidature
        $candidature = $this->candidatureModel->getById($id);

        // Vérification que la candidature existe et appartient à l'étudiant connecté
        if (!$candidature || $candidature['etudiant_id'] != $_SESSION['user_id']) {
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
        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Récupération de la wishlist
        $wishlist = $this->candidatureModel->getWishlist($_SESSION['user_id']);
        $totalWishlist = count($wishlist);

        // Pagination manuelle car getWishlist retourne déjà toutes les entrées
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        $wishlist = array_slice($wishlist, $offset, ITEMS_PER_PAGE);

        // Récupération des offres recommandées (basées sur les compétences des offres en wishlist)
        $recommendedOffers = [];

        // Cette partie serait à implémenter dans le modèle Offre
        // Pour l'instant, on laisse un tableau vide

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
            // Redirection vers la liste des offres si ID invalide
            redirect(url('offres'));
        }

        // Vérification que l'offre existe
        $offre = $this->offreModel->getById($offre_id);
        if (!$offre) {
            redirect(url('offres'));
        }

        // Ajout à la wishlist
        $result = $this->candidatureModel->addToWishlist($_SESSION['user_id'], $offre_id);

        // Message de confirmation
        if ($result) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "L'offre a été ajoutée à vos favoris."
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'message' => "Cette offre est déjà dans vos favoris."
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
            // Redirection vers la wishlist si ID invalide
            redirect(url('candidatures', 'afficher-wishlist'));
        }

        // Retrait de la wishlist
        $this->candidatureModel->removeFromWishlist($_SESSION['user_id'], $offre_id);

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
}