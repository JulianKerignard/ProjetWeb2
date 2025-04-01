<?php
/**
 * Script d'analyse approfondie de la structure de la base de données
 * Effectue des vérifications d'intégrité sur le schéma et les relations
 *
 * @version 1.0
 */

// Définition du chemin racine pour les inclusions
define('ROOT_PATH', __DIR__);

// Inclusion des fichiers de configuration
require_once 'config/config.php';
require_once 'config/database.php';

// Activation du niveau d'erreur maximal pour le diagnostic
error_reporting(E_ALL);
ini_set('display_errors', 1);

// En-tête HTML
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Analyse structurelle DB</title>';
echo '<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    line-height: 1.6;
    color: #333;
}
.container {
    max-width: 900px;
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
    font-family: monospace;
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
.status-ok {
    background-color: #dcfce7;
    color: #166534;
    padding: 2px 6px;
    border-radius: 4px;
}
.status-error {
    background-color: #fee2e2;
    color: #991b1b;
    padding: 2px 6px;
    border-radius: 4px;
}
.status-warning {
    background-color: #fff7ed;
    color: #92400e;
    padding: 2px 6px;
    border-radius: 4px;
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
.action-btn {
    display: inline-block;
    background: #16a34a;
    color: white;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 4px;
    margin-top: 10px;
    font-size: 14px;
}
.action-btn:hover {
    background: #15803d;
}
.collapsible {
    background: #f1f5f9;
    padding: 10px 15px;
    border-radius: 4px;
    cursor: pointer;
    border: 1px solid #e2e8f0;
    margin-bottom: 5px;
}
.collapsible-content {
    display: none;
    padding: 10px;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 4px 4px;
}
</style>';
echo '</head><body><div class="container">';
echo '<h1>Analyse approfondie de la base de données</h1>';

try {
    // Établir la connexion à la base de données
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception("Erreur critique: Impossible d'établir la connexion à la base de données.");
    }

    echo '<div class="success">✅ Connexion à la base de données établie avec succès.</div>';

    // Récupérer les informations de connexion
    echo '<h2>Informations de configuration du serveur</h2>';
    echo '<table>';
    echo '<tr><th>Propriété</th><th>Valeur</th></tr>';
    echo '<tr><td>Serveur</td><td>' . $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS) . '</td></tr>';
    echo '<tr><td>Version MySQL</td><td>' . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . '</td></tr>';
    echo '<tr><td>Driver PDO</td><td>' . $conn->getAttribute(PDO::ATTR_DRIVER_NAME) . '</td></tr>';
    echo '<tr><td>Mode d\'erreur PDO</td><td>' . $conn->getAttribute(PDO::ATTR_ERRMODE) . '</td></tr>';
    echo '<tr><td>Jeu de caractères</td><td>' . $conn->query('SELECT @@character_set_database')->fetchColumn() . '</td></tr>';
    echo '<tr><td>Collation</td><td>' . $conn->query('SELECT @@collation_database')->fetchColumn() . '</td></tr>';
    echo '</table>';

    // Vérifier l'existence des tables principales
    $requiredTables = [
        'utilisateurs' => 'Gestion des utilisateurs et authentification',
        'entreprises' => 'Stockage des informations sur les entreprises',
        'offres' => 'Gestion des offres de stage',
        'etudiants' => 'Profils des étudiants',
        'pilotes' => 'Profils des pilotes de promotion',
        'competences' => 'Catalogue des compétences disponibles',
        'candidatures' => 'Suivi des candidatures aux offres',
        'evaluations_entreprises' => 'Évaluations des entreprises par les étudiants',
        'wishlists' => 'Liste des offres favorites des étudiants',
        'offres_competences' => 'Relation entre offres et compétences requises'
    ];

    echo '<h2>Vérification des tables</h2>';
    echo '<table>';
    echo '<tr><th>Table</th><th>Description</th><th>Statut</th><th>Nombre d\'enregistrements</th></tr>';

    $missingTables = [];
    $emptyTables = [];

    foreach ($requiredTables as $table => $description) {
        $tableExists = $conn->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        echo '<tr>';
        echo '<td><code>' . $table . '</code></td>';
        echo '<td>' . $description . '</td>';

        if ($tableExists) {
            $count = $conn->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo '<td class="status-ok">Existe</td>';
            echo '<td>' . $count . '</td>';

            if ($count == 0) {
                $emptyTables[] = $table;
            }
        } else {
            echo '<td class="status-error">Manquante</td>';
            echo '<td>-</td>';
            $missingTables[] = $table;
        }

        echo '</tr>';
    }
    echo '</table>';

    // Alerte pour les tables manquantes
    if (!empty($missingTables)) {
        echo '<div class="error">';
        echo '<strong>Tables manquantes détectées!</strong> Les tables suivantes sont absentes: ';
        echo '<code>' . implode('</code>, <code>', $missingTables) . '</code>';
        echo '<p>Vous devez exécuter le script SQL d\'initialisation de la base de données pour créer ces tables.</p>';
        echo '</div>';
    }

    // Alerte pour les tables vides
    if (!empty($emptyTables)) {
        echo '<div class="warning">';
        echo '<strong>Tables vides détectées!</strong> Les tables suivantes ne contiennent aucune donnée: ';
        echo '<code>' . implode('</code>, <code>', $emptyTables) . '</code>';
        echo '</div>';
    }

    // Si toutes les tables sont présentes, vérifier les relations
    if (empty($missingTables)) {
        echo '<h2>Vérification des relations et clés étrangères</h2>';

        $relations = [
            ['etudiants', 'user_id', 'utilisateurs', 'id', 'Profil étudiant → Utilisateur'],
            ['pilotes', 'user_id', 'utilisateurs', 'id', 'Profil pilote → Utilisateur'],
            ['offres', 'entreprise_id', 'entreprises', 'id', 'Offre → Entreprise'],
            ['candidatures', 'offre_id', 'offres', 'id', 'Candidature → Offre'],
            ['candidatures', 'etudiant_id', 'etudiants', 'id', 'Candidature → Étudiant'],
            ['evaluations_entreprises', 'entreprise_id', 'entreprises', 'id', 'Évaluation → Entreprise'],
            ['evaluations_entreprises', 'etudiant_id', 'etudiants', 'id', 'Évaluation → Étudiant'],
            ['wishlists', 'etudiant_id', 'etudiants', 'id', 'Wishlist → Étudiant'],
            ['wishlists', 'offre_id', 'offres', 'id', 'Wishlist → Offre'],
            ['offres_competences', 'offre_id', 'offres', 'id', 'Compétence requise → Offre'],
            ['offres_competences', 'competence_id', 'competences', 'id', 'Compétence requise → Compétence']
        ];

        $integrityIssues = false;
        echo '<table>';
        echo '<tr><th>Relation</th><th>Description</th><th>Statut</th></tr>';

        foreach ($relations as $relation) {
            $childTable = $relation[0];
            $childColumn = $relation[1];
            $parentTable = $relation[2];
            $parentColumn = $relation[3];
            $description = $relation[4];

            $query = "SELECT COUNT($childTable.$childColumn) as invalid_count
                      FROM $childTable
                      LEFT JOIN $parentTable ON $childTable.$childColumn = $parentTable.$parentColumn
                      WHERE $parentTable.$parentColumn IS NULL AND $childTable.$childColumn IS NOT NULL";

            try {
                $invalidCount = $conn->query($query)->fetchColumn();

                echo '<tr>';
                echo "<td><code>$childTable.$childColumn → $parentTable.$parentColumn</code></td>";
                echo "<td>$description</td>";

                if ($invalidCount > 0) {
                    echo '<td class="status-error">Problème - ' . $invalidCount . ' références invalides</td>';
                    $integrityIssues = true;
                } else {
                    echo '<td class="status-ok">Intégrité OK</td>';
                }

                echo '</tr>';
            } catch (PDOException $e) {
                echo '<tr>';
                echo "<td><code>$childTable.$childColumn → $parentTable.$parentColumn</code></td>";
                echo "<td>$description</td>";
                echo '<td class="status-warning">Erreur - ' . $e->getMessage() . '</td>';
                echo '</tr>';
            }
        }

        echo '</table>';

        if ($integrityIssues) {
            echo '<div class="error">';
            echo '<strong>Problèmes d\'intégrité référentielle détectés!</strong>';
            echo '<p>Certaines références entre tables sont invalides, ce qui peut causer des erreurs dans l\'application.</p>';
            echo '</div>';
        } else {
            echo '<div class="success">';
            echo '<strong>L\'intégrité référentielle est maintenue!</strong>';
            echo '<p>Toutes les relations entre tables sont valides.</p>';
            echo '</div>';
        }
    }

    // Vérification spécifique du compte administrateur
    echo '<h2>Vérification du compte administrateur</h2>';

    $query = "SELECT id, email, role, password FROM utilisateurs WHERE email = 'admin@web4all.fr' AND role = 'admin'";
    $stmt = $conn->query($query);

    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        echo '<div class="success">';
        echo '✅ <strong>Compte administrateur trouvé</strong> (ID: ' . $admin['id'] . ')';
        echo '</div>';

        // Vérifier le hash du mot de passe
        $correctHash = '$2y$10$3OQWJkIKv2AE2dGBTWJy7.MwQ9hGUJbD3pdL7dFBVXHPSGG8mhUKy';
        if ($admin['password'] !== $correctHash) {
            echo '<div class="warning">';
            echo '⚠️ <strong>Hash de mot de passe potentiellement incorrect</strong>';
            echo '<p>Le hash du mot de passe administrateur ne correspond pas à la valeur attendue.</p>';
            echo '<a href="verify_admin.php" class="action-btn">Réinitialiser le compte administrateur</a>';
            echo '</div>';
        } else {
            echo '<div class="success">';
            echo '✅ <strong>Hash de mot de passe correct</strong>';
            echo '</div>';
        }
    } else {
        echo '<div class="error">';
        echo '❌ <strong>Compte administrateur non trouvé!</strong>';
        echo '<p>Le compte administrateur principal (admin@web4all.fr) n\'existe pas dans la base de données.</p>';
        echo '<a href="verify_admin.php" class="action-btn">Créer le compte administrateur</a>';
        echo '</div>';
    }

    // Structure détaillée de la base de données
    echo '<h2>Structure détaillée des tables</h2>';

    if (!empty($missingTables)) {
        echo '<div class="warning">';
        echo 'L\'analyse détaillée des structures est indisponible car certaines tables sont manquantes.';
        echo '</div>';
    } else {
        foreach ($requiredTables as $table => $description) {
            echo '<button class="collapsible"><strong>' . $table . '</strong> - ' . $description . '</button>';
            echo '<div class="collapsible-content">';

            try {
                $columns = $conn->query("DESCRIBE $table")->fetchAll(PDO::FETCH_ASSOC);

                echo '<table>';
                echo '<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th><th>Extra</th></tr>';

                foreach ($columns as $column) {
                    echo '<tr>';
                    echo '<td>' . $column['Field'] . '</td>';
                    echo '<td>' . $column['Type'] . '</td>';
                    echo '<td>' . $column['Null'] . '</td>';
                    echo '<td>' . $column['Key'] . '</td>';
                    echo '<td>' . ($column['Default'] !== null ? $column['Default'] : '<em>NULL</em>') . '</td>';
                    echo '<td>' . $column['Extra'] . '</td>';
                    echo '</tr>';
                }

                echo '</table>';

                // Afficher les index
                $indexes = $conn->query("SHOW INDEX FROM $table")->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($indexes)) {
                    echo '<h4>Index</h4>';
                    echo '<table>';
                    echo '<tr><th>Nom</th><th>Colonne</th><th>Unique</th><th>Type</th></tr>';

                    foreach ($indexes as $index) {
                        echo '<tr>';
                        echo '<td>' . $index['Key_name'] . '</td>';
                        echo '<td>' . $index['Column_name'] . '</td>';
                        echo '<td>' . ($index['Non_unique'] == 0 ? 'Oui' : 'Non') . '</td>';
                        echo '<td>' . $index['Index_type'] . '</td>';
                        echo '</tr>';
                    }

                    echo '</table>';
                }

            } catch (PDOException $e) {
                echo '<div class="error">Erreur lors de l\'analyse de la structure: ' . $e->getMessage() . '</div>';
            }

            echo '</div>';
        }
    }

    echo '<script>
    var coll = document.getElementsByClassName("collapsible");
    for (var i = 0; i < coll.length; i++) {
        coll[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var content = this.nextElementSibling;
            if (content.style.display === "block") {
                content.style.display = "none";
            } else {
                content.style.display = "block";
            }
        });
    }
    </script>';

} catch (Exception $e) {
    echo '<div class="error">';
    echo '<strong>Erreur critique:</strong> ' . $e->getMessage();
    echo '</div>';
} finally {
    echo '<p style="text-align: center; margin-top: 30px;">';
    echo '<a href="index.php" class="btn">Retour à l\'application</a> ';

    if (isset($missingTables) && !empty($missingTables)) {
        echo '<a href="verify_admin.php" class="btn" style="background:#16a34a;">Réparation automatique</a>';
    }

    echo '</p>';
    echo '</div></body></html>';
}