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
     * @return PDO
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
        } catch(PDOException $e) {
            echo "Erreur de connexion à la base de données : " . $e->getMessage();
        }

        return $this->conn;
    }
}