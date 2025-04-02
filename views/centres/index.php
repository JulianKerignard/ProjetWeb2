<?php
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Centres</li>
            </ol>
        </nav>

        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><?php echo $pageTitle; ?></h1>
            <?php if (isAdmin()): ?>
                <a href="<?php echo url('centres', 'creer'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter un centre
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

        <!-- Liste des centres -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i>Liste des centres
                    <span class="text-muted">(<?php echo $totalCentres; ?> centres)</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($centres)): ?>
                    <div class="alert alert-info m-3">
                        Aucun centre trouvé.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Code</th>
                                <th>Adresse</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($centres as $centre): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($centre['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($centre['code']); ?></td>
                                    <td><?php echo htmlspecialchars($centre['adresse']); ?></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="<?php echo url('centres', 'detail', ['id' => $centre['id']]); ?>" class="btn btn-sm btn-outline-primary" title="Détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (isAdmin()): ?>
                                                <a href="<?php echo url('centres', 'modifier', ['id' => $centre['id']]); ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo url('centres', 'supprimer', ['id' => $centre['id']]); ?>" class="btn btn-sm btn-outline-danger" title="Supprimer">
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
            <?php if ($totalCentres > ITEMS_PER_PAGE): ?>
                <div class="card-footer bg-white">
                    <?php
                    // Génération de la pagination
                    echo pagination($totalCentres, $page, 'centres', 'index', $filters);
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>