<?php
/**
 * Modèle pour la gestion de l'authentification
 */
class Auth {
    private $conn;

    /**
     * Constructeur
     */
    public function __construct() {
        // Connexion à la base de données
        require_once 'config/database.php';
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Authentification d'un utilisateur
     * @param string $email
     * @param string $password
     * @return array|bool
     */
    public function login($email, $password) {
        try {
            // Requête pour récupérer l'utilisateur par email
            $query = "SELECT u.id, u.email, u.password, u.role, 
                      COALESCE(e.nom, p.nom) as nom, 
                      COALESCE(e.prenom, p.prenom) as prenom
                      FROM utilisateurs u
                      LEFT JOIN etudiants e ON u.id = e.user_id
                      LEFT JOIN pilotes p ON u.id = p.user_id
                      WHERE u.email = :email";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Vérification du mot de passe
                if (password_verify($password, $user['password'])) {
                    // Suppression du mot de passe avant de retourner l'utilisateur
                    unset($user['password']);
                    return $user;
                }
            }

            return false;
        } catch (PDOException $e) {
            // Gérer l'erreur
            error_log("Erreur de connexion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Création d'un compte utilisateur
     * @param string $email
     * @param string $password
     * @param string $role
     * @return int|bool
     */
    public function register($email, $password, $role) {
        try {
            // Vérifier si l'email existe déjà
            $query = "SELECT id FROM utilisateurs WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return false; // L'email existe déjà
            }

            // Hashage du mot de passe
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insertion de l'utilisateur
            $query = "INSERT INTO utilisateurs (email, password, role, created_at) 
                      VALUES (:email, :password, :role, NOW())";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $passwordHash);
            $stmt->bindParam(':role', $role);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }

            return false;
        } catch (PDOException $e) {
            // Gérer l'erreur
            error_log("Erreur d'inscription: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer un utilisateur par son ID
     * @param int $id
     * @return array|bool
     */
    public function getUserById($id) {
        try {
            $query = "SELECT u.id, u.email, u.role, 
                      COALESCE(e.nom, p.nom) as nom, 
                      COALESCE(e.prenom, p.prenom) as prenom
                      FROM utilisateurs u
                      LEFT JOIN etudiants e ON u.id = e.user_id
                      LEFT JOIN pilotes p ON u.id = p.user_id
                      WHERE u.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }

            return false;
        } catch (PDOException $e) {
            // Gérer l'erreur
            error_log("Erreur de récupération utilisateur: " . $e->getMessage());
            return false;
        }
    }
}