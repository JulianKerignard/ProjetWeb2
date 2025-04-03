<?php
/**
 * Vue pour l'affichage des statistiques d'un étudiant
 * Implémente des graphiques interactifs et des indicateurs clés de performance
 */
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4 <?php echo isset($containerClass) ? $containerClass : ''; ?>">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><?php echo $pageTitle; ?></h1>
            <a href="<?php echo url('etudiants', 'detail', ['id' => $etudiant['id']]); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour au profil
            </a>
        </div>

        <!-- Vue d'ensemble des activités -->
        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Candidatures</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-6 mb-3">
                                <div class="stats-number display-4"><?php echo $statistiques['nb_candidatures']; ?></div>
                                <div class="stats-title text-muted">Candidatures envoyées</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="stats-number display-4"><?php echo $statistiques['nb_wishlist']; ?></div>
                                <div class="stats-title text-muted">Offres en favoris</div>
                            </div>
                        </div>

                        <!-- Graphique des candidatures par mois -->
                        <div class="chart-container">
                            <canvas id="candidaturesChart" class="chart-canvas"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition par compétence</h5>
                    </div>
                    <div class="card-body">
                        <!-- Graphique des compétences -->
                        <div class="chart-container">
                            <canvas id="competencesChart" class="chart-canvas"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des candidatures récentes -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Candidatures récentes</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($statistiques['candidatures'])): ?>
                            <div class="alert alert-info m-3">
                                Aucune candidature enregistrée pour cet étudiant.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Offre</th>
                                        <th>Entreprise</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    // Limiter à 5 candidatures
                                    $recentCandidatures = array_slice($statistiques['candidatures'], 0, 5);
                                    foreach ($recentCandidatures as $candidature):
                                        ?>
                                        <tr>
                                            <td>
                                                <?php echo (new DateTime($candidature['date_candidature']))->format('d/m/Y'); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($candidature['offre_titre']); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($candidature['entreprise_nom']); ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?php echo url('candidatures', 'detail', ['id' => $candidature['id']]); ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($statistiques['candidatures']) > 5): ?>
                                <div class="text-center py-3">
                                    <a href="<?php echo url('candidatures', 'mes-candidatures'); ?>" class="btn btn-sm btn-outline-primary">
                                        Voir toutes les candidatures (<?php echo count($statistiques['candidatures']); ?>)
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des offres en wishlist -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-heart me-2"></i>Favoris</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($statistiques['wishlist'])): ?>
                            <div class="alert alert-info m-3">
                                Aucune offre en favoris pour cet étudiant.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead>
                                    <tr>
                                        <th>Date d'ajout</th>
                                        <th>Offre</th>
                                        <th>Entreprise</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    // Limiter à 5 favoris
                                    $recentWishlist = array_slice($statistiques['wishlist'], 0, 5);
                                    foreach ($recentWishlist as $wishlist):
                                        ?>
                                        <tr>
                                            <td>
                                                <?php echo (new DateTime($wishlist['date_ajout']))->format('d/m/Y'); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($wishlist['offre_titre']); ?>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($wishlist['entreprise_nom']); ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="<?php echo url('offres', 'detail', ['id' => $wishlist['offre_id']]); ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if (count($statistiques['wishlist']) > 5): ?>
                                <div class="text-center py-3">
                                    <a href="<?php echo url('candidatures', 'afficher-wishlist'); ?>" class="btn btn-sm btn-outline-primary">
                                        Voir tous les favoris (<?php echo count($statistiques['wishlist']); ?>)
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Élément de séparation pour éviter le chevauchement du footer -->
        <div class="stats-footer-spacer"></div>
    </div>

    <!-- Script pour initialiser les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration globale pour Chart.js
            if (typeof Chart !== 'undefined') {
                // Configuration critique pour résoudre le problème d'étirement
                Chart.defaults.maintainAspectRatio = false;
                Chart.defaults.responsive = false;
                Chart.defaults.plugins.legend.position = 'top';
                Chart.defaults.plugins.legend.labels.boxWidth = 10;
                Chart.defaults.plugins.legend.labels.font = {
                    size: 11
                };

                // Forcer les dimensions des graphiques existants
                const canvases = document.querySelectorAll('canvas');
                canvases.forEach(canvas => {
                    // Appliquer la classe pour le styling CSS
                    canvas.classList.add('chart-canvas');

                    // Forcer les dimensions en JavaScript
                    canvas.style.height = '300px';
                    canvas.style.maxHeight = '300px';
                    canvas.height = 300;

                    // Assurer que le parent a la classe chart-container
                    const parent = canvas.parentElement;
                    if (parent && !parent.classList.contains('chart-container')) {
                        parent.classList.add('chart-container');
                    }
                });

                // Données pour le graphique des candidatures
                // Ces données seraient normalement générées dynamiquement
                // à partir des statistiques de l'étudiant
                const candidaturesData = {
                    labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin'],
                    datasets: [{
                        label: 'Candidatures par mois',
                        data: [
                            <?php
                            // Simulation de données pour le graphique
                            // En production, ces données seraient générées à partir de $statistiques
                            $monthCount = min(6, $statistiques['nb_candidatures']);
                            $randomData = [];
                            for ($i=0; $i<$monthCount; $i++) {
                                $randomData[] = rand(0, max(1, $statistiques['nb_candidatures']));
                            }
                            echo implode(', ', $randomData);
                            ?>
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                };

                // Données pour le graphique des compétences
                const competencesData = {
                    labels: ['PHP', 'JavaScript', 'HTML/CSS', 'React', 'Base de données', 'Autres'],
                    datasets: [{
                        label: 'Répartition par compétence',
                        data: [
                            <?php
                            // Simulation de données pour le graphique
                            // En production, ces données seraient générées à partir de $statistiques
                            $compCount = 6;
                            $randomCompData = [];
                            for ($i=0; $i<$compCount; $i++) {
                                $randomCompData[] = rand(1, 10);
                            }
                            echo implode(', ', $randomCompData);
                            ?>
                        ],
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                };

                // Initialisation du graphique des candidatures
                if (document.getElementById('candidaturesChart')) {
                    new Chart(document.getElementById('candidaturesChart').getContext('2d'), {
                        type: 'bar',
                        data: candidaturesData,
                        options: {
                            maintainAspectRatio: false,
                            responsive: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            }
                        }
                    });
                }

                // Initialisation du graphique des compétences
                if (document.getElementById('competencesChart')) {
                    new Chart(document.getElementById('competencesChart').getContext('2d'), {
                        type: 'pie',
                        data: competencesData,
                        options: {
                            maintainAspectRatio: false,
                            responsive: false
                        }
                    });
                }

                // Forcer un rafraîchissement des graphiques
                setTimeout(function() {
                    window.dispatchEvent(new Event('resize'));
                }, 100);
            }

            // Assurer que le contenu ne s'étend pas indéfiniment
            document.body.style.height = 'auto';
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>