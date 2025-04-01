<?php
/**
 * Script de diagnostic pour identifier les problèmes de configuration
 * Placez ce fichier à la racine de votre projet et accédez-y via http://localhost/ProjetWeb2/diagnostic.php
 */

// Afficher toutes les erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<html><head><title>Diagnostic ProjetWeb2</title>';
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h1 { color: #2563eb; }
    h2 { color: #1d4ed8; margin-top: 30px; }
    .section { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; margin-bottom: 20px; border-radius: 8px; }
    .important { color: #ef4444; font-weight: bold; }
    .success { color: #10b981; font-weight: bold; }
    pre { background: #1e293b; color: white; padding: 10px; border-radius: 5px; overflow-x: auto; }
    .test { margin: 10px 0; padding: 10px; border-radius: 5px; }
    .test-success { background: rgba(16, 185, 129, 0.1); }
    .test-error { background: rgba(239, 68, 68, 0.1); }
    table { border-collapse: collapse; width: 100%; }
    table, th, td { border: 1px solid #e2e8f0; }
    th, td { padding: 10px; text-align: left; }
    th { background: #f1f5f9; }
</style>';
echo '</head><body>';
echo '<h1>Diagnostic ProjetWeb2</h1>';

// 1. Informations sur le serveur
echo '<div class="section">';
echo '<h2>Informations sur le serveur</h2>';
echo '<ul>';
echo '<li><strong>Serveur:</strong> ' . $_SERVER['SERVER_SOFTWARE'] . '</li>';
echo '<li><strong>PHP Version:</strong> ' . phpversion() . '</li>';
echo '<li><strong>Document Root:</strong> ' . $_SERVER['DOCUMENT_ROOT'] . '</li>';
echo '<li><strong>Script Filename:</strong> ' . $_SERVER['SCRIPT_FILENAME'] . '</li>';
echo '<li><strong>Script Name:</strong> ' . $_SERVER['SCRIPT_NAME'] . '</li>';
echo '<li><strong>Request URI:</strong> ' . $_SERVER['REQUEST_URI'] . '</li>';
echo '<li><strong>HTTP_HOST:</strong> ' . $_SERVER['HTTP_HOST'] . '</li>';
echo '</ul>';
echo '</div>';

// 2. Vérification des fichiers essentiels
echo '<div class="section">';
echo '<h2>Vérification des fichiers essentiels</h2>';

$essentialFiles = [
    '.htaccess',
    'index.php',
    'config/config.php',
    'config/database.php',
    'includes/functions.php',
    'controllers/AuthController.php',
    'models/Auth.php',
    'views/auth/login.php',
    'views/templates/header.php',
    'views/templates/footer.php'
];

echo '<table>';
echo '<tr><th>Fichier</th><th>Status</th><th>Taille</th><th>Dernière modification</th></tr>';

foreach ($essentialFiles as $file) {
    echo '<tr>';
    echo '<td>' . $file . '</td>';

    if (file_exists($file)) {
        echo '<td class="success">Existe</td>';
        echo '<td>' . filesize($file) . ' octets</td>';
        echo '<td>' . date("Y-m-d H:i:s", filemtime($file)) . '</td>';
    } else {
        echo '<td class="important">Manquant</td>';
        echo '<td>-</td><td>-</td>';
    }

    echo '</tr>';
}

echo '</table>';
echo '</div>';

// 3. Vérification du mod_rewrite
echo '<div class="section">';
echo '<h2>Vérification du module mod_rewrite</h2>';

// Vérifie si mod_rewrite est chargé
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    $modRewriteEnabled = in_array('mod_rewrite', $modules);

    if ($modRewriteEnabled) {
        echo '<div class="test test-success">Le module mod_rewrite est activé sur ce serveur.</div>';
    } else {
        echo '<div class="test test-error">Le module mod_rewrite n\'est PAS activé! C\'est nécessaire pour le bon fonctionnement de l\'application.</div>';
        echo '<p>Pour l\'activer, modifiez le fichier httpd.conf d\'Apache et décommentez la ligne:</p>';
        echo '<pre>LoadModule rewrite_module modules/mod_rewrite.so</pre>';
        echo '<p>Puis redémarrez le serveur Apache.</p>';
    }
} else {
    echo '<div class="test test-error">Impossible de déterminer si mod_rewrite est activé (la fonction apache_get_modules() n\'est pas disponible).</div>';
    echo '<p>Vérifiez manuellement dans votre configuration Apache.</p>';
}
echo '</div>';

// 4. Test de génération d'URL
echo '<div class="section">';
echo '<h2>Test de génération d\'URL</h2>';

// Inclure le fichier de configuration et fonctions
if (file_exists('config/config.php') && file_exists('includes/functions.php')) {
    include_once 'config/config.php';
    include_once 'includes/functions.php';

    echo '<p>URL_ROOT défini comme: <strong>' . (defined('URL_ROOT') ? URL_ROOT : 'Non défini') . '</strong></p>';

    if (function_exists('url')) {
        echo '<ul>';
        echo '<li>URL Accueil: ' . url() . '</li>';
        echo '<li>URL Login: ' . url('auth', 'login') . '</li>';
        echo '<li>URL avec paramètres: ' . url('offres', 'rechercher', ['q' => 'test']) . '</li>';
        echo '</ul>';

        echo '<p>Pour tester le routage, cliquez sur les liens ci-dessous:</p>';
        echo '<ul>';
        echo '<li><a href="' . url() . '" target="_blank">Aller à l\'accueil</a></li>';
        echo '<li><a href="' . url('auth', 'login') . '" target="_blank">Aller à la page de connexion</a></li>';
        echo '</ul>';
    } else {
        echo '<div class="test test-error">La fonction url() n\'existe pas!</div>';
    }
} else {
    echo '<div class="test test-error">Impossible de charger les fichiers de configuration ou de fonctions pour tester la génération d\'URL.</div>';
}
echo '</div>';

// 5. Suggestions et corrections
echo '<div class="section">';
echo '<h2>Suggestions et corrections</h2>';

echo '<ol>';
echo '<li>Assurez-vous que le chemin défini dans <code>URL_ROOT</code> (dans config.php) correspond exactement à l\'URL de base de votre projet.</li>';
echo '<li>Vérifiez que <code>RewriteBase</code> dans .htaccess correspond au sous-dossier de votre projet (par exemple, /ProjetWeb2/).</li>';
echo '<li>Assurez-vous que mod_rewrite est activé dans Apache.</li>';
echo '<li>Si vous utilisez XAMPP, vérifiez que dans httpd.conf, vous avez bien les directives:
<pre>
&lt;Directory "C:/xampp/htdocs"&gt;
    Options Indexes FollowSymLinks Includes ExecCGI
    AllowOverride All
    Require all granted
&lt;/Directory&gt;
</pre>
</li>';
echo '<li>Si l\'erreur persiste, essayez d\'utiliser des liens directs pour accéder aux pages:
<pre>
&lt;a href="index.php?page=auth&action=login"&gt;Connexion&lt;/a&gt;
</pre>
</li>';
echo '</ol>';
echo '</div>';

echo '</body></html>';