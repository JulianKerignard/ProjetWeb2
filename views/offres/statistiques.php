<?php
// Vue pour l'affichage des statistiques des offres
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><?php echo $pageTitle; ?></h1>
            <a href="<?php echo url('offres'); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour aux offres
            </a>
        </div>

        <!-- Statistiques globales -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Aperçu global</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <div class="stats-card text-center p-4 border rounded">
                                    <div class="stats-icon mb-2">
                                        <i class="fas fa-clipboard-list fa-3x text-primary"></i>
                                    </div>
                                    <h2 class="stats-number"><?php echo $statistics['total_offres']; ?></h2>
                                    <p class="stats-title">Offres de stage</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="stats-card text-center p-4 border rounded">
                                    <div class="stats-icon mb-2">
                                        <i class="fas fa-building fa-3x text-secondary"></i>
                                    </div>
                                    <h2 class="stats-number">
                                        <?php
                                        // Récupération du nombre d'entreprises distinctes proposant des offres
                                        $entrepriseModel = new Entreprise();
                                        echo $entrepriseModel->countWithOffres();
                                        ?>
                                    </h2>
                                    <p class="stats-title">Entreprises partenaires</p>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <div class="stats-card text-center p-4 border rounded">
                                    <div class="stats-icon mb-2">
                                        <i class="fas fa-user-graduate fa-3x text-success"></i>
                                    </div>
                                    <h2 class="stats-number">
                                        <?php
                                        // Récupération du nombre de candidatures
                                        require_once MODELS_PATH . '/Candidature.php';
                                        $candidatureModel = new Candidature();
                                        echo $candidatureModel->countAll();
                                        ?>
                                    </h2>
                                    <p class="stats-title">Candidatures</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques détaillés -->
        <div class="row">
            <!-- Répartition par compétence -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Répartition par compétence</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($statistics['repartition_competences'])): ?>
                            <div class="alert alert-info">Aucune donnée disponible.</div>
                        <?php else: ?>
                            <canvas id="competencesChart" height="300"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Répartition par durée -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Répartition par durée</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($statistics['repartition_duree'])): ?>
                            <div class="alert alert-info">Aucune donnée disponible.</div>
                        <?php else: ?>
                            <canvas id="dureeChart" height="300"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top des offres -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Top des offres les plus populaires</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($statistics['top_wishlist'])): ?>
                            <div class="alert alert-info">Aucune donnée disponible.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Rang</th>
                                        <th>Offre</th>
                                        <th>Entreprise</th>
                                        <th class="text-center">Nombre de favoris</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($statistics['top_wishlist'] as $index => $offre): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                // Affichage du rang avec badge spécial pour le top 3
                                                $rank = $index + 1;
                                                if ($rank <= 3) {
                                                    $badgeClass = ($rank === 1) ? 'bg-warning text-dark' : (($rank === 2) ? 'bg-secondary' : 'bg-danger');
                                                    echo '<span class="badge ' . $badgeClass . '">' . $rank . '</span>';
                                                } else {
                                                    echo $rank;
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo url('offres', 'detail', ['id' => $offre['id']]); ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($offre['titre']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($offre['entreprise']); ?></td>
                                            <td class="text-center">
                                                <span class="badge bg-primary"><?php echo $offre['count']; ?></span>
                                            </td>
                                            <td>
                                                <a href="<?php echo url('offres', 'detail', ['id' => $offre['id']]); ?>" class="btn btn-sm btn-outline-primary">
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

    <!-- Inclusion de Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration des graphiques avec Chart.js

            <?php if (!empty($statistics['repartition_competences'])): ?>
            // Graphique de répartition par compétence
            const competencesCtx = document.getElementById('competencesChart').getContext('2d');
            new Chart(competencesCtx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php foreach ($statistics['repartition_competences'] as $item): ?>
                        "<?php echo addslashes($item['competence']); ?>",
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        label: 'Nombre d\'offres',
                        data: [
                            <?php foreach ($statistics['repartition_competences'] as $item): ?>
                            <?php echo $item['count']; ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.x !== null) {
                                        label += context.parsed.x + ' offre(s)';
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nombre d\'offres'
                            }
                        }
                    }
                }
            });
            <?php endif; ?>

            <?php if (!empty($statistics['repartition_duree'])): ?>
            // Graphique de répartition par durée
            const dureeCtx = document.getElementById('dureeChart').getContext('2d');
            new Chart(dureeCtx, {
                type: 'pie',
                data: {
                    labels: [
                        <?php foreach ($statistics['repartition_duree'] as $item): ?>
                        "<?php echo addslashes($item['duree']); ?>",
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        data: [
                            <?php foreach ($statistics['repartition_duree'] as $item): ?>
                            <?php echo $item['count']; ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)',
                            'rgba(255, 159, 64, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(201, 203, 207, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(201, 203, 207, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} offre(s) (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
            <?php endif; ?>
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>