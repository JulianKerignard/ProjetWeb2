<?php
// Vue pour l'affichage détaillé d'une entreprise
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4 detail-view">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('entreprises'); ?>">Entreprises</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($entreprise['nom']); ?></li>
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
            <!-- Détail de l'entreprise -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-building me-2"></i>Détail de l'entreprise
                        </h5>
                    </div>
                    <div class="card-body">
                        <h1 class="h2 mb-3 detail-text"><?php echo htmlspecialchars($entreprise['nom']); ?></h1>

                        <?php if (!empty($entreprise['description'])): ?>
                            <div class="mb-4">
                                <h5><i class="fas fa-info-circle me-2"></i>Description:</h5>
                                <p class="detail-text"><?php echo nl2br(htmlspecialchars($entreprise['description'])); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5><i class="fas fa-address-card me-2"></i>Contact:</h5>
                                <ul class="list-unstyled">
                                    <?php if (!empty($entreprise['email'])): ?>
                                        <li class="mb-2 detail-text">
                                            <i class="fas fa-envelope me-2"></i>
                                            <a href="mailto:<?php echo htmlspecialchars($entreprise['email']); ?>">
                                                <?php echo htmlspecialchars($entreprise['email']); ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (!empty($entreprise['telephone'])): ?>
                                        <li class="mb-2 detail-text">
                                            <i class="fas fa-phone me-2"></i>
                                            <a href="tel:<?php echo htmlspecialchars(str_replace(' ', '', $entreprise['telephone'])); ?>">
                                                <?php echo htmlspecialchars($entreprise['telephone']); ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if (empty($entreprise['email']) && empty($entreprise['telephone'])): ?>
                                        <li><em class="text-muted">Aucune information de contact disponible</em></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="fas fa-star me-2"></i>Évaluation:</h5>
                                <?php if ($entreprise['moyenne_evaluations']): ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="stars me-2">
                                            <?php
                                            $rating = round($entreprise['moyenne_evaluations']);
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating) {
                                                    echo '<i class="fas fa-star text-warning"></i>';
                                                } else {
                                                    echo '<i class="far fa-star text-muted"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <span class="fw-bold"><?php echo number_format($entreprise['moyenne_evaluations'], 1); ?>/5</span>
                                        <span class="text-muted ms-2">(<?php echo $entreprise['nb_evaluations']; ?> avis)</span>
                                    </div>
                                    <?php if (checkAccess('entreprise_evaluer')): ?>
                                        <a href="<?php echo url('entreprises', 'evaluer', ['id' => $entreprise['id']]); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-star me-1"></i>Évaluer cette entreprise
                                        </a>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="text-muted">Aucune évaluation pour le moment.</p>
                                    <?php if (checkAccess('entreprise_evaluer')): ?>
                                        <a href="<?php echo url('entreprises', 'evaluer', ['id' => $entreprise['id']]); ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-star me-1"></i>Soyez le premier à évaluer
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?php echo url('entreprises'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                            </a>

                            <div>
                                <?php if (checkAccess('entreprise_modifier')): ?>
                                    <a href="<?php echo url('entreprises', 'modifier', ['id' => $entreprise['id']]); ?>" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-edit me-2"></i>Modifier
                                    </a>
                                <?php endif; ?>

                                <?php if (checkAccess('entreprise_supprimer')): ?>
                                    <a href="<?php echo url('entreprises', 'supprimer', ['id' => $entreprise['id']]); ?>" class="btn btn-outline-danger">
                                        <i class="fas fa-trash me-2"></i>Supprimer
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-muted">
                        <i class="far fa-clock me-1"></i>Ajoutée le <?php echo (new DateTime($entreprise['created_at']))->format('d/m/Y à H:i'); ?>
                        <?php if (!empty($entreprise['updated_at']) && $entreprise['updated_at'] !== $entreprise['created_at']): ?>
                            | <i class="fas fa-edit me-1"></i>Modifiée le <?php echo (new DateTime($entreprise['updated_at']))->format('d/m/Y à H:i'); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Offres de stage de l'entreprise -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-clipboard-list me-2"></i>Offres de stage
                            <span class="text-muted">(<?php echo count($entreprise['offres']); ?>)</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($entreprise['offres'])): ?>
                            <div class="alert alert-info">
                                Aucune offre de stage active pour cette entreprise actuellement.
                            </div>
                            <?php if (checkAccess('offre_creer')): ?>
                                <a href="<?php echo url('offres', 'creer', ['entreprise_id' => $entreprise['id']]); ?>" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-2"></i>Créer une offre pour cette entreprise
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($entreprise['offres'] as $offre): ?>
                                    <a href="<?php echo url('offres', 'detail', ['id' => $offre['id']]); ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1 detail-text"><?php echo htmlspecialchars($offre['titre']); ?></h5>
                                            <small class="text-muted">
                                                <?php
                                                $dateDebut = new DateTime($offre['date_debut']);
                                                $dateFin = new DateTime($offre['date_fin']);
                                                echo $dateDebut->format('d/m/Y') . ' - ' . $dateFin->format('d/m/Y');
                                                ?>
                                            </small>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span>
                                            <?php if (!empty($offre['remuneration']) && $offre['remuneration'] > 0): ?>
                                                <span class="badge bg-success me-2"><?php echo number_format($offre['remuneration'], 2, ',', ' '); ?> €</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary me-2">Non rémunéré</span>
                                            <?php endif; ?>
                                            <span class="badge bg-info"><?php echo $offre['nb_candidatures']; ?> candidat(s)</span>
                                        </span>
                                            <span class="text-primary small">Voir le détail <i class="fas fa-chevron-right ms-1"></i></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="mt-3">
                                <a href="<?php echo url('offres', 'rechercher', ['entreprise_id' => $entreprise['id']]); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-2"></i>Voir toutes les offres
                                </a>
                                <?php if (checkAccess('offre_creer')): ?>
                                    <a href="<?php echo url('offres', 'creer', ['entreprise_id' => $entreprise['id']]); ?>" class="btn btn-primary ms-2">
                                        <i class="fas fa-plus-circle me-2"></i>Créer une offre
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Évaluations de l'entreprise -->
                <?php if (!empty($entreprise['evaluations'])): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fas fa-comments me-2"></i>Évaluations et avis
                                <span class="text-muted">(<?php echo count($entreprise['evaluations']); ?>)</span>
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                <?php foreach ($entreprise['evaluations'] as $evaluation): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <span class="fw-bold"><?php echo htmlspecialchars($evaluation['etudiant_prenom'] . ' ' . $evaluation['etudiant_nom']); ?></span>
                                                <small class="text-muted ms-2"><?php echo (new DateTime($evaluation['created_at']))->format('d/m/Y'); ?></small>
                                            </div>
                                            <div class="stars">
                                                <?php
                                                $rating = $evaluation['note'];
                                                for ($i = 1; $i <= 5; $i++) {
                                                    if ($i <= $rating) {
                                                        echo '<i class="fas fa-star text-warning"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star text-muted"></i>';
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <p class="mb-0 detail-text"><?php echo nl2br(htmlspecialchars($evaluation['commentaire'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar avec informations complémentaires -->
            <div class="col-lg-4">
                <!-- Méta-informations -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Offres actives
                                <span class="badge bg-primary rounded-pill"><?php echo count($entreprise['offres']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Évaluations
                                <span class="badge bg-primary rounded-pill"><?php echo $entreprise['nb_evaluations']; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Actions rapides</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="<?php echo url('offres', 'rechercher', ['entreprise_id' => $entreprise['id']]); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-clipboard-list me-2"></i>Voir toutes les offres
                            </a>
                            <?php if (checkAccess('offre_creer')): ?>
                                <a href="<?php echo url('offres', 'creer', ['entreprise_id' => $entreprise['id']]); ?>" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-2"></i>Créer une offre
                                </a>
                            <?php endif; ?>
                            <?php if (checkAccess('entreprise_evaluer')): ?>
                                <a href="<?php echo url('entreprises', 'evaluer', ['id' => $entreprise['id']]); ?>" class="btn btn-outline-warning">
                                    <i class="fas fa-star me-2"></i>Évaluer cette entreprise
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Entreprises similaires -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Explorer d'autres entreprises</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">Découvrez d'autres entreprises proposant des stages :</p>
                        <div class="d-grid">
                            <a href="<?php echo url('entreprises'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-list me-2"></i>Voir toutes les entreprises
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>