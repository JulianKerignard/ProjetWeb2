<?php
/**
 * Modèle pour la gestion de l'authentification
 * Version optimisée avec diagnostics améliorés
 *
 * @version 2.0
 */
class Auth {
    private $conn;
    private $table = 'utilisateurs';
    private $etudiantsTable = 'etudiants';
    private $pilotesTable = 'pilotes';

    /**
     * Constructeur - Initialise la connexion à la base de données
     */
    public function __construct() {
        // Initialisation de la connexion à la base de données
        require_once ROOT_PATH . '/config/database.php';
        $database = new Database();
        $this->conn = $database->getConnection();

        // Vérification critique de la connexion
        if ($this->conn === null) {
            error_log("[CRITICAL] Échec d'initialisation de la connexion DB dans Auth.php");
        }
    }

    /**
     * Authentification d'un utilisateur avec mécanisme robuste de journalisation
     *
     * @param string $email Identifiant de l'utilisateur
     * @param string $password Mot de passe en clair
     * @return array|bool Données utilisateur ou false en cas d'échec
     */
    public function login($email, $password) {
        try {
            // Journalisation de la tentative d'authentification (sécurité)
            error_log("[AUTH] Tentative d'authentification pour: " . $email);

            // Requête optimisée pour récupérer uniquement l'utilisateur principal
            $query = "SELECT u.id, u.email, u.password, u.role FROM {$this->table} u WHERE u.email = :email";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Vérification de l'existence de l'utilisateur
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Journalisation diagnostique (à désactiver en production)
                error_log("[AUTH] Utilisateur trouvé, vérification mot de passe pour: " . $email);
                // error_log("[DEBUG] Hash stocké: " . $user['password']); // Sécurité: à ne pas activer en production

                // Vérification sécurisée du mot de passe avec password_verify()
                if (password_verify($password, $user['password'])) {
                    // Suppression du hash de mot de passe des données retournées
                    unset($user['password']);

                    // Gestion spécifique pour l'administrateur
                    if ($user['role'] === 'admin') {
                        // L'administrateur n'a pas d'entrée dans les tables de profil
                        $user['nom'] = 'Administrateur';
                        $user['prenom'] = 'Système';

                        error_log("[AUTH] Authentification réussie pour l'administrateur: " . $email);
                        return $user;
                    }

                    // Récupération des informations complémentaires selon le rôle
                    $profileQuery = null;

                    if ($user['role'] === 'etudiant') {
                        $profileQuery = "SELECT nom, prenom FROM {$this->etudiantsTable} WHERE user_id = :user_id";
                    } else if ($user['role'] === 'pilote') {
                        $profileQuery = "SELECT nom, prenom FROM {$this->pilotesTable} WHERE user_id = :user_id";
                    }

                    if ($profileQuery) {
                        $profileStmt = $this->conn->prepare($profileQuery);
                        $profileStmt->bindParam(':user_id', $user['id']);
                        $profileStmt->execute();

                        if ($profileStmt->rowCount() > 0) {
                            $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);
                            $user['nom'] = $profile['nom'];
                            $user['prenom'] = $profile['prenom'];
                        } else {
                            // Profil incomplet - fournir des valeurs par défaut
                            error_log("[WARNING] Utilisateur sans profil associé: " . $email);
                            $user['nom'] = 'Utilisateur';
                            $user['prenom'] = '';
                        }
                    }

                    error_log("[AUTH] Authentification réussie pour: " . $email . " (rôle: " . $user['role'] . ")");
                    return $user;
                } else {
                    error_log("[AUTH] Échec de vérification du mot de passe pour: " . $email);
                }
            } else {
                error_log("[AUTH] Utilisateur non trouvé: " . $email);
            }

            return false;
        } catch (PDOException $e) {
            // Journalisation structurée de l'erreur
            error_log("[CRITICAL] Exception PDO dans Auth::login(): " . $e->getMessage());
            error_log("[CRITICAL] Trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Vérification d'existence d'un utilisateur
     *
     * @param string $email
     * @return bool
     */
    public function userExists($email) {
        try {
            $query = "SELECT id FROM {$this->table} WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("[ERROR] Erreur lors de la vérification d'existence: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Réinitialisation du mot de passe administrateur
     * Fonction de maintenance essentielle pour la récupération d'accès
     *
     * @return bool
     */
    public function resetAdminPassword() {
        try {
            // Hachage connu pour le mot de passe "admin123"
            $defaultHash = '$2y$10$3OQWJkIKv2AE2dGBTWJy7.MwQ9hGUJbD3pdL7dFBVXHPSGG8mhUKy';

            // Vérification de l'existence de l'admin
            $query = "SELECT id FROM {$this->table} WHERE email = 'admin@web4all.fr' AND role = 'admin'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                // Admin existe - mise à jour du mot de passe
                $query = "UPDATE {$this->table} SET password = :password 
                          WHERE email = 'admin@web4all.fr' AND role = 'admin'";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':password', $defaultHash);
                return $stmt->execute();
            } else {
                // Admin n'existe pas - création
                $query = "INSERT INTO {$this->table} (email, password, role, created_at) 
                          VALUES ('admin@web4all.fr', :password, 'admin', NOW())";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':password', $defaultHash);
                return $stmt->execute();
            }
        } catch (PDOException $e) {
            error_log("[CRITICAL] Erreur lors de la réinitialisation admin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer un utilisateur par son ID
     *
     * @param int $id
     * @return array|bool
     */
    public function getUserById($id) {
        try {
            // Requête optimisée avec jointures conditionnelles
            $query = "SELECT u.id, u.email, u.role,
                      e.nom as etudiant_nom, e.prenom as etudiant_prenom,
                      p.nom as pilote_nom, p.prenom as pilote_prenom
                      FROM {$this->table} u
                      LEFT JOIN {$this->etudiantsTable} e ON u.id = e.user_id AND u.role = 'etudiant'
                      LEFT JOIN {$this->pilotesTable} p ON u.id = p.user_id AND u.role = 'pilote'
                      WHERE u.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Consolidation des données utilisateur
                if ($user['role'] === 'admin') {
                    $user['nom'] = 'Administrateur';
                    $user['prenom'] = 'Système';
                } else if ($user['role'] === 'etudiant') {
                    $user['nom'] = $user['etudiant_nom'];
                    $user['prenom'] = $user['etudiant_prenom'];
                } else if ($user['role'] === 'pilote') {
                    $user['nom'] = $user['pilote_nom'];
                    $user['prenom'] = $user['pilote_prenom'];
                }

                // Nettoyage des champs redondants
                unset($user['etudiant_nom']);
                unset($user['etudiant_prenom']);
                unset($user['pilote_nom']);
                unset($user['pilote_prenom']);

                return $user;
            }

            return false;
        } catch (PDOException $e) {
            error_log("[ERROR] Erreur lors de la récupération utilisateur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Génération d'un hash de mot de passe sécurisé
     * Utilitaire pour la gestion des mots de passe
     *
     * @param string $password
     * @return string
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
    }
}