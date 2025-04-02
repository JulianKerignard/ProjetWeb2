<?php
// Vue pour la confirmation de suppression d'une offre
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('offres'); ?>">Offres de stage</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('offres', 'detail', ['id' => $offre['id']]); ?>"><?php echo htmlspecialchars($offre['titre']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Supprimer</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Confirmation de suppression
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0 list-unstyled">
                                    <?php foreach ($errors as $error): ?>
                                        <li><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-warning">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Attention !
                            </h5>
                            <p>Vous êtes sur le point de supprimer l'offre <strong><?php echo htmlspecialchars($offre['titre']); ?></strong>.</p>
                            <p class="mb-0">Cette action est irréversible. Toutes les candidatures et références à cette offre seront également supprimées.</p>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?php echo url('offres', 'detail', ['id' => $offre['id']]); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Annuler
                            </a>
                            <a href="<?php echo url('offres', 'supprimer', ['id' => $offre['id'], 'confirm' => 1]); ?>" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Confirmer la suppression
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>