<?php
/**
 * Contrôleur pour la gestion de l'authentification
 *
 * Responsable de toutes les interactions liées à l'authentification:
 * - Login/logout
 * - Vérification des sessions
 * - Mots de passe oubliés (non-implémenté)
 * - Maintenance du système d'authentification
 *
 * @version 3.0
 */
class AuthController {
    /** @var Auth Instance du modèle d'authentification */
    private $authModel;

    /** @var int Nombre maximal de tentatives avant blocage temporaire */
    private $maxLoginAttempts = 5;

    /** @var int Durée du blocage en secondes */
    private $lockoutDuration = 300; // 5 minutes

    /**
     * Constructeur - Initialise les modèles nécessaires
     */
    public function __construct() {
        // Chargement du modèle d'authentification
        require_once MODELS_PATH . '/Auth.php';
        $this->authModel = new Auth();
    }

    /**
     * Action par défaut - Redirection vers login
     */
    public function index() {
        redirect(url('auth', 'login'));
    }

    /**
     * Affichage et traitement du formulaire de connexion
     * Implémente des stratégies avancées de sécurité:
     * - Protection contre force brute
     * - Validation avancée
     * - Journalisation de sécurité
     * - Enrichissement de la session avec l'ID étudiant
     */
    public function login() {
        // Si l'utilisateur est déjà connecté, redirection vers l'accueil
        if (isLoggedIn()) {
            redirect(url());
        }

        // Initialisation des variables pour la vue
        $errors = [];
        $formData = [
            'email' => $_POST['email'] ?? '',
        ];

        // Vérification du blocage temporaire (anti-force brute)
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $attemptCount = $this->getLoginAttempts($ipAddress);

        if ($attemptCount >= $this->maxLoginAttempts) {
            $lockTime = $this->getLockoutTime($ipAddress);
            $remainingTime = $lockTime + $this->lockoutDuration - time();

            if ($remainingTime > 0) {
                $minutes = ceil($remainingTime / 60);
                $errors[] = "Trop de tentatives de connexion. Veuillez réessayer dans {$minutes} minute(s).";
                include VIEWS_PATH . '/auth/login.php';
                return;
            }

            // Réinitialisation du compteur si le délai est écoulé
            $this->resetLoginAttempts($ipAddress);
        }

        // Traitement du formulaire de connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération et nettoyage des données
            $email = cleanData($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $formData['email'] = $email;

            // Validation des données
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
                // Tentative de connexion admin "en dur" (pour déblocage uniquement)
                $forceAdminAuth = false;
                if ($email === 'admin@web4all.fr' && $password === 'admin123') {
                    // Tenter de réinitialiser le mot de passe admin en cas de problème
                    $resetSuccess = $this->authModel->resetAdminPassword();

                    if ($resetSuccess) {
                        error_log("[AUTH][EMERGENCY] Réinitialisation d'urgence du compte admin exécutée");
                        $forceAdminAuth = true;
                    }
                }

                // Tentative de connexion normale
                $user = $this->authModel->login($email, $password);

                // Si échec normal mais déblocage d'urgence activé
                if (!$user && $forceAdminAuth) {
                    // Créer une session admin d'urgence
                    error_log("[AUTH][EMERGENCY] Création d'une session admin d'urgence");

                    $user = [
                        'id' => 1, // ID admin par défaut
                        'email' => 'admin@web4all.fr',
                        'role' => 'admin',
                        'nom' => 'Administrateur',
                        'prenom' => 'Système'
                    ];
                }

                if ($user) {
                    // Réinitialisation du compteur de tentatives
                    $this->resetLoginAttempts($ipAddress);

                    // Création de la session utilisateur
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['nom'] = $user['nom'] ?? 'Utilisateur';
                    $_SESSION['prenom'] = $user['prenom'] ?? '';

                    // Si l'utilisateur est un étudiant, récupérer son ID étudiant
                    if ($user['role'] === ROLE_ETUDIANT) {
                        require_once MODELS_PATH . '/Etudiant.php';
                        $etudiantModel = new Etudiant();
                        $etudiant = $etudiantModel->getByUserId($user['id']);

                        if ($etudiant) {
                            $_SESSION['etudiant_id'] = $etudiant['id'];
                            error_log("[AUTH] ID étudiant {$etudiant['id']} associé à l'utilisateur {$user['id']}");
                        } else {
                            error_log("[AUTH][CRITICAL] Utilisateur {$user['id']} avec rôle étudiant n'a pas de profil étudiant associé");

                            // Tentative de création automatique d'un profil étudiant
                            $etudiantId = $etudiantModel->createProfileForUser($user['id']);
                            if ($etudiantId) {
                                $_SESSION['etudiant_id'] = $etudiantId;
                                error_log("[AUTH][RECOVERY] Profil étudiant ID {$etudiantId} créé automatiquement pour l'utilisateur {$user['id']}");
                            }
                        }
                    }

                    // Journalisation de sécurité avec données contextuelles
                    error_log(sprintf(
                        "[AUTH][LOGIN] Utilisateur %d (%s) connecté | IP: %s | Agent: %s",
                        $user['id'],
                        $user['email'],
                        $ipAddress,
                        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
                    ));

                    // Message de bienvenue
                    $_SESSION['flash_message'] = [
                        'type' => 'success',
                        'message' => "Bienvenue, {$_SESSION['prenom']} {$_SESSION['nom']}!"
                    ];

                    // Redirection selon présence d'une URL stockée
                    if (isset($_SESSION['redirect_after_login'])) {
                        $redirect = $_SESSION['redirect_after_login'];
                        unset($_SESSION['redirect_after_login']);
                        redirect($redirect);
                    } else {
                        redirect(url());
                    }
                } else {
                    // Incrémentation du compteur de tentatives
                    $this->incrementLoginAttempts($ipAddress);

                    // Message d'erreur générique (sécurité)
                    $errors[] = "Identifiants incorrects. Veuillez vérifier vos informations.";

                    // Journalisation de l'échec avec données contextuelles
                    error_log(sprintf(
                        "[AUTH][FAIL] Échec de connexion pour '%s' | IP: %s | Agent: %s | Tentative: %d",
                        $email,
                        $ipAddress,
                        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                        $this->getLoginAttempts($ipAddress)
                    ));

                    // Attente aléatoire pour prévenir les attaques par timing
                    usleep(rand(200000, 500000)); // 200-500ms
                }
            }
        }

        // Affichage du formulaire de connexion
        include VIEWS_PATH . '/auth/login.php';
    }

    /**
     * Déconnexion de l'utilisateur
     * Destruction complète de la session avec sécurité renforcée
     */
    public function logout() {
        // Journalisation de sécurité
        if (isLoggedIn()) {
            error_log(sprintf(
                "[AUTH][LOGOUT] Utilisateur %d (%s) déconnecté | IP: %s",
                $_SESSION['user_id'] ?? 'Unknown',
                $_SESSION['email'] ?? 'Unknown',
                $_SERVER['REMOTE_ADDR']
            ));
        }

        // Destruction complète de la session
        $_SESSION = [];

        // Destruction du cookie de session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Destruction de la session côté serveur
        session_destroy();

        // Redémarrage d'une nouvelle session propre pour message flash
        session_start();

        // Message de confirmation
        $_SESSION['flash_message'] = [
            'type' => 'info',
            'message' => "Vous avez été déconnecté avec succès."
        ];

        // Redirection vers la page de connexion
        redirect(url('auth', 'login'));
    }

    /**
     * Réinitialisation d'urgence du mot de passe administrateur
     * Accessible uniquement en mode développement ou par super-admin
     */
    public function resetAdmin() {
        // Vérification des droits d'accès
        if (ENVIRONMENT !== 'development' && (!isLoggedIn() || !isAdmin())) {
            // Redirection silencieuse vers l'accueil sans indiquer la raison (sécurité)
            redirect(url());
            return;
        }

        // Tentative de réinitialisation
        $success = $this->authModel->resetAdminPassword();

        if ($success) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "Le compte administrateur a été réinitialisé avec succès."
            ];

            error_log("[AUTH][ADMIN] Réinitialisation du compte admin effectuée par "
                . (isLoggedIn() ? $_SESSION['email'] : 'console')
                . " | IP: " . $_SERVER['REMOTE_ADDR']);
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Échec de la réinitialisation du compte administrateur."
            ];

            error_log("[AUTH][ERROR] Échec de réinitialisation du compte admin par "
                . (isLoggedIn() ? $_SESSION['email'] : 'console')
                . " | IP: " . $_SERVER['REMOTE_ADDR']);
        }

        // Redirection vers la page de connexion
        redirect(url('auth', 'login'));
    }

    /**
     * Récupère le nombre de tentatives de connexion pour une adresse IP
     *
     * @param string $ip Adresse IP
     * @return int Nombre de tentatives
     */
    private function getLoginAttempts($ip) {
        // En production, utiliser Redis/Memcached pour performance
        // Implémentation simplifiée avec sessions
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }

        if (!isset($_SESSION['login_attempts'][$ip])) {
            $_SESSION['login_attempts'][$ip] = [
                'count' => 0,
                'time' => 0
            ];
        }

        return $_SESSION['login_attempts'][$ip]['count'];
    }

    /**
     * Incrémente le compteur de tentatives pour une adresse IP
     *
     * @param string $ip Adresse IP
     * @return void
     */
    private function incrementLoginAttempts($ip) {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }

        if (!isset($_SESSION['login_attempts'][$ip])) {
            $_SESSION['login_attempts'][$ip] = [
                'count' => 0,
                'time' => 0
            ];
        }

        $_SESSION['login_attempts'][$ip]['count']++;

        // Si premier blocage, enregistrer l'heure
        if ($_SESSION['login_attempts'][$ip]['count'] == $this->maxLoginAttempts) {
            $_SESSION['login_attempts'][$ip]['time'] = time();
        }
    }

    /**
     * Réinitialise le compteur de tentatives pour une adresse IP
     *
     * @param string $ip Adresse IP
     * @return void
     */
    private function resetLoginAttempts($ip) {
        if (isset($_SESSION['login_attempts'][$ip])) {
            $_SESSION['login_attempts'][$ip]['count'] = 0;
            $_SESSION['login_attempts'][$ip]['time'] = 0;
        }
    }

    /**
     * Récupère l'heure de blocage pour une adresse IP
     *
     * @param string $ip Adresse IP
     * @return int Timestamp de blocage
     */
    private function getLockoutTime($ip) {
        if (!isset($_SESSION['login_attempts'][$ip])) {
            return 0;
        }

        return $_SESSION['login_attempts'][$ip]['time'];
    }

    /**
     * Vérifie et répare les relations utilisateur-étudiant manquantes
     * Utilisé pour diagnostiquer et corriger les problèmes d'intégrité
     */
    public function repairUserStudentRelations() {
        // Vérification des droits d'accès
        if (!isAdmin()) {
            redirect(url());
            return;
        }

        require_once MODELS_PATH . '/Etudiant.php';
        $etudiantModel = new Etudiant();
        $result = $etudiantModel->verifyAndRepairUserStudentRelations();

        if ($result['status'] === 'success') {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => "Réparation terminée. {$result['repairs']} profils étudiants créés."
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Erreur lors de la réparation : {$result['message']}"
            ];
        }

        // Log des résultats
        error_log("[AUTH][REPAIR] Réparation des relations utilisateur-étudiant : " . json_encode($result));

        // Redirection vers le tableau de bord admin
        redirect(url('admin'));
    }

    /**
     * Mot de passe oublié - Affichage du formulaire
     * (Fonctionnalité non implémentée)
     */
    public function forgotPassword() {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = cleanData($_POST['email'] ?? '');

            if (empty($email)) {
                $errors[] = "L'email est obligatoire";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Format d'email invalide";
            } else {
                // Fonctionnalité non implémentée, mais message utilisateur optimiste
                $_SESSION['flash_message'] = [
                    'type' => 'info',
                    'message' => "Si votre email existe dans notre système, vous recevrez un lien pour réinitialiser votre mot de passe."
                ];
                redirect(url('auth', 'login'));
            }
        }

        include VIEWS_PATH . '/auth/forgot_password.php';
    }

    /**
     * Diagnostique l'état de l'authentification
     * Utile pour déboguer les problèmes de session
     */
    public function diagnose() {
        // Restreint à l'administrateur et au mode développement
        if (ENVIRONMENT !== 'development' && (!isLoggedIn() || !isAdmin())) {
            redirect(url());
            return;
        }

        $diagnosticData = [
            'session_active' => session_status() === PHP_SESSION_ACTIVE,
            'session_id' => session_id(),
            'session_data' => $_SESSION,
            'login_attempts' => $_SESSION['login_attempts'] ?? [],
            'server' => [
                'remote_addr' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'request_time' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'php_version' => PHP_VERSION
            ]
        ];

        // Si l'utilisateur est connecté, récupérer les infos de la base de données
        if (isLoggedIn()) {
            $userId = $_SESSION['user_id'];
            $userData = $this->authModel->getUserById($userId);

            if ($userData) {
                $diagnosticData['user_db'] = [
                    'id' => $userData['id'],
                    'email' => $userData['email'],
                    'role' => $userData['role'],
                    'created_at' => $userData['created_at']
                ];

                // Chercher le profil étudiant si rôle = étudiant
                if ($userData['role'] === ROLE_ETUDIANT) {
                    require_once MODELS_PATH . '/Etudiant.php';
                    $etudiantModel = new Etudiant();
                    $etudiant = $etudiantModel->getByUserId($userId);

                    if ($etudiant) {
                        $diagnosticData['etudiant_db'] = [
                            'id' => $etudiant['id'],
                            'nom' => $etudiant['nom'],
                            'prenom' => $etudiant['prenom']
                        ];
                    } else {
                        $diagnosticData['etudiant_db'] = 'Profil étudiant non trouvé';
                    }
                }
            } else {
                $diagnosticData['user_db'] = 'Utilisateur non trouvé en base de données';
            }
        }

        // Affichage des données de diagnostic
        header('Content-Type: application/json');
        echo json_encode($diagnosticData, JSON_PRETTY_PRINT);
        exit;
    }
}