<?php
/**
 * Configuration de la connexion à la base de données
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'stages_db';
    private $username = 'root';
    private $password = '';
    private $conn = null;

    /**
     * Connexion à la base de données
     * @return PDO|null
     */
    public function getConnection() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES utf8");

            // Log de succès de connexion en mode développement
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                error_log("Connexion à la base de données établie avec succès.");
            }

        } catch(PDOException $e) {
            // Message d'erreur détaillé
            $errorMessage = "Erreur de connexion à la base de données : " . $e->getMessage();

            // Log l'erreur
            error_log($errorMessage);

            // Afficher l'erreur si en mode développement
            if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
                echo "<div style='border:1px solid #dc3545; padding:10px; margin:10px; background-color:#f8d7da; color:#721c24;'>";
                echo "<h3>Erreur de connexion à la base de données</h3>";
                echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
                echo "<p><strong>Code:</strong> " . $e->getCode() . "</p>";
                echo "<p>Vérifiez que:</p>";
                echo "<ul>";
                echo "<li>Le serveur MySQL est démarré</li>";
                echo "<li>Les identifiants dans config/database.php sont corrects</li>";
                echo "<li>La base de données '{$this->db_name}' existe</li>";
                echo "</ul>";
                echo "</div>";
            } else {
                // En production, ne pas exposer les détails
                error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            }

            // Retourne null en cas d'échec
            $this->conn = null;
        }

        return $this->conn;
    }
}