<?php
/**
 * Contrôleur pour la gestion de l'authentification
 * Implémente la logique de traitement des formulaires et la gestion des sessions
 *
 * @version 2.0
 */
class AuthController {
    private $authModel;

    /**
     * Constructeur - Initialise les modèles nécessaires
     */
    public function __construct() {
        require_once MODELS_PATH . '/Auth.php';
        $this->authModel = new Auth();
    }

    /**
     * Page par défaut - Redirection vers login
     */
    public function index() {
        redirect(url('auth', 'login'));
    }

    /**
     * Affichage et traitement du formulaire de connexion
     * Gestion complète du workflow d'authentification avec validation
     */
    public function login() {
        // Si l'utilisateur est déjà connecté, redirection vers l'accueil
        if (isLoggedIn()) {
            redirect(url());
        }

        $errors = [];
        $formData = [
            'email' => $_POST['email'] ?? '',
        ];

        // Traitement du formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $email = cleanData($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $formData['email'] = $email;

            // Validation des données côté serveur
            if (empty($email)) {
                $errors[] = "L'email est obligatoire";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format d'email invalide";
            }

            if (empty($password)) {
                $errors[] = "Le mot de passe est obligatoire";
            }

            // Si pas d'erreurs, tentative de connexion
            if (empty($errors)) {
                // Réinitialisation forcée du mot de passe administrateur pour le débogage
                // Décommentez cette ligne uniquement pour résoudre les problèmes de connexion admin
                // $this->authModel->resetAdminPassword();

                $user = $this->authModel->login($email, $password);

                if ($user) {
                    // Création de la session utilisateur
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nom'] = $user['nom'] ?? 'Utilisateur';
                    $_SESSION['prenom'] = $user['prenom'] ?? '';

                    // Journal de connexion avec données de sécurité
                    error_log("Connexion réussie - IP: {$_SERVER['REMOTE_ADDR']}, User: {$user['id']}, Role: {$user['role']}");

                    // Redirection vers la page d'accueil avec message de bienvenue
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "Bienvenue, {$_SESSION['prenom']} {$_SESSION['nom']}!"
                    ];
                    redirect(url());
                } else {
                    $errors[] = "Identifiants incorrects. Veuillez vérifier votre email et mot de passe.";

                    // Journal des échecs de connexion (sécurité)
                    error_log("Échec de connexion - IP: {$_SERVER['REMOTE_ADDR']}, Email: $email");

                    // Pour des raisons de sécurité, simuler un délai aléatoire pour prévenir les attaques par force brute
                    usleep(rand(100000, 300000)); // 100-300 ms
                }
            }
        }

        // Affichage du formulaire de connexion
        include VIEWS_PATH . '/auth/login.php';
    }

    /**
     * Déconnexion de l'utilisateur
     * Destruction complète de la session et redirection
     */
    public function logout() {
        // Journalisation de la déconnexion
        if (isLoggedIn()) {
            error_log("Déconnexion - User: {$_SESSION['user_id']}, Email: {$_SESSION['email']}");
        }

        // Destruction de la session
        session_unset();
        session_destroy();

        // Suppression du cookie de session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Message flash pour la prochaine session
        session_start();
        $_SESSION['flash_message'] = [
            'type' => 'info',
            'message' => "Vous avez été déconnecté avec succès."
        ];

        // Redirection vers la page de connexion
        redirect(url('auth', 'login'));
    }

    /**
     * Page de mot de passe oublié
     * Non implémentée, mais disponible pour extension
     */
    public function forgotPassword() {
        $errors = [];
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = cleanData($_POST['email'] ?? '');

            if (empty($email)) {
                $errors[] = "Veuillez saisir votre adresse email";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format d'email invalide";
            } else if (!$this->authModel->userExists($email)) {
                $errors[] = "Aucun compte n'est associé à cette adresse email";
            }

            if (empty($errors)) {
                // Non implémenté - serait la logique d'envoi d'email
                $success = true;
            }
        }

        include VIEWS_PATH . '/auth/forgot_password.php';
    }

    /**
     * Réinitialisation du compte administrateur
     * Fonction de maintenance pour débloquer l'accès administrateur
     */
    public function resetAdmin() {
        // Cette fonction ne devrait être accessible qu'en mode développement
        if (ENVIRONMENT !== 'development') {
            redirect(url());
            return;
        }

        $success = $this->authModel->resetAdminPassword();

        if ($success) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "Le compte administrateur a été réinitialisé avec succès. Email: admin@web4all.fr, Mot de passe: admin123"
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Échec de la réinitialisation du compte administrateur."
            ];
        }

        redirect(url('auth', 'login'));
    }
}