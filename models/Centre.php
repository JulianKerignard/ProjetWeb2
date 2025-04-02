<?php
/**
 * Modèle pour la gestion des centres
 *
 * @version 1.0
 * @author Web4All
 */
class Centre {
    /** @var PDO Instance de connexion à la base de données */
    private $conn;

    /** @var string Nom de la table principale */
    private $table = 'centres';

    /** @var bool Indicateur d'erreur de base de données */
    private $dbError = false;

    /**
     * Constructeur - Initialise la connexion à la BDD
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
                error_log("Mode dégradé activé: Impossible d'établir la connexion à la base de données dans Centre.php");
            }
        } catch (Exception $e) {
            $this->dbError = true;
            error_log("Exception dans Centre::__construct(): " . $e->getMessage());
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
     * Récupère tous les centres
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
            $query = "SELECT * FROM {$this->table}";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['nom'])) {
                $whereConditions[] = "nom LIKE :nom";
                $params[':nom'] = '%' . $filters['nom'] . '%';
            }

            if (!empty($filters['code'])) {
                $whereConditions[] = "code LIKE :code";
                $params[':code'] = '%' . $filters['code'] . '%';
            }

            // Ajout des conditions WHERE si présentes
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }

            // Tri des résultats
            $orderBy = !empty($filters['order_by']) ? $filters['order_by'] : 'nom';
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
            $centres = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $centres[] = $row;
            }

            return $centres;
        } catch (PDOException $e) {
            error_log("Erreur dans Centre::getAll() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère un centre par son ID
     *
     * @param int $id ID du centre
     * @return array|false
     */
    public function getById($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return false;
            }

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération du centre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée un nouveau centre
     *
     * @param array $data Données du centre
     * @return int|false ID du centre créé ou false en cas d'échec
     */
    public function create($data) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "INSERT INTO {$this->table} (nom, code, adresse, created_at)
                      VALUES (:nom, :code, :adresse, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nom', $data['nom']);
            $stmt->bindParam(':code', $data['code']);
            $stmt->bindParam(':adresse', $data['adresse']);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erreur lors de la création du centre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour un centre existant
     *
     * @param int $id ID du centre
     * @param array $data Nouvelles données
     * @return bool
     */
    public function update($id, $data) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "UPDATE {$this->table} SET
                      nom = :nom,
                      code = :code,
                      adresse = :adresse
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nom', $data['nom']);
            $stmt->bindParam(':code', $data['code']);
            $stmt->bindParam(':adresse', $data['adresse']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour du centre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un centre
     *
     * @param int $id ID du centre
     * @return bool
     */
    public function delete($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression du centre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les centres pour un select (dropdown)
     *
     * @return array
     */
    public function getAllForSelect() {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT id, nom, code FROM {$this->table} ORDER BY nom ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $centres = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $centres[] = [
                    'id' => $row['id'],
                    'nom' => $row['nom'] . ' (' . $row['code'] . ')'
                ];
            }

            return $centres;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des centres pour select: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre total de centres
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
            $query = "SELECT COUNT(*) as total FROM {$this->table}";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['nom'])) {
                $whereConditions[] = "nom LIKE :nom";
                $params[':nom'] = '%' . $filters['nom'] . '%';
            }

            if (!empty($filters['code'])) {
                $whereConditions[] = "code LIKE :code";
                $params[':code'] = '%' . $filters['code'] . '%';
            }

            // Ajout des conditions WHERE si présentes
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }

            // Exécution de la requête
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['total'];
        } catch (PDOException $e) {
            error_log("Erreur lors du comptage des centres: " . $e->getMessage());
            return 0;
        }
    }
}