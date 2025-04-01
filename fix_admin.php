<?php
// admin_fix.php - À placer à la racine du projet

define('ROOT_PATH', __DIR__);
require_once 'config/config.php';
require_once 'config/database.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Réparation système d\'authentification</title>
    <style>
        body{font-family:system-ui,-apple-system,sans-serif;line-height:1.6;margin:0;padding:20px;color:#333;background:#f7f9fc}
        .container{max-width:900px;margin:0 auto;background:#fff;padding:25px;border-radius:8px;box-shadow:0 2px 15px rgba(0,0,0,0.1)}
        h1,h2{color:#2563eb;margin-top:0}
        h1{border-bottom:2px solid #e5e7eb;padding-bottom:15px}
        h2{margin-top:30px;font-size:1.3rem}
        pre{background:#1e293b;color:#e2e8f0;padding:15px;border-radius:6px;overflow-x:auto;font-size:14px;font-family:monospace}
        code{background:#f1f5f9;color:#1e293b;padding:3px 6px;border-radius:4px;font-family:monospace;font-size:0.9em}
        .success{background:#dcfce7;color:#166534;padding:15px;border-radius:6px;margin:15px 0;border-left:5px solid #16a34a}
        .error{background:#fee2e2;color:#991b1b;padding:15px;border-radius:6px;margin:15px 0;border-left:5px solid #dc2626}
        .info{background:#dbeafe;color:#1e40af;padding:15px;border-radius:6px;margin:15px 0;border-left:5px solid #3b82f6}
        .warn{background:#fff7ed;color:#9a3412;padding:15px;border-radius:6px;margin:15px 0;border-left:5px solid #ea580c}
        table{width:100%;border-collapse:collapse;margin:15px 0}
        th,td{text-align:left;padding:12px;border-bottom:1px solid #e5e7eb}
        th{background:#f8fafc}
        .btn{display:inline-block;background:#2563eb;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;transition:background 0.3s}
        .btn:hover{background:#1d4ed8}
        .btn-danger{background:#dc2626}
        .btn-danger:hover{background:#b91c1c}
        .btn-success{background:#16a34a}
        .btn-success:hover{background:#15803d}
    </style>
</head>
<body>
<div class="container">
<h1>Diagnostic et correction du système d\'authentification</h1>';

try {
    // Établir la connexion à la base de données
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception("Erreur critique: Impossible d'établir la connexion à la base de données.");
    }

    echo '<div class="success">✅ Connexion à la base de données établie avec succès.</div>';

    // Phase 1: Vérification de l'existence de la table utilisateurs
    $tableSql = "SHOW TABLES LIKE 'utilisateurs'";
    $tableResult = $conn->query($tableSql);

    if ($tableResult->rowCount() === 0) {
        echo '<div class="error">❌ La table \'utilisateurs\' n\'existe pas!</div>';

        // Action corrective: Création de la table utilisateurs
        $createTableSql = "
        CREATE TABLE IF NOT EXISTS utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'pilote', 'etudiant') NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $conn->exec($createTableSql);
        echo '<div class="success">✅ Table \'utilisateurs\' créée avec succès.</div>';
    } else {
        echo '<div class="success">✅ Table \'utilisateurs\' trouvée dans la base de données.</div>';
    }

    // Phase 2: Vérification du compte administrateur
    $checkAdminSql = "SELECT id, email, password, role FROM utilisateurs WHERE email = 'admin@web4all.fr'";
    $stmt = $conn->prepare($checkAdminSql);
    $stmt->execute();

    $action = isset($_GET['action']) ? $_GET['action'] : null;

    // Traitement de l'action de réparation complète
    if ($action === 'repair') {
        // Générer un nouveau hash pour admin123
        $password = 'admin123';
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        // Vérifier que le hash généré est valide
        if (!password_verify($password, $newHash)) {
            throw new Exception("Erreur critique: Le hash généré ne peut pas être validé!");
        }

        // Vérifier si l'admin existe
        if ($stmt->rowCount() > 0) {
            // Mise à jour du hash existant
            $updateSql = "UPDATE utilisateurs SET password = :password WHERE email = 'admin@web4all.fr'";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindParam(':password', $newHash);
            $updateStmt->execute();

            echo '<div class="success">✅ Le mot de passe administrateur a été réinitialisé avec succès.</div>';
        } else {
            // Création du compte admin
            $insertSql = "INSERT INTO utilisateurs (email, password, role, created_at) 
                         VALUES ('admin@web4all.fr', :password, 'admin', NOW())";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bindParam(':password', $newHash);
            $insertStmt->execute();

            echo '<div class="success">✅ Compte administrateur créé avec succès.</div>';
        }

        echo '<div class="info">
            <h3>Informations de connexion:</h3>
            <ul>
                <li><strong>Email:</strong> admin@web4all.fr</li>
                <li><strong>Mot de passe:</strong> admin123</li>
            </ul>
            <p>Vous pouvez maintenant vous connecter en utilisant ces identifiants.</p>
        </div>';
    }
    // Traitement de l'action de suppression de table
    else if ($action === 'reset_table') {
        // Suppression de la table utilisateurs
        $dropSql = "DROP TABLE IF EXISTS utilisateurs";
        $conn->exec($dropSql);

        echo '<div class="warn">⚠️ La table \'utilisateurs\' a été supprimée.</div>';

        // Recréation de la table
        $createTableSql = "
        CREATE TABLE utilisateurs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'pilote', 'etudiant') NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $conn->exec($createTableSql);
        echo '<div class="success">✅ Table \'utilisateurs\' recréée avec succès.</div>';

        // Création du compte admin
        $password = 'admin123';
        $newHash = password_hash($password, PASSWORD_DEFAULT);

        $insertSql = "INSERT INTO utilisateurs (email, password, role, created_at) 
                     VALUES ('admin@web4all.fr', :password, 'admin', NOW())";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bindParam(':password', $newHash);
        $insertStmt->execute();

        echo '<div class="success">✅ Compte administrateur réinitialisé avec succès.</div>';

        echo '<div class="info">
            <h3>Informations de connexion:</h3>
            <ul>
                <li><strong>Email:</strong> admin@web4all.fr</li>
                <li><strong>Mot de passe:</strong> admin123</li>
            </ul>
            <p>Vous pouvez maintenant vous connecter en utilisant ces identifiants.</p>
        </div>';
    }
    // Affichage du diagnostic standard
    else {
        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            echo '<div class="success">✅ Compte administrateur trouvé (ID: ' . $admin['id'] . ').</div>';

            // Test du hash actuel
            $currentPassword = 'admin123';
            $isValidHash = password_verify($currentPassword, $admin['password']);

            echo '<h2>Diagnostic du hash actuel</h2>';
            echo '<pre>
// Test du hash actuel
$currentPassword = \'admin123\';
$storedHash = \'' . htmlspecialchars($admin['password']) . '\';
$isValid = password_verify($currentPassword, $storedHash);

// Résultat: ' . ($isValidHash ? 'true ✓' : 'false ✗') . '
</pre>';

            if (!$isValidHash) {
                echo '<div class="error">❌ Le hash actuel ne correspond pas au mot de passe attendu.</div>';

                // Test de génération d'un nouveau hash
                $newHash = password_hash($currentPassword, PASSWORD_DEFAULT);
                $testNewHash = password_verify($currentPassword, $newHash);

                echo '<pre>
// Test de génération d\'un nouveau hash
$newHash = password_hash($currentPassword, PASSWORD_DEFAULT);
$testNewHash = password_verify($currentPassword, $newHash);

// Résultat: ' . ($testNewHash ? 'true ✓' : 'false ✗') . '
</pre>';

                if ($testNewHash) {
                    echo '<div class="info">✅ Un nouveau hash valide a été généré avec succès.</div>';
                } else {
                    echo '<div class="error">❌ Impossible de générer un hash valide. Problème critique de configuration PHP.</div>';
                }
            } else {
                echo '<div class="success">✅ Le hash actuel est valide pour le mot de passe attendu.</div>';
            }
        } else {
            echo '<div class="error">❌ Aucun compte administrateur trouvé!</div>';
        }

        echo '<h2>Actions disponibles</h2>';
        echo '<p>Choisissez une des actions suivantes pour résoudre les problèmes détectés:</p>';

        echo '<a href="?action=repair" class="btn btn-success" style="margin-right: 10px;">Réparer le compte administrateur</a>';
        echo '<a href="?action=reset_table" class="btn btn-danger" onclick="return confirm(\'Attention: Cette action va supprimer et recréer la table utilisateurs. Toutes les données utilisateurs seront perdues. Continuer?\')">Réinitialisation complète (Dangereux)</a>';
    }

    echo '<div style="margin-top: 30px; text-align: center">
        <a href="index.php" class="btn">Retour à l\'application</a>
    </div>';

} catch (Exception $e) {
    echo '<div class="error">
        <h3>Erreur critique:</h3>
        <p>' . $e->getMessage() . '</p>
        <pre>' . $e->getTraceAsString() . '</pre>
    </div>';
}

echo '</div></body></html>';