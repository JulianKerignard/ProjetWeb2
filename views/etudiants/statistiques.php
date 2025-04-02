<?php
// Vue pour l'affichage des statistiques d'un étudiant
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('etudiants'); ?>">Étudiants</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('etudiants', 'detail', ['id' => $etudiant['id']]); ?>"><?php echo htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Statistiques</li>
            </ol>
        </nav>

        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">
                <i class="fas fa-chart-line me-2"></i>
                <?php echo $pageTitle; ?>
            </h1>

            <div>
                <a href="<?php echo url('etudiants', 'detail', ['id' => $etudiant['id']]); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au profil
                </a>
            </div>
        </div>

        <!-- Cartes de statistiques -->
        <div class="row mb-4">
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-4 mb-2"><?php echo $statistiques['nb_candidatures']; ?></div>
                        <div class="text-muted">Candidatures</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="display-4 mb-2"><?php echo $statistiques['nb_wishlist']; ?></div>
                        <div class="text-muted">Offres en favoris</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body text-center">
                        <canvas id="activityChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visualisation des données -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Dernières candidatures</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($statistiques['candidatures'])): ?>
                            <div class="alert alert-info m-3">
                                Aucune candidature enregistrée.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($statistiques['candidatures'], 0, 5) as $candidature): ?>
                                    <a href="<?php echo url('offres', 'detail', ['id' => $candidature['offre_id']]); ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($candidature['offre_titre']); ?></h6>
                                            <small class="text-muted"><?php echo (new DateTime($candidature['date_candidature']))->format('d/m/Y'); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($candidature['entreprise_nom']); ?></p>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-heart me-2"></i>Offres en favoris</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($statistiques['wishlist'])): ?>
                            <div class="alert alert-info m-3">
                                Aucune offre en favoris.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach (array_slice($statistiques['wishlist'], 0, 5) as $item): ?>
                                    <a href="<?php echo url('offres', 'detail', ['id' => $item['offre_id']]); ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['offre_titre']); ?></h6>
                                            <small class="text-muted"><?php echo (new DateTime($item['date_ajout']))->format('d/m/Y'); ?></small>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($item['entreprise_nom']); ?></p>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inclusion de Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Données pour le graphique
            const activityData = {
                labels: ['Candidatures', 'Favoris'],
                datasets: [{
                    label: 'Activité',
                    data: [<?php echo $statistiques['nb_candidatures']; ?>, <?php echo $statistiques['nb_wishlist']; ?>],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            };

            // Configuration du graphique
            const activityCtx = document.getElementById('activityChart').getContext('2d');
            new Chart(activityCtx, {
                type: 'pie',
                data: activityData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((acc, val) => acc + val, 0) || 1; // Éviter division par zéro
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>