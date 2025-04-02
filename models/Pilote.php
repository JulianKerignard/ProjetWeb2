<?php
/**
 * Modèle pour la gestion des pilotes de promotion
 *
 * Implémente les opérations CRUD et les services spécifiques aux pilotes
 * avec optimisations des requêtes et gestion robuste des erreurs.
 *
 * @version 2.0
 * @author Web4All
 */
class Pilote {
    /** @var PDO Instance de connexion à la base de données */
    private $conn;

    /** @var string Nom de la table principale */
    private $table = 'pilotes';

    /** @var string Nom de la table utilisateurs */
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
                error_log("Mode dégradé activé: Impossible d'établir la connexion à la base de données dans Pilote.php");
            }
        } catch (Exception $e) {
            $this->dbError = true;
            error_log("Exception dans Pilote::__construct(): " . $e->getMessage());
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
     * Récupère tous les pilotes avec pagination et filtrage
     *
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @param array $filters Critères de filtrage optionnels
     * @return array
     */
    public function getAll($page = 1, $limit = ITEMS_PER_PAGE, $filters = []) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            // Calcul de l'offset pour la pagination
            $offset = ($page - 1) * $limit;

            // Préparation de la requête SQL de base optimisée
            $query = "SELECT p.*, u.email, u.role
                     FROM {$this->table} p
                     LEFT JOIN {$this->usersTable} u ON p.user_id = u.id";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['nom'])) {
                $whereConditions[] = "p.nom LIKE :nom";
                $params[':nom'] = '%' . $filters['nom'] . '%';
            }

            if (!empty($filters['prenom'])) {
                $whereConditions[] = "p.prenom LIKE :prenom";
                $params[':prenom'] = '%' . $filters['prenom'] . '%';
            }

            if (!empty($filters['email'])) {
                $whereConditions[] = "u.email LIKE :email";
                $params[':email'] = '%' . $filters['email'] . '%';
            }

            // Ajout des conditions WHERE si présentes
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }

            // Tri des résultats (par défaut, par nom)
            $orderBy = !empty($filters['order_by']) ? $filters['order_by'] : 'p.nom';
            $orderDir = !empty($filters['order_dir']) ? $filters['order_dir'] : 'ASC';
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
            $pilotes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pilote = [
                    'id' => $row['id'],
                    'user_id' => $row['user_id'],
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'email' => $row['email'],
                    'role' => $row['role'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at']
                ];
                $pilotes[] = $pilote;
            }

            return $pilotes;
        } catch (PDOException $e) {
            error_log("Erreur dans Pilote::getAll() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre total de pilotes pour la pagination
     *
     * @param array $filters Critères de filtrage
     * @return int
     */
    public function countAll($filters = []) {
        // Mode dégradé - retourne 0
        if ($this->dbError) {
            return 0;
        }

        try {
            // Préparation de la requête SQL de base
            $query = "SELECT COUNT(DISTINCT p.id) as total FROM {$this->table} p
                      LEFT JOIN {$this->usersTable} u ON p.user_id = u.id";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['nom'])) {
                $whereConditions[] = "p.nom LIKE :nom";
                $params[':nom'] = '%' . $filters['nom'] . '%';
            }

            if (!empty($filters['prenom'])) {
                $whereConditions[] = "p.prenom LIKE :prenom";
                $params[':prenom'] = '%' . $filters['prenom'] . '%';
            }

            if (!empty($filters['email'])) {
                $whereConditions[] = "u.email LIKE :email";
                $params[':email'] = '%' . $filters['email'] . '%';
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
            error_log("Erreur lors du comptage des pilotes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère un pilote par son ID avec tous les détails associés
     *
     * @param int $id ID du pilote
     * @return array|false Données du pilote ou false si non trouvé
     */
    public function getById($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Requête optimisée
            $query = "SELECT p.*, u.email, u.role
                     FROM {$this->table} p
                     LEFT JOIN {$this->usersTable} u ON p.user_id = u.id
                     WHERE p.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $pilote = [
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'email' => $row['email'],
                'role' => $row['role'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];

            return $pilote;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du pilote: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée un nouveau pilote avec son compte utilisateur associé
     *
     * @param array $data Données du pilote
     * @return int|false ID du pilote créé ou false en cas d'échec
     */
    public function create($data) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Début de la transaction
            $this->conn->beginTransaction();

            // 1. Création du compte utilisateur
            $userQuery = "INSERT INTO {$this->usersTable} (email, password, role, created_at)
                         VALUES (:email, :password, 'pilote', NOW())";

            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->bindParam(':email', $data['email']);

            // Hashage du mot de passe
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $userStmt->bindParam(':password', $hashedPassword);

            $userStmt->execute();
            $userId = $this->conn->lastInsertId();

            // 2. Création du profil pilote
            $piloteQuery = "INSERT INTO {$this->table} (user_id, nom, prenom, created_at)
                           VALUES (:user_id, :nom, :prenom, NOW())";

            $piloteStmt = $this->conn->prepare($piloteQuery);
            $piloteStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $piloteStmt->bindParam(':nom', $data['nom']);
            $piloteStmt->bindParam(':prenom', $data['prenom']);

            $piloteStmt->execute();
            $piloteId = $this->conn->lastInsertId();

            // Validation de la transaction
            $this->conn->commit();

            return $piloteId;
        } catch (PDOException $e) {
            // Annulation de la transaction en cas d'erreur
            $this->conn->rollBack();
            error_log("Erreur lors de la création du pilote: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour un pilote existant
     *
     * @param int $id ID du pilote
     * @param array $data Nouvelles données
     * @return bool
     */
    public function update($id, $data) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Récupération du pilote pour obtenir le user_id
            $query = "SELECT user_id FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return false;
            }

            $pilote = $stmt->fetch(PDO::FETCH_ASSOC);
            $userId = $pilote['user_id'];

            // Début de la transaction
            $this->conn->beginTransaction();

            // 1. Mise à jour du profil pilote
            $piloteQuery = "UPDATE {$this->table} SET
                           nom = :nom,
                           prenom = :prenom,
                           updated_at = NOW()
                           WHERE id = :id";

            $piloteStmt = $this->conn->prepare($piloteQuery);
            $piloteStmt->bindParam(':nom', $data['nom']);
            $piloteStmt->bindParam(':prenom', $data['prenom']);
            $piloteStmt->bindParam(':id', $id, PDO::PARAM_INT);

            $piloteStmt->execute();

            // 2. Mise à jour de l'email utilisateur si fourni
            if (!empty($data['email'])) {
                $userQuery = "UPDATE {$this->usersTable} SET
                             email = :email,
                             updated_at = NOW()
                             WHERE id = :id";

                $userStmt = $this->conn->prepare($userQuery);
                $userStmt->bindParam(':email', $data['email']);
                $userStmt->bindParam(':id', $userId, PDO::PARAM_INT);

                $userStmt->execute();
            }

            // 3. Mise à jour du mot de passe si fourni
            if (!empty($data['password'])) {
                $userQuery = "UPDATE {$this->usersTable} SET
                             password = :password,
                             updated_at = NOW()
                             WHERE id = :id";

                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

                $userStmt = $this->conn->prepare($userQuery);
                $userStmt->bindParam(':password', $hashedPassword);
                $userStmt->bindParam(':id', $userId, PDO::PARAM_INT);

                $userStmt->execute();
            }

            // Validation de la transaction
            $this->conn->commit();

            return true;
        } catch (PDOException $e) {
            // Annulation de la transaction en cas d'erreur
            $this->conn->rollBack();
            error_log("Erreur lors de la mise à jour du pilote: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les étudiants assignés à un pilote
     *
     * @param int $piloteId ID du pilote
     * @return array Liste des étudiants assignés
     */
    public function getEtudiantsAssignes($piloteId) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT e.*, u.email, c.nom as centre_nom, c.code as centre_code, pe.date_attribution
                  FROM etudiants e
                  JOIN pilote_etudiant pe ON e.id = pe.etudiant_id
                  JOIN utilisateurs u ON e.user_id = u.id
                  LEFT JOIN centres c ON e.centre_id = c.id
                  WHERE pe.pilote_id = :pilote_id
                  ORDER BY e.nom, e.prenom";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pilote_id', $piloteId, PDO::PARAM_INT);
            $stmt->execute();

            $etudiants = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $etudiants[] = [
                    'id' => $row['id'],
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'email' => $row['email'],
                    'centre_id' => $row['centre_id'],
                    'centre_nom' => $row['centre_nom'],
                    'centre_code' => $row['centre_code'],
                    'date_attribution' => $row['date_attribution']
                ];
            }

            return $etudiants;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des étudiants assignés: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Attribue un étudiant à un pilote
     *
     * @param int $piloteId ID du pilote
     * @param int $etudiantId ID de l'étudiant
     * @return bool
     */
    public function assignerEtudiant($piloteId, $etudiantId) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Vérifier si l'attribution existe déjà
            $checkQuery = "SELECT COUNT(*) as count FROM pilote_etudiant 
                       WHERE pilote_id = :pilote_id AND etudiant_id = :etudiant_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':pilote_id', $piloteId, PDO::PARAM_INT);
            $checkStmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            $checkStmt->execute();
            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if ($row['count'] > 0) {
                // L'attribution existe déjà
                return true;
            }

            // Supprimer toute attribution existante pour cet étudiant
            $deleteQuery = "DELETE FROM pilote_etudiant WHERE etudiant_id = :etudiant_id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            $deleteStmt->execute();

            // Créer la nouvelle attribution
            $insertQuery = "INSERT INTO pilote_etudiant (pilote_id, etudiant_id, date_attribution)
                        VALUES (:pilote_id, :etudiant_id, NOW())";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bindParam(':pilote_id', $piloteId, PDO::PARAM_INT);
            $insertStmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            return $insertStmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de l'attribution de l'étudiant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retire l'attribution d'un étudiant à un pilote
     *
     * @param int $piloteId ID du pilote
     * @param int $etudiantId ID de l'étudiant
     * @return bool
     */
    public function retirerEtudiant($piloteId, $etudiantId) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "DELETE FROM pilote_etudiant 
                  WHERE pilote_id = :pilote_id AND etudiant_id = :etudiant_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pilote_id', $piloteId, PDO::PARAM_INT);
            $stmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors du retrait de l'attribution de l'étudiant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un pilote et son compte utilisateur associé
     *
     * @param int $id ID du pilote
     * @return bool
     */
    public function delete($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Récupération du pilote pour obtenir le user_id
            $query = "SELECT user_id FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return false;
            }

            $pilote = $stmt->fetch(PDO::FETCH_ASSOC);
            $userId = $pilote['user_id'];

            // Début de la transaction
            $this->conn->beginTransaction();

            // 1. Suppression du profil pilote
            $piloteQuery = "DELETE FROM {$this->table} WHERE id = :id";
            $piloteStmt = $this->conn->prepare($piloteQuery);
            $piloteStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $piloteStmt->execute();

            // 2. Suppression du compte utilisateur
            $userQuery = "DELETE FROM {$this->usersTable} WHERE id = :id";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $userStmt->execute();

            // Validation de la transaction
            $this->conn->commit();

            return true;
        } catch (PDOException $e) {
            // Annulation de la transaction en cas d'erreur
            $this->conn->rollBack();
            error_log("Erreur lors de la suppression du pilote: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les derniers pilotes inscrits
     *
     * @param int $limit Nombre maximum de pilotes à récupérer
     * @return array
     */
    public function getLatest($limit = 5) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT p.id, p.nom, p.prenom, p.created_at, u.email
                      FROM {$this->table} p
                      LEFT JOIN {$this->usersTable} u ON p.user_id = u.id
                      ORDER BY p.created_at DESC
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $pilotes = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $pilotes[] = $row;
            }

            return $pilotes;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des derniers pilotes: " . $e->getMessage());
            return [];
        }
    }
}