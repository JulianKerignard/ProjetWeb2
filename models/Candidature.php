<?php
/**
 * Modèle pour la gestion des candidatures aux offres de stage
 *
 * Implémente les opérations CRUD et les services spécifiques aux candidatures
 * avec optimisations des requêtes et gestion robuste des erreurs.
 *
 * @version 2.0
 * @author Web4All
 */
class Candidature {
    /** @var PDO Instance de connexion à la base de données */
    private $conn;

    /** @var string Nom de la table principale des candidatures */
    private $table = 'candidatures';

    /** @var string Nom de la table des wishlists */
    private $wishlistTable = 'wishlists';

    /** @var string Nom de la table des offres */
    private $offresTable = 'offres';

    /** @var string Nom de la table des entreprises */
    private $entreprisesTable = 'entreprises';

    /** @var string Nom de la table des étudiants */
    private $etudiantsTable = 'etudiants';

    /** @var bool Indicateur d'erreur de base de données */
    private $dbError = false;

    /** @var string Chemin d'upload pour les CVs */
    private $uploadDir;

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
                error_log("Mode dégradé activé: Impossible d'établir la connexion à la base de données dans Candidature.php");
            }

            // Initialisation du chemin d'upload
            $this->uploadDir = defined('UPLOAD_DIR') ? UPLOAD_DIR : ROOT_PATH . '/public/uploads/';
        } catch (Exception $e) {
            $this->dbError = true;
            error_log("Exception dans Candidature::__construct(): " . $e->getMessage());
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
     * Récupère toutes les candidatures avec pagination et filtrage
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

            // Construction de la requête SQL de base
            $query = "SELECT c.*, 
                     o.titre as offre_titre, 
                     e.id as entreprise_id, e.nom as entreprise_nom,
                     CONCAT(et.prenom, ' ', et.nom) as etudiant_nom
                     FROM {$this->table} c
                     LEFT JOIN {$this->offresTable} o ON c.offre_id = o.id
                     LEFT JOIN {$this->entreprisesTable} e ON o.entreprise_id = e.id
                     LEFT JOIN {$this->etudiantsTable} et ON c.etudiant_id = et.id";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['etudiant_id'])) {
                $whereConditions[] = "c.etudiant_id = :etudiant_id";
                $params[':etudiant_id'] = $filters['etudiant_id'];
            }

            if (!empty($filters['offre_id'])) {
                $whereConditions[] = "c.offre_id = :offre_id";
                $params[':offre_id'] = $filters['offre_id'];
            }

            if (!empty($filters['entreprise_id'])) {
                $whereConditions[] = "o.entreprise_id = :entreprise_id";
                $params[':entreprise_id'] = $filters['entreprise_id'];
            }

            if (!empty($filters['date_debut']) && !empty($filters['date_fin'])) {
                $whereConditions[] = "c.date_candidature BETWEEN :date_debut AND :date_fin";
                $params[':date_debut'] = $filters['date_debut'] . ' 00:00:00';
                $params[':date_fin'] = $filters['date_fin'] . ' 23:59:59';
            } else if (!empty($filters['date_debut'])) {
                $whereConditions[] = "c.date_candidature >= :date_debut";
                $params[':date_debut'] = $filters['date_debut'] . ' 00:00:00';
            } else if (!empty($filters['date_fin'])) {
                $whereConditions[] = "c.date_candidature <= :date_fin";
                $params[':date_fin'] = $filters['date_fin'] . ' 23:59:59';
            }

            // Ajout des conditions WHERE si présentes
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }

            // Tri des résultats
            $orderBy = !empty($filters['order_by']) ? $filters['order_by'] : 'c.date_candidature';
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
            $candidatures = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $candidatures[] = [
                    'id' => $row['id'],
                    'offre_id' => $row['offre_id'],
                    'etudiant_id' => $row['etudiant_id'],
                    'cv' => $row['cv'],
                    'lettre_motivation' => $row['lettre_motivation'],
                    'date_candidature' => $row['date_candidature'],
                    'offre_titre' => $row['offre_titre'],
                    'entreprise_id' => $row['entreprise_id'],
                    'entreprise_nom' => $row['entreprise_nom'],
                    'etudiant_nom' => $row['etudiant_nom']
                ];
            }

            return $candidatures;
        } catch (PDOException $e) {
            error_log("Erreur dans Candidature::getAll() - " . $e->getMessage());
            return [];
        }
    }

    /**
     * Compte le nombre total de candidatures pour la pagination
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
            // Construction de la requête SQL de base
            $query = "SELECT COUNT(*) as total FROM {$this->table} c
                      LEFT JOIN {$this->offresTable} o ON c.offre_id = o.id";

            // Construction des clauses WHERE selon les filtres
            $whereConditions = [];
            $params = [];

            if (!empty($filters['etudiant_id'])) {
                $whereConditions[] = "c.etudiant_id = :etudiant_id";
                $params[':etudiant_id'] = $filters['etudiant_id'];
            }

            if (!empty($filters['offre_id'])) {
                $whereConditions[] = "c.offre_id = :offre_id";
                $params[':offre_id'] = $filters['offre_id'];
            }

            if (!empty($filters['entreprise_id'])) {
                $whereConditions[] = "o.entreprise_id = :entreprise_id";
                $params[':entreprise_id'] = $filters['entreprise_id'];
            }

            if (!empty($filters['date_debut']) && !empty($filters['date_fin'])) {
                $whereConditions[] = "c.date_candidature BETWEEN :date_debut AND :date_fin";
                $params[':date_debut'] = $filters['date_debut'] . ' 00:00:00';
                $params[':date_fin'] = $filters['date_fin'] . ' 23:59:59';
            } else if (!empty($filters['date_debut'])) {
                $whereConditions[] = "c.date_candidature >= :date_debut";
                $params[':date_debut'] = $filters['date_debut'] . ' 00:00:00';
            } else if (!empty($filters['date_fin'])) {
                $whereConditions[] = "c.date_candidature <= :date_fin";
                $params[':date_fin'] = $filters['date_fin'] . ' 23:59:59';
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
            error_log("Erreur lors du comptage des candidatures: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère une candidature par son ID
     *
     * @param int $id ID de la candidature
     * @return array|false Données de la candidature ou false si non trouvée
     */
    public function getById($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "SELECT c.*, 
                     o.titre as offre_titre, o.description as offre_description, 
                     e.id as entreprise_id, e.nom as entreprise_nom,
                     CONCAT(et.prenom, ' ', et.nom) as etudiant_nom
                     FROM {$this->table} c
                     LEFT JOIN {$this->offresTable} o ON c.offre_id = o.id
                     LEFT JOIN {$this->entreprisesTable} e ON o.entreprise_id = e.id
                     LEFT JOIN {$this->etudiantsTable} et ON c.etudiant_id = et.id
                     WHERE c.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return false;
            }

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return [
                'id' => $row['id'],
                'offre_id' => $row['offre_id'],
                'etudiant_id' => $row['etudiant_id'],
                'cv' => $row['cv'],
                'lettre_motivation' => $row['lettre_motivation'],
                'date_candidature' => $row['date_candidature'],
                'offre_titre' => $row['offre_titre'],
                'offre_description' => $row['offre_description'],
                'entreprise_id' => $row['entreprise_id'],
                'entreprise_nom' => $row['entreprise_nom'],
                'etudiant_nom' => $row['etudiant_nom']
            ];
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de la candidature: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ajoute une candidature
     *
     * @param array $data Données de la candidature
     * @param array $files Fichiers uploadés
     * @return int|false ID de la candidature créée ou false en cas d'échec
     */
    public function create($data, $files = null) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Vérification si l'étudiant a déjà postulé à cette offre
            $checkQuery = "SELECT COUNT(*) as count FROM {$this->table} 
                          WHERE etudiant_id = :etudiant_id AND offre_id = :offre_id";

            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':etudiant_id', $data['etudiant_id'], PDO::PARAM_INT);
            $checkStmt->bindParam(':offre_id', $data['offre_id'], PDO::PARAM_INT);
            $checkStmt->execute();

            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($row['count'] > 0) {
                // L'étudiant a déjà posé sa candidature
                return false;
            }

            // Gérer l'upload du CV si présent
            $cvFilename = '';
            if (!empty($files['cv']) && $files['cv']['error'] === UPLOAD_ERR_OK) {
                $cvFilename = $this->uploadFile($files['cv'], $data['etudiant_id']);
                if (!$cvFilename) {
                    return false;
                }
            } else if (!empty($data['cv'])) {
                // Si un nom de fichier est fourni directement (chemin existant)
                $cvFilename = $data['cv'];
            }

            // Ajout de la candidature
            $query = "INSERT INTO {$this->table} 
                      (offre_id, etudiant_id, cv, lettre_motivation, date_candidature)
                      VALUES (:offre_id, :etudiant_id, :cv, :lettre_motivation, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':offre_id', $data['offre_id'], PDO::PARAM_INT);
            $stmt->bindParam(':etudiant_id', $data['etudiant_id'], PDO::PARAM_INT);
            $stmt->bindParam(':cv', $cvFilename);
            $stmt->bindParam(':lettre_motivation', $data['lettre_motivation']);

            if ($stmt->execute()) {
                // Supprimer l'offre de la wishlist si elle y était
                $this->removeFromWishlist($data['etudiant_id'], $data['offre_id']);

                return $this->conn->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            error_log("Erreur lors de la création de la candidature: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Met à jour une candidature existante
     *
     * @param int $id ID de la candidature
     * @param array $data Nouvelles données
     * @param array $files Fichiers uploadés
     * @return bool
     */
    public function update($id, $data, $files = null) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Récupération de la candidature existante
            $query = "SELECT etudiant_id, cv FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                return false;
            }

            $candidature = $stmt->fetch(PDO::FETCH_ASSOC);
            $cvFilename = $candidature['cv']; // Garder l'ancien CV par défaut

            // Gérer l'upload du nouveau CV si présent
            if (!empty($files['cv']) && $files['cv']['error'] === UPLOAD_ERR_OK) {
                $newCvFilename = $this->uploadFile($files['cv'], $candidature['etudiant_id']);
                if ($newCvFilename) {
                    // Supprimer l'ancien fichier
                    if (!empty($cvFilename) && file_exists($this->uploadDir . $cvFilename)) {
                        @unlink($this->uploadDir . $cvFilename);
                    }
                    $cvFilename = $newCvFilename;
                }
            }

            // Mise à jour de la candidature
            $updateQuery = "UPDATE {$this->table} SET 
                           cv = :cv,
                           lettre_motivation = :lettre_motivation
                           WHERE id = :id";

            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':cv', $cvFilename);
            $updateStmt->bindParam(':lettre_motivation', $data['lettre_motivation']);
            $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $updateStmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de la candidature: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une candidature
     *
     * @param int $id ID de la candidature
     * @return bool
     */
    public function delete($id) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Récupération de la candidature pour obtenir le fichier CV
            $query = "SELECT cv FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $candidature = $stmt->fetch(PDO::FETCH_ASSOC);
                $cvFilename = $candidature['cv'];

                // Suppression du fichier CV
                if (!empty($cvFilename) && file_exists($this->uploadDir . $cvFilename)) {
                    @unlink($this->uploadDir . $cvFilename);
                }
            }

            // Suppression de la candidature
            $deleteQuery = "DELETE FROM {$this->table} WHERE id = :id";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);

            return $deleteStmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de la candidature: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ajoute une offre à la wishlist d'un étudiant
     *
     * @param int $etudiantId ID de l'étudiant
     * @param int $offreId ID de l'offre
     * @return bool
     */
    public function addToWishlist($etudiantId, $offreId) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            // Vérification si l'entrée existe déjà
            $checkQuery = "SELECT COUNT(*) as count FROM {$this->wishlistTable} 
                          WHERE etudiant_id = :etudiant_id AND offre_id = :offre_id";

            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            $checkStmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
            $checkStmt->execute();

            $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
            if ($row['count'] > 0) {
                // L'entrée existe déjà
                return true;
            }

            // Ajout à la wishlist
            $query = "INSERT INTO {$this->wishlistTable} (etudiant_id, offre_id, date_ajout)
                     VALUES (:etudiant_id, :offre_id, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout à la wishlist: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retire une offre de la wishlist d'un étudiant
     *
     * @param int $etudiantId ID de l'étudiant
     * @param int $offreId ID de l'offre
     * @return bool
     */
    public function removeFromWishlist($etudiantId, $offreId) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "DELETE FROM {$this->wishlistTable} 
                     WHERE etudiant_id = :etudiant_id AND offre_id = :offre_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors du retrait de la wishlist: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifie si une offre est dans la wishlist d'un étudiant
     *
     * @param int $etudiantId ID de l'étudiant
     * @param int $offreId ID de l'offre
     * @return bool
     */
    public function isInWishlist($etudiantId, $offreId) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "SELECT COUNT(*) as count FROM {$this->wishlistTable} 
                     WHERE etudiant_id = :etudiant_id AND offre_id = :offre_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$row['count'] > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de la wishlist: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère la wishlist d'un étudiant
     *
     * @param int $etudiantId ID de l'étudiant
     * @return array
     */
    public function getWishlist($etudiantId) {
        // Mode dégradé - retourne un tableau vide
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT w.*, o.titre as offre_titre, o.date_debut, o.date_fin, o.remuneration,
                     e.nom as entreprise_nom, e.id as entreprise_id
                     FROM {$this->wishlistTable} w
                     LEFT JOIN {$this->offresTable} o ON w.offre_id = o.id
                     LEFT JOIN {$this->entreprisesTable} e ON o.entreprise_id = e.id
                     WHERE w.etudiant_id = :etudiant_id
                     ORDER BY w.date_ajout DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            $stmt->execute();

            $wishlist = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $wishlist[] = [
                    'offre_id' => $row['offre_id'],
                    'date_ajout' => $row['date_ajout'],
                    'offre_titre' => $row['offre_titre'],
                    'date_debut' => $row['date_debut'],
                    'date_fin' => $row['date_fin'],
                    'remuneration' => $row['remuneration'],
                    'entreprise_id' => $row['entreprise_id'],
                    'entreprise_nom' => $row['entreprise_nom']
                ];
            }

            return $wishlist;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération de la wishlist: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si un étudiant a déjà postulé à une offre
     *
     * @param int $etudiantId ID de l'étudiant
     * @param int $offreId ID de l'offre
     * @return bool
     */
    public function hasCandidature($etudiantId, $offreId) {
        // Mode dégradé - retourne false
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "SELECT COUNT(*) as count FROM {$this->table} 
                     WHERE etudiant_id = :etudiant_id AND offre_id = :offre_id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':etudiant_id', $etudiantId, PDO::PARAM_INT);
            $stmt->bindParam(':offre_id', $offreId, PDO::PARAM_INT);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)$row['count'] > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de la candidature: " . $e->getMessage());
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
        // Appel à la méthode getAll avec filtre sur l'étudiant
        return $this->getAll(1, 1000, ['etudiant_id' => $etudiantId]);
    }

    /**
     * Gère l'upload d'un fichier
     *
     * @param array $file Données du fichier uploadé
     * @param int $etudiantId ID de l'étudiant (pour le nommage)
     * @return string|false Nom du fichier ou false en cas d'échec
     */
    private function uploadFile($file, $etudiantId) {
        // Vérification des extensions autorisées
        $allowedExtensions = defined('ALLOWED_EXTENSIONS') ?
            ALLOWED_EXTENSIONS : ['pdf', 'doc', 'docx'];

        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedExtensions)) {
            error_log("Extension de fichier non autorisée: {$extension}");
            return false;
        }

        // Vérification de la taille
        $maxSize = defined('MAX_FILE_SIZE') ? MAX_FILE_SIZE : 5 * 1024 * 1024; // 5 Mo par défaut

        if ($file['size'] > $maxSize) {
            error_log("Fichier trop volumineux: {$file['size']} octets");
            return false;
        }

        // Création du répertoire d'upload si nécessaire
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // Génération d'un nom de fichier unique
        $newFilename = 'cv_' . $etudiantId . '_' . time() . '.' . $extension;
        $destination = $this->uploadDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return $newFilename;
        }

        error_log("Erreur lors de l'upload du fichier vers {$destination}");
        return false;
    }
}