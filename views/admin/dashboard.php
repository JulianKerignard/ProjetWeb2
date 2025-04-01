<?php
/**
 * Vue du tableau de bord principal d'administration
 *
 * Affiche un résumé des statistiques principales et des actions rapides
 * pour la gestion globale du système.
 */
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">
                <i class="fas fa-tachometer-alt me-2"></i>
                <?php echo $pageTitle; ?>
            </h1>

            <div>
                <a href="<?php echo url('admin', 'stats'); ?>" class="btn btn-primary">
                    <i class="fas fa-chart-line me-2"></i>Statistiques détaillées
                </a>
                <a href="<?php echo url('admin', 'logs'); ?>" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-history me-2"></i>Journaux d'activité
                </a>
            </div>
        </div>

        <!-- Messages flash -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['flash_message']['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <!-- Cartes de statistiques -->
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Offres de stage</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_offres']; ?></div>
                                <small class="text-muted">(<?php echo $stats['offres_actives']; ?> actives)</small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="<?php echo url('offres'); ?>" class="btn btn-sm btn-link text-primary">Voir les offres <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Entreprises</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_entreprises']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-building fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="<?php echo url('entreprises'); ?>" class="btn btn-sm btn-link text-success">Voir les entreprises <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Étudiants</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_etudiants']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="<?php echo url('etudiants'); ?>" class="btn btn-sm btn-link text-info">Voir les étudiants <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pilotes</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_pilotes']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0">
                        <a href="<?php echo url('pilotes'); ?>" class="btn btn-sm btn-link text-warning">Voir les pilotes <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu d'actions administratives rapides -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <a href="<?php echo url('entreprises', 'creer'); ?>" class="btn btn-outline-primary btn-block w-100 py-2">
                                    <i class="fas fa-plus-circle me-2"></i>Ajouter une entreprise
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <a href="<?php echo url('offres', 'creer'); ?>" class="btn btn-outline-primary btn-block w-100 py-2">
                                    <i class="fas fa-plus-circle me-2"></i>Ajouter une offre
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <a href="<?php echo url('pilotes', 'creer'); ?>" class="btn btn-outline-primary btn-block w-100 py-2">
                                    <i class="fas fa-plus-circle me-2"></i>Ajouter un pilote
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <a href="<?php echo url('etudiants', 'creer'); ?>" class="btn btn-outline-primary btn-block w-100 py-2">
                                    <i class="fas fa-plus-circle me-2"></i>Ajouter un étudiant
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <a href="<?php echo url('admin', 'stats'); ?>" class="btn btn-outline-info btn-block w-100 py-2">
                                    <i class="fas fa-chart-bar me-2"></i>Voir les statistiques
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <a href="<?php echo url('admin', 'permissions'); ?>" class="btn btn-outline-info btn-block w-100 py-2">
                                    <i class="fas fa-user-shield me-2"></i>Gérer les permissions
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <a href="<?php echo url('admin', 'logs'); ?>" class="btn btn-outline-info btn-block w-100 py-2">
                                    <i class="fas fa-history me-2"></i>Consulter les logs
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                                <a href="<?php echo url('admin', 'maintenance'); ?>" class="btn btn-outline-info btn-block w-100 py-2">
                                    <i class="fas fa-tools me-2"></i>Maintenance
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières offres et entreprises -->
        <div class="row">
            <!-- Dernières offres -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Dernières offres ajoutées</h5>
                            <a href="<?php echo url('offres'); ?>" class="btn btn-sm btn-link">Voir tout</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($latestOffers)): ?>
                                <div class="list-group-item">
                                    <p class="text-muted mb-0">Aucune offre disponible.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($latestOffers as $offre): ?>
                                    <a href="<?php echo url('offres', 'detail', ['id' => $offre['id']]); ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($offre['titre']); ?></h6>
                                            <small class="text-muted"><?php echo date('d/m/Y', strtotime($offre['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($offre['entreprise_nom']); ?></p>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dernières entreprises -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-building me-2"></i>Dernières entreprises ajoutées</h5>
                            <a href="<?php echo url('entreprises'); ?>" class="btn btn-sm btn-link">Voir tout</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($latestCompanies)): ?>
                                <div class="list-group-item">
                                    <p class="text-muted mb-0">Aucune entreprise disponible.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($latestCompanies as $entreprise): ?>
                                    <a href="<?php echo url('entreprises', 'detail', ['id' => $entreprise['id']]); ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($entreprise['nom']); ?></h6>
                                            <small class="text-muted"><?php echo date('d/m/Y', strtotime($entreprise['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo $entreprise['nb_offres']; ?> offre(s)</p>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .border-left-primary {
            border-left: .25rem solid #4e73df!important;
        }
        .border-left-success {
            border-left: .25rem solid #1cc88a!important;
        }
        .border-left-info {
            border-left: .25rem solid #36b9cc!important;
        }
        .border-left-warning {
            border-left: .25rem solid #f6c23e!important;
        }
        .text-xs {
            font-size: .7rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .text-gray-300 { color: #dddfeb!important; }
        .text-gray-800 { color: #5a5c69!important; }
    </style>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>