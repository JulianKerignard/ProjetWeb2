<?php
// Vue pour l'affichage de la liste des entreprises
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><?php echo $pageTitle; ?></h1>
            <?php if (checkAccess('entreprise_creer')): ?>
                <a href="<?php echo url('entreprises', 'creer'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter une entreprise
                </a>
            <?php endif; ?>
        </div>

        <!-- Messages flash -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['flash_message']['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <?php if (isset($dbError) && $dbError): ?>
            <!-- Message d'erreur en cas de problème de connexion -->
            <div class="alert alert-warning" role="alert">
                <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Problème de connexion</h4>
                <p>Impossible de se connecter à la base de données. Les fonctionnalités liées aux entreprises sont temporairement indisponibles.</p>
                <hr>
                <p class="mb-0">Veuillez vérifier que votre serveur de base de données est démarré et correctement configuré.</p>
            </div>
        <?php endif; ?>

        <!-- Formulaire de recherche -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>Recherche d'entreprises</h5>
                    <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-expanded="false">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse <?php echo !empty($_GET['nom']) || isset($_GET['with_offres']) ? 'show' : ''; ?>" id="searchCollapse">
                <div class="card-body">
                    <form id="filter-form" action="<?php echo url('entreprises', 'rechercher'); ?>" method="get" class="row g-3">
                        <input type="hidden" name="page" value="entreprises">
                        <input type="hidden" name="action" value="rechercher">

                        <!-- Filtre par nom -->
                        <div class="col-md-6">
                            <label for="nom" class="form-label">Nom de l'entreprise</label>
                            <input type="text" class="form-control" id="nom" name="nom"
                                   value="<?php echo isset($_GET['nom']) ? htmlspecialchars($_GET['nom']) : ''; ?>"
                                   placeholder="Rechercher...">
                        </div>

                        <!-- Filtre entreprises avec offres -->
                        <div class="col-md-6">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="with_offres" name="with_offres" value="1"
                                    <?php echo isset($_GET['with_offres']) && $_GET['with_offres'] == '1' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="with_offres">
                                    Uniquement les entreprises avec des offres
                                </label>
                            </div>
                        </div>

                        <!-- Tri des résultats -->
                        <div class="col-md-6">
                            <label for="order_by" class="form-label">Trier par</label>
                            <div class="input-group">
                                <select class="form-select" id="order_by" name="order_by">
                                    <option value="e.nom" <?php echo (isset($_GET['order_by']) && $_GET['order_by'] == 'e.nom') ? 'selected' : ''; ?>>Nom</option>
                                    <option value="nb_offres" <?php echo (isset($_GET['order_by']) && $_GET['order_by'] == 'nb_offres') ? 'selected' : ''; ?>>Nombre d'offres</option>
                                    <option value="moyenne_evaluations" <?php echo (isset($_GET['order_by']) && $_GET['order_by'] == 'moyenne_evaluations') ? 'selected' : ''; ?>>Évaluation moyenne</option>
                                </select>
                                <select class="form-select" id="order_dir" name="order_dir">
                                    <option value="ASC" <?php echo (isset($_GET['order_dir']) && $_GET['order_dir'] == 'ASC') ? 'selected' : ''; ?>>Croissant</option>
                                    <option value="DESC" <?php echo (isset($_GET['order_dir']) && $_GET['order_dir'] == 'DESC') ? 'selected' : ''; ?>>Décroissant</option>
                                </select>
                            </div>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="col-md-6 text-end align-self-end">
                            <button type="button" id="clear-filters" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-eraser me-1"></i>Effacer les filtres
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Rechercher
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Liste des entreprises -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i>Liste des entreprises
                    <span class="text-muted">(<?php echo $totalEntreprises; ?> résultats)</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($entreprises)): ?>
                    <div class="alert alert-info m-3">
                        <?php if (isset($dbError) && $dbError): ?>
                            Les données des entreprises ne sont pas disponibles en raison du problème de connexion.
                        <?php else: ?>
                            Aucune entreprise ne correspond à vos critères de recherche.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                            <tr>
                                <th>Entreprise</th>
                                <th>Contact</th>
                                <th class="text-center">Offres</th>
                                <th class="text-center">Évaluation</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($entreprises as $entreprise): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo url('entreprises', 'detail', ['id' => $entreprise['id']]); ?>" class="fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($entreprise['nom']); ?>
                                        </a>
                                        <?php if (!empty($entreprise['description'])): ?>
                                            <div class="text-muted small">
                                                <?php
                                                // Limiter la description à 100 caractères
                                                $description = htmlspecialchars($entreprise['description']);
                                                echo strlen($description) > 100 ? substr($description, 0, 100) . '...' : $description;
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($entreprise['email'])): ?>
                                            <div><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($entreprise['email']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($entreprise['telephone'])): ?>
                                            <div><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($entreprise['telephone']); ?></div>
                                        <?php endif; ?>
                                        <?php if (empty($entreprise['email']) && empty($entreprise['telephone'])): ?>
                                            <span class="text-muted">Aucune information de contact</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo url('offres', 'rechercher', ['entreprise_id' => $entreprise['id']]); ?>" class="badge bg-primary text-decoration-none">
                                            <?php echo $entreprise['nb_offres']; ?> offre(s)
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($entreprise['moyenne_evaluations']): ?>
                                            <div class="d-flex justify-content-center align-items-center">
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
                                                <span class="small"><?php echo number_format($entreprise['moyenne_evaluations'], 1); ?>/5</span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Non évalué</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="<?php echo url('entreprises', 'detail', ['id' => $entreprise['id']]); ?>" class="btn btn-sm btn-outline-primary" title="Détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (checkAccess('entreprise_modifier')): ?>
                                                <a href="<?php echo url('entreprises', 'modifier', ['id' => $entreprise['id']]); ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkAccess('entreprise_supprimer')): ?>
                                                <a href="<?php echo url('entreprises', 'supprimer', ['id' => $entreprise['id']]); ?>" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($totalEntreprises > ITEMS_PER_PAGE): ?>
                <div class="card-footer bg-white">
                    <?php
                    // Génération de la pagination
                    $currentUrl = isset($_GET['action']) && $_GET['action'] == 'rechercher' ? 'rechercher' : 'index';
                    echo pagination($totalEntreprises, $page, 'entreprises', $currentUrl, $_GET);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestionnaire pour effacer les filtres - ce script sera remplacé par le JS global
            // Nous le laissons ici comme référence et pour rétrocompatibilité
            document.getElementById('clear-filters').addEventListener('click', function() {
                // Réinitialiser tous les champs du formulaire
                document.getElementById('nom').value = '';
                document.getElementById('with_offres').checked = false;
                document.getElementById('order_by').selectedIndex = 0;
                document.getElementById('order_dir').selectedIndex = 0;

                // Soumettre le formulaire
                document.getElementById('filter-form').submit();
            });
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>