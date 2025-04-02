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
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-circle me-2"></i>Informations de profil
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="profile-avatar-container mb-3">
                                    <div class="profile-avatar">
                                        <?php
                                        // Générer une couleur basée sur le nom de l'utilisateur pour l'avatar
                                        $hash = md5($userProfile['email']);
                                        $color = '#' . substr($hash, 0, 6);
                                        $initialsFirstName = mb_substr($userProfile['prenom'], 0, 1);
                                        $initialsLastName = mb_substr($userProfile['nom'], 0, 1);
                                        ?>
                                        <div class="profile-initials" style="background-color: <?php echo $color; ?>">
                                            <?php echo strtoupper($initialsFirstName . $initialsLastName); ?>
                                        </div>
                                    </div>
                                </div>
                                <h4><?php echo htmlspecialchars($userProfile['prenom'] . ' ' . $userProfile['nom']); ?></h4>
                                <p class="badge bg-<?php
                                echo ($_SESSION['role'] === 'admin') ? 'danger' :
                                    (($_SESSION['role'] === 'pilote') ? 'success' : 'primary');
                                ?>">
                                    <?php
                                    echo ($_SESSION['role'] === 'admin') ? 'Administrateur' :
                                        (($_SESSION['role'] === 'pilote') ? 'Pilote' : 'Étudiant');
                                    ?>
                                </p>

                                <a href="<?php echo url('profile', 'edit'); ?>" class="btn btn-outline-primary btn-sm w-100 mt-2">
                                    <i class="fas fa-edit me-1"></i>Modifier mon profil
                                </a>
                            </div>
                            <div class="col-md-8">
                                <table class="table table-hover">
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
                                                <span class="badge bg-primary"><?php echo $userProfile['nb_candidatures']; ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><i class="fas fa-heart me-2"></i>Liste de souhaits :</th>
                                            <td>
                                                <span class="badge bg-danger"><?php echo $userProfile['nb_wishlist']; ?></span>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($_SESSION['role'] === ROLE_ETUDIANT): ?>
                    <!-- Dernières activités -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Aperçu de mon activité
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (isset($userProfile['activite_recente']) && !empty($userProfile['activite_recente'])): ?>
                                <div class="timeline">
                                    <?php foreach($userProfile['activite_recente'] as $activite): ?>
                                        <div class="timeline-item">
                                            <div class="timeline-date"><?php echo (new DateTime($activite['date']))->format('d/m/Y'); ?></div>
                                            <div class="timeline-content">
                                                <div class="timeline-icon bg-<?php echo $activite['type'] === 'candidature' ? 'primary' : 'danger'; ?>">
                                                    <i class="fas <?php echo $activite['type'] === 'candidature' ? 'fa-file-alt' : 'fa-heart'; ?>"></i>
                                                </div>
                                                <div class="timeline-text">
                                                    <?php echo $activite['message']; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                                    <p class="mb-0">Aucune activité récente à afficher.</p>
                                    <p class="text-muted">Commencez à postuler aux offres ou à ajouter des offres à vos favoris.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar avec raccourcis et statistiques -->
            <div class="col-lg-4">
                <?php if ($_SESSION['role'] === ROLE_ETUDIANT): ?>
                    <!-- Statistiques du profil -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Mes statistiques</h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="stat-circle">
                                        <span class="stat-number"><?php echo $userProfile['nb_candidatures']; ?></span>
                                    </div>
                                    <div class="stat-label">Candidatures</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="stat-circle">
                                        <span class="stat-number"><?php echo $userProfile['nb_wishlist']; ?></span>
                                    </div>
                                    <div class="stat-label">Favoris</div>
                                </div>
                            </div>

                            <?php if (isset($userProfile['nb_candidatures']) && $userProfile['nb_candidatures'] > 0): ?>
                                <div class="progress mb-3" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo min(100, $userProfile['nb_candidatures'] * 10); ?>%;"
                                         aria-valuenow="<?php echo $userProfile['nb_candidatures']; ?>" aria-valuemin="0" aria-valuemax="10">
                                        <?php echo $userProfile['nb_candidatures']; ?>/10
                                    </div>
                                </div>
                                <p class="small text-muted">Objectif recommandé: 10 candidatures</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Actions rapides -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions rapides</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="<?php echo url('candidatures', 'mes-candidatures'); ?>" class="btn btn-primary btn-lg">
                                    <i class="fas fa-file-alt me-2"></i>Mes candidatures
                                    <span class="badge bg-white text-primary ms-2"><?php echo $userProfile['nb_candidatures']; ?></span>
                                </a>
                                <a href="<?php echo url('candidatures', 'afficher-wishlist'); ?>" class="btn btn-danger btn-lg">
                                    <i class="fas fa-heart me-2"></i>Ma liste de souhaits
                                    <span class="badge bg-white text-danger ms-2"><?php echo $userProfile['nb_wishlist']; ?></span>
                                </a>
                                <a href="<?php echo url('offres'); ?>" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-search me-2"></i>Trouver des offres
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Actions Admin/Pilote -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Outils de gestion</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if (isAdmin()): ?>
                                    <a href="<?php echo url('admin'); ?>" class="btn btn-primary btn-lg">
                                        <i class="fas fa-tachometer-alt me-2"></i>Tableau de bord Admin
                                    </a>
                                    <a href="<?php echo url('pilotes'); ?>" class="btn btn-outline-primary">
                                        <i class="fas fa-user-tie me-2"></i>Gestion des pilotes
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo url('etudiants'); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-user-graduate me-2"></i>Gestion des étudiants
                                </a>
                                <a href="<?php echo url('offres', 'statistiques'); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-chart-pie me-2"></i>Statistiques des offres
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Dernières activités du site -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-rss me-2"></i>Activité récente du site</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (isset($actualites) && !empty($actualites)): ?>
                                <?php foreach ($actualites as $actualite): ?>
                                    <a href="<?php echo $actualite['url']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo $actualite['titre']; ?></h6>
                                            <small class="text-muted"><?php echo $actualite['date']; ?></small>
                                        </div>
                                        <p class="mb-1 small"><?php echo $actualite['description']; ?></p>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="list-group-item text-center py-3">
                                    <p class="mb-0 text-muted">Aucune actualité récente</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CSS personnalisé pour le profil -->
    <style>
        /* Avatar de profil */
        .profile-avatar-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }

        .profile-avatar {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .profile-initials {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
        }

        /* Statistiques */
        .stat-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 10px;
            background-color: #f8f9fa;
            border: 3px solid #dee2e6;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #495057;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding-left: 30px;
            margin-bottom: 20px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #e9ecef;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-date {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .timeline-content {
            display: flex;
            align-items: flex-start;
        }

        .timeline-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .timeline-text {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 5px;
            flex-grow: 1;
        }
    </style>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>