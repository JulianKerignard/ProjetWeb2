<?php
// Vue pour l'affichage détaillé d'une candidature
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('candidatures', 'mes-candidatures'); ?>">Mes candidatures</a></li>
                <li class="breadcrumb-item active" aria-current="page">Détail de la candidature</li>
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
            <!-- Informations de la candidature -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>Détail de la candidature
                        </h5>
                    </div>
                    <div class="card-body">
                        <h2 class="h4 mb-3"><?php echo htmlspecialchars($candidature['offre_titre']); ?></h2>

                        <div class="mb-4">
                            <p class="text-muted">
                                <i class="fas fa-building me-2"></i>
                                <strong>Entreprise:</strong> <?php echo htmlspecialchars($candidature['entreprise_nom']); ?>
                            </p>
                            <p class="text-muted">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <strong>Date de candidature:</strong> <?php echo (new DateTime($candidature['date_candidature']))->format('d/m/Y à H:i'); ?>
                            </p>
                        </div>

                        <h5 class="mt-4 mb-3">Lettre de motivation</h5>
                        <div class="card bg-light">
                            <div class="card-body">
                                <?php echo nl2br(htmlspecialchars($candidature['lettre_motivation'])); ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?php echo url('candidatures', 'mes-candidatures'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour
                            </a>

                            <div>
                                <?php if (!empty($candidature['cv'])): ?>
                                    <a href="<?php echo URL_ROOT . '/public/uploads/' . $candidature['cv']; ?>" class="btn btn-outline-primary me-2" target="_blank">
                                        <i class="fas fa-file-pdf me-2"></i>Voir le CV
                                    </a>
                                <?php endif; ?>

                                <a href="<?php echo url('offres', 'detail', ['id' => $candidature['offre_id']]); ?>" class="btn btn-outline-info me-2">
                                    <i class="fas fa-eye me-2"></i>Voir l'offre
                                </a>

                                <a href="<?php echo url('candidatures', 'supprimer', ['id' => $candidature['id']]); ?>" class="btn btn-outline-danger">
                                    <i class="fas fa-trash me-2"></i>Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations complémentaires -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Informations sur l'offre
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Période:</strong><br>
                            <?php
                            $dateDebut = new DateTime($candidature['offre_description']['date_debut']);
                            $dateFin = new DateTime($candidature['offre_description']['date_fin']);
                            echo $dateDebut->format('d/m/Y') . ' - ' . $dateFin->format('d/m/Y');
                            ?>
                        </p>

                        <?php if (!empty($candidature['offre_description']['remuneration'])): ?>
                            <p class="mb-2">
                                <strong>Rémunération:</strong><br>
                                <?php echo number_format($candidature['offre_description']['remuneration'], 2, ',', ' '); ?> €
                            </p>
                        <?php endif; ?>

                        <p class="mb-0">
                            <strong>Description:</strong><br>
                            <?php echo mb_substr(strip_tags($candidature['offre_description']['description']), 0, 150) . '...'; ?>
                        </p>

                        <a href="<?php echo url('offres', 'detail', ['id' => $candidature['offre_id']]); ?>" class="btn btn-sm btn-outline-primary mt-3 w-100">
                            <i class="fas fa-external-link-alt me-1"></i>Voir l'offre complète
                        </a>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if (!empty($candidature['cv'])): ?>
                                <a href="<?php echo URL_ROOT . '/public/uploads/' . $candidature['cv']; ?>" class="btn btn-outline-primary" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i>Télécharger mon CV
                                </a>
                            <?php endif; ?>

                            <a href="<?php echo url('candidatures', 'supprimer', ['id' => $candidature['id']]); ?>" class="btn btn-outline-danger">
                                <i class="fas fa-trash me-2"></i>Supprimer cette candidature
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>