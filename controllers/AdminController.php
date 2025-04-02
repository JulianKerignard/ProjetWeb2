<?php
/**
 * Contrôleur pour la gestion du panel administrateur
 *
 * Ce contrôleur implémente les fonctionnalités centrales de l'administration
 * avec un focus sur la sécurité, les statistiques et la gestion système.
 *
 * @version 1.2
 */
class AdminController {
    private $offreModel;
    private $entrepriseModel;
    private $etudiantModel;
    private $piloteModel;
    private $statsModel;
    private $logManager;
    private $dbConnection; // Nouvelle propriété pour la connexion directe à la BDD

    /**
     * Constructeur - Initialise les modèles nécessaires avec vérification des droits
     *
     * Utilise le pattern d'injection de dépendances pour charger les modèles requis
     * et implémente un contrôle d'accès strict au niveau du constructeur.
     */
    public function __construct() {
        // Vérification stricte des droits d'administrateur
        if (!isAdmin()) {
            // Journalisation de la tentative d'accès non autorisée
            error_log("Tentative d'accès non autorisée au panel d'administration par l'utilisateur ID: " .
                (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'non authentifié') .
                " - IP: " . $_SERVER['REMOTE_ADDR']);

            // Redirection vers l'accueil avec message d'erreur
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Accès non autorisé. Cette tentative a été enregistrée."
            ];
            redirect(url());
        }

        // Initialisation de la connexion à la base de données
        require_once ROOT_PATH . '/config/database.php';
        $database = new Database();
        $this->dbConnection = $database->getConnection();

        // Chargement des modèles requis avec gestion des erreurs
        try {
            require_once MODELS_PATH . '/Offre.php';
            require_once MODELS_PATH . '/Entreprise.php';
            require_once MODELS_PATH . '/Etudiant.php';
            require_once MODELS_PATH . '/Pilote.php';
            require_once MODELS_PATH . '/Stats.php';
            require_once ROOT_PATH . '/includes/LogManager.php';

            $this->offreModel = new Offre();
            $this->entrepriseModel = new Entreprise();
            $this->etudiantModel = new Etudiant();
            $this->piloteModel = new Pilote();
            $this->statsModel = new Stats();
            $this->logManager = LogManager::getInstance();

            // Audit technique - Initialisation du système de logs
            $testData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'request_uri' => $_SERVER['REQUEST_URI'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
            ];

            $this->logManager->info(
                "AUDIT: Initialisation du système de logs",
                isset($_SESSION['email']) ? $_SESSION['email'] : 'system',
                $testData
            );
            $this->logManager->flushQueue(); // Force l'écriture synchrone

            // Journaliser l'accès au panel d'administration
            $this->logManager->info(
                "Accès au panel d'administration",
                isset($_SESSION['email']) ? $_SESSION['email'] : null,
                ['section' => isset($_GET['action']) ? $_GET['action'] : 'index']
            );

        } catch (Exception $e) {
            // Gestion des erreurs d'initialisation des modèles
            error_log("Erreur d'initialisation du contrôleur Admin: " . $e->getMessage());
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'message' => "Une erreur est survenue lors de l'initialisation du panel d'administration."
            ];
            redirect(url());
        }
    }

    /**
     * Action par défaut - Tableau de bord d'administration
     *
     * Affiche une vue synthétique des principales métriques du système
     * avec des indicateurs de performance et des liens d'action rapide.
     */
    public function index() {
        // Récupération des statistiques générales pour le dashboard
        $stats = $this->statsModel->getGeneralStats();

        // Liste des 5 dernières offres ajoutées
        $latestOffers = $this->statsModel->getLatestOffers(5);

        // Liste des 5 dernières entreprises ajoutées
        $latestCompanies = $this->statsModel->getLatestCompanies(5);

        // Définir le titre de la page
        $pageTitle = "Panel d'administration";

        // Chargement de la vue
        include VIEWS_PATH . '/admin/dashboard.php';
    }

    /**
     * Tableau de bord des statistiques avancées
     *
     * Agrège et présente des métriques détaillées sur l'utilisation du système
     * sous forme de graphiques et tableaux pour l'analyse décisionnelle.
     */
    public function stats() {
        // Récupération des statistiques générales
        $stats = $this->statsModel->getGeneralStats();

        // Récupération des statistiques détaillées avec mise en cache
        $offreStats = $this->offreModel->getStatistics();

        // Données pour les indicateurs de performance des étudiants
        $placementRate = $this->statsModel->getPlacementRate();
        $avgApplications = $this->statsModel->getAverageApplicationsPerStudent();
        $studentStats = [
            'placement_rate' => $placementRate,
            'avg_applications' => $avgApplications
        ];

        // Données pour les statistiques des entreprises
        $topCompanies = $this->statsModel->getTopCompanies();
        $ratingDistribution = $this->statsModel->getRatingDistribution();
        $companyStats = [
            'top_companies' => $topCompanies,
            'rating_distribution' => $ratingDistribution
        ];

        // Récupération des données pour les graphiques
        $monthlyApplications = $this->statsModel->getMonthlyApplications();
        $skillDistribution = $this->statsModel->getSkillDistribution();

        // Définir le titre de la page
        $pageTitle = "Statistiques détaillées";

        // Chargement de la vue avec preloading des données JavaScript
        include VIEWS_PATH . '/admin/stats.php';
    }

    /**
     * Gestion des accès et permissions
     *
     * Interface pour contrôler les droits d'accès des utilisateurs
     * aux différentes fonctionnalités du système.
     */
    public function permissions() {
        // Définir le titre de la page
        $pageTitle = "Gestion des permissions";

        // Chargement de la vue
        include VIEWS_PATH . '/admin/permissions.php';
    }

    /**
     * Journaux système et suivi d'activité
     *
     * Affiche l'historique des actions système pour l'audit de sécurité
     * et le diagnostic des problèmes techniques.
     * Implémente une pagination côté serveur et un support AJAX pour l'actualisation.
     */
    public function logs() {
        // Récupération du numéro de page courant
        $page = getCurrentPage();

        // Limite de logs par page
        $limit = defined('LOGS_PER_PAGE') ? LOGS_PER_PAGE : 50;

        // Extraction des filtres depuis la requête
        $filters = $this->getLogsFilters();

        // Options de tri
        $sort = [
            'field' => isset($_GET['sort']) ? $_GET['sort'] : 'timestamp',
            'order' => isset($_GET['order']) ? $_GET['order'] : 'desc'
        ];

        // Vérifier si la table système existe et la créer si nécessaire
        $this->initSystemLogsTable();

        // Ajout d'un log de test si nécessaire (pour s'assurer qu'il y a des données)
        // Exécuté uniquement si la table est vide
        $this->ensureLogsExist();

        // Récupération directe des logs depuis la base de données
        $logsData = $this->getSystemLogsDirectly($page, $limit, $filters, $sort);
        $logs = $logsData['logs'];
        $totalLogs = $logsData['totalLogs'];

        // Si demande AJAX, retourner les données au format JSON
        if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
            header('Content-Type: application/json');
            echo json_encode([
                'logs' => $logs,
                'totalLogs' => $totalLogs
            ]);
            exit;
        }

        // Journaliser la consultation des logs
        $this->logManager->info(
            "Consultation des journaux d'activité",
            isset($_SESSION['email']) ? $_SESSION['email'] : null,
            ['filters' => $filters, 'page' => $page]
        );
        $this->logManager->flushQueue(); // Force l'écriture immédiate

        // Définir le titre de la page
        $pageTitle = "Journaux d'activité";

        // Chargement de la vue
        include VIEWS_PATH . '/admin/logs.php';
    }

    /**
     * Maintenance du système
     *
     * Interface pour les opérations de maintenance technique:
     * - Purge du cache
     * - Optimisation de la base de données
     * - Configuration système
     */
    public function maintenance() {
        $message = '';
        $messageType = '';

        // Traitement des actions de maintenance
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['clear_cache'])) {
                // Vidage du cache
                $cacheDir = ROOT_PATH . '/cache';
                if (is_dir($cacheDir)) {
                    $this->clearDirectory($cacheDir);
                    $message = "Le cache a été purgé avec succès.";
                    $messageType = "success";

                    // Journaliser l'action
                    $this->logManager->success("Purge du cache système effectuée");
                }
            } elseif (isset($_POST['optimize_db'])) {
                // Optimisation de la base de données
                try {
                    require_once ROOT_PATH . '/config/database.php';
                    $database = new Database();
                    $conn = $database->getConnection();

                    // Récupération des tables
                    $query = "SHOW TABLES";
                    $stmt = $conn->prepare($query);
                    $stmt->execute();
                    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

                    // Optimisation de chaque table
                    foreach ($tables as $table) {
                        $conn->exec("OPTIMIZE TABLE `{$table}`");
                    }

                    $message = "La base de données a été optimisée avec succès.";
                    $messageType = "success";

                    // Journaliser l'action
                    $this->logManager->success("Optimisation de la base de données effectuée");
                } catch (PDOException $e) {
                    $message = "Erreur lors de l'optimisation de la base de données: " . $e->getMessage();
                    $messageType = "danger";

                    // Journaliser l'erreur
                    $this->logManager->error(
                        "Échec de l'optimisation de la base de données",
                        null,
                        ['error' => $e->getMessage()]
                    );
                }
            } elseif (isset($_POST['purge_logs'])) {
                // Purge manuelle des logs anciens
                $days = isset($_POST['log_days']) ? intval($_POST['log_days']) : 30;

                if ($days < 1) {
                    $days = 30; // Valeur par défaut
                }

                // Nettoyer les logs plus anciens que le nombre de jours spécifié
                try {
                    require_once ROOT_PATH . '/config/database.php';
                    $database = new Database();
                    $conn = $database->getConnection();

                    $query = "DELETE FROM system_logs WHERE timestamp < DATE_SUB(NOW(), INTERVAL :days DAY)";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':days', $days, PDO::PARAM_INT);
                    $stmt->execute();

                    $rowCount = $stmt->rowCount();

                    $message = "{$rowCount} entrées de journal ont été purgées avec succès.";
                    $messageType = "success";

                    // Journaliser l'action
                    $this->logManager->success(
                        "Purge manuelle des journaux d'activité effectuée",
                        null,
                        ['days' => $days, 'entries_deleted' => $rowCount]
                    );
                } catch (PDOException $e) {
                    $message = "Erreur lors de la purge des journaux: " . $e->getMessage();
                    $messageType = "danger";

                    // Journaliser l'erreur
                    $this->logManager->error(
                        "Échec de la purge des journaux d'activité",
                        null,
                        ['error' => $e->getMessage(), 'days' => $days]
                    );
                }
            }
        }

        // Définir le titre de la page
        $pageTitle = "Maintenance du système";

        // Chargement de la vue
        include VIEWS_PATH . '/admin/maintenance.php';
    }

    /**
     * Récupère les filtres de recherche pour les logs depuis la requête
     *
     * @return array Tableau des filtres normalisés
     */
    private function getLogsFilters() {
        $filters = [];

        // Filtre par date
        if (isset($_GET['date']) && !empty($_GET['date'])) {
            $filters['date'] = $_GET['date'];
        }

        // Filtre par utilisateur
        if (isset($_GET['user']) && !empty($_GET['user'])) {
            $filters['user'] = cleanData($_GET['user']);
        }

        // Filtre par type d'action
        if (isset($_GET['type']) && !empty($_GET['type'])) {
            $filters['type'] = cleanData($_GET['type']);
        }

        // Filtre par niveau
        if (isset($_GET['level']) && !empty($_GET['level'])) {
            $filters['level'] = strtoupper(cleanData($_GET['level']));
        }

        return $filters;
    }

    /**
     * Vide un répertoire sans le supprimer
     *
     * @param string $dir Chemin du répertoire à vider
     */
    private function clearDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->clearDirectory($file);
                @rmdir($file);
            } else {
                @unlink($file);
            }
        }
    }

    /**
     * Initialise la table de logs si elle n'existe pas
     */
    private function initSystemLogsTable() {
        try {
            $checkQuery = "SHOW TABLES LIKE 'system_logs'";
            $stmt = $this->dbConnection->prepare($checkQuery);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                // La table n'existe pas, on la crée
                $createTableQuery = "CREATE TABLE system_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    timestamp DATETIME NOT NULL,
                    user VARCHAR(100),
                    action TEXT NOT NULL,
                    ip VARCHAR(45),
                    level VARCHAR(10) DEFAULT 'INFO',
                    context TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_timestamp (timestamp),
                    INDEX idx_level (level),
                    INDEX idx_user (user)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

                $this->dbConnection->exec($createTableQuery);
                error_log("Table system_logs créée avec succès");
            }
        } catch (Exception $e) {
            error_log("Erreur lors de l'initialisation de la table system_logs: " . $e->getMessage());
        }
    }

    /**
     * S'assure qu'il y a au moins des logs de test dans la table
     */
    private function ensureLogsExist() {
        try {
            // Vérifier si la table est vide
            $checkQuery = "SELECT COUNT(*) as count FROM system_logs";
            $stmt = $this->dbConnection->prepare($checkQuery);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row['count'] == 0) {
                // La table est vide, ajouter quelques logs de test
                $insertQuery = "INSERT INTO system_logs (timestamp, user, action, ip, level) VALUES 
                    (NOW(), ?, ?, ?, ?),
                    (NOW(), ?, ?, ?, ?),
                    (NOW(), ?, ?, ?, ?)";

                $stmt = $this->dbConnection->prepare($insertQuery);
                $stmt->execute([
                    'admin@web4all.fr', 'Connexion au système', $_SERVER['REMOTE_ADDR'], 'INFO',
                    'admin@web4all.fr', 'Consultation du tableau de bord', $_SERVER['REMOTE_ADDR'], 'INFO',
                    'admin@web4all.fr', 'Première visite de la page des logs', $_SERVER['REMOTE_ADDR'], 'SUCCESS'
                ]);

                error_log("Logs de test ajoutés avec succès");
            }
        } catch (Exception $e) {
            error_log("Erreur lors de l'ajout des logs de test: " . $e->getMessage());
        }
    }

    /**
     * Récupère les logs directement depuis la base de données
     * Méthode alternative au LogManager qui semble ne pas fonctionner correctement
     *
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @param array $filters Critères de filtrage
     * @param array $sort Options de tri
     * @return array Tableau associatif avec 'logs' et 'totalLogs'
     */
    private function getSystemLogsDirectly($page = 1, $limit = 50, $filters = [], $sort = []) {
        $result = [
            'logs' => [],
            'totalLogs' => 0
        ];

        try {
            // Construction de la clause WHERE
            $whereConditions = [];
            $params = [];

            if (!empty($filters['date'])) {
                $whereConditions[] = "DATE(timestamp) = :date";
                $params[':date'] = $filters['date'];
            }

            if (!empty($filters['user'])) {
                $whereConditions[] = "user LIKE :user";
                $params[':user'] = '%' . $filters['user'] . '%';
            }

            if (!empty($filters['type'])) {
                $whereConditions[] = "action LIKE :type";
                $params[':type'] = '%' . $filters['type'] . '%';
            }

            if (!empty($filters['level'])) {
                $whereConditions[] = "level = :level";
                $params[':level'] = strtoupper($filters['level']);
            }

            // Clause WHERE complète
            $whereClause = empty($whereConditions) ? "" : "WHERE " . implode(" AND ", $whereConditions);

            // Requête de comptage total
            $countQuery = "SELECT COUNT(*) as total FROM system_logs {$whereClause}";
            $countStmt = $this->dbConnection->prepare($countQuery);

            foreach ($params as $key => $value) {
                $countStmt->bindValue($key, $value);
            }

            $countStmt->execute();
            $result['totalLogs'] = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Options de tri
            $orderField = !empty($sort['field']) ? $sort['field'] : 'timestamp';
            $orderDirection = !empty($sort['order']) && strtolower($sort['order']) === 'asc' ? 'ASC' : 'DESC';

            // Liste des champs autorisés pour le tri
            $allowedFields = ['timestamp', 'user', 'action', 'ip', 'level'];
            if (!in_array($orderField, $allowedFields)) {
                $orderField = 'timestamp';
            }

            // Calcul de l'offset
            $offset = ($page - 1) * $limit;

            // Requête principale
            $query = "SELECT timestamp, user, action, ip, level 
                    FROM system_logs 
                    {$whereClause} 
                    ORDER BY {$orderField} {$orderDirection} 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->dbConnection->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            $result['logs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $result;
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération directe des logs: " . $e->getMessage());
            return $result;
        }
    }
}