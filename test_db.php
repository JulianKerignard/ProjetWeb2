<?php
// Chemins absolus pour éviter les problèmes d'inclusion
define('ROOT_PATH', __DIR__);

// Démarrer la session
session_start();

// Inclure les fichiers nécessaires
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

// En-tête HTML pour un affichage propre
echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic de la base de données</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
        h1 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Diagnostic de la Base de Données - Projet Web2</h1>";

try {
    // Connexion à la base de données
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        echo "<p class='error'>Échec de la connexion à la base de données.</p>";
        exit;
    }

    echo "<p class='success'>Connexion à la base de données réussie.</p>";

    // Vérification de la structure de la table candidatures
    echo "<h2>Structure de la table candidatures</h2>";
    $query = "DESCRIBE candidatures";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<pre>";
    print_r($columns);
    echo "</pre>";

    // Vérification des contraintes
    echo "<h2>Contraintes de la table candidatures</h2>";
    try {
        $query = "SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                  WHERE TABLE_NAME = 'candidatures' 
                  AND REFERENCED_TABLE_NAME IS NOT NULL";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($constraints)) {
            echo "<p>Aucune contrainte de clé étrangère trouvée.</p>";
        } else {
            echo "<pre>";
            print_r($constraints);
            echo "</pre>";
        }
    } catch (PDOException $e) {
        echo "<p class='error'>Erreur lors de la récupération des contraintes: " . $e->getMessage() . "</p>";
    }

    // Test d'insertion d'une candidature fictive
    echo "<h2>Test d'insertion de candidature (simulation)</h2>";

    // Récupération d'un ID d'offre valide
    $query = "SELECT id FROM offres LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $offre = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupération d'un ID d'étudiant valide
    $query = "SELECT id FROM etudiants LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$offre || !$etudiant) {
        echo "<p class='error'>Impossible de trouver des IDs valides pour le test.</p>";
    } else {
        echo "<p>Valeurs qui seraient utilisées pour l'insertion :</p>";
        echo "<ul>";
        echo "<li>offre_id: " . $offre['id'] . "</li>";
        echo "<li>etudiant_id: " . $etudiant['id'] . "</li>";
        echo "<li>cv: test_cv.pdf</li>";
        echo "<li>lettre_motivation: Test de lettre de motivation</li>";
        echo "<li>date_candidature: " . date('Y-m-d H:i:s') . "</li>";
        echo "</ul>";
    }

} catch (PDOException $e) {
    echo "<p class='error'>Erreur PDO: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p class='error'>Erreur générale: " . $e->getMessage() . "</p>";
}

echo "</body></html>";