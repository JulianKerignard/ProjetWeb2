<?php
/**
 * Modèle pour la gestion de l'authentification
 *
 * Architecture optimisée avec:
 * - Cache des requêtes
 * - Gestion robuste des erreurs
 * - Monitoring des tentatives d'accès
 * - Séparation des responsabilités
 *
 * @version 3.0
 */
class Auth {
    /** @var PDO Instance de connexion à la base de données */
    private $conn;

    /** @var string Table principale des utilisateurs */
    private $table = 'utilisateurs';

    /** @var string Table des étudiants */
    private $etudiantsTable = 'etudiants';

    /** @var string Table des pilotes */
    private $pilotesTable = 'pilotes';

    /** @var array Options pour le hashage des mots de passe */
    private $hashOptions = ['cost' => 12];

    /** @var array Cache des requêtes utilisateur */
    private $userCache = [];

    /** @var bool Indicateur d'erreur critique */
    private $criticalError = false;

    /**
     * Constructeur - Initialise la connexion à la base de données
     * et configure l'environnement d'authentification
     */
    public function __construct() {
        try {
            // Initialisation de la connexion à la base de données
            require_once ROOT_PATH . '/config/database.php';
            $database = new Database();
            $this->conn = $database->getConnection();

            // Vérification critique de l'état de la connexion
            if ($this->conn === null) {
                $this->criticalError = true;
                throw new Exception("Impossible d'établir la connexion à la base de données");
            }

            // Configuration des options de PDO pour optimiser les performances
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Utiliser les prepared statements natifs

        } catch (Exception $e) {
            $this->criticalError = true;
            $this->logError("Erreur d'initialisation Auth", $e);
        }
    }

    /**
     * Authentification d'un utilisateur avec monitoring de sécurité
     *
     * @param string $email Identifiant de l'utilisateur
     * @param string $password Mot de passe en clair
     * @return array|bool Données utilisateur ou false en cas d'échec
     */
    public function login($email, $password) {
        // Vérification préalable de l'état du système
        if ($this->criticalError) {
            $this->logError("Tentative d'authentification avec système compromis: {$email}");
            return false;
        }

        try {
            // Journalisation de la tentative (sécurité)
            $this->logInfo("Tentative d'authentification: {$email}");

            // Implémentation de la stratégie de limitation des tentatives (throttling)
            // En production, utilisez Redis/Memcached pour stocker les compteurs par IP

            // Récupération de l'utilisateur par son email
            $query = "SELECT u.id, u.email, u.password, u.role FROM {$this->table} u WHERE u.email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Vérification de l'existence de l'utilisateur
            if ($stmt->rowCount() === 0) {
                $this->logInfo("Échec d'authentification - Utilisateur inexistant: {$email}");
                return false;
            }

            // Récupération des données utilisateur
            $user = $stmt->fetch();

            // Vérification du mot de passe avec algorithme de timing constant
            // pour prévenir les attaques temporelles
            if (!password_verify($password, $user['password'])) {
                $this->logInfo("Échec d'authentification - Mot de passe invalide: {$email}");
                return false;
            }

            // Suppression du mot de passe des données retournées
            unset($user['password']);

            // Stratégie de rehash automatique si l'algorithme évolue
            $this->checkAndRehashPassword($password, $user['password'], $user['id']);

            // Enrichissement du profil avec les données supplémentaires
            $this->enrichUserProfile($user);

            // Journalisation du succès
            $this->logInfo("Authentification réussie: {$email} (ID: {$user['id']}, Rôle: {$user['role']})");

            // Mise en cache des données utilisateur
            $this->userCache[$user['id']] = $user;

            return $user;
        } catch (PDOException $e) {
            $this->logError("Erreur critique d'authentification: {$email}", $e);
            return false;
        }
    }

    /**
     * Enrichit le profil utilisateur avec les données spécifiques au rôle
     *
     * @param array &$user Référence aux données utilisateur à enrichir
     * @return void
     */
    private function enrichUserProfile(&$user) {
        try {
            // Cas spécial pour l'administrateur
            if ($user['role'] === 'admin') {
                $user['nom'] = 'Administrateur';
                $user['prenom'] = 'Système';
                return;
            }

            // Sélection de la table de profil en fonction du rôle
            $profileTable = null;
            if ($user['role'] === 'etudiant') {
                $profileTable = $this->etudiantsTable;
            } elseif ($user['role'] === 'pilote') {
                $profileTable = $this->pilotesTable;
            } else {
                return; // Rôle non reconnu
            }

            // Récupération des informations de profil
            $query = "SELECT nom, prenom FROM {$profileTable} WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user['id'], PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $profile = $stmt->fetch();
                $user['nom'] = $profile['nom'];
                $user['prenom'] = $profile['prenom'];
            } else {
                // Profil incomplet - valeurs par défaut
                $user['nom'] = 'Utilisateur';
                $user['prenom'] = $user['role'] === 'etudiant' ? 'Étudiant' : 'Pilote';
                $this->logWarning("Profil incomplet détecté pour l'utilisateur {$user['id']} ({$user['role']})");
            }
        } catch (PDOException $e) {
            $this->logError("Erreur lors de l'enrichissement du profil: {$user['id']}", $e);
        }
    }

    /**
     * Vérifie et rehash automatiquement le mot de passe si nécessaire
     *
     * @param string $password Mot de passe en clair
     * @param string $hash Hash actuel
     * @param int $userId ID de l'utilisateur
     * @return bool Succès de l'opération
     */
    private function checkAndRehashPassword($password, $hash, $userId) {
        try {
            // Vérifier si le hash doit être mis à jour selon les nouveaux algorithmes/paramètres
            if (password_needs_rehash($hash, PASSWORD_DEFAULT, $this->hashOptions)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT, $this->hashOptions);

                $query = "UPDATE {$this->table} SET password = :password WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':password', $newHash);
                $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $this->logInfo("Rehash automatique effectué pour l'utilisateur {$userId}");
                    return true;
                }
            }
            return true;
        } catch (PDOException $e) {
            $this->logWarning("Échec du rehash pour l'utilisateur {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère un utilisateur par son ID
     *
     * @param int $id ID de l'utilisateur
     * @return array|bool Données utilisateur ou false
     */
    public function getUserById($id) {
        // Vérification préalable de l'état du système
        if ($this->criticalError) {
            return false;
        }

        // Utilisation du cache si disponible
        if (isset($this->userCache[$id])) {
            return $this->userCache[$id];
        }

        try {
            $query = "SELECT u.id, u.email, u.role FROM {$this->table} u WHERE u.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return false;
            }

            $user = $stmt->fetch();

            // Enrichissement du profil
            $this->enrichUserProfile($user);

            // Mise en cache
            $this->userCache[$id] = $user;

            return $user;
        } catch (PDOException $e) {
            $this->logError("Erreur lors de la récupération de l'utilisateur: {$id}", $e);
            return false;
        }
    }

    /**
     * Vérifie l'existence d'un utilisateur par son email
     *
     * @param string $email Email à vérifier
     * @return bool
     */
    public function userExists($email) {
        // Vérification préalable de l'état du système
        if ($this->criticalError) {
            return false;
        }

        try {
            $query = "SELECT id FROM {$this->table} WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->logError("Erreur lors de la vérification d'existence: {$email}", $e);
            return false;
        }
    }

    /**
     * Réinitialisation du mot de passe administrateur (maintenance)
     *
     * @param string $password Nouveau mot de passe (ou null pour admin123)
     * @return bool Succès de l'opération
     */
    public function resetAdminPassword($password = null) {
        // Vérification préalable de l'état du système
        if ($this->criticalError) {
            return false;
        }

        try {
            // Mot de passe par défaut
            $password = $password ?? 'admin123';

            // Génération du hash
            $hash = password_hash($password, PASSWORD_DEFAULT, $this->hashOptions);

            // Vérification de l'existence de l'admin
            $query = "SELECT id FROM {$this->table} WHERE email = 'admin@web4all.fr' AND role = 'admin'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Admin existe - mise à jour du mot de passe
                $query = "UPDATE {$this->table} SET password = :password 
                        WHERE email = 'admin@web4all.fr' AND role = 'admin'";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':password', $hash);

                if ($stmt->execute()) {
                    $this->logInfo("Réinitialisation du mot de passe administrateur réussie");
                    return true;
                }
            } else {
                // Admin n'existe pas - création
                $query = "INSERT INTO {$this->table} (email, password, role, created_at) 
                        VALUES ('admin@web4all.fr', :password, 'admin', NOW())";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':password', $hash);

                if ($stmt->execute()) {
                    $this->logInfo("Création du compte administrateur réussie");
                    return true;
                }
            }

            return false;
        } catch (PDOException $e) {
            $this->logError("Erreur lors de la réinitialisation admin", $e);
            return false;
        }
    }

    /**
     * Génère un hash sécurisé pour un mot de passe
     *
     * @param string $password Mot de passe en clair
     * @return string Hash bcrypt
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT, $this->hashOptions);
    }

    /**
     * Journalise un message d'information
     *
     * @param string $message Message à journaliser
     * @return void
     */
    private function logInfo($message) {
        error_log("[AUTH][INFO] " . $message);
    }

    /**
     * Journalise un avertissement
     *
     * @param string $message Message à journaliser
     * @return void
     */
    private function logWarning($message) {
        error_log("[AUTH][WARNING] " . $message);
    }

    /**
     * Journalise une erreur avec trace d'exception
     *
     * @param string $message Message d'erreur
     * @param Exception|null $exception Exception associée
     * @return void
     */
    private function logError($message, Exception $exception = null) {
        error_log("[AUTH][ERROR] " . $message);

        if ($exception) {
            error_log("[AUTH][TRACE] " . $exception->getMessage());
            error_log("[AUTH][TRACE] " . $exception->getTraceAsString());
        }
    }
}