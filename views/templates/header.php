<?php
// Protection contre l'accès direct et initialisation des dépendances
if (!defined('ROOT_PATH')) {
    require_once realpath(dirname(__FILE__) . '/../../bootstrap.php');
}

// Detection du mode PhpStorm pour éviter les problèmes de chemins relatifs
$isPhpStormServer = defined('IS_PHPSTORM_SERVER') && IS_PHPSTORM_SERVER;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>

    <!-- Bootstrap CSS - Utilisation d'un CDN pour assurer la compatibilité -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome - Pour des icônes modernes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts - Pour une typographie améliorée -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Montserrat:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Styles personnalisés - Nouveau design moderne -->
    <link href="<?php echo URL_ROOT; ?>/public/css/style.css" rel="stylesheet">

    <!-- Styles personnalisés supplémentaires -->
    <link href="<?php echo URL_ROOT; ?>/public/css/custom.css" rel="stylesheet">
</head>
<body>
<!-- Navbar moderne avec animation subtile au scroll -->
<nav class="navbar navbar-expand-lg navbar-light bg-white">
    <div class="container">
        <a class="navbar-brand" href="<?php echo url(); ?>">
            <i class="fas fa-briefcase text-primary me-2"></i><?php echo SITE_NAME; ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo (empty($_GET['page']) || $_GET['page'] === 'accueil') ? 'active' : ''; ?>" href="<?php echo url(); ?>">
                        <i class="fas fa-home me-1"></i> Accueil
                    </a>
                </li>

                <!-- Menu Offres de stage - Toujours visible -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo (isset($_GET['page']) && $_GET['page'] === 'offres') ? 'active' : ''; ?>"
                       href="#" id="offresDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-clipboard-list me-1"></i> Offres de stage
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="offresDropdown">
                        <li>
                            <a class="dropdown-item" href="<?php echo url('offres'); ?>">
                                <i class="fas fa-list me-2"></i> Toutes les offres
                            </a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                            <?php if (checkAccess('offre_creer')): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo url('offres', 'creer'); ?>">
                                        <i class="fas fa-plus-circle me-2"></i> Créer une offre
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if (isAdmin() || isPilote()): ?>
                                <li>
                                    <a class="dropdown-item" href="<?php echo url('offres', 'statistiques'); ?>">
                                        <i class="fas fa-chart-pie me-2"></i> Statistiques
                                    </a>
                                </li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item" href="<?php echo url('offres', 'rechercher'); ?>">
                                    <i class="fas fa-search me-2"></i> Recherche avancée
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>

                <!-- Menu Entreprises - Toujours visible pour tous les utilisateurs -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo (isset($_GET['page']) && $_GET['page'] === 'entreprises') ? 'active' : ''; ?>"
                       href="#" id="entreprisesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-building me-1"></i> Entreprises
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="entreprisesDropdown">
                        <li>
                            <a class="dropdown-item" href="<?php echo url('entreprises'); ?>">
                                <i class="fas fa-list me-2"></i> Toutes les entreprises
                            </a>
                        </li>
                        <?php if (isLoggedIn() && checkAccess('entreprise_creer')): ?>
                            <li>
                                <a class="dropdown-item" href="<?php echo url('entreprises', 'creer'); ?>">
                                    <i class="fas fa-plus-circle me-2"></i> Ajouter une entreprise
                                </a>
                            </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="<?php echo url('entreprises', 'rechercher'); ?>">
                                <i class="fas fa-search me-2"></i> Recherche avancée
                            </a>
                        </li>
                    </ul>
                </li>

                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin() || isPilote()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-shield me-1"></i> Administration
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?php echo url('pilotes'); ?>">
                                            <i class="fas fa-user-tie me-2"></i> Pilotes
                                        </a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo url('etudiants'); ?>">
                                        <i class="fas fa-user-graduate me-2"></i> Étudiants
                                    </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ETUDIANT): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="candidaturesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-file-alt me-1"></i> Mes candidatures
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="candidaturesDropdown">
                                <li><a class="dropdown-item" href="<?php echo url('candidatures', 'afficher-wishlist'); ?>">
                                        <i class="fas fa-heart me-2"></i> Ma liste de souhaits
                                    </a></li>
                                <li><a class="dropdown-item" href="<?php echo url('candidatures', 'mes-candidatures'); ?>">
                                        <i class="fas fa-file-contract me-2"></i> Mes candidatures
                                    </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle user-badge" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?php echo isset($_SESSION['prenom']) ? $_SESSION['prenom'] . ' ' . $_SESSION['nom'] : 'Mon compte'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">
                                    <i class="fas fa-id-card me-2"></i> Mon profil
                                </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo url('auth', 'logout'); ?>">
                                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                </a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="<?php echo url('auth', 'login'); ?>">
                            <i class="fas fa-sign-in-alt me-1"></i> Connexion
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Contenu principal -->
<main class="py-4">