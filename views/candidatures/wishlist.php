<?php
// Vue pour l'affichage de la wishlist de l'étudiant
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><i class="fas fa-heart me-2"></i><?php echo $pageTitle; ?></h1>
            <a href="<?php echo url('candidatures', 'mes-candidatures'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-file-alt me-2"></i>Mes candidatures
            </a>
        </div>

        <!-- Messages flash -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['flash_message']['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <!-- Liste des offres en favoris -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-star me-2"></i>Offres favorites
                    <?php if (isset($totalWishlist)): ?>
                        <span class="text-muted">(<?php echo $totalWishlist; ?> offres)</span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($wishlist)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle me-2"></i>Vous n'avez pas encore ajouté d'offres à vos favoris.
                        <div class="mt-3">
                            <a href="<?php echo url('offres'); ?>" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Découvrir les offres de stage
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                            <tr>
                                <th>Offre</th>
                                <th>Entreprise</th>
                                <th>Période</th>
                                <th>Ajouté le</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($wishlist as $item): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo url('offres', 'detail', ['id' => $item['offre_id']]); ?>" class="text-decoration-none fw-medium">
                                            <?php echo htmlspecialchars($item['offre_titre']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['entreprise_nom']); ?></td>
                                    <td>
                                        <?php
                                        $dateDebut = new DateTime($item['date_debut']);
                                        $dateFin = new DateTime($item['date_fin']);
                                        echo $dateDebut->format('d/m/Y') . ' - ' . $dateFin->format('d/m/Y');
                                        ?>
                                    </td>
                                    <td><?php echo (new DateTime($item['date_ajout']))->format('d/m/Y'); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="<?php echo url('offres', 'detail', ['id' => $item['offre_id']]); ?>" class="btn btn-sm btn-outline-primary" title="Voir l'offre">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo url('candidatures', 'postuler', ['offre_id' => $item['offre_id']]); ?>" class="btn btn-sm btn-outline-success" title="Postuler à cette offre">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                            <a href="<?php echo url('candidatures', 'retirer-wishlist', ['offre_id' => $item['offre_id']]); ?>" class="btn btn-sm btn-outline-danger" title="Retirer des favoris">
                                                <i class="fas fa-heart-broken"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (isset($totalWishlist) && $totalWishlist > ITEMS_PER_PAGE): ?>
                <div class="card-footer bg-white">
                    <?php
                    // Génération de la pagination
                    echo pagination($totalWishlist, $page, 'candidatures', 'afficher-wishlist', []);
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recommandations d'offres similaires -->
        <?php if (!empty($wishlist) && !empty($recommendedOffers)): ?>
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Offres recommandées
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($recommendedOffers as $offre): ?>
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-2">
                                            <a href="<?php echo url('offres', 'detail', ['id' => $offre['id']]); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($offre['titre']); ?>
                                            </a>
                                        </h6>
                                        <p class="card-text small text-muted mb-2"><?php echo htmlspecialchars($offre['entreprise_nom']); ?></p>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <span class="badge bg-primary">
                                                <?php echo (new DateTime($offre['date_debut']))->format('d/m/Y'); ?>
                                            </span>
                                            <div>
                                                <?php if (!$offre['in_wishlist']): ?>
                                                    <a href="<?php echo url('candidatures', 'ajouter-wishlist', ['offre_id' => $offre['id']]); ?>" class="btn btn-sm btn-outline-primary me-1" title="Ajouter aux favoris">
                                                        <i class="far fa-heart"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?php echo url('offres', 'detail', ['id' => $offre['id']]); ?>" class="btn btn-sm btn-outline-secondary" title="Voir l'offre">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>