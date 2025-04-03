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
                            <div class="chart-container" style="position: relative; height: 300px; max-height: 300px; overflow: hidden;">
                                <canvas id="competencesChart" height="300"></canvas>
                            </div>
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
                            <div class="chart-container" style="position: relative; height: 300px; max-height: 300px; overflow: hidden;">
                                <canvas id="dureeChart" height="300"></canvas>
                            </div>
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

    <!-- Styles nécessaires pour corriger les problèmes de rendu -->
    <style>
        /* Correction pour l'étirement infini */
        .chart-container {
            position: relative;
            height: 300px;
            max-height: 300px;
            width: 100%;
            overflow: hidden !important;
            contain: strict;
        }

        canvas {
            max-height: 300px !important;
            height: 300px !important;
            width: 100% !important;
            contain: size layout;
        }

        /* Isolation du contexte de rendu pour éviter les propagations */
        .card {
            isolation: isolate;
            contain: content;
            overflow: hidden;
        }

        /* Correction spécifique pour le footer */
        .container + .clearfix {
            height: 50px !important;
            max-height: 50px !important;
            visibility: visible !important;
            display: block !important;
            position: relative !important;
            z-index: 100 !important;
        }
    </style>

    <!-- Inclusion de Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration globale pour limiter le rendu et éviter les fuites de mémoire
            Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif';
            Chart.defaults.font.size = 12;
            Chart.defaults.animation.duration = 500;
            Chart.defaults.responsive = false;
            Chart.defaults.maintainAspectRatio = false;

            // Données prétraitées pour éviter les calculs excessifs
            const processedData = {
                competences: <?php echo !empty($statistics['repartition_competences']) ? json_encode($statistics['repartition_competences']) : '[]'; ?>,
                duree: <?php echo !empty($statistics['repartition_duree']) ? json_encode($statistics['repartition_duree']) : '[]'; ?>
            };

            // Fonction optimisée de création des graphiques pour éviter les appels récursifs
            function createOptimizedCharts() {
                const charts = [];

                <?php if (!empty($statistics['repartition_competences'])): ?>
                // Graphique de répartition par compétence avec optimisation de rendu
                const competencesCtx = document.getElementById('competencesChart').getContext('2d');
                const competencesChart = new Chart(competencesCtx, {
                    type: 'bar',
                    data: {
                        labels: processedData.competences.map(item => item.competence.substring(0, 15)),
                        datasets: [{
                            label: 'Nombre d\'offres',
                            data: processedData.competences.map(item => item.count),
                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: false,
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
                                },
                                // Limiter explicitement les marges
                                ticks: {
                                    padding: 5,
                                    maxTicksLimit: 8
                                }
                            },
                            y: {
                                // Limiter explicitement les étiquettes
                                ticks: {
                                    maxTicksLimit: 10,
                                    callback: function(value, index, values) {
                                        const label = this.getLabelForValue(value);
                                        return label.length > 15 ? label.substring(0, 15) + '...' : label;
                                    },
                                    padding: 5
                                }
                            }
                        },
                        // Nouvelle option pour fixer la hauteur
                        layout: {
                            padding: {
                                bottom: 10
                            }
                        }
                    }
                });
                charts.push(competencesChart);
                <?php endif; ?>

                <?php if (!empty($statistics['repartition_duree'])): ?>
                // Graphique de répartition par durée avec optimisation de rendu
                const dureeCtx = document.getElementById('dureeChart').getContext('2d');
                const dureeChart = new Chart(dureeCtx, {
                    type: 'pie',
                    data: {
                        labels: processedData.duree.map(item => item.duree),
                        datasets: [{
                            data: processedData.duree.map(item => item.count),
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
                        responsive: false,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 11
                                    }
                                }
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
                        },
                        // Désactive le redimensionnement automatique
                        layout: {
                            padding: 10
                        }
                    }
                });
                charts.push(dureeChart);
                <?php endif; ?>

                // Retourner les instances pour gestion centralisée
                return charts;
            }

            // Créer les graphiques une seule fois
            const activeCharts = createOptimizedCharts();

            // Fonction de nettoyage pour libérer la mémoire lors de la navigation
            window.addEventListener('beforeunload', function() {
                if (activeCharts && activeCharts.length) {
                    activeCharts.forEach(chart => {
                        if (chart && typeof chart.destroy === 'function') {
                            chart.destroy();
                        }
                    });
                }
            });

            // Forcer un rendu final pour éviter les reflows continus
            setTimeout(function() {
                document.body.style.height = 'auto';
                window.dispatchEvent(new Event('resize'));
            }, 100);
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>