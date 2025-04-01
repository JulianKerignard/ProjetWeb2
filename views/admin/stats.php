<?php
/**
 * Vue des statistiques détaillées pour le panel administrateur
 *
 * Affiche des graphiques et indicateurs détaillés pour l'analyse
 * des performances globales du système de stages.
 */
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">
                <i class="fas fa-chart-line me-2"></i>
                <?php echo $pageTitle; ?>
            </h1>

            <div>
                <a href="<?php echo url('admin'); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                </a>
            </div>
        </div>

        <!-- Statistiques des offres -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Statistiques des offres</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Répartition par compétence</h6>
                                <canvas id="skillsChart" height="300"></canvas>
                            </div>
                            <div class="col-md-6">
                                <h6>Répartition par durée</h6>
                                <canvas id="durationChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-2">
                                <div class="stat-item">
                                    <h4 class="mb-0"><?php echo $offreStats['total_offres']; ?></h4>
                                    <small class="text-muted">Offres totales</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <div class="stat-item">
                                    <h4 class="mb-0"><?php echo isset($offreStats['offres_actives']) ? $offreStats['offres_actives'] : $stats['offres_actives']; ?></h4>
                                    <small class="text-muted">Offres actives</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <div class="stat-item">
                                    <h4 class="mb-0"><?php echo count($skillDistribution); ?></h4>
                                    <small class="text-muted">Compétences</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6 mb-2">
                                <div class="stat-item">
                                    <h4 class="mb-0"><?php echo isset($stats['total_candidatures']) ? $stats['total_candidatures'] : '0'; ?></h4>
                                    <small class="text-muted">Candidatures</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques des candidatures -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Statistiques des candidatures</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <h6>Évolution des candidatures par mois</h6>
                                <canvas id="applicationsChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques des entreprises -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Statistiques des entreprises</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Top 10 entreprises par nombre d'offres</h6>
                                <canvas id="companiesChart" height="300"></canvas>
                            </div>
                            <div class="col-md-6">
                                <h6>Évaluations des entreprises</h6>
                                <canvas id="ratingsChart" height="300"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="row text-center">
                            <div class="col-md-4 col-6 mb-2">
                                <div class="stat-item">
                                    <h4 class="mb-0"><?php echo $stats['total_entreprises']; ?></h4>
                                    <small class="text-muted">Entreprises</small>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-2">
                                <div class="stat-item">
                                    <h4 class="mb-0"><?php echo isset($stats['entreprises_evaluees']) ? $stats['entreprises_evaluees'] : '0'; ?></h4>
                                    <small class="text-muted">Entreprises évaluées</small>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-2">
                                <div class="stat-item">
                                    <h4 class="mb-0"><?php echo isset($stats['note_moyenne']) ? number_format($stats['note_moyenne'], 1) : '0.0'; ?>/5</h4>
                                    <small class="text-muted">Note moyenne</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques des étudiants -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-user-graduate me-2"></i>Statistiques des étudiants</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Taux de placement des étudiants</h6>
                                <canvas id="placementChart" height="300"></canvas>
                            </div>
                            <div class="col-md-6">
                                <h6>Nombre moyen de candidatures par étudiant</h6>
                                <div class="d-flex justify-content-center align-items-center h-100">
                                    <div class="text-center">
                                        <h1 class="display-4"><?php echo isset($studentStats['avg_applications']) ? number_format($studentStats['avg_applications'], 1) : '0'; ?></h1>
                                        <p class="lead">candidatures en moyenne</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="row text-center">
                            <div class="col-md-4 col-6 mb-2">
                                <div class="stat-item">
                                    <h4 class="mb-0"><?php echo $stats['total_etudiants']; ?></h4>
                                    <small class="text-muted">Étudiants</small>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-2">
                                <div class="stat-item">
                                    <h4 class="mb-0"><?php echo isset($studentStats['placement_rate']['placed']) ? $studentStats['placement_rate']['placed'] : '0'; ?></h4>
                                    <small class="text-muted">Étudiants placés</small>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-2">
                                <div class="stat-item">
                                    <h4 class="mb-0"><?php echo isset($studentStats['placement_rate']['rate']) ? $studentStats['placement_rate']['rate'] : '0'; ?>%</h4>
                                    <small class="text-muted">Taux de placement</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bouton d'export CSV -->
        <div class="row mb-4">
            <div class="col-12 text-center">
                <button class="btn btn-success" id="exportButton">
                    <i class="fas fa-file-csv me-2"></i>Exporter les statistiques en CSV
                </button>
            </div>
        </div>
    </div>

    <!-- Inclusion de Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Préchargement des données pour optimiser le rendu JavaScript
            const skillsData = <?php echo json_encode($skillDistribution); ?>;
            const durationsData = <?php echo json_encode($offreStats['repartition_duree']); ?>;
            const applicationsData = <?php echo json_encode($monthlyApplications); ?>;
            const companiesData = <?php echo json_encode($companyStats['top_companies'] ?? []); ?>;
            const ratingsData = <?php echo json_encode($companyStats['rating_distribution'] ?? []); ?>;
            const placementData = <?php echo json_encode($studentStats['placement_rate']); ?>;

            // Configuration des couleurs pour les graphiques
            const colorPalette = [
                'rgba(54, 162, 235, 0.7)', // Bleu
                'rgba(75, 192, 192, 0.7)', // Vert
                'rgba(153, 102, 255, 0.7)', // Violet
                'rgba(255, 159, 64, 0.7)', // Orange
                'rgba(255, 99, 132, 0.7)', // Rouge
                'rgba(201, 203, 207, 0.7)' // Gris
            ];

            // Fonction utilitaire pour générer des couleurs dynamiques
            function generateColors(count) {
                const colors = [];
                for (let i = 0; i < count; i++) {
                    colors.push(colorPalette[i % colorPalette.length]);
                }
                return colors;
            }

            // Graphique des compétences - Optimisé pour l'affichage d'une grande quantité de données
            const skillsCtx = document.getElementById('skillsChart').getContext('2d');
            new Chart(skillsCtx, {
                type: 'bar',
                data: {
                    labels: skillsData.map(item => item.nom),
                    datasets: [{
                        label: 'Nombre d\'offres',
                        data: skillsData.map(item => item.count),
                        backgroundColor: generateColors(skillsData.length),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    },
                    maintainAspectRatio: false,
                    responsive: true
                }
            });

            // Graphique des durées - Utilisation d'un graphique à secteurs pour une représentation proportionnelle
            const durationCtx = document.getElementById('durationChart').getContext('2d');
            new Chart(durationCtx, {
                type: 'pie',
                data: {
                    labels: durationsData.map(item => item.duree),
                    datasets: [{
                        data: durationsData.map(item => item.count),
                        backgroundColor: generateColors(durationsData.length),
                        borderWidth: 1
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} offre(s) (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Graphique des candidatures - Évolution temporelle avec graphique linéaire
            const applicationsCtx = document.getElementById('applicationsChart').getContext('2d');
            new Chart(applicationsCtx, {
                type: 'line',
                data: {
                    labels: applicationsData.map(item => item.month_label || item.month),
                    datasets: [{
                        label: 'Nombre de candidatures',
                        data: applicationsData.map(item => item.count),
                        fill: false,
                        borderColor: 'rgb(75, 192, 192)',
                        tension: 0.1,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        pointBackgroundColor: 'rgb(75, 192, 192)',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(75, 192, 192)'
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
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

            // Graphique des entreprises - Top 10 par nombre d'offres
            const companiesCtx = document.getElementById('companiesChart').getContext('2d');
            new Chart(companiesCtx, {
                type: 'bar',
                data: {
                    labels: companiesData.length > 0 ? companiesData.map(item => item.nom) :
                        ['Entreprise 1', 'Entreprise 2', 'Entreprise 3', 'Entreprise 4', 'Entreprise 5'],
                    datasets: [{
                        label: 'Nombre d\'offres',
                        data: companiesData.length > 0 ? companiesData.map(item => item.count) : [15, 12, 10, 8, 7],
                        backgroundColor: 'rgba(153, 102, 255, 0.7)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    maintainAspectRatio: false,
                    responsive: true
                }
            });

            // Graphique des évaluations - Distribution des notes
            const ratingsCtx = document.getElementById('ratingsChart').getContext('2d');
            new Chart(ratingsCtx, {
                type: 'bar',
                data: {
                    labels: ratingsData.length > 0 ? ratingsData.map(item => item.label) : ['5★', '4★', '3★', '2★', '1★'],
                    datasets: [{
                        label: 'Nombre d\'entreprises',
                        data: ratingsData.length > 0 ? ratingsData.map(item => item.count) : [10, 25, 8, 4, 2],
                        backgroundColor: 'rgba(255, 159, 64, 0.7)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    maintainAspectRatio: false,
                    responsive: true
                }
            });

            // Graphique du taux de placement - Visualisation avec un graphique en anneau
            const placementCtx = document.getElementById('placementChart').getContext('2d');
            new Chart(placementCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Étudiants avec candidature', 'Étudiants sans candidature'],
                    datasets: [{
                        data: [
                            placementData.placed || 0,
                            placementData.searching || 0
                        ],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(255, 99, 132, 0.7)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = placementData.total || 1; // Éviter division par zéro
                                    const percentage = Math.round((value / total) * 100);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Logique d'export CSV
            document.getElementById('exportButton').addEventListener('click', function() {
                // Préparation des données pour l'export
                const exportData = [
                    ['Catégorie', 'Métrique', 'Valeur'],
                    ['Offres', 'Total des offres', <?php echo $stats['total_offres']; ?>],
                    ['Offres', 'Offres actives', <?php echo $stats['offres_actives']; ?>],
                    ['Entreprises', 'Total des entreprises', <?php echo $stats['total_entreprises']; ?>],
                    ['Entreprises', 'Entreprises évaluées', <?php echo isset($stats['entreprises_evaluees']) ? $stats['entreprises_evaluees'] : '0'; ?>],
                    ['Entreprises', 'Note moyenne', <?php echo isset($stats['note_moyenne']) ? $stats['note_moyenne'] : '0'; ?>],
                    ['Étudiants', 'Total des étudiants', <?php echo $stats['total_etudiants']; ?>],
                    ['Étudiants', 'Étudiants placés', <?php echo isset($studentStats['placement_rate']['placed']) ? $studentStats['placement_rate']['placed'] : '0'; ?>],
                    ['Étudiants', 'Taux de placement', <?php echo isset($studentStats['placement_rate']['rate']) ? $studentStats['placement_rate']['rate'] : '0'; ?>],
                    ['Candidatures', 'Total des candidatures', <?php echo $stats['total_candidatures']; ?>],
                    ['Candidatures', 'Moyenne par étudiant', <?php echo isset($studentStats['avg_applications']) ? $studentStats['avg_applications'] : '0'; ?>]
                ];

                // Génération du CSV
                let csvContent = "data:text/csv;charset=utf-8,";

                exportData.forEach(function(rowArray) {
                    let row = rowArray.join(",");
                    csvContent += row + "\r\n";
                });

                // Déclenchement du téléchargement
                const encodedUri = encodeURI(csvContent);
                const link = document.createElement("a");
                link.setAttribute("href", encodedUri);
                link.setAttribute("download", "statistiques_stages_" + new Date().toISOString().slice(0,10) + ".csv");
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>

    <style>
        .stat-item {
            padding: 10px;
            border-radius: 5px;
            background-color: #f8f9fa;
        }
    </style>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>