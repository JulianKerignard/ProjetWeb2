<?php
/**
 * Vue pour l'affichage des journaux d'activité
 * avec pagination avancée et filtrage par type
 */
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">
                <i class="fas fa-history me-2"></i>
                <?php echo $pageTitle; ?>
            </h1>

            <div>
                <a href="<?php echo url('admin'); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                </a>
                <?php if (isAdmin()): ?>
                    <button class="btn btn-danger ms-2" data-bs-toggle="modal" data-bs-target="#purgeLogsModal">
                        <i class="fas fa-trash me-2"></i>Purger les logs
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filtres de recherche avancés -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
                    <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    <form action="<?php echo url('admin', 'logs'); ?>" method="get" class="row g-3">
                        <input type="hidden" name="page" value="admin">
                        <input type="hidden" name="action" value="logs">

                        <!-- Type de log -->
                        <div class="col-md-3">
                            <label for="level" class="form-label">Niveau</label>
                            <select class="form-select" id="level" name="level">
                                <option value="">Tous les niveaux</option>
                                <option value="INFO" <?php echo (isset($_GET['level']) && $_GET['level'] === 'INFO') ? 'selected' : ''; ?>>Info</option>
                                <option value="WARNING" <?php echo (isset($_GET['level']) && $_GET['level'] === 'WARNING') ? 'selected' : ''; ?>>Avertissement</option>
                                <option value="ERROR" <?php echo (isset($_GET['level']) && $_GET['level'] === 'ERROR') ? 'selected' : ''; ?>>Erreur</option>
                                <option value="CRITICAL" <?php echo (isset($_GET['level']) && $_GET['level'] === 'CRITICAL') ? 'selected' : ''; ?>>Critique</option>
                            </select>
                        </div>

                        <!-- Plage de dates -->
                        <div class="col-md-3">
                            <label for="dateFrom" class="form-label">Date début</label>
                            <input type="date" class="form-control" id="dateFrom" name="dateFrom"
                                   value="<?php echo isset($_GET['dateFrom']) ? htmlspecialchars($_GET['dateFrom']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="dateTo" class="form-label">Date fin</label>
                            <input type="date" class="form-control" id="dateTo" name="dateTo"
                                   value="<?php echo isset($_GET['dateTo']) ? htmlspecialchars($_GET['dateTo']) : ''; ?>">
                        </div>

                        <!-- Recherche par utilisateur -->
                        <div class="col-md-3">
                            <label for="user" class="form-label">Utilisateur</label>
                            <input type="text" class="form-control" id="user" name="user"
                                   value="<?php echo isset($_GET['user']) ? htmlspecialchars($_GET['user']) : ''; ?>"
                                   placeholder="Email de l'utilisateur">
                        </div>

                        <!-- Recherche textuelle -->
                        <div class="col-md-6">
                            <label for="q" class="form-label">Recherche</label>
                            <input type="text" class="form-control" id="q" name="q"
                                   value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                                   placeholder="Recherche dans les messages">
                        </div>

                        <!-- Options d'affichage -->
                        <div class="col-md-6 text-end align-self-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Rechercher
                            </button>
                            <a href="<?php echo url('admin', 'logs'); ?>" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-eraser me-2"></i>Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Liste des logs -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-list-alt me-2"></i>Journaux d'activité
                    <?php if (isset($totalLogs)): ?>
                        <span class="text-muted">(<?php echo $totalLogs; ?> entrées)</span>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle me-2"></i>Aucun journal d'activité ne correspond à vos critères.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                            <tr>
                                <th width="160">Horodatage</th>
                                <th width="100">Niveau</th>
                                <th width="180">Utilisateur</th>
                                <th>Message</th>
                                <th width="120">IP</th>
                                <th width="80">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr class="<?php echo getLogRowClass($log['level'] ?? 'INFO'); ?>">
                                    <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                    <td>
                                        <span class="badge <?php echo getLogBadgeClass($log['level'] ?? 'INFO'); ?>">
                                            <?php echo htmlspecialchars($log['level'] ?? 'INFO'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['user']); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip']); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#logDetailModal"
                                                data-log-id="<?php echo $log['id'] ?? '0'; ?>"
                                                data-log-message="<?php echo htmlspecialchars($log['action']); ?>"
                                                data-log-context="<?php echo htmlspecialchars($log['context'] ?? '{}'); ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (!empty($logs) && isset($totalLogs) && $totalLogs > $limit): ?>
                <div class="card-footer bg-white">
                    <?php
                    // Génération de la pagination
                    echo pagination($totalLogs, $page, 'admin', 'logs', $_GET);
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Exportation et statistiques -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-download me-2"></i>Exporter les logs</h5>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo url('admin', 'exportLogs'); ?>" method="post" class="row g-3">
                            <div class="col-md-6">
                                <label for="export-format" class="form-label">Format</label>
                                <select class="form-select" id="export-format" name="format">
                                    <option value="csv">CSV</option>
                                    <option value="json">JSON</option>
                                    <option value="txt">Texte brut</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="export-period" class="form-label">Période</label>
                                <select class="form-select" id="export-period" name="period">
                                    <option value="current">Résultats actuels</option>
                                    <option value="day">Dernières 24 heures</option>
                                    <option value="week">Dernière semaine</option>
                                    <option value="month">Dernier mois</option>
                                    <option value="all">Tous les logs</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-file-export me-2"></i>Exporter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Statistiques des logs</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="logsStatsChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de détail du log -->
    <div class="modal fade" id="logDetailModal" tabindex="-1" aria-labelledby="logDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logDetailModalLabel">Détail du journal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Message</h6>
                    <p id="logDetailMessage" class="border p-2 bg-light"></p>

                    <h6>Contexte</h6>
                    <pre id="logDetailContext" class="border p-2 bg-light"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de purge -->
    <div class="modal fade" id="purgeLogsModal" tabindex="-1" aria-labelledby="purgeLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="purgeLogsModalLabel"><i class="fas fa-exclamation-triangle me-2"></i>Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <p><strong>Attention !</strong> Vous êtes sur le point de purger les journaux d'activité.</p>
                        <p class="mb-0">Cette action est irréversible et supprimera définitivement les données selon les critères sélectionnés.</p>
                    </div>

                    <form id="purgeLogsForm" action="<?php echo url('admin', 'purgeLogs'); ?>" method="post">
                        <div class="mb-3">
                            <label for="purge-period" class="form-label">Période à purger</label>
                            <select class="form-select" id="purge-period" name="period" required>
                                <option value="">Sélectionnez une période</option>
                                <option value="older_month">Plus anciens qu'un mois</option>
                                <option value="older_quarter">Plus anciens qu'un trimestre</option>
                                <option value="older_year">Plus anciens qu'un an</option>
                                <option value="all">Tous les logs</option>
                            </select>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="confirm-purge" name="confirm" required>
                            <label class="form-check-label" for="confirm-purge">Je comprends que cette action est irréversible</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="purgeLogsForm" class="btn btn-danger">Purger les logs</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Script pour le détail des logs et les statistiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuration pour l'affichage des détails de log
            const logDetailModal = document.getElementById('logDetailModal');
            if (logDetailModal) {
                logDetailModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const logId = button.getAttribute('data-log-id');
                    const logMessage = button.getAttribute('data-log-message');
                    const logContext = button.getAttribute('data-log-context');

                    document.getElementById('logDetailMessage').textContent = logMessage;

                    try {
                        const contextObj = JSON.parse(logContext);
                        document.getElementById('logDetailContext').textContent = JSON.stringify(contextObj, null, 2);
                    } catch (e) {
                        document.getElementById('logDetailContext').textContent = logContext || 'Aucun contexte disponible';
                    }
                });
            }

            // Statistiques des logs (graphique)
            const ctx = document.getElementById('logsStatsChart');
            if (ctx) {
                // Récupération des données statistiques
                const logStats = <?php echo json_encode($logStats ?? []); ?>;

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: logStats.labels || ['INFO', 'WARNING', 'ERROR', 'CRITICAL'],
                        datasets: [{
                            label: 'Nombre de logs par niveau',
                            data: logStats.data || [65, 25, 15, 5],
                            backgroundColor: [
                                'rgba(54, 162, 235, 0.5)',
                                'rgba(255, 205, 86, 0.5)',
                                'rgba(255, 99, 132, 0.5)',
                                'rgba(201, 203, 207, 0.5)'
                            ],
                            borderColor: [
                                'rgb(54, 162, 235)',
                                'rgb(255, 205, 86)',
                                'rgb(255, 99, 132)',
                                'rgb(201, 203, 207)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
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
        });
    </script>

<?php
/**
 * Fonctions d'aide pour l'affichage des logs
 */
function getLogRowClass($level) {
    switch ($level) {
        case 'ERROR':
        case 'CRITICAL':
        case 'EMERGENCY':
            return 'table-danger';
        case 'WARNING':
            return 'table-warning';
        case 'INFO':
        default:
            return '';
    }
}

function getLogBadgeClass($level) {
    switch ($level) {
        case 'ERROR':
        case 'CRITICAL':
        case 'EMERGENCY':
            return 'bg-danger';
        case 'WARNING':
            return 'bg-warning text-dark';
        case 'INFO':
            return 'bg-info text-dark';
        default:
            return 'bg-secondary';
    }
}
?>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>