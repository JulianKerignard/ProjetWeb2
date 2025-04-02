<?php
/**
 * Contrôleur pour la gestion du profil utilisateur
 *
 * Permet aux utilisateurs de visualiser et modifier leurs informations personnelles
 * selon leur rôle (étudiant, pilote, admin)
 */
class ProfileController {
    private $etudiantModel;
    private $piloteModel;
    private $authModel;
    private $offreModel;
    private $candidatureModel;

    /**
     * Constructeur - Initialise les modèles nécessaires selon le rôle de l'utilisateur
     */
    public function __construct() {
        // Vérification d'authentification
        if (!isLoggedIn()) {
            redirect(url('auth', 'login'));
        }

        // Chargement des modèles selon le rôle
        require_once MODELS_PATH . '/Auth.php';
        $this->authModel = new Auth();

        if ($_SESSION['role'] === ROLE_ETUDIANT) {
            require_once MODELS_PATH . '/Etudiant.php';
            $this->etudiantModel = new Etudiant();

            // Chargement des modèles additionnels pour étudiants
            require_once MODELS_PATH . '/Candidature.php';
            $this->candidatureModel = new Candidature();
        } elseif ($_SESSION['role'] === ROLE_PILOTE) {
            require_once MODELS_PATH . '/Pilote.php';
            $this->piloteModel = new Pilote();
        }

        // Modèle offre pour les actualités
        require_once MODELS_PATH . '/Offre.php';
        $this->offreModel = new Offre();
    }

    /**
     * Action par défaut - Affiche le profil de l'utilisateur connecté
     */
    public function index() {
        // Récupération des informations de l'utilisateur selon son rôle
        $userProfile = $this->getUserProfile();

        if (!$userProfile) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Impossible de charger votre profil. Veuillez contacter l'administrateur."
            ];
            redirect(url());
        }

        // Récupération des données additionnelles pour enrichir le profil
        if ($_SESSION['role'] === ROLE_ETUDIANT) {
            // Récupération de l'activité récente pour les étudiants
            $userProfile['activite_recente'] = $this->getRecentActivity($userProfile['id']);
        }

        // Récupération des actualités récentes du site (dernières offres)
        $actualites = $this->getRecentSiteActivity();

        // Titre de la page
        $pageTitle = "Mon Profil";

        // Chargement de la vue
        include VIEWS_PATH . '/profile/index.php';
    }

    /**
     * Formulaire et traitement de modification du profil
     */
    public function edit() {
        // Récupération des informations actuelles
        $userProfile = $this->getUserProfile();

        if (!$userProfile) {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Impossible de charger votre profil. Veuillez contacter l'administrateur."
            ];
            redirect(url('profile'));
        }

        $errors = [];
        $success = false;

        // Traitement du formulaire de modification
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $updatedProfile = [
                'nom' => isset($_POST['nom']) ? cleanData($_POST['nom']) : '',
                'prenom' => isset($_POST['prenom']) ? cleanData($_POST['prenom']) : '',
                'email' => isset($_POST['email']) ? cleanData($_POST['email']) : '',
                'password' => isset($_POST['password']) ? $_POST['password'] : '',
                'confirm_password' => isset($_POST['confirm_password']) ? $_POST['confirm_password'] : ''
            ];

            // Validation des données
            $errors = $this->validateProfileData($updatedProfile);

            // Si pas d'erreurs, mise à jour du profil
            if (empty($errors)) {
                $result = $this->updateProfile($updatedProfile);

                if ($result) {
                    $success = true;
                    // Mise à jour des données de session si l'email a changé
                    if ($updatedProfile['email'] != $_SESSION['email']) {
                        $_SESSION['email'] = $updatedProfile['email'];
                    }

                    // Mise à jour du nom/prénom dans la session
                    $_SESSION['nom'] = $updatedProfile['nom'];
                    $_SESSION['prenom'] = $updatedProfile['prenom'];

                    // Rafraîchissement des données du profil
                    $userProfile = $this->getUserProfile();
                } else {
                    $errors[] = "Une erreur est survenue lors de la mise à jour de votre profil.";
                }
            }
        }

        // Titre de la page
        $pageTitle = "Modifier Mon Profil";

        // Chargement de la vue
        include VIEWS_PATH . '/profile/edit.php';
    }

    /**
     * Récupère les informations du profil de l'utilisateur connecté
     *
     * @return array|false Données du profil ou false en cas d'échec
     */
    private function getUserProfile() {
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        if ($role === ROLE_ETUDIANT) {
            // Récupération du profil étudiant
            $etudiantId = isset($_SESSION['etudiant_id']) ? $_SESSION['etudiant_id'] : null;

            if (!$etudiantId) {
                $etudiantId = $this->etudiantModel->getEtudiantIdFromUserId($userId);
                if ($etudiantId) {
                    $_SESSION['etudiant_id'] = $etudiantId;
                } else {
                    return false;
                }
            }

            return $this->etudiantModel->getById($etudiantId);
        } elseif ($role === ROLE_PILOTE) {
            // Récupération du profil pilote
            return $this->piloteModel->getByUserId($userId);
        } else {
            // Pour les administrateurs, on récupère juste les infos utilisateur de base
            return $this->authModel->getUserById($userId);
        }
    }

    /**
     * Met à jour le profil de l'utilisateur
     *
     * @param array $data Nouvelles données
     * @return bool
     */
    private function updateProfile($data) {
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        // Suppression du champ confirm_password qui n'est pas nécessaire pour la mise à jour
        unset($data['confirm_password']);

        if ($role === ROLE_ETUDIANT) {
            $etudiantId = $_SESSION['etudiant_id'];
            return $this->etudiantModel->update($etudiantId, $data);
        } elseif ($role === ROLE_PILOTE) {
            $pilote = $this->piloteModel->getByUserId($userId);
            return $this->piloteModel->update($pilote['id'], $data);
        } else {
            // Pour les administrateurs, on met à jour uniquement les infos utilisateur de base
            return $this->authModel->updateUser($userId, $data);
        }
    }

    /**
     * Récupère l'activité récente de l'étudiant (candidatures et wishlist)
     *
     * @param int $etudiantId ID de l'étudiant
     * @return array Activités récentes
     */
    private function getRecentActivity($etudiantId) {
        $activities = [];

        // Récupération des candidatures récentes
        $candidatures = $this->etudiantModel->getCandidaturesForEtudiant($etudiantId);
        foreach (array_slice($candidatures, 0, 5) as $candidature) {
            $activities[] = [
                'type' => 'candidature',
                'date' => $candidature['date_candidature'],
                'message' => 'Vous avez postulé à l\'offre "' . htmlspecialchars($candidature['offre_titre']) . '" chez ' . htmlspecialchars($candidature['entreprise_nom'])
            ];
        }

        // Récupération des wishlist récentes
        $wishlist = $this->etudiantModel->getWishlistForEtudiant($etudiantId);
        foreach (array_slice($wishlist, 0, 5) as $item) {
            $activities[] = [
                'type' => 'wishlist',
                'date' => $item['date_ajout'],
                'message' => 'Vous avez ajouté l\'offre "' . htmlspecialchars($item['offre_titre']) . '" à vos favoris'
            ];
        }

        // Tri par date décroissante
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        // Limiter aux 10 dernières activités
        return array_slice($activities, 0, 10);
    }

    /**
     * Récupère l'activité récente du site (dernières offres, etc.)
     *
     * @return array Actualités du site
     */
    private function getRecentSiteActivity() {
        $actualites = [];

        // Récupération des dernières offres
        $latestOffers = $this->offreModel->getLatest(5);
        foreach ($latestOffers as $offre) {
            $actualites[] = [
                'titre' => 'Nouvelle offre: ' . htmlspecialchars($offre['titre']),
                'description' => 'Publiée par ' . htmlspecialchars($offre['entreprise_nom']),
                'date' => (new DateTime($offre['created_at']))->format('d/m/Y'),
                'url' => url('offres', 'detail', ['id' => $offre['id']])
            ];
        }

        return $actualites;
    }

    /**
     * Validation des données du formulaire profil
     *
     * @param array $data Données à valider
     * @return array Liste des erreurs
     */
    private function validateProfileData($data) {
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

        // Validation du mot de passe uniquement si fourni
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
            } elseif ($data['password'] !== $data['confirm_password']) {
                $errors[] = "Les mots de passe ne correspondent pas.";
            }
        }

        return $errors;
    }
}