<?php
/**
 * Fonctions utilitaires pour l'application
 */

/**
 * Redirection vers une URL
 * @param string $url
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

/**
 * Générer une URL complète
 * @param string $page
 * @param string $action
 * @param array $params
 * @return string
 */
function url($page = 'accueil', $action = '', $params = []) {
    $url = URL_ROOT . '/index.php?page=' . $page;

    if (!empty($action)) {
        $url .= '&action=' . $action;
    }

    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $url .= '&' . $key . '=' . $value;
        }
    }

    return $url;
}

/**
 * Nettoyer une chaîne de caractères
 * @param string $data
 * @return string
 */
function cleanData($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Vérifier si l'utilisateur est connecté
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifier si l'utilisateur a un rôle spécifique
 * @param string $role
 * @return bool
 */
function hasRole($role) {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] == $role;
}

/**
 * Vérifier si l'utilisateur est admin
 * @return bool
 */
function isAdmin() {
    return hasRole(ROLE_ADMIN);
}

/**
 * Vérifier si l'utilisateur est pilote
 * @return bool
 */
function isPilote() {
    return hasRole(ROLE_PILOTE) || isAdmin();
}

/**
 * Vérifier l'accès à une fonctionnalité selon le rôle
 * @param string $feature
 * @return bool
 */
function checkAccess($feature) {
    // Liste des fonctionnalités par rôle
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
        return true; // Admin a accès à tout
    }

    $userRole = $_SESSION['role'];

    if (isset($accessMatrix[$userRole]) && in_array($feature, $accessMatrix[$userRole])) {
        return true;
    }

    return false;
}

/**
 * Afficher un message d'alerte
 * @param string $message
 * @param string $type (success, danger, warning, info)
 * @return string
 */
function alert($message, $type = 'info') {
    return '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">
                ' . $message . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
}

/**
 * Générer la pagination
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

    // Pages
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $pageParams = array_merge($params, ['p' => $i]);
            $html .= '<li class="page-item"><a class="page-link" href="' . url($page, $action, $pageParams) . '">' . $i . '</a></li>';
        }
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