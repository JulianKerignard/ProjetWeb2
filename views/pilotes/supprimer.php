<?php
// Vue pour la confirmation de suppression d'un pilote
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('pilotes'); ?>">Pilotes</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('pilotes', 'detail', ['id' => $pilote['id']]); ?>"><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></a></li>
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
                        <div class="alert alert-warning">
                            <h5 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Attention !
                            </h5>
                            <p>Vous êtes sur le point de supprimer le pilote <strong><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></strong>.</p>
                            <p class="mb-0">Cette action est irréversible. Le compte utilisateur associé sera également supprimé.</p>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?php echo url('pilotes', 'detail', ['id' => $pilote['id']]); ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Annuler
                            </a>
                            <a href="<?php echo url('pilotes', 'supprimer', ['id' => $pilote['id'], 'confirm' => 1]); ?>" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>Confirmer la suppression
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>