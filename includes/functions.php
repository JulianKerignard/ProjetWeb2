<?php
/**
 * Fonctions utilitaires pour l'application avec support multi-environnement
 */

/**
 * Gestionnaire de redirection optimisé
 * @param string $url
 * @param int $statusCode
 */
function redirect($url, $statusCode = 302) {
    header("Location: {$url}", true, $statusCode);
    exit;
}

/**
 * Générateur d'URL compatible multi-environnement
 * @param string $page
 * @param string $action
 * @param array $params
 * @return string
 */
function url($page = 'accueil', $action = '', $params = []) {
    // Construction de l'URL de base
    $baseUrl = rtrim(URL_ROOT, '/');
    $url = $baseUrl . '/index.php';

    // Construction des paramètres de requête
    $queryParams = ['page' => $page];

    if (!empty($action)) {
        $queryParams['action'] = $action;
    }

    if (!empty($params)) {
        $queryParams = array_merge($queryParams, $params);
    }

    // Génération de la chaîne de requête URL-encoded
    return $url . '?' . http_build_query($queryParams);
}

/**
 * Nettoie et sécurise les données d'entrée
 * @param string $data
 * @return string
 */
function cleanData($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Vérifie si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    // Vérification robuste de l'état de la session
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    // Vérification préalable de la connexion et de l'existence du rôle
    if (!isLoggedIn() || !isset($_SESSION['role'])) {
        return false;
    }

    // Traitement spécial pour le rôle administrateur qui a tous les accès
    if ($_SESSION['role'] === ROLE_ADMIN) {
        return true;
    }

    return $_SESSION['role'] == $role;
}

/**
 * Vérifie si l'utilisateur est admin
 * @return bool
 */
function isAdmin() {
    return hasRole(ROLE_ADMIN);
}

/**
 * Vérifie si l'utilisateur est pilote
 * @return bool
 */
function isPilote() {
    return hasRole(ROLE_PILOTE) || isAdmin();
}

/**
 * Vérifie l'accès à une fonctionnalité selon la matrice de rôles
 * @param string $feature
 * @return bool
 */
function checkAccess($feature) {
    // Matrice d'accès basée sur les rôles
    $accessMatrix = [
        ROLE_ADMIN => [
            'entreprise_creer', 'entreprise_modifier', 'entreprise_supprimer',
            'offre_creer', 'offre_modifier', 'offre_supprimer',
            'pilote_creer', 'pilote_modifier', 'pilote_supprimer',
            'etudiant_creer', 'etudiant_modifier', 'etudiant_supprimer'
        ],
        ROLE_PILOTE => [
            'entreprise_creer', 'entreprise_modifier',
            'offre_creer', 'offre_modifier',
            'etudiant_creer', 'etudiant_modifier'
        ],
        ROLE_ETUDIANT => [
            'entreprise_evaluer',
            'wishlist_ajouter', 'wishlist_retirer', 'wishlist_afficher',
            'offre_postuler', 'candidatures_afficher'
        ]
    ];

    if (!isLoggedIn()) {
        return false;
    }

    if (isAdmin()) {
        return true; // Admin a accès à toutes les fonctionnalités
    }

    $userRole = $_SESSION['role'];

    if (isset($accessMatrix[$userRole]) && in_array($feature, $accessMatrix[$userRole])) {
        return true;
    }

    return false;
}

/**
 * Génère un composant d'alerte Bootstrap
 * @param string $message
 * @param string $type (success, danger, warning, info)
 * @param bool $dismissible
 * @return string
 */
function alert($message, $type = 'info', $dismissible = true) {
    $dismissibleClass = $dismissible ? 'alert-dismissible fade show' : '';
    $dismissButton = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';

    return '<div class="alert alert-' . $type . ' ' . $dismissibleClass . '" role="alert">
                ' . $message . '
                ' . $dismissButton . '
            </div>';
}

/**
 * Composant de pagination avec optimisation UX
 * @param int $totalItems
 * @param int $currentPage
 * @param string $page
 * @param string $action
 * @param array $params
 * @return string
 */
function pagination($totalItems, $currentPage, $page, $action, $params = []) {
    $totalPages = ceil($totalItems / ITEMS_PER_PAGE);

    if ($totalPages <= 1) {
        return '';
    }

    $html = '<nav aria-label="Pagination">
                <ul class="pagination">';

    // Bouton précédent
    if ($currentPage > 1) {
        $prevParams = array_merge($params, ['p' => $currentPage - 1]);
        $html .= '<li class="page-item">
                    <a class="page-link" href="' . url($page, $action, $prevParams) . '" aria-label="Précédent">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>';
    } else {
        $html .= '<li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">&laquo;</span>
                </li>';
    }

    // Algorithme optimisé pour pagination avec beaucoup de pages
    $startPage = max(1, min($currentPage - 2, $totalPages - 4));
    $endPage = min($totalPages, max($currentPage + 2, 5));

    // Première page + ellipsis si nécessaire
    if ($startPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . url($page, $action, array_merge($params, ['p' => 1])) . '">1</a></li>';
        if ($startPage > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }

    // Pages numérotées
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $pageParams = array_merge($params, ['p' => $i]);
            $html .= '<li class="page-item"><a class="page-link" href="' . url($page, $action, $pageParams) . '">' . $i . '</a></li>';
        }
    }

    // Dernière page + ellipsis si nécessaire
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . url($page, $action, array_merge($params, ['p' => $totalPages])) . '">' . $totalPages . '</a></li>';
    }

    // Bouton suivant
    if ($currentPage < $totalPages) {
        $nextParams = array_merge($params, ['p' => $currentPage + 1]);
        $html .= '<li class="page-item">
                    <a class="page-link" href="' . url($page, $action, $nextParams) . '" aria-label="Suivant">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>';
    } else {
        $html .= '<li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">&raquo;</span>
                </li>';
    }

    $html .= '</ul>
            </nav>';

    return $html;
}

/**
 * Inclusion intelligente des fichiers de vue
 * @param string $path
 * @param array $data
 * @return void
 */
function viewInclude($path, $data = []) {
    // Extraction des variables pour les rendre disponibles dans la vue
    if (!empty($data)) {
        extract($data);
    }

    $fullPath = ROOT_PATH . '/' . $path;

    if (!file_exists($fullPath)) {
        throw new Exception("Vue non trouvée: {$path}");
    }

    include $fullPath;
}

/**
 * Logger pour le débogage en environnement de développement
 * @param mixed $data
 * @param string $level
 * @return void
 */
function devLog($data, $level = 'info') {
    if (ENVIRONMENT !== 'development') {
        return;
    }

    $timestamp = date('Y-m-d H:i:s');
    $serialized = is_string($data) ? $data : print_r($data, true);
    error_log("[{$timestamp}] [{$level}] {$serialized}");
}

/**
 * Récupère la valeur actuelle de pagination depuis la query string
 * @param int $default
 * @return int
 */
function getCurrentPage($default = 1) {
    return isset($_GET['p']) && is_numeric($_GET['p']) ? max(1, (int)$_GET['p']) : $default;
}