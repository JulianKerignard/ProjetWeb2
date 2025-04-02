<?php
/**
 * Vue d'administration pour l'affichage des journaux d'activité système
 * Cette vue utilise une structure tabulaire optimisée pour l'affichage
 * de données de journalisation avec filtrage et exportation.
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
                <button id="exportLogsBtn" class="btn btn-outline-primary">
                    <i class="fas fa-file-export me-2"></i>Exporter les logs
                </button>
                <a href="<?php echo url('admin'); ?>" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-arrow-left me-2"></i>Retour
                </a>
            </div>
        </div>

        <!-- Filtres de recherche -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtres</h5>
                    <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    <form id="logFilterForm" class="row g-3">
                        <!-- Type de log -->
                        <div class="col-md-4">
                            <label for="logType" class="form-label">Type d'activité</label>
                            <select class="form-select" id="logType">
                                <option value="">Tous</option>
                                <option value="connexion">Connexion</option>
                                <option value="modification">Modification</option>
                                <option value="création">Création</option>
                                <option value="suppression">Suppression</option>
                            </select>
                        </div>

                        <!-- Utilisateur -->
                        <div class="col-md-4">
                            <label for="logUser" class="form-label">Utilisateur</label>
                            <input type="text" class="form-control" id="logUser" placeholder="Rechercher par utilisateur">
                        </div>

                        <!-- Date -->
                        <div class="col-md-4">
                            <label for="logDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="logDate">
                        </div>

                        <!-- Boutons d'action -->
                        <div class="col-12 text-end">
                            <button type="button" id="clearFilters" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-eraser me-1"></i>Effacer
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tableau des logs -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Journal d'activité
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle me-2"></i>Aucune activité à afficher.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                            <tr>
                                <th>Date et heure</th>
                                <th>Utilisateur</th>
                                <th>Action</th>
                                <th>Adresse IP</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                    <td><?php echo htmlspecialchars($log['user']); ?></td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td><?php echo htmlspecialchars($log['ip']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filtrage côté client pour les logs
            const logTable = document.querySelector('table');
            const logFilterForm = document.getElementById('logFilterForm');
            const clearFiltersBtn = document.getElementById('clearFilters');

            if (logFilterForm) {
                logFilterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    filterLogs();
                });
            }

            if (clearFiltersBtn) {
                clearFiltersBtn.addEventListener('click', function() {
                    document.getElementById('logType').value = '';
                    document.getElementById('logUser').value = '';
                    document.getElementById('logDate').value = '';
                    filterLogs();
                });
            }

            function filterLogs() {
                const type = document.getElementById('logType').value.toLowerCase();
                const user = document.getElementById('logUser').value.toLowerCase();
                const date = document.getElementById('logDate').value;

                if (logTable) {
                    const rows = logTable.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        const action = row.cells[2].textContent.toLowerCase();
                        const userText = row.cells[1].textContent.toLowerCase();
                        const timestamp = row.cells[0].textContent;

                        const matchType = !type || action.includes(type);
                        const matchUser = !user || userText.includes(user);
                        const matchDate = !date || timestamp.includes(date);

                        if (matchType && matchUser && matchDate) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                }
            }

            // Fonctionnalité d'export
            const exportBtn = document.getElementById('exportLogsBtn');
            if (exportBtn) {
                exportBtn.addEventListener('click', function() {
                    exportLogsToCSV();
                });
            }

            function exportLogsToCSV() {
                if (!logTable) return;

                const rows = logTable.querySelectorAll('tbody tr');
                if (rows.length === 0) return;

                let csv = 'Date et heure,Utilisateur,Action,Adresse IP\n';

                rows.forEach(row => {
                    if (row.style.display !== 'none') {
                        const timestamp = row.cells[0].textContent.trim();
                        const user = row.cells[1].textContent.trim();
                        const action = row.cells[2].textContent.trim();
                        const ip = row.cells[3].textContent.trim();

                        // Échapper les champs qui contiennent des virgules
                        const formattedRow = [
                            timestamp.includes(',') ? `"${timestamp}"` : timestamp,
                            user.includes(',') ? `"${user}"` : user,
                            action.includes(',') ? `"${action}"` : action,
                            ip.includes(',') ? `"${ip}"` : ip
                        ].join(',');

                        csv += formattedRow + '\n';
                    }
                });

                // Création et déclenchement du téléchargement
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.setAttribute('href', url);
                link.setAttribute('download', 'journaux_activite_' + new Date().toISOString().slice(0,10) + '.csv');
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>