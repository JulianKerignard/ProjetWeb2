<?php
// Démarre la session et charge les fichiers nécessaires
session_start();
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic des Candidatures</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #333; }
        section { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        h2 { color: #2c3e50; margin-top: 0; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .success { color: green; }
        .error { color: red; font-weight: bold; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
<h1>Diagnostic des Candidatures et Wishlist</h1>

<section>
    <h2>Informations sur PHP</h2>
    <p>Version PHP: <?php echo phpversion(); ?></p>
    <p>Extensions chargées: <?php echo count(get_loaded_extensions()); ?></p>
    <p>Affichage des erreurs: <?php echo ini_get('display_errors') ? 'Activé' : 'Désactivé'; ?></p>
</section>

<section>
    <h2>Vérification des constantes essentielles</h2>
    <table>
        <tr>
            <th>Constante</th>
            <th>Valeur</th>
            <th>Statut</th>
        </tr>
        <tr>
            <td>ROLE_ETUDIANT</td>
            <td><?php echo defined('ROLE_ETUDIANT') ? ROLE_ETUDIANT : 'Non définie'; ?></td>
            <td><?php echo defined('ROLE_ETUDIANT') ? '<span class="success">OK</span>' : '<span class="error">ERREUR</span>'; ?></td>
        </tr>
        <tr>
            <td>UPLOAD_DIR</td>
            <td><?php echo defined('UPLOAD_DIR') ? UPLOAD_DIR : 'Non définie'; ?></td>
            <td><?php echo defined('UPLOAD_DIR') ? '<span class="success">OK</span>' : '<span class="error">ERREUR</span>'; ?></td>
        </tr>
        <tr>
            <td>MAX_FILE_SIZE</td>
            <td><?php echo defined('MAX_FILE_SIZE') ? MAX_FILE_SIZE : 'Non définie'; ?></td>
            <td><?php echo defined('MAX_FILE_SIZE') ? '<span class="success">OK</span>' : '<span class="error">ERREUR</span>'; ?></td>
        </tr>
    </table>
</section>

<section>
    <h2>Vérification des répertoires</h2>
    <?php
    $uploadDir = defined('UPLOAD_DIR') ? UPLOAD_DIR : ROOT_PATH . '/public/uploads/';

    if (!file_exists($uploadDir)) {
        echo "<p class='error'>Le répertoire d'upload n'existe pas: {$uploadDir}</p>";
        echo "<p>Tentative de création: ";
        if (mkdir($uploadDir, 0755, true)) {
            echo "<span class='success'>Réussi</span></p>";
        } else {
            echo "<span class='error'>Échec</span></p>";
        }
    } else {
        echo "<p class='success'>Le répertoire d'upload existe: {$uploadDir}</p>";

        if (is_writable($uploadDir)) {
            echo "<p class='success'>Le répertoire d'upload est accessible en écriture</p>";
        } else {
            echo "<p class='error'>Le répertoire d'upload n'est PAS accessible en écriture</p>";
        }
    }
    ?>
</section>

<section>
    <h2>Vérification des tables de base de données</h2>
    <?php
    try {
        $database = new Database();
        $conn = $database->getConnection();

        if ($conn) {
            echo "<p class='success'>Connexion à la base de données réussie</p>";

            $tables = ['candidatures', 'wishlists', 'offres', 'etudiants'];

            foreach ($tables as $table) {
                try {
                    $stmt = $conn->query("SHOW TABLES LIKE '{$table}'");
                    $exists = $stmt->rowCount() > 0;

                    if ($exists) {
                        echo "<p class='success'>Table {$table}: Existe</p>";

                        // Vérifier la structure de la table
                        $stmt = $conn->query("DESCRIBE {$table}");
                        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        echo "<p>Colonnes: " . implode(", ", $columns) . "</p>";
                    } else {
                        echo "<p class='error'>Table {$table}: N'existe pas</p>";
                    }
                } catch (PDOException $e) {
                    echo "<p class='error'>Erreur lors de la vérification de la table {$table}: " . $e->getMessage() . "</p>";
                }
            }
        } else {
            echo "<p class='error'>Échec de la connexion à la base de données</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>Exception lors de la connexion à la base de données: " . $e->getMessage() . "</p>";
    }
    ?>
</section>

<section>
    <h2>Vérification de la session et des rôles</h2>
    <?php
    echo "<p>Session actuelle:</p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";

    if (isset($_SESSION['role'])) {
        echo "<p>Rôle de l'utilisateur: {$_SESSION['role']}</p>";

        if ($_SESSION['role'] === ROLE_ETUDIANT) {
            echo "<p class='success'>L'utilisateur est un étudiant (peut postuler et ajouter aux favoris)</p>";
        } else {
            echo "<p class='error'>L'utilisateur n'est PAS un étudiant (ne peut pas postuler)</p>";
        }
    } else {
        echo "<p class='error'>Aucun rôle défini dans la session</p>";
    }
    ?>
</section>

<section>
    <h2>Tests de comparaison de chaînes (crucial)</h2>
    <?php
    $testRole = 'etudiant';
    $sessionRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'non défini';
    $definedRole = defined('ROLE_ETUDIANT') ? ROLE_ETUDIANT : 'non défini';

    echo "<table>";
    echo "<tr><th>Test</th><th>Résultat</th></tr>";

    echo "<tr>";
    echo "<td>\$_SESSION['role'] === 'etudiant'</td>";
    echo "<td>" . ($sessionRole === $testRole ? "<span class='success'>VRAI</span>" : "<span class='error'>FAUX</span>") . "</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>\$_SESSION['role'] === ROLE_ETUDIANT</td>";
    echo "<td>" . ($sessionRole === $definedRole ? "<span class='success'>VRAI</span>" : "<span class='error'>FAUX</span>") . "</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>ROLE_ETUDIANT === 'etudiant'</td>";
    echo "<td>" . ($definedRole === $testRole ? "<span class='success'>VRAI</span>" : "<span class='error'>FAUX</span>") . "</td>";
    echo "</tr>";

    echo "</table>";

    echo "<p>Valeurs de comparaison:</p>";
    echo "<ul>";
    echo "<li>\$_SESSION['role'] = '{$sessionRole}'</li>";
    echo "<li>ROLE_ETUDIANT = '{$definedRole}'</li>";
    echo "<li>Chaîne littérale = 'etudiant'</li>";
    echo "</ul>";
    ?>
</section>

<section>
    <h2>Actions recommandées</h2>
    <ol>
        <li>Vérifier que les tables <strong>candidatures</strong> et <strong>wishlists</strong> existent bien dans la base de données</li>
        <li>S'assurer que le répertoire d'upload existe et possède les bonnes permissions (chmod 755)</li>
        <li>Vérifier que la constante ROLE_ETUDIANT est correctement définie dans config.php</li>
        <li>Vider le cache du navigateur (Ctrl+F5)</li>
        <li>Consulter les logs PHP dans le fichier d'erreur de votre serveur</li>
    </ol>

    <p>Commandes utiles pour créer le répertoire d'upload et définir les permissions:</p>
    <pre>
mkdir -p <?php echo ROOT_PATH; ?>/public/uploads
chmod 755 <?php echo ROOT_PATH; ?>/public/uploads
</pre>
</section>

</body>
</html>