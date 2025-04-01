<?php
// Vue pour l'affichage de la liste des pilotes
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><?php echo $pageTitle; ?></h1>
            <?php if (isAdmin()): ?>
                <a href="<?php echo url('pilotes', 'creer'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter un pilote
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

        <!-- Formulaire de recherche -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>Recherche</h5>
                    <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-expanded="false">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse <?php echo !empty($filters) ? 'show' : ''; ?>" id="searchCollapse">
                <div class="card-body">
                    <form id="filter-form" action="<?php echo url('pilotes', 'rechercher'); ?>" method="get" class="row g-3">
                        <input type="hidden" name="page" value="pilotes">
                        <input type="hidden" name="action" value="rechercher">

                        <!-- Filtre par nom -->
                        <div class="col-md-4">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom"
                                   value="<?php echo isset($filters['nom']) ? htmlspecialchars($filters['nom']) : ''; ?>"
                                   placeholder="Nom du pilote">
                        </div>

                        <!-- Filtre par prénom -->
                        <div class="col-md-4">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom"
                                   value="<?php echo isset($filters['prenom']) ? htmlspecialchars($filters['prenom']) : ''; ?>"
                                   placeholder="Prénom du pilote">
                        </div>

                        <!-- Filtre par email -->
                        <div class="col-md-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="text" class="form-control" id="email" name="email"
                                   value="<?php echo isset($filters['email']) ? htmlspecialchars($filters['email']) : ''; ?>"
                                   placeholder="Email du pilote">
                        </div>

                        <!-- Boutons d'action -->
                        <div class="col-12 text-end">
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

        <!-- Liste des pilotes -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-tie me-2"></i>Liste des pilotes
                    <span class="text-muted">(<?php echo $totalPilotes; ?> résultats)</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pilotes)): ?>
                    <div class="alert alert-info m-3">
                        Aucun pilote ne correspond à vos critères de recherche.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Date de création</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($pilotes as $pilote): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pilote['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($pilote['prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($pilote['email']); ?></td>
                                    <td><?php echo (new DateTime($pilote['created_at']))->format('d/m/Y H:i'); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="<?php echo url('pilotes', 'detail', ['id' => $pilote['id']]); ?>" class="btn btn-sm btn-outline-primary" title="Détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (isAdmin()): ?>
                                                <a href="<?php echo url('pilotes', 'modifier', ['id' => $pilote['id']]); ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo url('pilotes', 'supprimer', ['id' => $pilote['id']]); ?>" class="btn btn-sm btn-outline-danger" title="Supprimer">
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
            <?php if ($totalPilotes > ITEMS_PER_PAGE): ?>
                <div class="card-footer bg-white">
                    <?php
                    // Génération de la pagination
                    echo pagination($totalPilotes, $page, 'pilotes', 'rechercher', $filters);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gestionnaire pour effacer les filtres
            document.getElementById('clear-filters').addEventListener('click', function() {
                // Réinitialiser tous les champs du formulaire
                document.getElementById('nom').value = '';
                document.getElementById('prenom').value = '';
                document.getElementById('email').value = '';

                // Soumettre le formulaire
                document.getElementById('filter-form').submit();
            });
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>