<?php
/**
 * Contrôleur pour la gestion de l'authentification
 */
class AuthController {
    private $authModel;

    public function __construct() {
        require_once 'models/Auth.php';
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
     */
    public function login() {
        // Si l'utilisateur est déjà connecté, redirection vers l'accueil
        if (isLoggedIn()) {
            redirect(url());
        }

        $errors = [];

        // Traitement du formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $email = cleanData($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            // Validation des données
            if (empty($email)) {
                $errors[] = "L'email est obligatoire";
            }

            if (empty($password)) {
                $errors[] = "Le mot de passe est obligatoire";
            }

            // Si pas d'erreurs, tentative de connexion
            if (empty($errors)) {
                $user = $this->authModel->login($email, $password);

                if ($user) {
                    // Création de la session utilisateur
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nom'] = $user['nom'];
                    $_SESSION['prenom'] = $user['prenom'];

                    // Redirection vers la page d'accueil
                    redirect(url());
                } else {
                    $errors[] = "Email ou mot de passe incorrect";
                }
            }
        }

        // Affichage du formulaire de connexion
        include ROOT_PATH . '/views/auth/login.php';
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout() {
        // Destruction de la session
        session_unset();
        session_destroy();

        // Redirection vers la page de connexion
        redirect(url('auth', 'login'));
    }

    /**
     * Inscription (si nécessaire)
     */
    public function register() {
        // À implémenter si nécessaire
        echo "Fonctionnalité d'inscription à implémenter";
    }
}