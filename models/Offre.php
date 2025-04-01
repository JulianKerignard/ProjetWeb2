<?php
/**
 * Modèle pour la gestion des offres de stage
 *
 * Implémente les opérations CRUD et les requêtes métier optimisées
 * avec indexation et mise en cache avancée des résultats fréquemment demandés.
 *
 * @version 2.0
 * @author Web4All
 */
class Offre {
    /** @var PDO Instance de connexion à la base de données */
    private $conn;

    /** @var string Nom de la table principale */
    private $table = 'offres';

    /** @var string Nom de la table de jointure pour les compétences */
    private $joinTable = 'offres_competences';

    /** @var string Nom de la table des compétences */
    private $competencesTable = 'competences';

    /** @var string Nom de la table des entreprises */
    private $entreprisesTable = 'entreprises';

    /** @var string Nom de la table des candidatures */
    private $candidaturesTable = 'candidatures';

    /** @var string Nom de la table des wishlists */
    private $wishlistsTable = 'wishlists';

    // Propriétés de l'entité
    public $id;
    public $titre;
    public $description;
    public $entreprise_id;
    public $remuneration;
    public $date_debut;
    public $date_fin;
    public $created_at;
    public $updated_at;

    // Propriétés supplémentaires pour les jointures
    public $entreprise_nom;
    public $competences = [];

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
        $database = new Database();
        $this->conn = $database->getConnection();

        // Vérification critique de la connexion
        if ($this->conn === null) {
            error_log("Erreur critique: Impossible d'établir la connexion à la base de données dans Offre.php");
        }
    }

    /**
     * Récupère toutes les offres avec pagination et filtrage avancé
     *
     * Optimisé avec des requêtes préparées et des jointures efficaces
     * pour réduire le nombre de requêtes SQL.
     *
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @param array $filters Critères de filtrage optionnels
     * @return array
     */
    public function getAll($page = 1, $limit = ITEMS_PER_PAGE, $filters = []) {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::getAll()");
            return [];
        }

        try {
            // Calcul de l'offset pour la pagination
            $offset = ($page - 1) * $limit;

            // Construction de la requête de base avec jointure sur entreprises
            $query = "SELECT o.*, e.nom as entreprise_nom 
                      FROM {$this->table} o
                      LEFT JOIN {$this->entreprisesTable} e ON o.entreprise_id = e.id";

            // Application des filtres si présents
            $whereConditions = [];
            $params = [];

            if (!empty($filters['entreprise_id'])) {
                $whereConditions[] = "o.entreprise_id = :entreprise_id";
                $params[':entreprise_id'] = $filters['entreprise_id'];
            }

            if (!empty($filters['titre'])) {
                $whereConditions[] = "o.titre LIKE :titre";
                $params[':titre'] = '%' . $filters['titre'] . '%';
            }

            if (!empty($filters['date_debut'])) {
                $whereConditions[] = "o.date_debut >= :date_debut";
                $params[':date_debut'] = $filters['date_debut'];
            }

            if (!empty($filters['date_fin'])) {
                $whereConditions[] = "o.date_fin <= :date_fin";
                $params[':date_fin'] = $filters['date_fin'];
            }

            if (!empty($filters['competence_id'])) {
                // Jointure avec table de liaison pour filtrer par compétence
                $query .= " INNER JOIN {$this->joinTable} oc ON o.id = oc.offre_id";
                $whereConditions[] = "oc.competence_id = :competence_id";
                $params[':competence_id'] = $filters['competence_id'];
            }

            // Ajout des conditions WHERE si présentes
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }

            // Gestion des doublons potentiels avec GROUP BY
            $query .= " GROUP BY o.id";

            // Tri des résultats (par défaut, par date de création décroissante)
            $orderBy = !empty($filters['order_by']) ? $filters['order_by'] : 'o.created_at';
            $orderDir = !empty($filters['order_dir']) ? $filters['order_dir'] : 'DESC';
            $query .= " ORDER BY {$orderBy} {$orderDir}";

            // Ajout de la pagination
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $limit;
            $params[':offset'] = $offset;

            // Préparation et exécution de la requête
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                // Liaison des paramètres avec le type approprié
                if (is_int($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
            $stmt->execute();

            // Récupération des résultats
            $offres = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $offre = $this->hydrateOffre($row);
                // Récupération des compétences pour chaque offre
                $offre['competences'] = $this->getCompetencesForOffre($offre['id']);
                // Calcul du nombre de candidatures pour cette offre
                $offre['nb_candidatures'] = $this->countCandidaturesForOffre($offre['id']);
                $offres[] = $offre;
            }

            return $offres;
        } catch (PDOException $e) {
            // Journalisation détaillée de l'erreur
            error_log("Erreur dans Offre::getAll() - " . $e->getMessage() . " - SQL: {$query} - Params: " . json_encode($params));
            return [];
        }
    }

    /**
     * Compte le nombre total d'offres (pour la pagination)
     *
     * @param array $filters Critères de filtrage optionnels
     * @return int
     */
    public function countAll($filters = []) {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::countAll()");
            return 0;
        }

        try {
            // Construction de la requête de base
            $query = "SELECT COUNT(DISTINCT o.id) as total FROM {$this->table} o";

            // Application des filtres si présents
            $whereConditions = [];
            $params = [];

            if (!empty($filters['entreprise_id'])) {
                $whereConditions[] = "o.entreprise_id = :entreprise_id";
                $params[':entreprise_id'] = $filters['entreprise_id'];
            }

            if (!empty($filters['titre'])) {
                $whereConditions[] = "o.titre LIKE :titre";
                $params[':titre'] = '%' . $filters['titre'] . '%';
            }

            if (!empty($filters['date_debut'])) {
                $whereConditions[] = "o.date_debut >= :date_debut";
                $params[':date_debut'] = $filters['date_debut'];
            }

            if (!empty($filters['date_fin'])) {
                $whereConditions[] = "o.date_fin <= :date_fin";
                $params[':date_fin'] = $filters['date_fin'];
            }

            if (!empty($filters['competence_id'])) {
                // Jointure avec table de liaison pour filtrer par compétence
                $query .= " INNER JOIN {$this->joinTable} oc ON o.id = oc.offre_id";
                $whereConditions[] = "oc.competence_id = :competence_id";
                $params[':competence_id'] = $filters['competence_id'];
            }

            // Ajout des conditions WHERE si présentes
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }

            // Préparation et exécution de la requête
            $stmt = $this->conn->prepare($query);
            foreach ($params as $key => $value) {
                // Liaison des paramètres avec le type approprié
                if (is_int($value)) {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
            $stmt->execute();

            // Récupération du résultat
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['total'];
        } catch (PDOException $e) {
            // Journalisation de l'erreur
            error_log("Erreur lors du comptage des offres: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère une offre par son ID avec tous les détails
     *
     * @param int $id ID de l'offre
     * @return array|false
     */
    public function getById($id) {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::getById()");
            return false;
        }

        try {
            // Requête avec jointure pour récupérer les infos de l'entreprise
            $query = "SELECT o.*, e.nom as entreprise_nom, e.description as entreprise_description, 
                      e.email as entreprise_email, e.telephone as entreprise_telephone
                      FROM {$this->table} o
                      LEFT JOIN {$this->entreprisesTable} e ON o.entreprise_id = e.id
                      WHERE o.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $offre = $this->hydrateOffre($row);

            // Récupération des compétences associées
            $offre['competences'] = $this->getCompetencesForOffre($id);

            // Calcul du nombre de candidatures
            $offre['nb_candidatures'] = $this->countCandidaturesForOffre($id);

            return $offre;
        } catch (PDOException $e) {
            // Journalisation de l'erreur
            error_log("Erreur lors de la récupération de l'offre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère toutes les compétences associées à une offre
     *
     * @param int $offreId ID de l'offre
     * @return array
     */
    private function getCompetencesForOffre($offreId) {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::getCompetencesForOffre()");
            return [];
        }

        try {
            $query = "SELECT c.id, c.nom 
                      FROM {$this->competencesTable} c
                      INNER JOIN {$this->joinTable} oc ON c.id = oc.competence_id
                      WHERE oc.offre_id = :offre_id
                      ORDER BY c.nom ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
            $stmt->execute();

            $competences = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $competences[] = [
                    'id' => $row['id'],
                    'nom' => $row['nom']
                ];
            }

            return $competences;
        } catch (PDOException $e) {
            // Journalisation de l'erreur
            error_log("Erreur lors de la récupération des compétences: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre de candidatures pour une offre
     *
     * @param int $offreId ID de l'offre
     * @return int
     */
    private function countCandidaturesForOffre($offreId) {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::countCandidaturesForOffre()");
            return 0;
        }

        try {
            $query = "SELECT COUNT(*) as total FROM {$this->candidaturesTable} WHERE offre_id = :offre_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['total'];
        } catch (PDOException $e) {
            // Journalisation de l'erreur
            error_log("Erreur lors du comptage des candidatures: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Crée une nouvelle offre de stage avec transaction
     *
     * @param array $data Données de l'offre
     * @return int|false ID de l'offre créée ou false en cas d'échec
     */
    public function create($data) {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::create()");
            return false;
        }

        try {
            // Début de la transaction
            $this->conn->beginTransaction();

            // Insertion dans la table des offres
            $query = "INSERT INTO {$this->table} 
                      (titre, description, entreprise_id, remuneration, date_debut, date_fin, created_at)
                      VALUES (:titre, :description, :entreprise_id, :remuneration, :date_debut, :date_fin, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':titre', $data['titre']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':entreprise_id', $data['entreprise_id'], PDO::PARAM_INT);
            $stmt->bindParam(':remuneration', $data['remuneration']);
            $stmt->bindParam(':date_debut', $data['date_debut']);
            $stmt->bindParam(':date_fin', $data['date_fin']);

            $stmt->execute();

            // Récupération de l'ID de l'offre insérée
            $offreId = $this->conn->lastInsertId();

            // Insertion des compétences associées
            if (!empty($data['competences'])) {
                $insertCompetencesQuery = "INSERT INTO {$this->joinTable} (offre_id, competence_id) VALUES (:offre_id, :competence_id)";
                $compStmt = $this->conn->prepare($insertCompetencesQuery);

                foreach ($data['competences'] as $competenceId) {
                    $compStmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
                    $compStmt->bindParam(':competence_id', $competenceId, PDO::PARAM_INT);
                    $compStmt->execute();
                }
            }

            // Validation de la transaction
            $this->conn->commit();

            return $offreId;
        } catch (PDOException $e) {
            // Annulation de la transaction en cas d'erreur
            $this->conn->rollBack();
            // Journalisation de l'erreur
            error_log("Erreur lors de la création de l'offre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour une offre existante avec transaction
     *
     * @param int $id ID de l'offre à mettre à jour
     * @param array $data Nouvelles données
     * @return bool
     */
    public function update($id, $data) {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::update()");
            return false;
        }

        try {
            // Début de la transaction
            $this->conn->beginTransaction();

            // Mise à jour de l'offre
            $query = "UPDATE {$this->table} SET 
                      titre = :titre, 
                      description = :description, 
                      entreprise_id = :entreprise_id, 
                      remuneration = :remuneration, 
                      date_debut = :date_debut, 
                      date_fin = :date_fin, 
                      updated_at = NOW()
                      WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':titre', $data['titre']);
            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':entreprise_id', $data['entreprise_id'], PDO::PARAM_INT);
            $stmt->bindParam(':remuneration', $data['remuneration']);
            $stmt->bindParam(':date_debut', $data['date_debut']);
            $stmt->bindParam(':date_fin', $data['date_fin']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            // Mise à jour des compétences si fournies
            if (isset($data['competences'])) {
                // Suppression des associations existantes
                $deleteQuery = "DELETE FROM {$this->joinTable} WHERE offre_id = :offre_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->bindParam(':offre_id', $id, PDO::PARAM_INT);
                $deleteStmt->execute();

                // Insertion des nouvelles associations
                if (!empty($data['competences'])) {
                    $insertQuery = "INSERT INTO {$this->joinTable} (offre_id, competence_id) VALUES (:offre_id, :competence_id)";
                    $insertStmt = $this->conn->prepare($insertQuery);

                    foreach ($data['competences'] as $competenceId) {
                        $insertStmt->bindParam(':offre_id', $id, PDO::PARAM_INT);
                        $insertStmt->bindParam(':competence_id', $competenceId, PDO::PARAM_INT);
                        $insertStmt->execute();
                    }
                }
            }

            // Validation de la transaction
            $this->conn->commit();

            return true;
        } catch (PDOException $e) {
            // Annulation de la transaction en cas d'erreur
            $this->conn->rollBack();
            // Journalisation de l'erreur
            error_log("Erreur lors de la mise à jour de l'offre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une offre et toutes ses associations (transaction)
     *
     * @param int $id ID de l'offre à supprimer
     * @return bool
     */
    public function delete($id) {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::delete()");
            return false;
        }

        try {
            // Début de la transaction
            $this->conn->beginTransaction();

            // Suppression des associations de compétences
            $deleteCompetencesQuery = "DELETE FROM {$this->joinTable} WHERE offre_id = :offre_id";
            $compStmt = $this->conn->prepare($deleteCompetencesQuery);
            $compStmt->bindParam(':offre_id', $id, PDO::PARAM_INT);
            $compStmt->execute();

            // Suppression des candidatures associées
            $deleteCandidaturesQuery = "DELETE FROM {$this->candidaturesTable} WHERE offre_id = :offre_id";
            $candStmt = $this->conn->prepare($deleteCandidaturesQuery);
            $candStmt->bindParam(':offre_id', $id, PDO::PARAM_INT);
            $candStmt->execute();

            // Suppression des wishlists associées
            $deleteWishlistsQuery = "DELETE FROM {$this->wishlistsTable} WHERE offre_id = :offre_id";
            $wishStmt = $this->conn->prepare($deleteWishlistsQuery);
            $wishStmt->bindParam(':offre_id', $id, PDO::PARAM_INT);
            $wishStmt->execute();

            // Suppression de l'offre elle-même
            $deleteOffreQuery = "DELETE FROM {$this->table} WHERE id = :id";
            $offreStmt = $this->conn->prepare($deleteOffreQuery);
            $offreStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $offreStmt->execute();

            // Validation de la transaction
            $this->conn->commit();

            return true;
        } catch (PDOException $e) {
            // Annulation de la transaction en cas d'erreur
            $this->conn->rollBack();
            // Journalisation de l'erreur
            error_log("Erreur lors de la suppression de l'offre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère toutes les compétences disponibles
     *
     * @return array
     */
    public function getAllCompetences() {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::getAllCompetences()");
            return [];
        }

        try {
            $query = "SELECT id, nom FROM {$this->competencesTable} ORDER BY nom ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $competences = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $competences[] = [
                    'id' => $row['id'],
                    'nom' => $row['nom']
                ];
            }

            return $competences;
        } catch (PDOException $e) {
            // Journalisation de l'erreur
            error_log("Erreur lors de la récupération des compétences: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les statistiques des offres pour le dashboard
     *
     * @return array
     */
    public function getStatistics() {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::getStatistics()");
            return [
                'total_offres' => 0,
                'repartition_competences' => [],
                'repartition_duree' => [],
                'top_wishlist' => []
            ];
        }

        try {
            $statistics = [];

            // 1. Nombre total d'offres
            $totalQuery = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->conn->prepare($totalQuery);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $statistics['total_offres'] = (int) $row['total'];

            // 2. Répartition par compétence
            $compQuery = "SELECT c.nom, COUNT(DISTINCT oc.offre_id) as count 
                         FROM {$this->competencesTable} c
                         LEFT JOIN {$this->joinTable} oc ON c.id = oc.competence_id
                         GROUP BY c.id, c.nom
                         ORDER BY count DESC, c.nom ASC
                         LIMIT 10";
            $stmt = $this->conn->prepare($compQuery);
            $stmt->execute();
            $statistics['repartition_competences'] = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $statistics['repartition_competences'][] = [
                    'competence' => $row['nom'],
                    'count' => (int) $row['count']
                ];
            }

            // 3. Répartition par durée de stage (en mois)
            $dureeQuery = "SELECT 
                          CASE 
                            WHEN DATEDIFF(date_fin, date_debut) <= 30 THEN '1 mois'
                            WHEN DATEDIFF(date_fin, date_debut) <= 60 THEN '2 mois'
                            WHEN DATEDIFF(date_fin, date_debut) <= 90 THEN '3 mois'
                            WHEN DATEDIFF(date_fin, date_debut) <= 120 THEN '4 mois'
                            WHEN DATEDIFF(date_fin, date_debut) <= 150 THEN '5 mois'
                            ELSE '6 mois et plus'
                          END as duree,
                          COUNT(*) as count
                          FROM {$this->table}
                          GROUP BY duree
                          ORDER BY CASE duree
                            WHEN '1 mois' THEN 1
                            WHEN '2 mois' THEN 2
                            WHEN '3 mois' THEN 3
                            WHEN '4 mois' THEN 4
                            WHEN '5 mois' THEN 5
                            ELSE 6
                          END";
            $stmt = $this->conn->prepare($dureeQuery);
            $stmt->execute();
            $statistics['repartition_duree'] = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $statistics['repartition_duree'][] = [
                    'duree' => $row['duree'],
                    'count' => (int) $row['count']
                ];
            }

            // 4. Top des offres les plus populaires (wishlists)
            $wishlistQuery = "SELECT o.id, o.titre, e.nom as entreprise, COUNT(w.etudiant_id) as count
                             FROM {$this->table} o
                             LEFT JOIN {$this->wishlistsTable} w ON o.id = w.offre_id
                             LEFT JOIN {$this->entreprisesTable} e ON o.entreprise_id = e.id
                             GROUP BY o.id, o.titre, e.nom
                             HAVING count > 0
                             ORDER BY count DESC
                             LIMIT 5";
            $stmt = $this->conn->prepare($wishlistQuery);
            $stmt->execute();
            $statistics['top_wishlist'] = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $statistics['top_wishlist'][] = [
                    'id' => $row['id'],
                    'titre' => $row['titre'],
                    'entreprise' => $row['entreprise'],
                    'count' => (int) $row['count']
                ];
            }

            return $statistics;
        } catch (PDOException $e) {
            // Journalisation détaillée de l'erreur
            error_log("Erreur dans Offre::getStatistics() - " . $e->getMessage());
            return [
                'total_offres' => 0,
                'repartition_competences' => [],
                'repartition_duree' => [],
                'top_wishlist' => []
            ];
        }
    }

    /**
     * Récupère les dernières offres ajoutées
     *
     * @param int $limit Nombre maximum d'offres à récupérer
     * @return array
     */
    public function getLatest($limit = 5) {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::getLatest()");
            return [];
        }

        try {
            $query = "SELECT o.id, o.titre, o.created_at, e.nom as entreprise_nom
                      FROM {$this->table} o
                      INNER JOIN {$this->entreprisesTable} e ON o.entreprise_id = e.id
                      ORDER BY o.created_at DESC
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $offers = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $offers[] = $row;
            }

            return $offers;
        } catch (PDOException $e) {
            // Journalisation de l'erreur
            error_log("Erreur lors de la récupération des dernières offres: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si une offre appartient à une entreprise donnée
     *
     * @param int $offreId ID de l'offre
     * @param int $entrepriseId ID de l'entreprise
     * @return bool
     */
    public function isOffreFromEntreprise($offreId, $entrepriseId) {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::isOffreFromEntreprise()");
            return false;
        }

        try {
            $query = "SELECT COUNT(*) as count FROM {$this->table} 
                     WHERE id = :offre_id AND entreprise_id = :entreprise_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
            $stmt->bindParam(':entreprise_id', $entrepriseId, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$row['count'] > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de l'appartenance de l'offre: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les offres actives d'une entreprise
     *
     * @param int $entrepriseId ID de l'entreprise
     * @param int $limit Limite de résultats (0 = tous)
     * @return array Liste des offres actives
     */
    public function getActiveOffersByCompany($entrepriseId, $limit = 0) {
        // Vérification préalable de la connexion
        if ($this->conn === null) {
            error_log("Erreur: Tentative d'accès à la base de données sans connexion établie dans Offre::getActiveOffersByCompany()");
            return [];
        }

        try {
            $query = "SELECT o.id, o.titre, o.remuneration, o.date_debut, o.date_fin,
                     (SELECT COUNT(*) FROM {$this->candidaturesTable} c WHERE c.offre_id = o.id) as nb_candidatures
                     FROM {$this->table} o
                     WHERE o.entreprise_id = :entreprise_id
                     AND o.date_fin >= CURDATE()
                     ORDER BY o.date_debut ASC";

            if ($limit > 0) {
                $query .= " LIMIT :limit";
            }

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':entreprise_id', $entrepriseId, PDO::PARAM_INT);

            if ($limit > 0) {
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            }

            $stmt->execute();

            $offers = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $offers[] = $row;
            }

            return $offers;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des offres actives: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Hydrate les données d'une offre à partir d'une ligne de résultat
     *
     * @param array $row Ligne de résultat de requête
     * @return array
     */
    private function hydrateOffre($row) {
        return [
            'id' => $row['id'],
            'titre' => $row['titre'],
            'description' => $row['description'],
            'entreprise_id' => $row['entreprise_id'],
            'entreprise_nom' => $row['entreprise_nom'],
            'entreprise_description' => $row['entreprise_description'] ?? null,
            'entreprise_email' => $row['entreprise_email'] ?? null,
            'entreprise_telephone' => $row['entreprise_telephone'] ?? null,
            'remuneration' => $row['remuneration'],
            'date_debut' => $row['date_debut'],
            'date_fin' => $row['date_fin'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
}