<?php
/**
 * Configuration générale de l'application
 */

// Définir les chemins de base
define('ROOT_PATH', dirname(__DIR__));
define('URL_ROOT', 'http://localhost/projet-stage');
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