<?php
// Fichier: diagnostic_wishlist.php
define('ROOT_PATH', __DIR__);
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/database.php';

// Démarrer la session
session_start();

// En-tête HTML pour un affichage formaté
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Diagnostic Wishlist</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
        h1 { color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .section { margin-bottom: 20px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .warning { color: orange; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .success { color: green; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { text-align: left; padding: 8px; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
<h1>Diagnostic des Wishlists</h1>

<div class="section">
    <h2>1. Informations sur la session</h2>
    <?php
    echo "<p>Session active: " . (session_status() === PHP_SESSION_ACTIVE ? "Oui" : "Non") . "</p>";
    echo "<p>Session ID: " . session_id() . "</p>";

    echo "<h3>Variables de session importantes:</h3>";
    echo "<ul>";
    echo "<li>user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Non définie') . "</li>";
    echo "<li>role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'Non défini') . "</li>";
    echo "<li>etudiant_id: " . (isset($_SESSION['etudiant_id']) ? $_SESSION['etudiant_id'] : 'Non définie') . "</li>";
    echo "</ul>";

    // Vérification du problème potentiel
    if (!isset($_SESSION['etudiant_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'etudiant') {
        echo "<p class='warning'>ALERTE: Vous êtes connecté en tant qu'étudiant mais l'ID étudiant n'est pas défini dans la session!</p>";
    }
    ?>
</div>

<div class="section">
    <h2>2. Vérification en base de données</h2>
    <?php
    try {
        // Connexion à la BDD
        $db = new Database();
        $conn = $db->getConnection();

        echo "<p class='success'>Connexion à la base de données réussie</p>";

        // Vérifier l'étudiant associé à l'utilisateur
        if (isset($_SESSION['user_id'])) {
            $query = "SELECT id, nom, prenom FROM etudiants WHERE user_id = :user_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($etudiant) {
                echo "<p class='success'>Profil étudiant trouvé en base de données pour user_id=" . $_SESSION['user_id'] . ":</p>";
                echo "<ul>";
                echo "<li>ID étudiant: " . $etudiant['id'] . "</li>";
                echo "<li>Nom: " . $etudiant['nom'] . "</li>";
                echo "<li>Prénom: " . $etudiant['prenom'] . "</li>";
                echo "</ul>";

                // Problème potentiel détecté
                if (isset($_SESSION['etudiant_id']) && $_SESSION['etudiant_id'] != $etudiant['id']) {
                    echo "<p class='error'>PROBLÈME CRITIQUE: ID étudiant dans la session (" . $_SESSION['etudiant_id'] .
                        ") différent de celui en BDD (" . $etudiant['id'] . ")!</p>";
                }
            } else {
                echo "<p class='error'>Aucun profil étudiant trouvé en base de données pour user_id=" . $_SESSION['user_id'] . "</p>";
            }
        } else {
            echo "<p class='warning'>Impossible de vérifier le profil étudiant: user_id non défini dans la session</p>";
        }

        // Vérifier les entrées dans la wishlist
        if (isset($_SESSION['etudiant_id'])) {
            $query = "SELECT w.*, o.titre, e.nom as entreprise
                         FROM wishlists w
                         JOIN offres o ON w.offre_id = o.id
                         JOIN entreprises e ON o.entreprise_id = e.id
                         WHERE w.etudiant_id = :etudiant_id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':etudiant_id', $_SESSION['etudiant_id'], PDO::PARAM_INT);
            $stmt->execute();
            $wishlists = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo "<h3>Wishlists pour etudiant_id=" . $_SESSION['etudiant_id'] . ":</h3>";

            if (count($wishlists) > 0) {
                echo "<table>";
                echo "<tr><th>Offre ID</th><th>Titre</th><th>Entreprise</th><th>Date d'ajout</th></tr>";
                foreach ($wishlists as $item) {
                    echo "<tr>";
                    echo "<td>" . $item['offre_id'] . "</td>";
                    echo "<td>" . $item['titre'] . "</td>";
                    echo "<td>" . $item['entreprise'] . "</td>";
                    echo "<td>" . $item['date_ajout'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>Aucune offre trouvée dans la wishlist.</p>";
            }

            // Vérifier s'il y a des entrées pour l'ID étudiant de la BDD
            if (isset($etudiant) && $etudiant && $_SESSION['etudiant_id'] != $etudiant['id']) {
                $query = "SELECT COUNT(*) as count FROM wishlists WHERE etudiant_id = :etudiant_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':etudiant_id', $etudiant['id'], PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($result['count'] > 0) {
                    echo "<p class='error'>PROBLÈME DÉTECTÉ: " . $result['count'] . " offres trouvées dans la wishlist pour l'ID étudiant de la BDD (" . $etudiant['id'] . "), mais elles ne sont pas visibles car la session utilise l'ID " . $_SESSION['etudiant_id'] . "</p>";
                }
            }
        } else {
            echo "<p class='warning'>Impossible de vérifier les wishlists: etudiant_id non défini dans la session</p>";
        }

        // Vérifier la structure de la table wishlists
        $query = "DESCRIBE wishlists";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>Structure de la table wishlists:</h3>";
        echo "<table>";
        echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            foreach ($column as $key => $value) {
                echo "<td>" . ($value === null ? 'NULL' : $value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";

        // Vérifier les contraintes de clé étrangère
        $query = "SELECT * FROM information_schema.KEY_COLUMN_USAGE 
                     WHERE TABLE_NAME = 'wishlists' 
                     AND REFERENCED_TABLE_NAME IS NOT NULL";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<h3>Contraintes de clé étrangère:</h3>";
        if (count($constraints) > 0) {
            echo "<table>";
            echo "<tr><th>Nom de la contrainte</th><th>Colonne</th><th>Table référencée</th><th>Colonne référencée</th></tr>";
            foreach ($constraints as $constraint) {
                echo "<tr>";
                echo "<td>" . $constraint['CONSTRAINT_NAME'] . "</td>";
                echo "<td>" . $constraint['COLUMN_NAME'] . "</td>";
                echo "<td>" . $constraint['REFERENCED_TABLE_NAME'] . "</td>";
                echo "<td>" . $constraint['REFERENCED_COLUMN_NAME'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warning'>Aucune contrainte de clé étrangère trouvée. Cela peut causer des problèmes d'intégrité.</p>";
        }

    } catch (PDOException $e) {
        echo "<p class='error'>Erreur de base de données: " . $e->getMessage() . "</p>";
    } catch (Exception $e) {
        echo "<p class='error'>Erreur: " . $e->getMessage() . "</p>";
    }
    ?>
</div>

<div class="section">
    <h2>3. Solutions recommandées</h2>
    <?php
    // Générer un lien de réparation
    $repairUrl = "index.php?page=candidatures&action=repairWishlist";

    echo "<h3>Actions possibles:</h3>";
    echo "<ol>";
    echo "<li><a href='$repairUrl' class='button'>Réparer la wishlist</a> - Cette action va purger l'ID étudiant de la session et forcer sa récupération depuis la base de données</li>";
    echo "<li>Vérifier que la table wishlists contient des contraintes de clé étrangère correctes</li>";
    echo "<li>Si les problèmes persistent, essayez de vous déconnecter puis de vous reconnecter</li>";
    echo "</ol>";

    // Générer le code SQL de réparation
    if (isset($etudiant) && $etudiant && isset($_SESSION['etudiant_id']) && $_SESSION['etudiant_id'] != $etudiant['id']) {
        $wrongId = $_SESSION['etudiant_id'];
        $correctId = $etudiant['id'];

        echo "<h3>Code SQL pour réparer les données:</h3>";
        echo "<pre>";
        echo "-- VÉRIFICATION PRÉALABLE\n";
        echo "SELECT * FROM wishlists WHERE etudiant_id = $wrongId;\n\n";
        echo "-- MIGRATION DES DONNÉES\n";
        echo "UPDATE wishlists SET etudiant_id = $correctId WHERE etudiant_id = $wrongId;\n\n";
        echo "-- VÉRIFICATION APRÈS CORRECTION\n";
        echo "SELECT * FROM wishlists WHERE etudiant_id = $correctId;\n";
        echo "</pre>";

        echo "<p class='warning'>ATTENTION: Ne pas exécuter ce code SQL sans sauvegarde préalable des données!</p>";
    }
    ?>
</div>
</body>
</html>