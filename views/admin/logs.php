<?php
/**
 * Vue d'administration pour l'affichage des journaux d'activité système
 * Implémente une pagination côté serveur et un mécanisme de journalisation asynchrone
 * avec des optimisations de performance pour les jeux de données volumineux.
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

        <!-- Message de statut -->
        <div id="statusMessage" class="alert alert-info mb-4 d-none">
            <i class="fas fa-info-circle me-2"></i>
            <span id="statusText">Chargement des données...</span>
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
            <div class="collapse show" id="filterCollapse">
                <div class="card-body">
                    <form id="logFilterForm" class="row g-3" method="get" action="<?php echo url('admin', 'logs'); ?>">
                        <input type="hidden" name="page" value="admin">
                        <input type="hidden" name="action" value="logs">

                        <!-- Type de log -->
                        <div class="col-md-4">
                            <label for="logType" class="form-label">Type d'activité</label>
                            <select class="form-select" id="logType" name="type">
                                <option value="">Tous</option>
                                <option value="connexion" <?php echo isset($_GET['type']) && $_GET['type'] == 'connexion' ? 'selected' : ''; ?>>Connexion</option>
                                <option value="modification" <?php echo isset($_GET['type']) && $_GET['type'] == 'modification' ? 'selected' : ''; ?>>Modification</option>
                                <option value="création" <?php echo isset($_GET['type']) && $_GET['type'] == 'création' ? 'selected' : ''; ?>>Création</option>
                                <option value="suppression" <?php echo isset($_GET['type']) && $_GET['type'] == 'suppression' ? 'selected' : ''; ?>>Suppression</option>
                            </select>
                        </div>

                        <!-- Utilisateur -->
                        <div class="col-md-4">
                            <label for="logUser" class="form-label">Utilisateur</label>
                            <input type="text" class="form-control" id="logUser" name="user" placeholder="Rechercher par utilisateur" value="<?php echo isset($_GET['user']) ? htmlspecialchars($_GET['user']) : ''; ?>">
                        </div>

                        <!-- Date -->
                        <div class="col-md-4">
                            <label for="logDate" class="form-label">Date</label>
                            <input type="date" class="form-control" id="logDate" name="date" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>">
                        </div>

                        <!-- Niveau (optionnel) -->
                        <div class="col-md-4">
                            <label for="logLevel" class="form-label">Niveau</label>
                            <select class="form-select" id="logLevel" name="level">
                                <option value="">Tous</option>
                                <option value="info" <?php echo isset($_GET['level']) && strtolower($_GET['level']) == 'info' ? 'selected' : ''; ?>>INFO</option>
                                <option value="warning" <?php echo isset($_GET['level']) && strtolower($_GET['level']) == 'warning' ? 'selected' : ''; ?>>WARNING</option>
                                <option value="error" <?php echo isset($_GET['level']) && strtolower($_GET['level']) == 'error' ? 'selected' : ''; ?>>ERROR</option>
                                <option value="success" <?php echo isset($_GET['level']) && strtolower($_GET['level']) == 'success' ? 'selected' : ''; ?>>SUCCESS</option>
                            </select>
                        </div>

                        <!-- Boutons d'action -->
                        <div class="col-12 text-end">
                            <a href="<?php echo url('admin', 'logs'); ?>" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-eraser me-1"></i>Effacer
                            </a>
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
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Journal d'activité
                    </h5>
                    <div>
                        <div id="refreshIndicator" class="spinner-border spinner-border-sm text-primary d-none" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <button id="refreshLogsBtn" class="btn btn-sm btn-outline-primary ms-2">
                            <i class="fas fa-sync-alt me-1"></i>Actualiser
                        </button>
                        <a href="<?php echo url('admin', 'add-test-logs'); ?>" class="btn btn-sm btn-outline-success ms-2">
                            <i class="fas fa-plus me-1"></i>Ajouter des logs de test
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($logs)): ?>
                    <div class="alert alert-info m-3">
                        <i class="fas fa-info-circle me-2"></i>Aucune activité à afficher.
                        <p class="mt-2 mb-0">
                            Vous pouvez <a href="<?php echo url('admin', 'add-test-logs'); ?>">ajouter des logs de test</a> pour voir comment fonctionne l'affichage.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0" id="logsTable">
                            <thead class="table-light">
                            <tr>
                                <th>
                                    <a href="<?php echo url('admin', 'logs', array_merge($_GET, ['sort' => 'timestamp', 'order' => (isset($_GET['sort']) && $_GET['sort'] == 'timestamp' && isset($_GET['order']) && $_GET['order'] == 'asc') ? 'desc' : 'asc'])); ?>">
                                        Date et heure
                                        <?php if (isset($_GET['sort']) && $_GET['sort'] == 'timestamp'): ?>
                                            <i class="fas fa-sort-<?php echo (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?php echo url('admin', 'logs', array_merge($_GET, ['sort' => 'user', 'order' => (isset($_GET['sort']) && $_GET['sort'] == 'user' && isset($_GET['order']) && $_GET['order'] == 'asc') ? 'desc' : 'asc'])); ?>">
                                        Utilisateur
                                        <?php if (isset($_GET['sort']) && $_GET['sort'] == 'user'): ?>
                                            <i class="fas fa-sort-<?php echo (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?php echo url('admin', 'logs', array_merge($_GET, ['sort' => 'action', 'order' => (isset($_GET['sort']) && $_GET['sort'] == 'action' && isset($_GET['order']) && $_GET['order'] == 'asc') ? 'desc' : 'asc'])); ?>">
                                        Action
                                        <?php if (isset($_GET['sort']) && $_GET['sort'] == 'action'): ?>
                                            <i class="fas fa-sort-<?php echo (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="<?php echo url('admin', 'logs', array_merge($_GET, ['sort' => 'ip', 'order' => (isset($_GET['sort']) && $_GET['sort'] == 'ip' && isset($_GET['order']) && $_GET['order'] == 'asc') ? 'desc' : 'asc'])); ?>">
                                        Adresse IP
                                        <?php if (isset($_GET['sort']) && $_GET['sort'] == 'ip'): ?>
                                            <i class="fas fa-sort-<?php echo (isset($_GET['order']) && $_GET['order'] == 'asc') ? 'up' : 'down'; ?>"></i>
                                        <?php else: ?>
                                            <i class="fas fa-sort"></i>
                                        <?php endif; ?>
                                    </a>
                                </th>
                                <th class="text-center">Niveau</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr class="<?php echo getLogRowClass($log); ?>">
                                    <td><?php echo isset($log['timestamp']) ? htmlspecialchars($log['timestamp']) : ''; ?></td>
                                    <td><?php echo isset($log['user']) ? htmlspecialchars($log['user']) : ''; ?></td>
                                    <td><?php echo isset($log['action']) ? htmlspecialchars($log['action']) : ''; ?></td>
                                    <td><?php echo isset($log['ip']) ? htmlspecialchars($log['ip']) : ''; ?></td>
                                    <td class="text-center">
                                        <?php if (isset($log['level'])): ?>
                                            <span class="badge <?php echo getLogLevelBadgeClass($log['level']); ?>">
                                                <?php echo htmlspecialchars($log['level']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">INFO</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <div>
                            <span class="text-muted">Affichage de <?php echo count($logs); ?> logs sur <?php echo $totalLogs; ?></span>
                        </div>
                        <?php echo pagination($totalLogs, $page, 'admin', 'logs', array_diff_key($_GET, ['page' => '', 'action' => '', 'p' => ''])); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modèle pour le téléchargement des logs -->
    <script type="text/template" id="log-entry-template">
        <tr class="{rowClass}">
            <td>{timestamp}</td>
            <td>{user}</td>
            <td>{action}</td>
            <td>{ip}</td>
            <td class="text-center"><span class="badge {badgeClass}">{level}</span></td>
        </tr>
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation des variables
            const refreshLogsBtn = document.getElementById('refreshLogsBtn');
            const refreshIndicator = document.getElementById('refreshIndicator');
            const statusMessage = document.getElementById('statusMessage');
            const statusText = document.getElementById('statusText');
            let isLoading = false;
            let logPollingInterval = null;

            // Fonction d'affichage des messages de statut
            function showStatus(message, type = 'info') {
                statusMessage.className = `alert alert-${type} mb-4`;
                statusText.textContent = message;
                statusMessage.classList.remove('d-none');

                // Masquer le message après 5 secondes si c'est un message de succès
                if (type === 'success') {
                    setTimeout(() => {
                        statusMessage.classList.add('d-none');
                    }, 5000);
                }
            }

            // Fonction de récupération asynchrone des logs
            async function fetchLogs() {
                if (isLoading) return;

                isLoading = true;
                refreshIndicator.classList.remove('d-none');
                showStatus('Chargement des logs en cours...', 'info');

                try {
                    // Construction de l'URL avec les filtres actuels
                    const urlParams = new URLSearchParams(window.location.search);

                    // Ajout du paramètre AJAX pour indiquer une requête asynchrone
                    urlParams.set('ajax', '1');

                    const response = await fetch(`${window.location.pathname}?${urlParams.toString()}`);

                    if (!response.ok) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }

                    const data = await response.json();

                    // Mise à jour du tableau des logs
                    updateLogsTable(data.logs);

                    // Mise à jour du compteur
                    updateLogCounter(data.totalLogs, data.logs.length);

                    showStatus('Logs chargés avec succès', 'success');

                } catch (error) {
                    console.error('Erreur lors de la récupération des logs:', error);
                    showStatus(`Erreur: ${error.message}`, 'danger');
                } finally {
                    isLoading = false;
                    refreshIndicator.classList.add('d-none');
                }
            }

            // Fonction de mise à jour du tableau des logs
            function updateLogsTable(logs) {
                const tableBody = document.querySelector('#logsTable tbody');
                if (!tableBody) return;

                // Template pour chaque entrée
                const template = document.getElementById('log-entry-template').innerHTML;

                // Si aucun log, afficher un message
                if (logs.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-3">Aucune activité à afficher.</td></tr>`;
                    return;
                }

                // Ne mettre à jour que si les logs ont changé
                const currentFirstLog = tableBody.querySelector('tr:first-child td:first-child');
                const currentLastLog = tableBody.querySelector('tr:last-child td:first-child');

                // Si le premier ou dernier log a changé, mettre à jour tout le tableau
                if (!currentFirstLog || !currentLastLog ||
                    currentFirstLog.textContent !== logs[0].timestamp ||
                    currentLastLog.textContent !== logs[logs.length - 1].timestamp) {

                    // Vider le tableau
                    tableBody.innerHTML = '';

                    // Ajouter chaque log
                    logs.forEach(log => {
                        // Déterminer la classe de ligne en fonction du niveau
                        let rowClass = '';
                        let badgeClass = 'bg-secondary';

                        if (log.level) {
                            switch(log.level.toUpperCase()) {
                                case 'ERROR':
                                    rowClass = 'table-danger';
                                    badgeClass = 'bg-danger';
                                    break;
                                case 'WARNING':
                                    rowClass = 'table-warning';
                                    badgeClass = 'bg-warning text-dark';
                                    break;
                                case 'SUCCESS':
                                    rowClass = 'table-success';
                                    badgeClass = 'bg-success';
                                    break;
                                case 'INFO':
                                    badgeClass = 'bg-info text-dark';
                                    break;
                                default:
                                    badgeClass = 'bg-secondary';
                            }
                        }

                        // Remplacer les variables dans le template
                        let row = template
                            .replace('{timestamp}', log.timestamp ? log.timestamp : '')
                            .replace('{user}', log.user ? log.user : '')
                            .replace('{action}', log.action ? log.action : '')
                            .replace('{ip}', log.ip ? log.ip : '')
                            .replace('{level}', log.level ? log.level.toUpperCase() : 'INFO')
                            .replace('{rowClass}', rowClass)
                            .replace('{badgeClass}', badgeClass);

                        // Ajouter la ligne au tableau
                        tableBody.innerHTML += row;
                    });
                }
            }

            // Fonction de mise à jour du compteur de logs
            function updateLogCounter(total, displayed) {
                const counter = document.querySelector('.text-muted');
                if (counter) {
                    counter.textContent = `Affichage de ${displayed} logs sur ${total}`;
                }
            }

            // Gestionnaire d'événement pour le bouton d'actualisation
            if (refreshLogsBtn) {
                refreshLogsBtn.addEventListener('click', fetchLogs);
            }

            // Initialiser la mise à jour périodique des logs si activée
            const autoRefresh = <?php echo defined('LOG_AUTO_REFRESH') && LOG_AUTO_REFRESH ? 'true' : 'false'; ?>;
            if (autoRefresh) {
                const refreshInterval = <?php echo defined('LOG_REFRESH_INTERVAL') ? LOG_REFRESH_INTERVAL : 30000; ?>;
                logPollingInterval = setInterval(fetchLogs, refreshInterval);

                // Arrêter l'intervalle quand l'utilisateur quitte la page
                window.addEventListener('beforeunload', () => {
                    if (logPollingInterval) {
                        clearInterval(logPollingInterval);
                    }
                });
            }

            // Fonctionnalité d'export
            const exportBtn = document.getElementById('exportLogsBtn');
            if (exportBtn) {
                exportBtn.addEventListener('click', function() {
                    exportLogsToCSV();
                });
            }

            function exportLogsToCSV() {
                const table = document.getElementById('logsTable');
                if (!table) return;

                // En-tête UTF-8 avec BOM pour Excel
                let csv = '\uFEFF';

                // En-têtes du CSV
                csv += 'Date et heure,Utilisateur,Action,Adresse IP,Niveau\n';

                // Lignes de données
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    if (row.style.display !== 'none') {
                        const cells = row.querySelectorAll('td');
                        if (cells.length >= 5) {
                            // Extraire les valeurs des cellules
                            const timestamp = cells[0].textContent.trim();
                            const user = cells[1].textContent.trim();
                            const action = cells[2].textContent.trim();
                            const ip = cells[3].textContent.trim();
                            const level = cells[4].textContent.trim();

                            // Échapper les champs CSV
                            const escapeCsv = (str) => {
                                if (str.includes(',') || str.includes('"') || str.includes('\n')) {
                                    return `"${str.replace(/"/g, '""')}"`;
                                }
                                return str;
                            };

                            // Ajouter la ligne au CSV
                            csv += `${escapeCsv(timestamp)},${escapeCsv(user)},${escapeCsv(action)},${escapeCsv(ip)},${escapeCsv(level)}\n`;
                        }
                    }
                });

                // Créer le blob et déclencher le téléchargement
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

<?php
// Fonctions auxiliaires pour le formatage des logs
function getLogRowClass($log) {
    if (!isset($log['level'])) {
        return '';
    }

    switch(strtoupper($log['level'])) {
        case 'ERROR':
            return 'table-danger';
        case 'WARNING':
            return 'table-warning';
        case 'SUCCESS':
            return 'table-success';
        default:
            return '';
    }
}

function getLogLevelBadgeClass($level) {
    switch(strtoupper($level)) {
        case 'ERROR':
            return 'bg-danger';
        case 'WARNING':
            return 'bg-warning text-dark';
        case 'SUCCESS':
            return 'bg-success';
        case 'INFO':
            return 'bg-info text-dark';
        default:
            return 'bg-secondary';
    }
}
?>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>