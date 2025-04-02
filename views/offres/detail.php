<?php
// Vue pour l'affichage détaillé d'une offre
include ROOT_PATH . '/views/templates/header.php';

// Formatage des dates
$dateDebut = new DateTime($offre['date_debut']);
$dateFin = new DateTime($offre['date_fin']);

// Calcul de la durée en mois et jours
$interval = $dateDebut->diff($dateFin);
$months = $interval->m + ($interval->y * 12);
$days = $interval->d;

// Calcul du nombre de jours total
$totalDays = $dateDebut->diff($dateFin)->days;

// État de l'offre (active ou expirée)
$now = new DateTime();
$isActive = $dateFin >= $now;
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('offres'); ?>">Offres de stage</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($offre['titre']); ?></li>
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
            <!-- Détail de l'offre -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-clipboard-list me-2"></i>Détail de l'offre
                            </h5>
                            <div>
                                <?php if (!$isActive): ?>
                                    <span class="badge bg-danger">Stage terminé</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Stage actif</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h1 class="h2 mb-3"><?php echo htmlspecialchars($offre['titre']); ?></h1>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong><i class="fas fa-building me-2"></i>Entreprise:</strong>
                                    <a href="<?php echo url('entreprises', 'detail', ['id' => $offre['entreprise_id']]); ?>">
                                        <?php echo htmlspecialchars($offre['entreprise_nom']); ?>
                                    </a>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-calendar-alt me-2"></i>Période:</strong>
                                    <?php echo $dateDebut->format('d/m/Y') . ' au ' . $dateFin->format('d/m/Y'); ?>
                                    <div class="text-muted small">
                                        <?php
                                        // Affichage de la durée
                                        if ($months > 0) {
                                            echo $months . ' mois';
                                        }
                                        if ($days > 0) {
                                            echo ' et ' . $days . ' jour' . ($days > 1 ? 's' : '');
                                        }
                                        echo ' (' . $totalDays . ' jours)';
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong><i class="fas fa-euro-sign me-2"></i>Rémunération:</strong>
                                    <?php
                                    if (!empty($offre['remuneration']) && $offre['remuneration'] > 0) {
                                        echo number_format($offre['remuneration'], 2, ',', ' ') . ' €';
                                    } else {
                                        echo '<span class="text-muted">Non rémunéré</span>';
                                    }
                                    ?>
                                </div>
                                <div class="mb-3">
                                    <strong><i class="fas fa-users me-2"></i>Candidatures:</strong>
                                    <?php echo $offre['nb_candidatures']; ?> candidat(s)
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5><i class="fas fa-tags me-2"></i>Compétences requises:</h5>
                            <div>
                                <?php if (!empty($offre['competences'])): ?>
                                    <?php foreach ($offre['competences'] as $competence): ?>
                                        <span class="badge bg-primary me-1 mb-1 p-2">
                                        <?php echo htmlspecialchars($competence['nom']); ?>
                                    </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted">Aucune compétence spécifiée.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5><i class="fas fa-align-left me-2"></i>Description:</h5>
                            <div class="description-content">
                                <?php
                                // Affichage de la description avec préservation du formatage
                                echo nl2br(htmlspecialchars($offre['description']));
                                ?>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="<?php echo url('offres'); ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Retour aux offres
                            </a>

                            <div>
                                <?php if (isLoggedIn() && $_SESSION['role'] === ROLE_ETUDIANT): ?>
                                    <?php if ($inWishlist): ?>
                                        <a href="<?php echo url('candidatures', 'retirer-wishlist', ['offre_id' => $offre['id']]); ?>" class="btn btn-outline-danger me-2">
                                            <i class="fas fa-heart-broken me-2"></i>Retirer des favoris
                                        </a>
                                    <?php else: ?>
                                        <a href="<?php echo url('candidatures', 'ajouter-wishlist', ['offre_id' => $offre['id']]); ?>" class="btn btn-outline-primary me-2">
                                            <i class="far fa-heart me-2"></i>Ajouter aux favoris
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?php echo url('candidatures', 'postuler', ['offre_id' => $offre['id']]); ?>" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Postuler
                                    </a>
                                <?php endif; ?>

                                <?php if (checkAccess('offre_modifier')): ?>
                                    <a href="<?php echo url('offres', 'modifier', ['id' => $offre['id']]); ?>" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-edit me-2"></i>Modifier
                                    </a>
                                <?php endif; ?>

                                <?php if (checkAccess('offre_supprimer')): ?>
                                    <a href="<?php echo url('offres', 'supprimer', ['id' => $offre['id']]); ?>" class="btn btn-outline-danger">
                                        <i class="fas fa-trash me-2"></i>Supprimer
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-muted">
                        <i class="far fa-clock me-1"></i>Publié le <?php echo (new DateTime($offre['created_at']))->format('d/m/Y à H:i'); ?>
                        <?php if (!empty($offre['updated_at']) && $offre['updated_at'] !== $offre['created_at']): ?>
                            | <i class="fas fa-edit me-1"></i>Modifié le <?php echo (new DateTime($offre['updated_at']))->format('d/m/Y à H:i'); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Informations complémentaires -->
            <div class="col-lg-4">
                <!-- Carte entreprise -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>À propos de l'entreprise</h5>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($offre['entreprise_nom']); ?></h5>
                        <?php if (!empty($offre['entreprise_description'])): ?>
                            <p class="card-text">
                                <?php
                                // Limiter la description à 150 caractères
                                $description = htmlspecialchars($offre['entreprise_description']);
                                if (strlen($description) > 150) {
                                    echo substr($description, 0, 150) . '...';
                                } else {
                                    echo $description;
                                }
                                ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($offre['entreprise_email']) || !empty($offre['entreprise_telephone'])): ?>
                            <hr>
                            <div class="contact-info">
                                <?php if (!empty($offre['entreprise_email'])): ?>
                                    <div class="mb-2">
                                        <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($offre['entreprise_email']); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($offre['entreprise_telephone'])): ?>
                                    <div>
                                        <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($offre['entreprise_telephone']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <a href="<?php echo url('entreprises', 'detail', ['id' => $offre['entreprise_id']]); ?>" class="btn btn-outline-primary btn-sm mt-3 w-100">
                            <i class="fas fa-info-circle me-2"></i>Voir le profil de l'entreprise
                        </a>
                    </div>
                </div>

                <!-- Offres similaires -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-th-list me-2"></i>Offres similaires</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">
                            Pour voir d'autres offres similaires, vous pouvez filtrer par :
                        </p>
                        <div class="d-grid gap-2">
                            <a href="<?php echo url('offres', 'rechercher', ['entreprise_id' => $offre['entreprise_id']]); ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-building me-1"></i>Même entreprise
                            </a>
                            <?php if (!empty($offre['competences'])): ?>
                                <?php foreach (array_slice($offre['competences'], 0, 3) as $competence): ?>
                                    <a href="<?php echo url('offres', 'rechercher', ['competence_id' => $competence['id']]); ?>" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-tag me-1"></i>Compétence : <?php echo htmlspecialchars($competence['nom']); ?>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>