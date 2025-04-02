<?php
// Définition du chemin racine
define('ROOT_PATH', __DIR__);

// Inclusion des dépendances
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

// En-tête HTML
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vérification de la Table Candidatures</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Vérification de la Structure de la Table Candidatures</h1>';

try {
    // Connexion à la base de données
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        echo "<p class='error'>Échec de connexion à la base de données.</p>";
        exit;
    }

    echo "<p class='success'>Connexion à la base de données réussie.</p>";

    // 1. Vérification de l'existence de la table
    $query = "SHOW TABLES LIKE 'candidatures'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        echo "<p class='error'>La table 'candidatures' n'existe pas!</p>";

        // Proposition de création
        echo "<h2>SQL pour créer la table :</h2>";
        echo "<pre>
CREATE TABLE candidatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    offre_id INT NOT NULL,
    etudiant_id INT NOT NULL,
    cv VARCHAR(255) NOT NULL,
    lettre_motivation TEXT NOT NULL,
    date_candidature DATETIME NOT NULL,
    FOREIGN KEY (offre_id) REFERENCES offres(id) ON DELETE CASCADE,
    FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE
) ENGINE=InnoDB;
        </pre>";

        echo "<p>Pour créer cette table, copiez le code SQL ci-dessus et exécutez-le dans votre gestionnaire de base de données.</p>";
    } else {
        echo "<p class='success'>La table 'candidatures' existe.</p>";

        // 2. Vérification de la structure
        $query = "DESCRIBE candidatures";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Structure de la table :</h2>";
        echo "<table>";
        echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";

        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "<td>{$column['Extra']}</td>";
            echo "</tr>";
        }

        echo "</table>";

        // 3. Vérification des contraintes de clé étrangère
        $query = "SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                  WHERE TABLE_NAME = 'candidatures' 
                  AND REFERENCED_TABLE_NAME IS NOT NULL";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h2>Contraintes de clé étrangère :</h2>";

        if (empty($constraints)) {
            echo "<p class='error'>Aucune contrainte de clé étrangère trouvée. Cela peut causer des problèmes d'insertion.</p>";
            echo "<h3>SQL pour ajouter les contraintes :</h3>";
            echo "<pre>
ALTER TABLE candidatures
ADD CONSTRAINT fk_candidatures_offre FOREIGN KEY (offre_id) REFERENCES offres(id) ON DELETE CASCADE,
ADD CONSTRAINT fk_candidatures_etudiant FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE;
            </pre>";
        } else {
            echo "<table>";
            echo "<tr><th>Nom de la contrainte</th><th>Colonne</th><th>Table référencée</th><th>Colonne référencée</th></tr>";

            foreach ($constraints as $constraint) {
                echo "<tr>";
                echo "<td>{$constraint['CONSTRAINT_NAME']}</td>";
                echo "<td>{$constraint['COLUMN_NAME']}</td>";
                echo "<td>{$constraint['REFERENCED_TABLE_NAME']}</td>";
                echo "<td>{$constraint['REFERENCED_COLUMN_NAME']}</td>";
                echo "</tr>";
            }

            echo "</table>";
        }

        // 4. Test des références
        echo "<h2>Vérification des références :</h2>";

        // Vérifier les étudiants
        $query = "SELECT COUNT(*) as count FROM etudiants";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $etudiantsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        echo "<p>Nombre d'étudiants dans la base : <strong>{$etudiantsCount}</strong></p>";

        // Vérifier les offres
        $query = "SELECT COUNT(*) as count FROM offres";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $offresCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        echo "<p>Nombre d'offres dans la base : <strong>{$offresCount}</strong></p>";

        if ($etudiantsCount == 0 || $offresCount == 0) {
            echo "<p class='error'>Attention : Au moins une des tables référencées est vide. Les insertions peuvent échouer.</p>";
        } else {
            echo "<p class='success'>Les tables référencées contiennent des données.</p>";
        }

        // 5. Vérifier le répertoire d'upload
        $uploadDir = defined('UPLOAD_DIR') ? UPLOAD_DIR : ROOT_PATH . '/public/uploads/';
        echo "<h2>Vérification du répertoire d'upload :</h2>";

        if (!file_exists($uploadDir)) {
            echo "<p class='error'>Le répertoire d'upload n'existe pas : {$uploadDir}</p>";
            echo "<p>Création du répertoire...</p>";

            if (mkdir($uploadDir, 0755, true)) {
                echo "<p class='success'>Répertoire créé avec succès.</p>";
            } else {
                echo "<p class='error'>Échec de la création du répertoire.</p>";
            }
        } else {
            echo "<p class='success'>Le répertoire d'upload existe : {$uploadDir}</p>";

            if (is_writable($uploadDir)) {
                echo "<p class='success'>Le répertoire est accessible en écriture.</p>";
            } else {
                echo "<p class='error'>Le répertoire n'est PAS accessible en écriture!</p>";
            }
        }
    }

} catch (PDOException $e) {
    echo "<p class='error'>Erreur PDO : " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>Erreur générale : " . $e->getMessage() . "</p>";
}

// Pied de page
echo '</body></html>';