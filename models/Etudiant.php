<?php
/**
 * Modèle pour la gestion des étudiants
 *
 * Implémente les opérations CRUD et les services spécifiques aux étudiants
 * avec optimisations des requêtes et gestion robuste des erreurs.
 *
 * @version 2.1
 * @author Web4All
 */
class Etudiant {
    /** @var PDO Instance de connexion à la base de données */
    private $conn;

    /** @var string Nom de la table principale */
    private $table = 'etudiants';

    /** @var string Nom de la table utilisateurs */
    private $usersTable = 'utilisateurs';

    /** @var string Nom de la table candidatures */
    private $candidaturesTable = 'candidatures';

    /** @var string Nom de la table wishlist */
    private $wishlistTable = 'wishlists';

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
                error_log("Mode dégradé activé: Impossible d'établir la connexion à la base de données dans Etudiant.php");
            }
        } catch (Exception $e) {
            $this->dbError = true;
            error_log("Exception dans Etudiant::__construct(): " . $e->getMessage());
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
     * Récupère un étudiant par ID utilisateur
     *
     * @param int $userId ID de l'utilisateur
     * @return array|false Données de l'étudiant ou false si non trouvé
     */
    public function getByUserId($userId) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "SELECT e.*, u.email, u.role
                     FROM {$this->table} e
                     JOIN {$this->usersTable} u ON e.user_id = u.id
                     WHERE e.user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                error_log("Aucun étudiant trouvé pour l'ID utilisateur: $userId");
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'étudiant par ID utilisateur: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère l'ID étudiant correspondant à un ID utilisateur
     *
     * @param int $userId ID utilisateur
     * @return int|false ID étudiant ou false si non trouvé
     */
    public function getEtudiantIdFromUserId($userId) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "SELECT id FROM {$this->table} WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                error_log("Aucun ID étudiant trouvé pour l'ID utilisateur: $userId");
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$row['id'];
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'ID étudiant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les étudiants avec pagination et filtrage
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
            $query = "SELECT e.*, u.email, u.role,
                     (SELECT COUNT(*) FROM {$this->candidaturesTable} c WHERE c.etudiant_id = e.id) as nb_candidatures,
                     (SELECT COUNT(*) FROM {$this->wishlistTable} w WHERE w.etudiant_id = e.id) as nb_wishlist
                     FROM {$this->table} e
                     LEFT JOIN {$this->usersTable} u ON e.user_id = u.id";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['nom'])) {
                $whereConditions[] = "e.nom LIKE :nom";
                $params[':nom'] = '%' . $filters['nom'] . '%';
            }

            if (!empty($filters['prenom'])) {
                $whereConditions[] = "e.prenom LIKE :prenom";
                $params[':prenom'] = '%' . $filters['prenom'] . '%';
            }

            if (!empty($filters['email'])) {
                $whereConditions[] = "u.email LIKE :email";
                $params[':email'] = '%' . $filters['email'] . '%';
            }

            if (!empty($filters['with_candidatures'])) {
                $whereConditions[] = "(SELECT COUNT(*) FROM {$this->candidaturesTable} c WHERE c.etudiant_id = e.id) > 0";
            }

            // Ajout des conditions WHERE si présentes
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }

            // Tri des résultats (par défaut, par nom)
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
            $etudiants = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $etudiant = [
                    'id' => $row['id'],
                    'user_id' => $row['user_id'],
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'email' => $row['email'],
                    'role' => $row['role'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'nb_candidatures' => $row['nb_candidatures'],
                    'nb_wishlist' => $row['nb_wishlist']
                ];
                $etudiants[] = $etudiant;
            }

            return $etudiants;
        } catch (PDOException $e) {
            error_log("Erreur dans Etudiant::getAll() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre total d'étudiants pour la pagination
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
            $query = "SELECT COUNT(DISTINCT e.id) as total FROM {$this->table} e
                      LEFT JOIN {$this->usersTable} u ON e.user_id = u.id";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['nom'])) {
                $whereConditions[] = "e.nom LIKE :nom";
                $params[':nom'] = '%' . $filters['nom'] . '%';
            }

            if (!empty($filters['prenom'])) {
                $whereConditions[] = "e.prenom LIKE :prenom";
                $params[':prenom'] = '%' . $filters['prenom'] . '%';
            }

            if (!empty($filters['email'])) {
                $whereConditions[] = "u.email LIKE :email";
                $params[':email'] = '%' . $filters['email'] . '%';
            }

            if (!empty($filters['with_candidatures'])) {
                $whereConditions[] = "(SELECT COUNT(*) FROM {$this->candidaturesTable} c WHERE c.etudiant_id = e.id) > 0";
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
            error_log("Erreur lors du comptage des étudiants: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère un étudiant par son ID avec tous les détails associés
     *
     * @param int $id ID de l'étudiant
     * @return array|false Données de l'étudiant ou false si non trouvé
     */
    public function getById($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Requête optimisée avec sous-requêtes pour les agrégations
            $query = "SELECT e.*, u.email, u.role,
                     (SELECT COUNT(*) FROM {$this->candidaturesTable} c WHERE c.etudiant_id = e.id) as nb_candidatures,
                     (SELECT COUNT(*) FROM {$this->wishlistTable} w WHERE w.etudiant_id = e.id) as nb_wishlist
                     FROM {$this->table} e
                     LEFT JOIN {$this->usersTable} u ON e.user_id = u.id
                     WHERE e.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $etudiant = [
                'id' => $row['id'],
                'user_id' => $row['user_id'],
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'email' => $row['email'],
                'role' => $row['role'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'nb_candidatures' => $row['nb_candidatures'],
                'nb_wishlist' => $row['nb_wishlist']
            ];

            // Récupération des candidatures associées
            $etudiant['candidatures'] = $this->getCandidaturesForEtudiant($id);

            // Récupération des offres en wishlist
            $etudiant['wishlist'] = $this->getWishlistForEtudiant($id);

            return $etudiant;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de l'étudiant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les candidatures d'un étudiant
     *
     * @param int $etudiantId ID de l'étudiant
     * @return array
     */
    public function getCandidaturesForEtudiant($etudiantId) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT c.*, o.titre as offre_titre, e.nom as entreprise_nom 
                      FROM {$this->candidaturesTable} c
                      LEFT JOIN offres o ON c.offre_id = o.id
                      LEFT JOIN entreprises e ON o.entreprise_id = e.id
                      WHERE c.etudiant_id = :etudiant_id
                      ORDER BY c.date_candidature DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            $stmt->execute();

            $candidatures = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $candidatures[] = [
                    'id' => $row['id'],
                    'offre_id' => $row['offre_id'],
                    'offre_titre' => $row['offre_titre'],
                    'entreprise_nom' => $row['entreprise_nom'],
                    'cv' => $row['cv'],
                    'lettre_motivation' => $row['lettre_motivation'],
                    'date_candidature' => $row['date_candidature']
                ];
            }

            return $candidatures;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des candidatures: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les offres en wishlist d'un étudiant
     *
     * @param int $etudiantId ID de l'étudiant
     * @return array
     */
    public function getWishlistForEtudiant($etudiantId) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT w.*, o.titre as offre_titre, e.nom as entreprise_nom 
                      FROM {$this->wishlistTable} w
                      LEFT JOIN offres o ON w.offre_id = o.id
                      LEFT JOIN entreprises e ON o.entreprise_id = e.id
                      WHERE w.etudiant_id = :etudiant_id
                      ORDER BY w.date_ajout DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            $stmt->execute();

            $wishlist = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $wishlist[] = [
                    'offre_id' => $row['offre_id'],
                    'offre_titre' => $row['offre_titre'],
                    'entreprise_nom' => $row['entreprise_nom'],
                    'date_ajout' => $row['date_ajout']
                ];
            }

            return $wishlist;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de la wishlist: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crée un nouvel étudiant avec son compte utilisateur associé
     *
     * @param array $data Données de l'étudiant
     * @return int|false ID de l'étudiant créé ou false en cas d'échec
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
                         VALUES (:email, :password, 'etudiant', NOW())";

            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->bindParam(':email', $data['email']);

            // Hashage du mot de passe
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $userStmt->bindParam(':password', $hashedPassword);

            $userStmt->execute();
            $userId = $this->conn->lastInsertId();

            // 2. Création du profil étudiant
            $etudiantQuery = "INSERT INTO {$this->table} (user_id, nom, prenom, created_at)
                             VALUES (:user_id, :nom, :prenom, NOW())";

            $etudiantStmt = $this->conn->prepare($etudiantQuery);
            $etudiantStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $etudiantStmt->bindParam(':nom', $data['nom']);
            $etudiantStmt->bindParam(':prenom', $data['prenom']);

            $etudiantStmt->execute();
            $etudiantId = $this->conn->lastInsertId();

            // Validation de la transaction
            $this->conn->commit();

            return $etudiantId;
        } catch (PDOException $e) {
            // Annulation de la transaction en cas d'erreur
            $this->conn->rollBack();
            error_log("Erreur lors de la création de l'étudiant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour un étudiant existant
     *
     * @param int $id ID de l'étudiant
     * @param array $data Nouvelles données
     * @return bool
     */
    public function update($id, $data) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Récupération de l'étudiant pour obtenir le user_id
            $query = "SELECT user_id FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return false;
            }

            $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);
            $userId = $etudiant['user_id'];

            // Début de la transaction
            $this->conn->beginTransaction();

            // 1. Mise à jour du profil étudiant
            $etudiantQuery = "UPDATE {$this->table} SET
                             nom = :nom,
                             prenom = :prenom,
                             updated_at = NOW()
                             WHERE id = :id";

            $etudiantStmt = $this->conn->prepare($etudiantQuery);
            $etudiantStmt->bindParam(':nom', $data['nom']);
            $etudiantStmt->bindParam(':prenom', $data['prenom']);
            $etudiantStmt->bindParam(':id', $id, PDO::PARAM_INT);

            $etudiantStmt->execute();

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
            error_log("Erreur lors de la mise à jour de l'étudiant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un étudiant et son compte utilisateur associé
     *
     * @param int $id ID de l'étudiant
     * @return bool
     */
    public function delete($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Récupération de l'étudiant pour obtenir le user_id
            $query = "SELECT user_id FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return false;
            }

            $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);
            $userId = $etudiant['user_id'];

            // Début de la transaction
            $this->conn->beginTransaction();

            // 1. Suppression des candidatures
            $candQuery = "DELETE FROM {$this->candidaturesTable} WHERE etudiant_id = :etudiant_id";
            $candStmt = $this->conn->prepare($candQuery);
            $candStmt->bindParam(':etudiant_id', $id, PDO::PARAM_INT);
            $candStmt->execute();

            // 2. Suppression des wishlist
            $wishQuery = "DELETE FROM {$this->wishlistTable} WHERE etudiant_id = :etudiant_id";
            $wishStmt = $this->conn->prepare($wishQuery);
            $wishStmt->bindParam(':etudiant_id', $id, PDO::PARAM_INT);
            $wishStmt->execute();

            // 3. Suppression du profil étudiant
            $etudiantQuery = "DELETE FROM {$this->table} WHERE id = :id";
            $etudiantStmt = $this->conn->prepare($etudiantQuery);
            $etudiantStmt->bindParam(':id', $id, PDO::PARAM_INT);
            $etudiantStmt->execute();

            // 4. Suppression du compte utilisateur
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
            error_log("Erreur lors de la suppression de l'étudiant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les derniers étudiants inscrits
     *
     * @param int $limit Nombre maximum d'étudiants à récupérer
     * @return array
     */
    public function getLatest($limit = 5) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT e.id, e.nom, e.prenom, e.created_at, u.email
                      FROM {$this->table} e
                      LEFT JOIN {$this->usersTable} u ON e.user_id = u.id
                      ORDER BY e.created_at DESC
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $etudiants = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $etudiants[] = $row;
            }

            return $etudiants;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des derniers étudiants: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les statistiques des étudiants pour le dashboard
     *
     * @return array
     */
    public function getStatistics() {
        // Mode dégradé - retourne structure vide
        if ($this->dbError) {
            return [
                'total_etudiants' => 0,
                'with_candidatures' => 0,
                'avg_candidatures' => 0,
                'most_active' => []
            ];
        }

        try {
            $statistics = [];

            // Requête multi-statistiques optimisée
            $statsQuery = "SELECT 
                         (SELECT COUNT(*) FROM {$this->table}) AS total_etudiants,
                         (SELECT COUNT(DISTINCT etudiant_id) FROM {$this->candidaturesTable}) AS with_candidatures,
                         (SELECT COUNT(*) FROM {$this->candidaturesTable}) / 
                         GREATEST((SELECT COUNT(DISTINCT etudiant_id) FROM {$this->candidaturesTable}), 1) AS avg_candidatures";

            $stmt = $this->conn->prepare($statsQuery);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $statistics['total_etudiants'] = (int)$row['total_etudiants'];
            $statistics['with_candidatures'] = (int)$row['with_candidatures'];
            $statistics['avg_candidatures'] = round((float)$row['avg_candidatures'], 1);

            // Étudiants les plus actifs (par nombre de candidatures)
            $activeQuery = "SELECT e.id, e.nom, e.prenom, COUNT(c.id) as num_candidatures
                           FROM {$this->table} e
                           INNER JOIN {$this->candidaturesTable} c ON e.id = c.etudiant_id
                           GROUP BY e.id
                           ORDER BY num_candidatures DESC
                           LIMIT 5";

            $stmt = $this->conn->prepare($activeQuery);
            $stmt->execute();

            $mostActive = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $mostActive[] = [
                    'id' => $row['id'],
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'num_candidatures' => (int)$row['num_candidatures']
                ];
            }
            $statistics['most_active'] = $mostActive;

            return $statistics;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des statistiques des étudiants: " . $e->getMessage());
            return [
                'total_etudiants' => 0,
                'with_candidatures' => 0,
                'avg_candidatures' => 0,
                'most_active' => []
            ];
        }
    }

    /**
     * Crée automatiquement un profil étudiant pour un utilisateur avec le rôle 'etudiant'
     * qui n'a pas de profil étudiant associé
     *
     * @param int $userId ID de l'utilisateur
     * @param string $nom Nom de l'étudiant (facultatif)
     * @param string $prenom Prénom de l'étudiant (facultatif)
     * @return int|false ID du profil étudiant créé ou false en cas d'échec
     */
    public function createProfileForUser($userId, $nom = 'Nom', $prenom = 'Prenom') {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Vérifier si l'utilisateur existe et a le rôle 'etudiant'
            $userQuery = "SELECT id, email, role FROM {$this->usersTable} WHERE id = :id AND role = 'etudiant'";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $userStmt->execute();

            if ($userStmt->rowCount() == 0) {
                error_log("Impossible de créer un profil étudiant pour l'utilisateur $userId: soit l'utilisateur n'existe pas, soit il n'a pas le rôle 'etudiant'");
                return false;
            }

            $user = $userStmt->fetch(PDO::FETCH_ASSOC);

            // Vérifier si un profil étudiant existe déjà
            $checkQuery = "SELECT id FROM {$this->table} WHERE user_id = :user_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                $existingProfile = $checkStmt->fetch(PDO::FETCH_ASSOC);
                error_log("Un profil étudiant existe déjà pour l'utilisateur $userId (étudiant ID: {$existingProfile['id']})");
                return $existingProfile['id'];
            }

            // Création du profil étudiant
            $createQuery = "INSERT INTO {$this->table} (user_id, nom, prenom, created_at)
                           VALUES (:user_id, :nom, :prenom, NOW())";

            $createStmt = $this->conn->prepare($createQuery);
            $createStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $createStmt->bindParam(':nom', $nom);
            $createStmt->bindParam(':prenom', $prenom);
            $createStmt->execute();

            $etudiantId = $this->conn->lastInsertId();

            error_log("Profil étudiant créé automatiquement pour l'utilisateur $userId (étudiant ID: $etudiantId)");

            return $etudiantId;
        } catch (PDOException $e) {
            error_log("Erreur lors de la création automatique du profil étudiant: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie et répare les relations utilisateur-étudiant
     *
     * @return array Résultats de la vérification/réparation
     */
    public function verifyAndRepairUserStudentRelations() {
        // Mode dégradé - retourne structure vide
        if ($this->dbError) {
            return [
                'status' => 'error',
                'message' => 'Connexion à la base de données non disponible',
                'repairs' => 0
            ];
        }

        try {
            // Identifier les utilisateurs de rôle 'etudiant' sans entrée dans la table etudiants
            $query = "SELECT u.id, u.email, u.role
                     FROM {$this->usersTable} u
                     LEFT JOIN {$this->table} e ON u.id = e.user_id
                     WHERE u.role = 'etudiant' AND e.id IS NULL";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $usersWithoutProfile = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $repairs = 0;
            $failed = 0;

            // Créer les profils manquants
            foreach ($usersWithoutProfile as $user) {
                $emailParts = explode('@', $user['email']);
                $username = $emailParts[0];

                // Extraire nom et prénom du nom d'utilisateur (si possible)
                $parts = explode('.', $username);
                $prenom = ucfirst($parts[0] ?? 'Prenom');
                $nom = ucfirst($parts[1] ?? 'Nom');

                $result = $this->createProfileForUser($user['id'], $nom, $prenom);

                if ($result) {
                    $repairs++;
                } else {
                    $failed++;
                }
            }

            return [
                'status' => 'success',
                'message' => "Vérification terminée. $repairs profils créés, $failed échecs.",
                'repairs' => $repairs,
                'failed' => $failed,
                'identified' => count($usersWithoutProfile)
            ];
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification/réparation des relations utilisateur-étudiant: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => "Erreur lors de la vérification: " . $e->getMessage(),
                'repairs' => 0
            ];
        }
    }
}