<?php
/**
 * Modèle pour la gestion des permissions
 *
 * Implémente le système de contrôle d'accès basé sur les rôles
 * avec prise en charge de transactions robustes et diagnostics avancés.
 *
 * @version 2.0.1
 */
class Permission {
    /** @var PDO Connexion à la base de données */
    private $conn;

    /** @var string Nom de la table des permissions */
    private $table = 'role_permissions';

    /** @var bool Indicateur d'erreur de connexion */
    private $dbError = false;

    /**
     * Constructeur - Initialise la connexion à la BDD avec gestion d'erreurs
     */
    public function __construct() {
        // Vérifier si ROOT_PATH est défini
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));
        }

        // Charger la configuration de la base de données
        require_once ROOT_PATH . '/config/database.php';

        try {
            $database = new Database();
            $this->conn = $database->getConnection();

            if ($this->conn === null) {
                $this->dbError = true;
                error_log("Mode dégradé activé: Impossible d'établir la connexion à la base de données dans Permission.php");
            }

            // Vérifier l'existence de la table au moment de l'initialisation
            if (!$this->dbError && !$this->tableExists()) {
                $this->dbError = true;
                error_log("Mode dégradé activé: La table {$this->table} n'existe pas dans la base de données");
            }
        } catch (Exception $e) {
            $this->dbError = true;
            error_log("Exception dans Permission::__construct(): " . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
    }

    /**
     * Vérifie si la table de permissions existe
     *
     * @return bool
     */
    private function tableExists() {
        try {
            $query = "SHOW TABLES LIKE :table";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':table', $this->table);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de l'existence de la table: " . $e->getMessage());
            return false;
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
     * Récupère toutes les permissions pour tous les rôles
     *
     * @return array
     */
    public function getAllPermissions() {
        if ($this->dbError) {
            return [];
        }

        try {
            $query = "SELECT role, permission FROM {$this->table}";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $permissions = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $permissions[$row['role']][] = $row['permission'];
            }

            return $permissions;
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des permissions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Vérifie si un rôle a une permission spécifique
     *
     * @param string $role Rôle à vérifier
     * @param string $permission Permission à vérifier
     * @return bool
     */
    public function hasPermission($role, $permission) {
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "SELECT id FROM {$this->table} WHERE role = :role AND permission = :permission";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':permission', $permission);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification de la permission: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ajoute une permission à un rôle
     *
     * @param string $role Rôle
     * @param string $permission Permission
     * @return bool|string True en cas de succès, String avec message d'erreur sinon
     */
    public function addPermission($role, $permission) {
        if ($this->dbError) {
            return "Erreur de connexion à la base de données";
        }

        try {
            // Vérifier si la permission existe déjà
            if ($this->hasPermission($role, $permission)) {
                return true; // Déjà présente
            }

            $query = "INSERT INTO {$this->table} (role, permission, created_at) VALUES (:role, :permission, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':permission', $permission);

            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                error_log("Erreur SQL lors de l'ajout de permission: " . json_encode($error));
                return "Erreur SQL: " . ($error[2] ?? "Erreur inconnue");
            }

            return true;
        } catch (PDOException $e) {
            $errorMessage = "Erreur lors de l'ajout de la permission: " . $e->getMessage();
            error_log($errorMessage);
            return $errorMessage;
        }
    }

    /**
     * Supprime une permission d'un rôle
     *
     * @param string $role Rôle
     * @param string $permission Permission
     * @return bool
     */
    public function removePermission($role, $permission) {
        if ($this->dbError) {
            return false;
        }

        try {
            $query = "DELETE FROM {$this->table} WHERE role = :role AND permission = :permission";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':permission', $permission);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de la suppression de la permission: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime toutes les permissions d'un rôle
     *
     * @param string $role Rôle dont les permissions doivent être supprimées
     * @return bool|string True en cas de succès, String avec message d'erreur sinon
     */
    public function removeAllPermissions($role) {
        if ($this->dbError) {
            return "Erreur de connexion à la base de données";
        }

        try {
            $query = "DELETE FROM {$this->table} WHERE role = :role";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':role', $role);

            if (!$stmt->execute()) {
                $error = $stmt->errorInfo();
                error_log("Erreur SQL lors de la suppression des permissions: " . json_encode($error));
                return "Erreur SQL: " . ($error[2] ?? "Erreur inconnue");
            }

            return true;
        } catch (PDOException $e) {
            $errorMessage = "Erreur lors de la suppression des permissions: " . $e->getMessage();
            error_log($errorMessage);
            return $errorMessage;
        }
    }

    /**
     * Initialise les permissions par défaut
     *
     * @return bool
     */
    public function initDefaultPermissions() {
        if ($this->dbError) {
            return false;
        }

        // Définir la matrice des permissions par défaut
        $defaultPermissions = [
            ROLE_ADMIN => [
                'entreprise_creer', 'entreprise_modifier', 'entreprise_supprimer',
                'offre_creer', 'offre_modifier', 'offre_supprimer',
                'pilote_creer', 'pilote_modifier', 'pilote_supprimer',
                'etudiant_creer', 'etudiant_modifier', 'etudiant_supprimer'
            ],
            ROLE_PILOTE => [
                'entreprise_creer', 'entreprise_modifier',
                'offre_creer', 'offre_modifier',
                'etudiant_creer', 'etudiant_modifier'
            ],
            ROLE_ETUDIANT => [
                'entreprise_evaluer',
                'wishlist_ajouter', 'wishlist_retirer', 'wishlist_afficher',
                'offre_postuler', 'candidatures_afficher'
            ]
        ];

        try {
            $this->conn->beginTransaction();

            // Vérifier d'abord l'existence de la table
            if (!$this->tableExists()) {
                // La table n'existe pas, on la crée
                $createQuery = "CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    role VARCHAR(50) NOT NULL,
                    permission VARCHAR(100) NOT NULL,
                    created_at DATETIME NOT NULL,
                    UNIQUE KEY (role, permission)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

                $this->conn->exec($createQuery);
                error_log("Table {$this->table} créée avec succès");
            }

            // Vider la table pour une réinitialisation complète
            $this->conn->exec("TRUNCATE TABLE {$this->table}");

            // Ajouter les permissions par défaut
            $addQuery = "INSERT INTO {$this->table} (role, permission, created_at) VALUES (:role, :permission, NOW())";
            $addStmt = $this->conn->prepare($addQuery);

            $successCount = 0;
            $errorCount = 0;

            foreach ($defaultPermissions as $role => $permissions) {
                foreach ($permissions as $permission) {
                    $addStmt->bindParam(':role', $role);
                    $addStmt->bindParam(':permission', $permission);

                    if ($addStmt->execute()) {
                        $successCount++;
                    } else {
                        $errorCount++;
                        $error = $addStmt->errorInfo();
                        error_log("Erreur lors de l'ajout de la permission {$permission} pour {$role}: " . json_encode($error));
                    }
                }
            }

            // Validation de la transaction
            $this->conn->commit();

            error_log("Initialisation des permissions par défaut: {$successCount} ajoutées, {$errorCount} erreurs");

            return $errorCount === 0;
        } catch (PDOException $e) {
            // Annulation de la transaction en cas d'erreur
            $this->conn->rollBack();
            error_log("Erreur critique lors de l'initialisation des permissions par défaut: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Diagnostic technique de l'état de la table permissions
     * Renvoie un tableau détaillé de l'état de la base de données
     *
     * @return array
     */
    public function diagnoseTechnicalStatus() {
        if ($this->dbError) {
            return ["error" => "Connexion à la base de données impossible"];
        }

        try {
            $results = [
                "timestamp" => date('Y-m-d H:i:s'),
                "php_version" => PHP_VERSION,
                "memory_usage" => round(memory_get_usage() / 1024 / 1024, 2) . " MB"
            ];

            // 1. Vérifier l'existence de la table
            $tableQuery = "SHOW TABLES LIKE '{$this->table}'";
            $stmt = $this->conn->prepare($tableQuery);
            $stmt->execute();
            $results["table_exists"] = ($stmt->rowCount() > 0);

            if (!$results["table_exists"]) {
                return $results;
            }

            // 2. Vérifier la structure de la table
            $structureQuery = "DESCRIBE {$this->table}";
            $stmt = $this->conn->prepare($structureQuery);
            $stmt->execute();
            $results["table_structure"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 3. Vérifier le contenu de la table
            $contentQuery = "SELECT role, COUNT(*) as count FROM {$this->table} GROUP BY role";
            $stmt = $this->conn->prepare($contentQuery);
            $stmt->execute();
            $results["permissions_count"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 4. Vérifier les droits sur la table
            $results["can_insert"] = $this->testPermission("INSERT");
            $results["can_update"] = $this->testPermission("UPDATE");
            $results["can_delete"] = $this->testPermission("DELETE");

            // 5. Vérifier les verrous actifs
            $locksQuery = "SHOW OPEN TABLES WHERE `Table` = '{$this->table}'";
            $stmt = $this->conn->prepare($locksQuery);
            $stmt->execute();
            $results["table_locks"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 6. Vérifier les transactions actives
            $results["in_transaction"] = $this->conn->inTransaction();

            // 7. Échantillon des données
            $sampleQuery = "SELECT * FROM {$this->table} LIMIT 10";
            $stmt = $this->conn->prepare($sampleQuery);
            $stmt->execute();
            $results["data_sample"] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $results;
        } catch (PDOException $e) {
            error_log("Diagnostic Permission: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return [
                "critical_error" => $e->getMessage(),
                "trace" => $e->getTraceAsString()
            ];
        }
    }

    /**
     * Teste les droits SQL sur la table
     *
     * @param string $operation Type d'opération à tester (INSERT, UPDATE, DELETE)
     * @return bool
     */
    private function testPermission($operation) {
        try {
            switch ($operation) {
                case "INSERT":
                    $this->conn->beginTransaction();
                    $stmt = $this->conn->prepare("INSERT INTO {$this->table} (role, permission, created_at) VALUES ('_test_', '_test_', NOW())");
                    $result = $stmt->execute();
                    $this->conn->rollBack();
                    return $result;

                case "UPDATE":
                    $this->conn->beginTransaction();
                    $stmt = $this->conn->prepare("UPDATE {$this->table} SET permission = '_test_update_' WHERE 1=0");
                    $result = $stmt->execute();
                    $this->conn->rollBack();
                    return $result;

                case "DELETE":
                    $this->conn->beginTransaction();
                    $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE role = '_non_existant_role_'");
                    $result = $stmt->execute();
                    $this->conn->rollBack();
                    return $result;

                default:
                    return false;
            }
        } catch (PDOException $e) {
            error_log("Test Permission {$operation}: " . $e->getMessage());
            try {
                $this->conn->rollBack();
            } catch (Exception $ex) {
                // Ignorer les erreurs de rollback
            }
            return false;
        }
    }
}