<?php
/**
 * Script de test pour vérifier la connexion à la base de données
 */

// Définir l'environnement pour activer les messages d'erreur détaillés
define('ENVIRONMENT', 'development');

// Définir le chemin racine
define('ROOT_PATH', __DIR__);

// En-tête HTML pour une meilleure présentation
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de connexion à la base de données</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; max-width: 800px; margin: 0 auto; }
        h1, h2 { color: #333; }
        .success { color: green; background-color: #dff0d8; padding: 10px; border-radius: 5px; }
        .error { color: #a94442; background-color: #f2dede; padding: 10px; border-radius: 5px; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .step { border-left: 3px solid #007bff; padding-left: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Test de connexion à la base de données</h1>';

// Étape 1: Vérification du fichier de configuration
echo '<div class="step">
    <h2>Étape 1: Vérification du fichier de configuration</h2>';

if (file_exists(ROOT_PATH . '/config/database.php')) {
    echo '<p class="success">✅ Le fichier de configuration database.php existe.</p>';
} else {
    echo '<p class="error">❌ Le fichier de configuration database.php n\'existe pas!</p>';
    exit;
}

echo '</div>';

// Étape 2: Inclusion du fichier de configuration
echo '<div class="step">
    <h2>Étape 2: Chargement de la classe Database</h2>';

try {
    require_once ROOT_PATH . '/config/database.php';
    echo '<p class="success">✅ La classe Database a été chargée avec succès.</p>';
} catch (Exception $e) {
    echo '<p class="error">❌ Erreur lors du chargement de la classe Database: ' . $e->getMessage() . '</p>';
    exit;
}

echo '</div>';

// Étape 3: Tentative de connexion
echo '<div class="step">
    <h2>Étape 3: Tentative de connexion à la base de données</h2>';

try {
    $database = new Database();
    $conn = $database->getConnection();

    if ($conn) {
        echo '<p class="success">✅ Connexion à la base de données réussie!</p>';

        // Informations sur la connexion
        echo '<h3>Informations sur la connexion:</h3>';
        echo '<pre>';
        echo 'Version du serveur MySQL: ' . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
        echo 'Version du client MySQL: ' . $conn->getAttribute(PDO::ATTR_CLIENT_VERSION) . "\n";
        echo 'Statut de la connexion: ' . ($conn->getAttribute(PDO::ATTR_CONNECTION_STATUS) ?? 'Information non disponible');
        echo '</pre>';

        // Test d'une requête simple
        echo '<h3>Test d\'une requête simple:</h3>';
        try {
            $stmt = $conn->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($tables) > 0) {
                echo '<p class="success">✅ Requête SQL exécutée avec succès.</p>';
                echo '<p>Tables trouvées dans la base de données:</p>';
                echo '<pre>';
                foreach ($tables as $table) {
                    echo "- $table\n";
                }
                echo '</pre>';
            } else {
                echo '<p class="error">⚠️ Aucune table trouvée dans la base de données.</p>';
                echo '<p>Vous devez importer le schéma de la base de données depuis le fichier SQL fourni.</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="error">❌ Erreur lors de l\'exécution de la requête: ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p class="error">❌ Impossible d\'établir une connexion à la base de données.</p>';
    }
} catch (Exception $e) {
    echo '<p class="error">❌ Exception lors de la connexion: ' . $e->getMessage() . '</p>';
}

echo '</div>';

// Conseils de dépannage
echo '<div class="step">
    <h2>Conseils de dépannage:</h2>
    <ol>
        <li>Vérifiez que MySQL est démarré sur votre machine.</li>
        <li>Vérifiez les identifiants dans le fichier config/database.php.</li>
        <li>Assurez-vous que la base de données "stages_db" existe:
            <pre>CREATE DATABASE IF NOT EXISTS stages_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</pre>
        </li>
        <li>Si la base existe mais est vide, importez le schéma depuis le fichier stage_db.sql.</li>
    </ol>
</div>';

// Fin du HTML
echo '</body></html>';