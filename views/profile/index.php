<?php
// Vue pour l'affichage du profil utilisateur
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Mon Profil</li>
            </ol>
        </nav>

        <!-- Messages flash -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['flash_message']['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="row">
            <!-- Informations du profil -->
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle me-2"></i>Informations de profil
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="profile-img">
                                    <i class="fas fa-user-circle fa-6x text-primary"></i>
                                </div>
                                <h4 class="mt-3"><?php echo htmlspecialchars($userProfile['prenom'] . ' ' . $userProfile['nom']); ?></h4>
                                <p class="badge bg-<?php
                                echo ($_SESSION['role'] === 'admin') ? 'danger' :
                                    (($_SESSION['role'] === 'pilote') ? 'success' : 'primary');
                                ?>">
                                    <?php
                                    echo ($_SESSION['role'] === 'admin') ? 'Administrateur' :
                                        (($_SESSION['role'] === 'pilote') ? 'Pilote' : 'Étudiant');
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-8">
                                <table class="table">
                                    <tr>
                                        <th><i class="fas fa-user me-2"></i>Nom :</th>
                                        <td><?php echo htmlspecialchars($userProfile['nom']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-user me-2"></i>Prénom :</th>
                                        <td><?php echo htmlspecialchars($userProfile['prenom']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-envelope me-2"></i>Email :</th>
                                        <td><?php echo htmlspecialchars($userProfile['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th><i class="fas fa-calendar-alt me-2"></i>Inscrit le :</th>
                                        <td><?php echo (new DateTime($userProfile['created_at']))->format('d/m/Y'); ?></td>
                                    </tr>
                                    <?php if ($_SESSION['role'] === ROLE_ETUDIANT && isset($userProfile['nb_candidatures'])): ?>
                                        <tr>
                                            <th><i class="fas fa-file-alt me-2"></i>Candidatures :</th>
                                            <td>
                                                <a href="<?php echo url('candidatures', 'mes-candidatures'); ?>">
                                                    <?php echo $userProfile['nb_candidatures']; ?> candidature(s)
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><i class="fas fa-heart me-2"></i>Liste de souhaits :</th>
                                            <td>
                                                <a href="<?php echo url('candidatures', 'afficher-wishlist'); ?>">
                                                    <?php echo $userProfile['nb_wishlist']; ?> offre(s)
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </table>

                                <div class="mt-4">
                                    <a href="<?php echo url('profile', 'edit'); ?>" class="btn btn-primary">
                                        <i class="fas fa-edit me-2"></i>Modifier mon profil
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>