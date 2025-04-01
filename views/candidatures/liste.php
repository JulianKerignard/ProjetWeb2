<?php
// Vue pour l'affichage des candidatures de l'étudiant
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><i class="fas fa-file-alt me-2"></i><?php echo $pageTitle; ?></h1>
            <a href="<?php echo url('candidatures', 'afficher-wishlist'); ?>" class="btn btn-outline-primary">
                <i class="fas fa-heart me-2"></i>Ma liste de souhaits
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

        <!-- Filtres de recherche -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
                    <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    <form action="<?php echo url('candidatures', 'mes-candidatures'); ?>" method="get" class="row g-3">
                        <input type="hidden" name="page" value="candidatures">
                        <input type="hidden" name="action" value="mes-candidatures">

                        <!-- Filtre par date de candidature -->
                        <div class="col-md-4">
                            <label for="date_debut" class="form-label">Date de candidature (début)</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut"
                                   value="<?php echo isset($_GET['date_debut']) ? htmlspecialchars($_GET['date_debut']) : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="date_fin" class="form-label">Date de candidature (fin)</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin"
                                   value="<?php echo isset($_GET['date_fin']) ? htmlspecialchars($_GET['date_fin']) : ''; ?>">
                        </div>

                        <!-- Boutons d'action -->
                        <div class="col-md-4 align-self-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Liste des candidatures -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-list me-2"></i>Mes candidatures
                    <?php if (isset($totalCandidatures)): ?>
                        <span class="text-muted">(<?php echo $totalCandidatures; ?> candidatures)</span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($candidatures)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle me-2"></i>Vous n'avez pas encore postulé à des offres de stage.
                        <div class="mt-3">
                            <a href="<?php echo url('offres'); ?>" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Consulter les offres de stage
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
                                <th>Date de candidature</th>
                                <th>CV</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($candidatures as $candidature): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo url('offres', 'detail', ['id' => $candidature['offre_id']]); ?>" class="text-decoration-none fw-medium">
                                            <?php echo htmlspecialchars($candidature['offre_titre']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($candidature['entreprise_nom']); ?></td>
                                    <td><?php echo (new DateTime($candidature['date_candidature']))->format('d/m/Y H:i'); ?></td>
                                    <td>
                                        <?php if (!empty($candidature['cv'])): ?>
                                            <a href="<?php echo URL_ROOT . '/public/uploads/' . $candidature['cv']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="<?php echo url('offres', 'detail', ['id' => $candidature['offre_id']]); ?>" class="btn btn-sm btn-outline-primary" title="Voir l'offre">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo url('candidatures', 'detail', ['id' => $candidature['id']]); ?>" class="btn btn-sm btn-outline-info" title="Détails de la candidature">
                                                <i class="fas fa-file-alt"></i>
                                            </a>
                                            <a href="<?php echo url('candidatures', 'supprimer', ['id' => $candidature['id']]); ?>" class="btn btn-sm btn-outline-danger" title="Supprimer la candidature">
                                                <i class="fas fa-trash"></i>
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
            <?php if (isset($totalCandidatures) && $totalCandidatures > ITEMS_PER_PAGE): ?>
                <div class="card-footer bg-white">
                    <?php
                    // Génération de la pagination
                    $currentFilters = [];
                    if (isset($_GET['date_debut'])) $currentFilters['date_debut'] = $_GET['date_debut'];
                    if (isset($_GET['date_fin'])) $currentFilters['date_fin'] = $_GET['date_fin'];

                    echo pagination($totalCandidatures, $page, 'candidatures', 'mes-candidatures', $currentFilters);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>