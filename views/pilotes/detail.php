<?php
// Vue pour l'affichage détaillé d'un pilote
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('pilotes'); ?>">Pilotes</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></li>
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
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-tie me-2"></i>Détail du pilote
                        </h5>
                    </div>
                    <div class="card-body">
                        <h2 class="h3 mb-4"><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></h2>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-envelope me-2"></i>Email:</strong>
                                    <a href="mailto:<?php echo htmlspecialchars($pilote['email']); ?>">
                                        <?php echo htmlspecialchars($pilote['email']); ?>
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-calendar-alt me-2"></i>Date de création:</strong>
                                    <?php echo (new DateTime($pilote['created_at']))->format('d/m/Y H:i'); ?>
                                </p>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <p><strong><i class="fas fa-building me-2"></i>Centre:</strong>
                                    <?php if (!empty($pilote['centre_nom'])): ?>
                                        <?php echo htmlspecialchars($pilote['centre_nom']); ?> (<?php echo htmlspecialchars($pilote['centre_code']); ?>)
                                    <?php else: ?>
                                        <span class="text-muted">Non assigné</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?php echo url('pilotes'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
                            </a>

                            <div>
                                <a href="<?php echo url('pilotes', 'etudiants', ['id' => $pilote['id']]); ?>" class="btn btn-outline-info me-2">
                                    <i class="fas fa-user-graduate me-1"></i>Étudiants assignés
                                </a>

                                <?php if (isAdmin()): ?>
                                    <a href="<?php echo url('pilotes', 'modifier', ['id' => $pilote['id']]); ?>" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-edit me-2"></i>Modifier
                                    </a>
                                    <a href="<?php echo url('pilotes', 'supprimer', ['id' => $pilote['id']]); ?>" class="btn btn-outline-danger">
                                        <i class="fas fa-trash me-2"></i>Supprimer
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>