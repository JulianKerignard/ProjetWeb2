<?php
// Fichier de diagnostic pour identifier les problèmes de session et de constantes
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Charger la configuration
require_once 'config/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic PHP</title>
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
<h1>Diagnostic PHP pour l'application de gestion de stages</h1>

<section>
    <h2>Informations sur PHP</h2>
    <p>Version PHP: <?php echo phpversion(); ?></p>
    <p>Extensions chargées: <?php echo count(get_loaded_extensions()); ?></p>
    <p>Affichage des erreurs: <?php echo ini_get('display_errors') ? 'Activé' : 'Désactivé'; ?></p>
</section>

<section>
    <h2>Chemins importants</h2>
    <p>Dossier racine: <?php echo defined('ROOT_PATH') ? ROOT_PATH : 'Non défini'; ?></p>
    <p>Dossier vues: <?php echo defined('VIEWS_PATH') ? VIEWS_PATH : 'Non défini'; ?></p>
    <p>Le fichier 'postuler.php' existe: <?php
        echo defined('VIEWS_PATH') && file_exists(VIEWS_PATH . '/candidatures/postuler.php') ?
            '<span class="success">Oui</span>' :
            '<span class="error">Non</span>';
        ?></p>
</section>

<section>
    <h2>Variables de session</h2>
    <pre><?php print_r($_SESSION); ?></pre>
</section>

<section>
    <h2>Constantes définies</h2>
    <table>
        <tr>
            <th>Constante</th>
            <th>Valeur</th>
        </tr>
        <tr>
            <td>ROLE_ETUDIANT</td>
            <td><?php echo defined('ROLE_ETUDIANT') ? ROLE_ETUDIANT : '<span class="error">Non définie</span>'; ?></td>
        </tr>
        <tr>
            <td>ROLE_PILOTE</td>
            <td><?php echo defined('ROLE_PILOTE') ? ROLE_PILOTE : '<span class="error">Non définie</span>'; ?></td>
        </tr>
        <tr>
            <td>ROLE_ADMIN</td>
            <td><?php echo defined('ROLE_ADMIN') ? ROLE_ADMIN : '<span class="error">Non définie</span>'; ?></td>
        </tr>
        <tr>
            <td>ITEMS_PER_PAGE</td>
            <td><?php echo defined('ITEMS_PER_PAGE') ? ITEMS_PER_PAGE : '<span class="error">Non définie</span>'; ?></td>
        </tr>
    </table>
</section>

<section>
    <h2>Validations clés</h2>
    <p>
        <strong>Test de condition critique:</strong>
        <?php
        $testRole = 'etudiant';
        $testVar = isset($_SESSION['role']) && $_SESSION['role'] === $testRole;
        echo "isset(\$_SESSION['role']) && \$_SESSION['role'] === 'etudiant' => " . ($testVar ? 'true' : 'false');
        ?>
    </p>
    <p>
        <strong>Comparaison directe:</strong>
        <?php
        echo "SESSION['role'] (" . (isset($_SESSION['role']) ? $_SESSION['role'] : 'non défini') . ") === ";
        echo "ROLE_ETUDIANT (" . (defined('ROLE_ETUDIANT') ? ROLE_ETUDIANT : 'non défini') . ") => ";
        echo (isset($_SESSION['role']) && defined('ROLE_ETUDIANT') && $_SESSION['role'] === ROLE_ETUDIANT) ? 'true' : 'false';
        ?>
    </p>
</section>

<section>
    <h2>Actions recommandées</h2>
    <p>Si les valeurs semblent correctes mais que le formulaire ne s'affiche toujours pas:</p>
    <ol>
        <li>Vérifiez les permissions des fichiers (lecture/écriture)</li>
        <li>Videz complètement le cache de votre navigateur (Ctrl+F5 ou Shift+F5)</li>
        <li>Essayez dans un navigateur différent ou en mode navigation privée</li>
        <li>Vérifiez les logs PHP dans votre fichier d'erreur (<?php echo ini_get('error_log'); ?>)</li>
        <li>Essayez de détruire et recréer votre session (déconnexion/reconnexion)</li>
    </ol>
</section>
</body>
</html>