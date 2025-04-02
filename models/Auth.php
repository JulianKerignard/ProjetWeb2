<?php
/**
 * Modèle pour la gestion de l'authentification et des utilisateurs
 *
 * Gère les opérations liées à l'authentification, comme la connexion,
 * l'inscription, et la gestion des utilisateurs.
 *
 * @version 2.0
 * @author Web4All
 */
class Auth {
    /** @var PDO Instance de connexion à la base de données */
    private $conn;

    /** @var string Nom de la table des utilisateurs */
    private $usersTable = 'utilisateurs';

    /** @var bool Indicateur d'erreur de base de données */
    private $dbError = false;

    /**
     * Constructeur - Initialise la connexion à la BDD avec gestion d'erreurs
     */
    public function __construct() {
        // Vérifier si ROOT_PATH est défini
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));
        }

        // Utiliser le chemin absolu pour l'inclusion
        require_once ROOT_PATH . '/config/database.php';

        try {
            $database = new Database();
            $this->conn = $database->getConnection();

            // Vérification critique de la connexion
            if ($this->conn === null) {
                $this->dbError = true;
                error_log("Mode dégradé activé: Impossible d'établir la connexion à la base de données dans Auth.php");
            }
        } catch (Exception $e) {
            $this->dbError = true;
            error_log("Exception dans Auth::__construct(): " . $e->getMessage());
        }
    }

    /**
     * Indique si une erreur de BDD est survenue
     *
     * @return bool
     */
    public function hasError() {
        return $this->dbError;
    }

    /**
     * Authentification d'un utilisateur
     *
     * @param string $email Email de l'utilisateur
     * @param string $password Mot de passe en clair
     * @return array|false Données de l'utilisateur ou false si échec
     */
    public function login($email, $password) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "SELECT u.*, 
                     e.id as etudiant_id, e.nom as etudiant_nom, e.prenom as etudiant_prenom,
                     p.id as pilote_id, p.nom as pilote_nom, p.prenom as pilote_prenom
                     FROM {$this->usersTable} u
                     LEFT JOIN etudiants e ON u.id = e.user_id
                     LEFT JOIN pilotes p ON u.id = p.user_id
                     WHERE u.email = :email";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                // Utilisateur non trouvé
                return false;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Vérification du mot de passe
            if (!password_verify($password, $user['password'])) {
                // Mot de passe incorrect
                return false;
            }

            // Préparation des données à retourner (sans le mot de passe)
            $userData = [
                'id' => $user['id'],
                'email' => $user['email'],
                'role' => $user['role'],
                'created_at' => $user['created_at']
            ];

            // Ajout des données spécifiques selon le rôle
            if ($user['role'] === 'etudiant' && $user['etudiant_id']) {
                $userData['etudiant_id'] = $user['etudiant_id'];
                $userData['nom'] = $user['etudiant_nom'];
                $userData['prenom'] = $user['etudiant_prenom'];
            } elseif ($user['role'] === 'pilote' && $user['pilote_id']) {
                $userData['pilote_id'] = $user['pilote_id'];
                $userData['nom'] = $user['pilote_nom'];
                $userData['prenom'] = $user['pilote_prenom'];
            } else {
                // Pour les administrateurs, on utilise des valeurs par défaut
                $userData['nom'] = 'Administrateur';
                $userData['prenom'] = 'Système';
            }

            return $userData;
        } catch (PDOException $e) {
            error_log("Erreur lors de la connexion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inscription d'un nouvel utilisateur
     *
     * @param array $data Données de l'utilisateur
     * @return int|false ID de l'utilisateur créé ou false en cas d'échec
     */
    public function register($data) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Vérification si l'email existe déjà
            $checkQuery = "SELECT COUNT(*) as count FROM {$this->usersTable} WHERE email = :email";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':email', $data['email']);
            $checkStmt->execute();

            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($row['count'] > 0) {
                // Email déjà utilisé
                return false;
            }

            // Hachage du mot de passe
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insertion du nouvel utilisateur
            $query = "INSERT INTO {$this->usersTable} (email, password, role, created_at)
                     VALUES (:email, :password, :role, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $data['role']);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erreur lors de l'inscription: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Réinitialise le mot de passe administrateur au défaut
     *
     * @return bool
     */
    public function resetAdminPassword() {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Hachage du mot de passe par défaut
            $defaultPassword = 'admin123';
            $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

            // Mise à jour du mot de passe admin
            $query = "UPDATE {$this->usersTable} SET password = :password WHERE email = 'admin@web4all.fr'";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':password', $hashedPassword);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la réinitialisation du mot de passe admin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les informations d'un utilisateur par son ID
     *
     * @param int $id ID de l'utilisateur
     * @return array|false Données de l'utilisateur ou false si non trouvé
     */
    public function getUserById($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "SELECT id, email, role, created_at, updated_at FROM {$this->usersTable} WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return false;
            }

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Pour les admins, on ajoute des champs supplémentaires car ils n'ont pas de table spécifique
            if ($user['role'] === 'admin') {
                $user['nom'] = isset($_SESSION['nom']) ? $_SESSION['nom'] : 'Administrateur';
                $user['prenom'] = isset($_SESSION['prenom']) ? $_SESSION['prenom'] : 'Système';
            }

            return $user;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'utilisateur par ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour les informations d'un utilisateur
     *
     * @param int $id ID de l'utilisateur
     * @param array $data Nouvelles données
     * @return bool
     */
    public function updateUser($id, $data) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Construction de la requête en fonction des données fournies
            $updateFields = [];
            $params = [];

            if (!empty($data['email'])) {
                $updateFields[] = "email = :email";
                $params[':email'] = $data['email'];
            }

            if (!empty($data['password'])) {
                $updateFields[] = "password = :password";
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $params[':password'] = $hashedPassword;
            }

            if (empty($updateFields)) {
                // Rien à mettre à jour
                return true;
            }

            $updateFields[] = "updated_at = NOW()";

            $query = "UPDATE {$this->usersTable} SET " . implode(', ', $updateFields) . " WHERE id = :id";
            $params[':id'] = $id;

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les utilisateurs avec pagination et filtrage
     *
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @param array $filters Critères de filtrage optionnels
     * @return array
     */
    public function getAllUsers($page = 1, $limit = ITEMS_PER_PAGE, $filters = []) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            // Calcul de l'offset pour la pagination
            $offset = ($page - 1) * $limit;

            // Construction de la requête SQL de base
            $query = "SELECT id, email, role, created_at, updated_at FROM {$this->usersTable}";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['email'])) {
                $whereConditions[] = "email LIKE :email";
                $params[':email'] = '%' . $filters['email'] . '%';
            }

            if (!empty($filters['role'])) {
                $whereConditions[] = "role = :role";
                $params[':role'] = $filters['role'];
            }

            // Ajout des conditions WHERE si présentes
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }

            // Tri des résultats (par défaut, par date de création décroissante)
            $orderBy = !empty($filters['order_by']) ? $filters['order_by'] : 'created_at';
            $orderDir = !empty($filters['order_dir']) ? $filters['order_dir'] : 'DESC';
            $query .= " ORDER BY {$orderBy} {$orderDir}";

            // Ajout de la pagination
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;

            // Exécution de la requête
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
            $stmt->execute();

            // Récupération des résultats
            $users = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = $row;
            }

            return $users;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des utilisateurs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre total d'utilisateurs pour la pagination
     *
     * @param array $filters Critères de filtrage
     * @return int
     */
    public function countUsers($filters = []) {
        // Mode dégradé - retourne 0
        if ($this->dbError) {
            return 0;
        }

        try {
            // Construction de la requête SQL de base
            $query = "SELECT COUNT(*) as total FROM {$this->usersTable}";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['email'])) {
                $whereConditions[] = "email LIKE :email";
                $params[':email'] = '%' . $filters['email'] . '%';
            }

            if (!empty($filters['role'])) {
                $whereConditions[] = "role = :role";
                $params[':role'] = $filters['role'];
            }

            // Ajout des conditions WHERE si présentes
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }

            // Exécution de la requête
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['total'];
        } catch (PDOException $e) {
            error_log("Erreur lors du comptage des utilisateurs: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Supprime un utilisateur
     *
     * @param int $id ID de l'utilisateur
     * @return bool
     */
    public function deleteUser($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "DELETE FROM {$this->usersTable} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'utilisateur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si un email est déjà utilisé
     *
     * @param string $email Email à vérifier
     * @param int $excludeId ID utilisateur à exclure (pour les mises à jour)
     * @return bool True si l'email est déjà utilisé
     */
    public function isEmailTaken($email, $excludeId = null) {
        // Mode dégradé - retourne true par sécurité
        if ($this->dbError) {
            return true;
        }

        try {
            $query = "SELECT COUNT(*) as count FROM {$this->usersTable} WHERE email = :email";
            $params = [':email' => $email];

            if ($excludeId !== null) {
                $query .= " AND id != :id";
                $params[':id'] = $excludeId;
            }

            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                if (is_int($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['count'] > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de l'email: " . $e->getMessage());
            return true; // Par sécurité, considérer que l'email est pris en cas d'erreur
        }
    }
}