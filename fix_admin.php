<?php
/**
 * Script de diagnostic et réparation du compte administrateur
 *
 * Ce script autonome permet de vérifier et de réparer le compte administrateur
 * lorsque l'accès à l'interface d'administration est impossible.
 *
 * @version 1.0
 */

// Définition du chemin racine pour les inclusions
define('ROOT_PATH', __DIR__);

// Inclusion des fichiers de configuration
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

// Activation du niveau d'erreur maximal pour le diagnostic
error_reporting(E_ALL);
ini_set('display_errors', 1);

// En-tête HTML pour présentation
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Diagnostic Admin</title>';
echo '<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    line-height: 1.6;
    color: #333;
}
.container {
    max-width: 800px;
    margin: 0 auto;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
h1 {
    color: #2563eb;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 10px;
}
h2 {
    margin-top: 30px;
    color: #1d4ed8;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 5px;
}
.success {
    color: #166534;
    background-color: #dcfce7;
    padding: 12px 16px;
    border-radius: 4px;
    margin: 15px 0;
    border-left: 4px solid #16a34a;
}
.error {
    color: #991b1b;
    background-color: #fee2e2;
    padding: 12px 16px;
    border-radius: 4px;
    margin: 15px 0;
    border-left: 4px solid #dc2626;
}
.warning {
    color: #92400e;
    background-color: #fff7ed;
    padding: 12px 16px;
    border-radius: 4px;
    margin: 15px 0;
    border-left: 4px solid #f97316;
}
pre {
    background: #f1f5f9;
    padding: 15px;
    border-radius: 4px;
    overflow-x: auto;
    border: 1px solid #e2e8f0;
}
code {
    background: #f1f5f9;
    padding: 2px 4px;
    border-radius: 2px;
    font-family: monospace;
}
.btn {
    display: inline-block;
    background: #2563eb;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    font-size: 16px;
    transition: background 0.3s;
}
.btn:hover {
    background: #1d4ed8;
}
table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}
th, td {
    border: 1px solid #e5e7eb;
    padding: 12px;
    text-align: left;
}
th {
    background: #f1f5f9;
    font-weight: bold;
}
</style>';
echo '</head><body><div class="container">';
echo '<h1>Diagnostic du compte administrateur</h1>';

try {
    // Établir la connexion à la base de données
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception("Erreur de connexion à la base de données.");
    }

    echo '<div class="success">✅ Connexion à la base de données établie.</div>';

    // Vérifier l'existence de la table utilisateurs
    $query = "SHOW TABLES LIKE 'utilisateurs'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        throw new Exception("La table 'utilisateurs' n'existe pas dans la base de données.");
    }

    echo '<div class="success">✅ Table utilisateurs trouvée.</div>';

    // Vérifier l'existence du compte admin
    $query = "SELECT id, email, password, role FROM utilisateurs WHERE email = 'admin@web4all.fr'";
    $stmt = $conn->prepare($query);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        echo '<div class="error">❌ Compte administrateur non trouvé.</div>';
        echo '<h2>Création du compte administrateur</h2>';

        // Hash pour le mot de passe "admin123"
        $passwordHash = '$2y$10$3OQWJkIKv2AE2dGBTWJy7.MwQ9hGUJbD3pdL7dFBVXHPSGG8mhUKy';

        $query = "INSERT INTO utilisateurs (email, password, role, created_at) 
                  VALUES ('admin@web4all.fr', :password, 'admin', NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':password', $passwordHash);

        if ($stmt->execute()) {
            echo '<div class="success">✅ Compte administrateur créé avec succès.</div>';
        } else {
            throw new Exception("Échec de la création du compte administrateur.");
        }
    } else {
        echo '<div class="success">✅ Compte administrateur trouvé.</div>';
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier si le hash est correct
        $correctHash = '$2y$10$3OQWJkIKv2AE2dGBTWJy7.MwQ9hGUJbD3pdL7dFBVXHPSGG8mhUKy';
        if ($admin['password'] !== $correctHash) {
            echo '<div class="warning">⚠️ Le hash du mot de passe est incorrect.</div>';
            echo '<h2>Réinitialisation du mot de passe</h2>';

            $query = "UPDATE utilisateurs SET password = :password WHERE email = 'admin@web4all.fr'";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':password', $correctHash);

            if ($stmt->execute()) {
                echo '<div class="success">✅ Mot de passe administrateur réinitialisé avec succès.</div>';
            } else {
                throw new Exception("Échec de la réinitialisation du mot de passe.");
            }
        } else {
            echo '<div class="success">✅ Le hash du mot de passe est correct.</div>';
        }
    }

    echo '<h2>Test de vérification du mot de passe</h2>';
    $testResult = password_verify("admin123", '$2y$10$3OQWJkIKv2AE2dGBTWJy7.MwQ9hGUJbD3pdL7dFBVXHPSGG8mhUKy');
    echo '<pre>password_verify("admin123", "' . htmlspecialchars('$2y$10$3OQWJkIKv2AE2dGBTWJy7.MwQ9hGUJbD3pdL7dFBVXHPSGG8mhUKy') . '") => '
        . ($testResult ? 'true ✓' : 'false ✗') . '</pre>';

    if (!$testResult) {
        echo '<div class="error">❌ La fonction password_verify() ne fonctionne pas correctement avec le hash fourni. Vérifiez la configuration PHP.</div>';
    }

    echo '<h2>Récapitulatif</h2>';
    echo '<div class="success">';
    echo '<p><strong>Informations de connexion:</strong></p>';
    echo '<table>';
    echo '<tr><th>Email</th><td>admin@web4all.fr</td></tr>';
    echo '<tr><th>Mot de passe</th><td>admin123</td></tr>';
    echo '<tr><th>Rôle</th><td>Administrateur</td></tr>';
    echo '</table>';
    echo '</div>';

    // Informations sur la configuration PHP
    echo '<h2>Informations sur l\'environnement PHP</h2>';
    echo '<table>';
    echo '<tr><th>Version PHP</th><td>' . phpversion() . '</td></tr>';
    echo '<tr><th>Extensions PDO</th><td>' . (extension_loaded('pdo') ? 'Activée ✓' : 'Désactivée ✗') . '</td></tr>';
    echo '<tr><th>PDO MySQL</th><td>' . (extension_loaded('pdo_mysql') ? 'Activée ✓' : 'Désactivée ✗') . '</td></tr>';
    echo '<tr><th>Fonction password_hash()</th><td>' . (function_exists('password_hash') ? 'Disponible ✓' : 'Non disponible ✗') . '</td></tr>';
    echo '<tr><th>Fonction password_verify()</th><td>' . (function_exists('password_verify') ? 'Disponible ✓' : 'Non disponible ✗') . '</td></tr>';
    echo '</table>';

} catch (Exception $e) {
    echo '<div class="error">❌ ' . $e->getMessage() . '</div>';
    echo '<p>Veuillez vérifier votre configuration de base de données et exécuter le script SQL pour initialiser la structure de la base de données.</p>';
} finally {
    echo '<p style="text-align: center; margin-top: 30px;">';
    echo '<a href="index.php" class="btn">Retour à l\'application</a>';
    echo '</p>';
    echo '</div></body></html>';
}