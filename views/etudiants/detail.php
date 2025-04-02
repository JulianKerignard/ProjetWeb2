<?php
// Vue pour l'affichage détaillé d'un étudiant
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('etudiants'); ?>">Étudiants</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></li>
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
            <!-- Informations de l'étudiant -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-graduate me-2"></i>Profil de l'étudiant
                        </h5>
                    </div>
                    <div class="card-body">
                        <h2 class="h3 mb-3"><?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></h2>

                        <div class="mb-3">
                            <p><strong><i class="fas fa-envelope me-2"></i>Email:</strong>
                                <a href="mailto:<?php echo htmlspecialchars($etudiant['email']); ?>">
                                    <?php echo htmlspecialchars($etudiant['email']); ?>
                                </a>
                            </p>
                            <p><strong><i class="fas fa-calendar-alt me-2"></i>Inscrit le:</strong>
                                <?php echo (new DateTime($etudiant['created_at']))->format('d/m/Y'); ?>
                            </p>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <div class="text-center">
                                <h4 class="mb-0"><?php echo $etudiant['nb_candidatures']; ?></h4>
                                <small class="text-muted">Candidatures</small>
                            </div>
                            <div class="text-center">
                                <h4 class="mb-0"><?php echo $etudiant['nb_wishlist']; ?></h4>
                                <small class="text-muted">Favoris</small>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?php echo url('etudiants'); ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Retour
                            </a>

                            <div>
                                <a href="<?php echo url('etudiants', 'statistiques', ['id' => $etudiant['id']]); ?>" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-chart-bar me-1"></i>Statistiques
                                </a>
                                <?php if (isAdmin() || isPilote()): ?>
                                    <a href="<?php echo url('etudiants', 'modifier', ['id' => $etudiant['id']]); ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-edit me-1"></i>Modifier
                                    </a>
                                    <a href="<?php echo url('etudiants', 'supprimer', ['id' => $etudiant['id']]); ?>" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash me-1"></i>Supprimer
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Candidatures -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt me-2"></i>Candidatures
                            <span class="text-muted">(<?php echo count($etudiant['candidatures']); ?>)</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($etudiant['candidatures'])): ?>
                            <div class="alert alert-info m-3">
                                Cet étudiant n'a pas encore postulé à des offres de stage.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead>
                                    <tr>
                                        <th>Offre</th>
                                        <th>Entreprise</th>
                                        <th>Date de candidature</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($etudiant['candidatures'] as $candidature): ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo url('offres', 'detail', ['id' => $candidature['offre_id']]); ?>">
                                                    <?php echo htmlspecialchars($candidature['offre_titre']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($candidature['entreprise_nom']); ?></td>
                                            <td><?php echo (new DateTime($candidature['date_candidature']))->format('d/m/Y H:i'); ?></td>
                                            <td class="text-end">
                                                <a href="<?php echo url('offres', 'detail', ['id' => $candidature['offre_id']]); ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Wishlist -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-heart me-2"></i>Liste de souhaits
                            <span class="text-muted">(<?php echo count($etudiant['wishlist']); ?>)</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($etudiant['wishlist'])): ?>
                            <div class="alert alert-info m-3">
                                Cet étudiant n'a pas ajouté d'offres à sa liste de souhaits.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead>
                                    <tr>
                                        <th>Offre</th>
                                        <th>Entreprise</th>
                                        <th>Date d'ajout</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($etudiant['wishlist'] as $item): ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo url('offres', 'detail', ['id' => $item['offre_id']]); ?>">
                                                    <?php echo htmlspecialchars($item['offre_titre']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['entreprise_nom']); ?></td>
                                            <td><?php echo (new DateTime($item['date_ajout']))->format('d/m/Y H:i'); ?></td>
                                            <td class="text-end">
                                                <a href="<?php echo url('offres', 'detail', ['id' => $item['offre_id']]); ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>