<?php
/**
 * Modèle pour la gestion des entreprises
 * Implémente les opérations CRUD et les services spécifiques aux entreprises
 */
class Entreprise {
    private $conn;
    private $table = 'entreprises';
    private $evaluationsTable = 'evaluations_entreprises';
    private $offresTable = 'offres';
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
                error_log("Mode dégradé activé: Impossible d'établir la connexion à la base de données dans Entreprise.php");
            }
        } catch (Exception $e) {
            $this->dbError = true;
            error_log("Exception dans Entreprise::__construct(): " . $e->getMessage());
        }
    }

    /**
     * Indique si une erreur de BDD est survenue
     * @return bool
     */
    public function hasError() {
        return $this->dbError;
    }

    /**
     * Récupère toutes les entreprises avec pagination
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

            // Préparation de la requête SQL de base
            $query = "SELECT e.*, 
                     (SELECT COUNT(*) FROM {$this->offresTable} o WHERE o.entreprise_id = e.id) as nb_offres,
                     (SELECT AVG(note) FROM {$this->evaluationsTable} ev WHERE ev.entreprise_id = e.id) as moyenne_evaluations
                     FROM {$this->table} e";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['nom'])) {
                $whereConditions[] = "e.nom LIKE :nom";
                $params[':nom'] = '%' . $filters['nom'] . '%';
            }

            if (!empty($filters['with_offres'])) {
                $whereConditions[] = "(SELECT COUNT(*) FROM {$this->offresTable} o WHERE o.entreprise_id = e.id) > 0";
            }

            // Ajout des conditions WHERE si présentes
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }

            // Tri des résultats
            $orderBy = !empty($filters['order_by']) ? $filters['order_by'] : 'e.nom';
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
            $entreprises = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $entreprise = [
                    'id' => $row['id'],
                    'nom' => $row['nom'],
                    'description' => $row['description'],
                    'email' => $row['email'],
                    'telephone' => $row['telephone'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'nb_offres' => $row['nb_offres'],
                    'moyenne_evaluations' => $row['moyenne_evaluations'] ? round($row['moyenne_evaluations'], 1) : null
                ];
                $entreprises[] = $entreprise;
            }

            return $entreprises;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des entreprises: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre total d'entreprises pour la pagination
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
            $query = "SELECT COUNT(*) as total FROM {$this->table} e";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['nom'])) {
                $whereConditions[] = "e.nom LIKE :nom";
                $params[':nom'] = '%' . $filters['nom'] . '%';
            }

            if (!empty($filters['with_offres'])) {
                $whereConditions[] = "(SELECT COUNT(*) FROM {$this->offresTable} o WHERE o.entreprise_id = e.id) > 0";
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
            error_log("Erreur lors du comptage des entreprises: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère une entreprise par son ID
     *
     * @param int $id ID de l'entreprise
     * @return array|false Données de l'entreprise ou false si non trouvée
     */
    public function getById($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "SELECT e.*,
                     (SELECT COUNT(*) FROM {$this->offresTable} o WHERE o.entreprise_id = e.id) as nb_offres,
                     (SELECT AVG(note) FROM {$this->evaluationsTable} ev WHERE ev.entreprise_id = e.id) as moyenne_evaluations,
                     (SELECT COUNT(*) FROM {$this->evaluationsTable} ev WHERE ev.entreprise_id = e.id) as nb_evaluations
                     FROM {$this->table} e
                     WHERE e.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $entreprise = [
                'id' => $row['id'],
                'nom' => $row['nom'],
                'description' => $row['description'],
                'email' => $row['email'],
                'telephone' => $row['telephone'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'nb_offres' => $row['nb_offres'],
                'moyenne_evaluations' => $row['moyenne_evaluations'] ? round($row['moyenne_evaluations'], 1) : null,
                'nb_evaluations' => $row['nb_evaluations']
            ];

            // Récupération des évaluations
            $entreprise['evaluations'] = $this->getEvaluations($id);

            // Récupération des offres actives
            $entreprise['offres'] = $this->getOffresActives($id);

            return $entreprise;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'entreprise: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crée une nouvelle entreprise
     *
     * @param array $data Données de l'entreprise
     * @return int|false ID de l'entreprise créée ou false en cas d'échec
     */
    public function create($data) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "INSERT INTO {$this->table} (nom, description, email, telephone, created_at)
                      VALUES (:nom, :description, :email, :telephone, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nom', $data['nom']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':telephone', $data['telephone']);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erreur lors de la création de l'entreprise: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour une entreprise existante
     *
     * @param int $id ID de l'entreprise
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
                      description = :description,
                      email = :email,
                      telephone = :telephone,
                      updated_at = NOW()
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nom', $data['nom']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':telephone', $data['telephone']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'entreprise: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une entreprise
     *
     * @param int $id ID de l'entreprise
     * @return bool
     */
    public function delete($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Vérification des offres liées
            $query = "SELECT COUNT(*) as count FROM {$this->offresTable} WHERE entreprise_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row['count'] > 0) {
                // Ne pas supprimer si des offres sont liées
                return false;
            }

            // Suppression des évaluations
            $query = "DELETE FROM {$this->evaluationsTable} WHERE entreprise_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Suppression de l'entreprise
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de l'entreprise: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les évaluations d'une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return array
     */
    public function getEvaluations($entrepriseId) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT ev.*, e.nom, e.prenom
                      FROM {$this->evaluationsTable} ev
                      LEFT JOIN etudiants e ON ev.etudiant_id = e.id
                      WHERE ev.entreprise_id = :entreprise_id
                      ORDER BY ev.created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':entreprise_id', $entrepriseId, PDO::PARAM_INT);
            $stmt->execute();

            $evaluations = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $evaluations[] = [
                    'id' => $row['id'],
                    'etudiant_id' => $row['etudiant_id'],
                    'etudiant_nom' => $row['nom'],
                    'etudiant_prenom' => $row['prenom'],
                    'note' => $row['note'],
                    'commentaire' => $row['commentaire'],
                    'created_at' => $row['created_at']
                ];
            }

            return $evaluations;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des évaluations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ajoute une évaluation pour une entreprise
     *
     * @param array $data Données de l'évaluation
     * @return bool
     */
    public function addEvaluation($data) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "INSERT INTO {$this->evaluationsTable} (entreprise_id, etudiant_id, note, commentaire, created_at)
                      VALUES (:entreprise_id, :etudiant_id, :note, :commentaire, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':entreprise_id', $data['entreprise_id'], PDO::PARAM_INT);
            $stmt->bindParam(':etudiant_id', $data['etudiant_id'], PDO::PARAM_INT);
            $stmt->bindParam(':note', $data['note'], PDO::PARAM_INT);
            $stmt->bindParam(':commentaire', $data['commentaire']);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout de l'évaluation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les offres actives d'une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @return array
     */
    public function getOffresActives($entrepriseId) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT o.id, o.titre, o.remuneration, o.date_debut, o.date_fin,
                      (SELECT COUNT(*) FROM candidatures c WHERE c.offre_id = o.id) as nb_candidatures
                      FROM {$this->offresTable} o
                      WHERE o.entreprise_id = :entreprise_id
                      AND o.date_fin >= CURDATE()
                      ORDER BY o.date_debut ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':entreprise_id', $entrepriseId, PDO::PARAM_INT);
            $stmt->execute();

            $offres = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $offres[] = [
                    'id' => $row['id'],
                    'titre' => $row['titre'],
                    'remuneration' => $row['remuneration'],
                    'date_debut' => $row['date_debut'],
                    'date_fin' => $row['date_fin'],
                    'nb_candidatures' => $row['nb_candidatures']
                ];
            }

            return $offres;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des offres: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère toutes les entreprises pour un select (dropdown)
     *
     * @return array
     */
    public function getAllForSelect() {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT id, nom FROM {$this->table} ORDER BY nom ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $entreprises = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $entreprises[] = [
                    'id' => $row['id'],
                    'nom' => $row['nom']
                ];
            }

            return $entreprises;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des entreprises pour select: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les entreprises pour le filtre des offres
     *
     * @return array
     */
    public function getAllForFilter() {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        return $this->getAllForSelect();
    }

    /**
     * Compte le nombre d'entreprises avec des offres
     *
     * @return int
     */
    public function countWithOffres() {
        // Mode dégradé - retourne 0
        if ($this->dbError) {
            return 0;
        }

        try {
            $query = "SELECT COUNT(DISTINCT e.id) as total 
                      FROM {$this->table} e 
                      INNER JOIN {$this->offresTable} o ON e.id = o.entreprise_id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['total'];
        } catch (PDOException $e) {
            error_log("Erreur lors du comptage des entreprises avec offres: " . $e->getMessage());
            return 0;
        }
    }
}