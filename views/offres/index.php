<?php
// Protection contre l'accès direct et initialisation des dépendances
if (!defined('ROOT_PATH')) {
    require_once realpath(dirname(__FILE__) . '/../../bootstrap.php');
}

// Titre de la page
$pageTitle = "Offres de stage";

// Protection contre l'exécution directe sans données du contrôleur
if (!isset($offres)) {
    require_once ROOT_PATH . '/controllers/OffreController.php';
    $controller = new OffreController();
    // Simulation de l'exécution du contrôleur
    $controller->index();
    exit; // Le contrôleur se chargera d'inclure cette vue à nouveau
}

include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><?php echo $pageTitle; ?></h1>
            <?php if (checkAccess('offre_creer')): ?>
                <a href="<?php echo url('offres', 'creer'); ?>" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Ajouter une offre
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

        <!-- Formulaire de recherche avancée -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-search me-2"></i>Recherche avancée</h5>
                    <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#searchCollapse" aria-expanded="false">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse <?php echo !empty($filters) ? 'show' : ''; ?>" id="searchCollapse">
                <div class="card-body">
                    <form id="filter-form" action="<?php echo url('offres', 'rechercher'); ?>" method="get" class="row g-3">
                        <input type="hidden" name="page" value="offres">
                        <input type="hidden" name="action" value="rechercher">

                        <!-- Filtre par titre -->
                        <div class="col-md-4">
                            <label for="titre" class="form-label">Titre</label>
                            <input type="text" class="form-control" id="titre" name="titre"
                                   value="<?php echo isset($filters['titre']) ? htmlspecialchars($filters['titre']) : ''; ?>"
                                   placeholder="Mots-clés...">
                        </div>

                        <!-- Filtre par entreprise -->
                        <div class="col-md-4">
                            <label for="entreprise_id" class="form-label">Entreprise</label>
                            <select class="form-select" id="entreprise_id" name="entreprise_id">
                                <option value="">Toutes les entreprises</option>
                                <?php foreach ($entreprises as $entreprise): ?>
                                    <option value="<?php echo $entreprise['id']; ?>"
                                        <?php echo (isset($filters['entreprise_id']) && $filters['entreprise_id'] == $entreprise['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($entreprise['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filtre par compétence -->
                        <div class="col-md-4">
                            <label for="competence_id" class="form-label">Compétence</label>
                            <select class="form-select" id="competence_id" name="competence_id">
                                <option value="">Toutes les compétences</option>
                                <?php foreach ($competences as $competence): ?>
                                    <option value="<?php echo $competence['id']; ?>"
                                        <?php echo (isset($filters['competence_id']) && $filters['competence_id'] == $competence['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($competence['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filtre par date de début -->
                        <div class="col-md-4">
                            <label for="date_debut" class="form-label">Date de début (à partir de)</label>
                            <input type="date" class="form-control" id="date_debut" name="date_debut"
                                   value="<?php echo isset($filters['date_debut']) ? htmlspecialchars($filters['date_debut']) : ''; ?>">
                        </div>

                        <!-- Filtre par date de fin -->
                        <div class="col-md-4">
                            <label for="date_fin" class="form-label">Date de fin (jusqu'à)</label>
                            <input type="date" class="form-control" id="date_fin" name="date_fin"
                                   value="<?php echo isset($filters['date_fin']) ? htmlspecialchars($filters['date_fin']) : ''; ?>">
                        </div>

                        <!-- Tri des résultats -->
                        <div class="col-md-4">
                            <label for="order_by" class="form-label">Trier par</label>
                            <div class="input-group">
                                <select class="form-select" id="order_by" name="order_by">
                                    <option value="o.created_at" <?php echo (isset($filters['order_by']) && $filters['order_by'] == 'o.created_at') ? 'selected' : ''; ?>>Date de publication</option>
                                    <option value="o.titre" <?php echo (isset($filters['order_by']) && $filters['order_by'] == 'o.titre') ? 'selected' : ''; ?>>Titre</option>
                                    <option value="e.nom" <?php echo (isset($filters['order_by']) && $filters['order_by'] == 'e.nom') ? 'selected' : ''; ?>>Entreprise</option>
                                    <option value="o.date_debut" <?php echo (isset($filters['order_by']) && $filters['order_by'] == 'o.date_debut') ? 'selected' : ''; ?>>Date de début</option>
                                    <option value="o.remuneration" <?php echo (isset($filters['order_by']) && $filters['order_by'] == 'o.remuneration') ? 'selected' : ''; ?>>Rémunération</option>
                                </select>
                                <select class="form-select" id="order_dir" name="order_dir">
                                    <option value="DESC" <?php echo (isset($filters['order_dir']) && $filters['order_dir'] == 'DESC') ? 'selected' : ''; ?>>Décroissant</option>
                                    <option value="ASC" <?php echo (isset($filters['order_dir']) && $filters['order_dir'] == 'ASC') ? 'selected' : ''; ?>>Croissant</option>
                                </select>
                            </div>
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

        <!-- Résultats de recherche -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>Résultats
                        <span class="text-muted">(<?php echo $totalOffres; ?> offres trouvées)</span>
                    </h5>
                    <?php if (isAdmin() || isPilote()): ?>
                        <a href="<?php echo url('offres', 'statistiques'); ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-chart-pie me-1"></i>Statistiques
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($offres)): ?>
                    <div class="alert alert-info m-3">
                        Aucune offre ne correspond à vos critères de recherche.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Entreprise</th>
                                <th>Compétences</th>
                                <th>Période</th>
                                <th>Rémunération</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($offres as $offre): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo url('offres', 'detail', ['id' => $offre['id']]); ?>" class="fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($offre['titre']); ?>
                                        </a>
                                        <div class="text-muted small">
                                            <i class="fas fa-users me-1"></i><?php echo $offre['nb_candidatures']; ?> candidat(s)
                                        </div>
                                    </td>
                                    <td>
                                        <a href="<?php echo url('entreprises', 'detail', ['id' => $offre['entreprise_id']]); ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($offre['entreprise_nom']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if (!empty($offre['competences'])): ?>
                                            <?php foreach (array_slice($offre['competences'], 0, 3) as $index => $competence): ?>
                                                <span class="badge bg-primary"><?php echo htmlspecialchars($competence['nom']); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($offre['competences']) > 3): ?>
                                                <span class="badge bg-primary">+<?php echo count($offre['competences']) - 3; ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $dateDebut = new DateTime($offre['date_debut']);
                                        $dateFin = new DateTime($offre['date_fin']);
                                        echo $dateDebut->format('d/m/Y') . ' - ' . $dateFin->format('d/m/Y');

                                        // Calcul de la durée en mois
                                        $interval = $dateDebut->diff($dateFin);
                                        $months = $interval->m + ($interval->y * 12);
                                        $days = $interval->d;

                                        // Ajout d'un mois si plus de 15 jours
                                        if ($days > 15) {
                                            $months++;
                                        }

                                        echo '<div class="text-muted small">';
                                        echo ($months > 0) ? $months . ' mois' : '';
                                        if ($months == 0 || ($days > 0 && $days <= 15)) {
                                            echo ($months > 0) ? ' et ' : '';
                                            echo $days . ' jour' . ($days > 1 ? 's' : '');
                                        }
                                        echo '</div>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if (!empty($offre['remuneration']) && $offre['remuneration'] > 0) {
                                            echo number_format($offre['remuneration'], 2, ',', ' ') . ' €';
                                        } else {
                                            echo '<span class="text-muted">Non rémunéré</span>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="<?php echo url('offres', 'detail', ['id' => $offre['id']]); ?>" class="btn btn-sm btn-outline-primary" title="Détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if (checkAccess('offre_modifier')): ?>
                                                <a href="<?php echo url('offres', 'modifier', ['id' => $offre['id']]); ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkAccess('offre_supprimer')): ?>
                                                <a href="<?php echo url('offres', 'supprimer', ['id' => $offre['id']]); ?>" class="btn btn-sm btn-outline-danger" title="Supprimer">
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
            <?php if ($totalOffres > ITEMS_PER_PAGE): ?>
                <div class="card-footer bg-white">
                    <?php
                    // Génération de la pagination
                    echo pagination($totalOffres, $page, 'offres', 'rechercher', $filters);
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
                document.getElementById('titre').value = '';
                document.getElementById('entreprise_id').selectedIndex = 0;
                document.getElementById('competence_id').selectedIndex = 0;
                document.getElementById('date_debut').value = '';
                document.getElementById('date_fin').value = '';
                document.getElementById('order_by').selectedIndex = 0;
                document.getElementById('order_dir').selectedIndex = 0;

                // Soumettre le formulaire
                document.getElementById('filter-form').submit();
            });
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>