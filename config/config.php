<?php
/**
 * Configuration générale de l'application
 */

// Définir les chemins de base
define('ROOT_PATH', dirname(__DIR__));

// Modifier cette ligne pour qu'elle corresponde à votre environnement local
// Option 1: Chemin absolu si vous n'utilisez pas de VirtualHost
define('URL_ROOT', 'http://localhost/ProjetWeb2');

// Option 2: Si vous avez configuré un VirtualHost, utilisez:
// define('URL_ROOT', 'http://projet-stage.local');

define('SITE_NAME', 'Stages Web4All');

// Rôles utilisateurs
define('ROLE_ADMIN', 'admin');
define('ROLE_PILOTE', 'pilote');
define('ROLE_ETUDIANT', 'etudiant');

// Configuration des uploads
define('UPLOAD_DIR', ROOT_PATH . '/public/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 Mo
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx']);

// Pagination
define('ITEMS_PER_PAGE', 10);