<?php
/**
 * Vue d'administration pour la maintenance du système
 *
 * Permet d'effectuer des opérations de maintenance technique:
 * - Purge du cache
 * - Optimisation de la base de données
 * - Gestion des logs
 * - Configuration système
 */
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">
                <i class="fas fa-tools me-2"></i>
                <?php echo $pageTitle; ?>
            </h1>

            <div>
                <a href="<?php echo url('admin'); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                </a>
            </div>
        </div>

        <!-- Message de retour -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Cartes de maintenance -->
        <div class="row">
            <!-- Gestion du cache -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-database me-2"></i>Gestion du cache</h5>
                    </div>
                    <div class="card-body">
                        <p>
                            Le cache du système améliore les performances en stockant temporairement des données fréquemment utilisées.
                            La purge du cache peut résoudre certains problèmes d'affichage ou de données obsolètes.
                        </p>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Le cache sera automatiquement régénéré après sa purge. Cette opération peut ralentir temporairement l'application.
                        </div>
                        <form method="post" class="mt-4">
                            <button type="submit" name="clear_cache" class="btn btn-warning">
                                <i class="fas fa-trash me-2"></i>Purger le cache
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Optimisation de la base de données -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-database me-2"></i>Optimisation de la base de données</h5>
                    </div>
                    <div class="card-body">
                        <p>
                            L'optimisation de la base de données permet de réduire sa taille, d'améliorer les performances des requêtes
                            et de réparer les tables potentiellement corrompues.
                        </p>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Cette opération peut prendre plusieurs minutes et ralentir temporairement l'application. Utilisez-la pendant les périodes de faible activité.
                        </div>
                        <form method="post" class="mt-4">
                            <button type="submit" name="optimize_db" class="btn btn-primary">
                                <i class="fas fa-wrench me-2"></i>Optimiser la base de données
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Gestion des logs -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Gestion des journaux d'activité</h5>
                    </div>
                    <div class="card-body">
                        <p>
                            Les journaux d'activité enregistrent les actions importantes effectuées sur la plateforme.
                            Ils sont essentiels pour le débogage et la sécurité, mais peuvent occuper beaucoup d'espace au fil du temps.
                        </p>

                        <div class="d-flex justify-content-between mb-3">
                            <a href="<?php echo url('admin', 'logs'); ?>" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-2"></i>Consulter les journaux
                            </a>
                        </div>

                        <form method="post" class="mt-4">
                            <div class="mb-3">
                                <label for="log_days" class="form-label">Purger les journaux plus anciens que</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="log_days" name="log_days" min="1" max="365" value="30">
                                    <span class="input-group-text">jours</span>
                                </div>
                                <div class="form-text">Les journaux sont automatiquement purgés après 30 jours par défaut.</div>
                            </div>
                            <button type="submit" name="purge_logs" class="btn btn-danger">
                                <i class="fas fa-eraser me-2"></i>Purger les anciens journaux
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- État du système -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-heartbeat me-2"></i>État du système</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-3">Informations serveur</h6>
                        <ul class="list-group mb-4">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Serveur Web</span>
                                <span class="badge bg-primary rounded-pill"><?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Version PHP</span>
                                <span class="badge bg-primary rounded-pill"><?php echo phpversion(); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Extensions PHP chargées</span>
                                <span class="badge bg-primary rounded-pill"><?php echo count(get_loaded_extensions()); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Mémoire limite PHP</span>
                                <span class="badge bg-primary rounded-pill"><?php echo ini_get('memory_limit'); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>Taille maximale d'upload</span>
                                <span class="badge bg-primary rounded-pill"><?php echo ini_get('upload_max_filesize'); ?></span>
                            </li>
                        </ul>

                        <h6 class="mb-3">Vérification des dossiers système</h6>
                        <ul class="list-group">
                            <?php
                            // Vérifier les dossiers critiques
                            $folders = [
                                ROOT_PATH . '/logs' => 'Journaux',
                                ROOT_PATH . '/cache' => 'Cache',
                                ROOT_PATH . '/public/uploads' => 'Uploads',
                                ROOT_PATH . '/config' => 'Configuration'
                            ];

                            foreach ($folders as $path => $label):
                                $exists = is_dir($path);
                                $writable = $exists && is_writable($path);
                                $statusClass = !$exists ? 'danger' : ($writable ? 'success' : 'warning');
                                $statusIcon = !$exists ? 'times' : ($writable ? 'check' : 'exclamation-triangle');
                                $statusText = !$exists ? 'Manquant' : ($writable ? 'OK' : 'Non inscriptible');
                                ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?php echo $label; ?> (<?php echo basename($path); ?>)</span>
                                    <span class="badge bg-<?php echo $statusClass; ?> rounded-pill">
                                    <i class="fas fa-<?php echo $statusIcon; ?> me-1"></i>
                                    <?php echo $statusText; ?>
                                </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations supplémentaires -->
        <div class="card shadow-sm mt-2">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations supplémentaires</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Conseil:</strong> Il est recommandé d'effectuer régulièrement les opérations de maintenance pour garantir les performances optimales du système.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <h6>Maintenance automatique</h6>
                        <ul>
                            <li>Les journaux d'activité sont automatiquement purgés après 30 jours.</li>
                            <li>Le cache est automatiquement nettoyé toutes les 24 heures.</li>
                            <li>Les fichiers temporaires sont supprimés après 24 heures.</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Recommandations</h6>
                        <ul>
                            <li>Effectuez une sauvegarde de la base de données avant toute opération de maintenance.</li>
                            <li>Planifiez les opérations pendant les périodes de faible activité.</li>
                            <li>Consultez les journaux régulièrement pour détecter les anomalies.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>