<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="<?php echo URL_ROOT; ?>/public/css/bootstrap.min.css" rel="stylesheet">
    <!-- Styles personnalisés -->
    <link href="<?php echo URL_ROOT; ?>/public/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?php echo url(); ?>"><?php echo SITE_NAME; ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url(); ?>">Accueil</a>
                </li>

                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('offres'); ?>">Offres de stage</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('entreprises'); ?>">Entreprises</a>
                    </li>

                    <?php if (isAdmin() || isPilote()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Administration
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="<?php echo url('pilotes'); ?>">Pilotes</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="<?php echo url('etudiants'); ?>">Étudiants</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] === ROLE_ETUDIANT): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="candidaturesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Mes candidatures
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="candidaturesDropdown">
                                <li><a class="dropdown-item" href="<?php echo url('candidatures', 'afficher-wishlist'); ?>">Ma liste de souhaits</a></li>
                                <li><a class="dropdown-item" href="<?php echo url('candidatures', 'mes-candidatures'); ?>">Mes candidatures</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['prenom'] . ' ' . $_SESSION['nom']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo url('auth', 'logout'); ?>">Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('auth', 'login'); ?>">Connexion</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Contenu principal -->
<main class="py-4">