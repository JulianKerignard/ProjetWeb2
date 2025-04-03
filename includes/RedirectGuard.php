<?php
/**
 * Garde de redirection pour prévenir les boucles infinies
 *
 * Ce middleware intercepte les redirections potentiellement cycliques
 * et les interrompt avant qu'elles ne causent un dysfonctionnement système.
 *
 * @version 1.0.0
 * @package LeBonPlan\Security
 */
class RedirectGuard {
    /**
     * Nombre maximum de redirections autorisées vers la même route en 10 secondes
     */
    const MAX_REDIRECTS = 3;

    /**
     * Période d'observation des redirections en secondes
     */
    const WINDOW_SECONDS = 10;

    /**
     * Prévient les boucles de redirection
     *
     * @param string $route Identifiant de la route concernée
     * @return bool True si la redirection est autorisée, sinon affiche une erreur et termine le script
     */
    public static function preventLoop($route) {
        // Protection contre les redirections cycliques
        if (!isset($_SESSION['redirect_history'])) {
            $_SESSION['redirect_history'] = [];
        }

        $currentTime = time();
        $redirectHistory = &$_SESSION['redirect_history'];

        // Nettoyage des entrées trop anciennes (> 10 secondes)
        foreach ($redirectHistory as $path => $timestamps) {
            $redirectHistory[$path] = array_filter($timestamps, function($time) use ($currentTime) {
                return ($currentTime - $time) < self::WINDOW_SECONDS;
            });

            if (empty($redirectHistory[$path])) {
                unset($redirectHistory[$path]);
            }
        }

        // Vérifier si la redirection vers cette route est trop fréquente
        if (isset($redirectHistory[$route])) {
            if (count($redirectHistory[$route]) >= self::MAX_REDIRECTS) {
                // Cycle détecté, interruption
                unset($_SESSION['redirect_history']);
                error_log("Boucle de redirection détectée vers $route");

                // Forcer la déconnexion de session en cas de problème grave
                session_unset();
                session_destroy();

                // Afficher une page d'erreur explicite
                self::renderErrorPage($route);
                exit;
            }

            // Ajouter l'horodatage actuel à l'historique
            $redirectHistory[$route][] = $currentTime;
        } else {
            // Initialiser l'historique pour cette route
            $redirectHistory[$route] = [$currentTime];
        }

        return true;
    }

    /**
     * Affiche une page d'erreur diagnostique
     *
     * @param string $route Route qui a causé la boucle de redirection
     */
    private static function renderErrorPage($route) {
        echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Erreur système - Boucle de redirection</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        h1 {
            color: #dc3545;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        .alert {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            border-left: 5px solid #dc3545;
            margin-bottom: 20px;
        }
        code {
            background-color: #f1f1f1;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        ul {
            background-color: #fff;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        li {
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #0069d9;
        }
        .technical {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 4px;
            margin-top: 30px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <h1>Erreur système détectée</h1>
    
    <div class="alert">
        <strong>Problème critique :</strong> Une boucle de redirection a été détectée vers la page: <code>' . htmlspecialchars($route) . '</code>
    </div>
    
    <h2>Diagnostic technique</h2>
    <p>Le système a détecté une condition de redirection cyclique qui pourrait indiquer un problème sous-jacent dans l\'application.</p>
    
    <h3>Causes possibles :</h3>
    <ul>
        <li>Problème d\'accès à la base de données (connexion, permissions ou structure)</li>
        <li>Transaction SQL interrompue ou verrou non libéré</li>
        <li>Corruption d\'état de session PHP</li>
        <li>Requête POST avec validation échouée mais redirection persistante</li>
    </ul>
    
    <h3>Solutions recommandées :</h3>
    <ul>
        <li>Vider complètement le cache navigateur (pas seulement rafraîchir)</li>
        <li>Supprimer les cookies du site</li>
        <li>Vérifier les journaux d\'erreurs serveur (error.log)</li>
        <li>Vérifier la structure et les permissions de la table <code>role_permissions</code></li>
        <li>Exécuter une requête de diagnostic SQL: <code>SHOW OPEN TABLES WHERE In_use > 0;</code></li>
    </ul>
    
    <a href="' . (function_exists('url') ? url() : '/') . '" class="btn">Retour à l\'accueil</a>
    
    <div class="technical">
        <p><strong>Informations techniques supplémentaires :</strong></p>
        <p>Timestamp: ' . date('Y-m-d H:i:s') . '<br>
        Session ID: ' . session_id() . '<br>
        User Agent: ' . htmlspecialchars($_SERVER['HTTP_USER_AGENT']) . '</p>
    </div>
</body>
</html>';
    }
}