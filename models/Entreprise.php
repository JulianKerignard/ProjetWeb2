<?php
/**
 * Modèle pour la gestion des entreprises
 *
 * Implémente les opérations CRUD et les services spécifiques aux entreprises
 * avec optimisations des requêtes et gestion robuste des erreurs.
 *
 * @version 2.0
 * @author Web4All
 */
class Entreprise {
    /** @var PDO Instance de connexion à la base de données */
    private $conn;

    /** @var string Nom de la table principale */
    private $table = 'entreprises';

    /** @var string Nom de la table des évaluations */
    private $evaluationsTable = 'evaluations_entreprises';

    /** @var string Nom de la table des offres */
    private $offresTable = 'offres';

    /** @var string Nom de la table des étudiants */
    private $etudiantsTable = 'etudiants';

    /** @var bool Indicateur d'erreur de base de données */
    private $dbError = false;

    /** @var int Durée de vie du cache en secondes (5 minutes) */
    private $cacheExpiry = 300;

    /** @var bool Activation du cache */
    private $cacheEnabled = true;

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
                error_log("Mode dégradé activé: Impossible d'établir la connexion à la base de données dans Entreprise.php");
            }

            // Désactiver le cache en mode développement si nécessaire
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                $this->cacheEnabled = false;
            }
        } catch (Exception $e) {
            $this->dbError = true;
            error_log("Exception dans Entreprise::__construct(): " . $e->getMessage());
        }
    }

    /**
     * Indique si une erreur de BDD est survenue
     *
     * Permet aux contrôleurs d'adapter leur comportement en cas d'erreur
     * de connexion à la base de données.
     *
     * @return bool
     */
    public function hasError() {
        return $this->dbError;
    }

    /**
     * Récupère toutes les entreprises avec pagination et filtrage avancé
     *
     * Optimisé avec des requêtes préparées et des calculs d'agrégation
     * pour réduire le nombre de requêtes SQL.
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

            // Tri des résultats avec gestion des cas spéciaux
            $orderBy = !empty($filters['order_by']) ? $filters['order_by'] : 'e.nom';
            $orderDir = !empty($filters['order_dir']) ? $filters['order_dir'] : 'ASC';

            // Adaptation de la clause ORDER BY pour les colonnes calculées
            if ($orderBy === 'nb_offres') {
                $orderBy = "(SELECT COUNT(*) FROM {$this->offresTable} o WHERE o.entreprise_id = e.id)";
            } elseif ($orderBy === 'moyenne_evaluations') {
                $orderBy = "(SELECT AVG(note) FROM {$this->evaluationsTable} ev WHERE ev.entreprise_id = e.id)";
            }

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
            error_log("Erreur dans Entreprise::getAll() - " . $e->getMessage() . " - SQL: {$query} - Params: " . json_encode($params));
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
     * Récupère une entreprise par son ID avec tous les détails associés
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
            // Vérification du cache si activé
            $cacheFile = ROOT_PATH . '/cache/entreprise_' . $id . '.cache';
            if ($this->cacheEnabled && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $this->cacheExpiry)) {
                return unserialize(file_get_contents($cacheFile));
            }

            // Requête optimisée avec sous-requêtes pour les agrégations
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

            // Mise en cache des résultats si le cache est activé
            if ($this->cacheEnabled) {
                // Création du répertoire de cache si nécessaire
                if (!file_exists(ROOT_PATH . '/cache')) {
                    mkdir(ROOT_PATH . '/cache', 0755, true);
                }
                file_put_contents($cacheFile, serialize($entreprise));
            }

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
                // Invalidation du cache
                $this->invalidateCache();
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

            $success = $stmt->execute();

            // Invalidation du cache spécifique
            if ($success && $this->cacheEnabled) {
                $cacheFile = ROOT_PATH . '/cache/entreprise_' . $id . '.cache';
                if (file_exists($cacheFile)) {
                    unlink($cacheFile);
                }
            }

            return $success;
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

            // Début de la transaction
            $this->conn->beginTransaction();

            // Suppression des évaluations
            $query = "DELETE FROM {$this->evaluationsTable} WHERE entreprise_id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            // Suppression de l'entreprise
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();

            // Validation de la transaction
            $this->conn->commit();

            // Invalidation du cache spécifique
            if ($result && $this->cacheEnabled) {
                $cacheFile = ROOT_PATH . '/cache/entreprise_' . $id . '.cache';
                if (file_exists($cacheFile)) {
                    unlink($cacheFile);
                }
                // Invalidation du cache global également
                $this->invalidateCache();
            }

            return $result;
        } catch (PDOException $e) {
            // Annulation de la transaction en cas d'erreur
            $this->conn->rollBack();
            error_log("Erreur lors de la suppression de l'entreprise: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les évaluations d'une entreprise avec pagination
     *
     * @param int $entrepriseId ID de l'entreprise
     * @param int $page Numéro de page actuel
     * @param int $limit Nombre d'évaluations par page
     * @return array Tableau contenant les évaluations et le compte total
     */
    public function getEvaluationsPaginated($entrepriseId, $page = 1, $limit = 5) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return ['evaluations' => [], 'total' => 0];
        }

        try {
            // Récupération du nombre total d'évaluations
            $countQuery = "SELECT COUNT(*) as total 
                      FROM {$this->evaluationsTable} 
                      WHERE entreprise_id = :entreprise_id";

            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->bindParam(':entreprise_id', $entrepriseId, PDO::PARAM_INT);
            $countStmt->execute();

            $totalRow = $countStmt->fetch(PDO::FETCH_ASSOC);
            $total = (int)$totalRow['total'];

            // Calcul de l'offset pour la pagination
            $offset = ($page - 1) * $limit;

            // Requête principale avec pagination
            $query = "SELECT ev.*, e.nom, e.prenom
                  FROM {$this->evaluationsTable} ev
                  LEFT JOIN {$this->etudiantsTable} e ON ev.etudiant_id = e.id
                  WHERE ev.entreprise_id = :entreprise_id
                  ORDER BY ev.created_at DESC
                  LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':entreprise_id', $entrepriseId, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
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

            return [
                'evaluations' => $evaluations,
                'total' => $total
            ];
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des évaluations paginées: " . $e->getMessage());
            return ['evaluations' => [], 'total' => 0];
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
            // Vérification si l'étudiant a déjà évalué cette entreprise
            $checkQuery = "SELECT COUNT(*) as count FROM {$this->evaluationsTable} 
                          WHERE entreprise_id = :entreprise_id AND etudiant_id = :etudiant_id";

            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':entreprise_id', $data['entreprise_id'], PDO::PARAM_INT);
            $checkStmt->bindParam(':etudiant_id', $data['etudiant_id'], PDO::PARAM_INT);
            $checkStmt->execute();

            $result = $checkStmt->fetch(PDO::FETCH_ASSOC);

            // Si l'étudiant a déjà évalué, mettre à jour plutôt qu'insérer
            if ($result['count'] > 0) {
                $query = "UPDATE {$this->evaluationsTable} 
                          SET note = :note, commentaire = :commentaire 
                          WHERE entreprise_id = :entreprise_id AND etudiant_id = :etudiant_id";
            } else {
                $query = "INSERT INTO {$this->evaluationsTable} (entreprise_id, etudiant_id, note, commentaire, created_at)
                          VALUES (:entreprise_id, :etudiant_id, :note, :commentaire, NOW())";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':entreprise_id', $data['entreprise_id'], PDO::PARAM_INT);
            $stmt->bindParam(':etudiant_id', $data['etudiant_id'], PDO::PARAM_INT);
            $stmt->bindParam(':note', $data['note'], PDO::PARAM_INT);
            $stmt->bindParam(':commentaire', $data['commentaire']);

            $success = $stmt->execute();

            // Invalidation du cache pour cette entreprise
            if ($success && $this->cacheEnabled) {
                $cacheFile = ROOT_PATH . '/cache/entreprise_' . $data['entreprise_id'] . '.cache';
                if (file_exists($cacheFile)) {
                    unlink($cacheFile);
                }
            }

            return $success;
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
     * avec optimisation des performances pour les listes volumineuses
     *
     * @return array
     */
    public function getAllForSelect() {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            // Vérification du cache
            $cacheFile = ROOT_PATH . '/cache/entreprises_select.cache';
            if ($this->cacheEnabled && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $this->cacheExpiry)) {
                return unserialize(file_get_contents($cacheFile));
            }

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

            // Mise en cache du résultat
            if ($this->cacheEnabled) {
                if (!file_exists(ROOT_PATH . '/cache')) {
                    mkdir(ROOT_PATH . '/cache', 0755, true);
                }
                file_put_contents($cacheFile, serialize($entreprises));
            }

            return $entreprises;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des entreprises pour select: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les entreprises pour le filtre des offres
     * (alias vers getAllForSelect pour maintenir la compatibilité API)
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

    /**
     * Récupère les dernières entreprises ajoutées
     *
     * @param int $limit Nombre maximum d'entreprises à récupérer
     * @return array
     */
    public function getLatest($limit = 5) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT e.id, e.nom, e.created_at,
                     (SELECT COUNT(*) FROM {$this->offresTable} o WHERE o.entreprise_id = e.id) as nb_offres
                     FROM {$this->table} e
                     ORDER BY e.created_at DESC
                     LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $companies = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $companies[] = $row;
            }

            return $companies;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des dernières entreprises: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les statistiques des entreprises pour le dashboard
     *
     * @return array
     */
    public function getStatistics() {
        // Mode dégradé - retourne structure vide
        if ($this->dbError) {
            return [
                'total_entreprises' => 0,
                'with_offres' => 0,
                'with_evaluations' => 0,
                'top_rated' => [],
                'most_active' => []
            ];
        }

        try {
            // Vérification du cache
            $cacheFile = ROOT_PATH . '/cache/entreprises_stats.cache';
            if ($this->cacheEnabled && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $this->cacheExpiry)) {
                return unserialize(file_get_contents($cacheFile));
            }

            $statistics = [];

            // Requête multi-statistiques optimisée
            $statsQuery = "SELECT 
                         (SELECT COUNT(*) FROM {$this->table}) AS total_entreprises,
                         (SELECT COUNT(DISTINCT entreprise_id) FROM {$this->offresTable}) AS with_offres,
                         (SELECT COUNT(DISTINCT entreprise_id) FROM {$this->evaluationsTable}) AS with_evaluations,
                         (SELECT AVG(note) FROM {$this->evaluationsTable}) AS avg_rating";

            $stmt = $this->conn->prepare($statsQuery);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $statistics['total_entreprises'] = (int)$row['total_entreprises'];
            $statistics['with_offres'] = (int)$row['with_offres'];
            $statistics['with_evaluations'] = (int)$row['with_evaluations'];
            $statistics['avg_rating'] = round((float)$row['avg_rating'], 1);

            // Top entreprises les mieux notées
            $topRatedQuery = "SELECT e.id, e.nom, AVG(ev.note) as avg_rating, COUNT(ev.id) as num_ratings
                             FROM {$this->table} e
                             INNER JOIN {$this->evaluationsTable} ev ON e.id = ev.entreprise_id
                             GROUP BY e.id
                             HAVING num_ratings >= 3
                             ORDER BY avg_rating DESC, num_ratings DESC
                             LIMIT 5";

            $stmt = $this->conn->prepare($topRatedQuery);
            $stmt->execute();

            $topRated = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $topRated[] = [
                    'id' => $row['id'],
                    'nom' => $row['nom'],
                    'avg_rating' => round((float)$row['avg_rating'], 1),
                    'num_ratings' => (int)$row['num_ratings']
                ];
            }
            $statistics['top_rated'] = $topRated;

            // Entreprises les plus actives (par nombre d'offres)
            $mostActiveQuery = "SELECT e.id, e.nom, COUNT(o.id) as num_offres
                               FROM {$this->table} e
                               INNER JOIN {$this->offresTable} o ON e.id = o.entreprise_id
                               GROUP BY e.id
                               ORDER BY num_offres DESC
                               LIMIT 5";

            $stmt = $this->conn->prepare($mostActiveQuery);
            $stmt->execute();

            $mostActive = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $mostActive[] = [
                    'id' => $row['id'],
                    'nom' => $row['nom'],
                    'num_offres' => (int)$row['num_offres']
                ];
            }
            $statistics['most_active'] = $mostActive;

            // Mise en cache du résultat
            if ($this->cacheEnabled) {
                if (!file_exists(ROOT_PATH . '/cache')) {
                    mkdir(ROOT_PATH . '/cache', 0755, true);
                }
                file_put_contents($cacheFile, serialize($statistics));
            }

            return $statistics;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des statistiques des entreprises: " . $e->getMessage());
            return [
                'total_entreprises' => 0,
                'with_offres' => 0,
                'with_evaluations' => 0,
                'top_rated' => [],
                'most_active' => []
            ];
        }
    }

    /**
     * Invalide le cache global des entreprises
     *
     * @return void
     */
    private function invalidateCache() {
        if (!$this->cacheEnabled) {
            return;
        }

        $cacheFiles = [
            ROOT_PATH . '/cache/entreprises_select.cache',
            ROOT_PATH . '/cache/entreprises_stats.cache'
        ];

        foreach ($cacheFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}