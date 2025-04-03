<?php
/**
 * Modèle pour la gestion des permissions
 */
class Permission {
    private $conn;
    private $table = 'role_permissions';
    private $dbError = false;

    /**
     * Constructeur - Initialise la connexion à la BDD
     */
    public function __construct() {
        // Vérifier si ROOT_PATH est défini
        if (!defined('ROOT_PATH')) {
            define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));
        }

        require_once ROOT_PATH . '/config/database.php';
        try {
            $database = new Database();
            $this->conn = $database->getConnection();

            if ($this->conn === null) {
                $this->dbError = true;
                error_log("Mode dégradé activé: Impossible d'établir la connexion à la base de données dans Permission.php");
            }
        } catch (Exception $e) {
            $this->dbError = true;
            error_log("Exception dans Permission::__construct(): " . $e->getMessage());
        }
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
     * @return bool
     */
    public function addPermission($role, $permission) {
        if ($this->dbError) {
            return false;
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

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout de la permission: " . $e->getMessage());
            return false;
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

            // Vider la table pour une réinitialisation complète
            $this->conn->exec("TRUNCATE TABLE {$this->table}");

            // Ajouter les permissions par défaut
            foreach ($defaultPermissions as $role => $permissions) {
                foreach ($permissions as $permission) {
                    $this->addPermission($role, $permission);
                }
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Erreur lors de l'initialisation des permissions par défaut: " . $e->getMessage());
            return false;
        }
    }
}