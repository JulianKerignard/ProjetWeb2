<?php
/**
 * Classe LogManager
 *
 * Gestionnaire centralisé des journaux d'activité avec support pour:
 * - Journalisation asynchrone via queue
 * - Niveaux de criticité (INFO, WARNING, ERROR, SUCCESS)
 * - Rotation et purge automatique des logs
 * - Stockage flexible (fichier, base de données)
 *
 * @version 1.0
 */
class LogManager {
    /** @var string Répertoire de stockage des fichiers de log */
    private $logDir;

    /** @var string Nom du fichier de log principal */
    private $logFile;

    /** @var PDO Instance de connexion à la base de données */
    private $dbConnection;

    /** @var bool Indicateur d'utilisation de la BDD */
    private $useDatabase;

    /** @var string Nom de la table pour les logs */
    private $logTable = 'system_logs';

    /** @var array File d'attente des logs pour traitement asynchrone */
    private static $logQueue = [];

    /** @var int Taille maximale de la file d'attente avant déchargement */
    private $queueMaxSize = 20;

    /** @var int Âge maximal des logs en jours (pour rotation) */
    private $maxLogAge = 30;

    /** @var LogManager Instance singleton */
    private static $instance = null;

    /**
     * Constructeur privé - Pattern Singleton
     *
     * @param bool $useDatabase Utiliser la BDD pour le stockage
     * @param string $logDir Répertoire des logs (si stockage fichier)
     */
    private function __construct($useDatabase = true, $logDir = null) {
        // Définir le répertoire de logs
        $this->logDir = $logDir ?: ROOT_PATH . '/logs';

        // Créer le répertoire s'il n'existe pas
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }

        // Définir le fichier de log principal
        $this->logFile = $this->logDir . '/activity_' . date('Y-m') . '.log';

        // Initialiser le mode de stockage
        $this->useDatabase = $useDatabase;

        // Si stockage en BDD, préparer la connexion
        if ($this->useDatabase) {
            $this->initDatabaseConnection();
        }

        // Enregistrer la fonction de déchargement à la fin de l'exécution
        register_shutdown_function([$this, 'flushQueue']);

        // Initialiser la rotation des logs
        $this->initLogRotation();
    }

    /**
     * Obtient l'instance unique du LogManager (Singleton)
     *
     * @param bool $useDatabase Utiliser la BDD pour le stockage
     * @param string $logDir Répertoire des logs (si stockage fichier)
     * @return LogManager
     */
    public static function getInstance($useDatabase = true, $logDir = null) {
        if (self::$instance === null) {
            self::$instance = new self($useDatabase, $logDir);
        }
        return self::$instance;
    }

    /**
     * Initialise la connexion à la base de données
     */
    private function initDatabaseConnection() {
        // Vérification si la table existe, sinon la créer
        try {
            require_once ROOT_PATH . '/config/database.php';
            $database = new Database();
            $this->dbConnection = $database->getConnection();

            // Vérifier si la table existe
            $query = "SHOW TABLES LIKE '{$this->logTable}'";
            $stmt = $this->dbConnection->prepare($query);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                // Créer la table si elle n'existe pas
                $this->createLogTable();
            }
        } catch (PDOException $e) {
            // En cas d'erreur de connexion, basculer vers le stockage fichier
            $this->useDatabase = false;
            error_log("Erreur de connexion BDD dans LogManager: " . $e->getMessage() . ". Basculement vers stockage fichier.");
        }
    }

    /**
     * Crée la table de logs dans la base de données
     */
    private function createLogTable() {
        $query = "CREATE TABLE {$this->logTable} (
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

        try {
            $this->dbConnection->exec($query);
        } catch (PDOException $e) {
            error_log("Erreur de création de la table de logs: " . $e->getMessage());
            $this->useDatabase = false;
        }
    }

    /**
     * Initialise la rotation automatique des logs
     */
    private function initLogRotation() {
        // Vérifier si la rotation a déjà été effectuée aujourd'hui
        $lastRotationFile = $this->logDir . '/.last_rotation';
        $currentDate = date('Y-m-d');

        if (file_exists($lastRotationFile)) {
            $lastRotation = file_get_contents($lastRotationFile);

            // Si la rotation a déjà été effectuée aujourd'hui, ne rien faire
            if ($lastRotation === $currentDate) {
                return;
            }
        }

        // Exécuter la rotation (1% de chance par requête pour répartir la charge)
        if (mt_rand(1, 100) <= 1) {
            $this->rotateLogs();
            file_put_contents($lastRotationFile, $currentDate);
        }
    }

    /**
     * Effectue la rotation et purge des logs anciens
     */
    private function rotateLogs() {
        $cutoffDate = date('Y-m-d', strtotime("-{$this->maxLogAge} days"));

        if ($this->useDatabase) {
            // Purge en BDD
            try {
                $query = "DELETE FROM {$this->logTable} WHERE DATE(timestamp) < :cutoff_date";
                $stmt = $this->dbConnection->prepare($query);
                $stmt->bindParam(':cutoff_date', $cutoffDate);
                $stmt->execute();

                // Optimiser la table après la purge
                $this->dbConnection->exec("OPTIMIZE TABLE {$this->logTable}");
            } catch (PDOException $e) {
                error_log("Erreur lors de la purge des logs: " . $e->getMessage());
            }
        } else {
            // Purge des fichiers
            $cutoffTimestamp = strtotime($cutoffDate);

            // Parcourir les fichiers de log
            $logFiles = glob($this->logDir . '/activity_*.log');
            foreach ($logFiles as $file) {
                // Extraire la date du nom de fichier (format: activity_YYYY-MM.log)
                $filename = basename($file);

                // Vérifier si le fichier est antérieur à la date limite
                $fileTime = filemtime($file);
                if ($fileTime && $fileTime < $cutoffTimestamp) {
                    // Supprimer le fichier ou le déplacer dans une archive
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Ajoute une entrée de journal à la file d'attente
     *
     * @param string $action Description de l'action
     * @param string $level Niveau de criticité (INFO, WARNING, ERROR, SUCCESS)
     * @param string $user Identifiant de l'utilisateur (email par défaut)
     * @param array $context Données contextuelles supplémentaires
     */
    public function log($action, $level = 'INFO', $user = null, $context = []) {
        // Si l'utilisateur n'est pas spécifié, utiliser celui de la session
        if ($user === null && isset($_SESSION['email'])) {
            $user = $_SESSION['email'];
        }

        // Récupérer l'adresse IP du client
        $ip = $this->getClientIP();

        // Créer l'entrée de log
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user' => $user,
            'action' => $action,
            'ip' => $ip,
            'level' => strtoupper($level),
            'context' => $context ? json_encode($context) : null
        ];

        // Ajouter à la file d'attente
        self::$logQueue[] = $logEntry;

        // Si la file d'attente atteint sa taille maximale, la décharger
        if (count(self::$logQueue) >= $this->queueMaxSize) {
            $this->flushQueue();
        }
    }

    /**
     * Effectue un log de niveau INFO
     *
     * @param string $action Description de l'action
     * @param string $user Identifiant de l'utilisateur
     * @param array $context Données contextuelles supplémentaires
     */
    public function info($action, $user = null, $context = []) {
        $this->log($action, 'INFO', $user, $context);
    }

    /**
     * Effectue un log de niveau WARNING
     *
     * @param string $action Description de l'action
     * @param string $user Identifiant de l'utilisateur
     * @param array $context Données contextuelles supplémentaires
     */
    public function warning($action, $user = null, $context = []) {
        $this->log($action, 'WARNING', $user, $context);
    }

    /**
     * Effectue un log de niveau ERROR
     *
     * @param string $action Description de l'action
     * @param string $user Identifiant de l'utilisateur
     * @param array $context Données contextuelles supplémentaires
     */
    public function error($action, $user = null, $context = []) {
        $this->log($action, 'ERROR', $user, $context);
    }

    /**
     * Effectue un log de niveau SUCCESS
     *
     * @param string $action Description de l'action
     * @param string $user Identifiant de l'utilisateur
     * @param array $context Données contextuelles supplémentaires
     */
    public function success($action, $user = null, $context = []) {
        $this->log($action, 'SUCCESS', $user, $context);
    }

    /**
     * Décharge la file d'attente des logs dans le stockage permanent
     */
    public function flushQueue() {
        if (empty(self::$logQueue)) {
            return;
        }

        if ($this->useDatabase) {
            $this->flushQueueToDatabase();
        } else {
            $this->flushQueueToFile();
        }

        // Vider la file d'attente
        self::$logQueue = [];
    }

    /**
     * Décharge la file d'attente dans la base de données
     */
    private function flushQueueToDatabase() {
        if (!$this->dbConnection) {
            $this->flushQueueToFile();
            return;
        }

        try {
            // Préparer la requête d'insertion
            $query = "INSERT INTO {$this->logTable} 
                     (timestamp, user, action, ip, level, context) 
                     VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $this->dbConnection->prepare($query);

            // Démarrer une transaction pour les insertions multiples
            $this->dbConnection->beginTransaction();

            foreach (self::$logQueue as $log) {
                $stmt->execute([
                    $log['timestamp'],
                    $log['user'],
                    $log['action'],
                    $log['ip'],
                    $log['level'],
                    $log['context']
                ]);
            }

            $this->dbConnection->commit();
        } catch (PDOException $e) {
            // En cas d'échec, annuler la transaction et basculer vers le fichier
            if ($this->dbConnection->inTransaction()) {
                $this->dbConnection->rollBack();
            }
            error_log("Erreur lors de l'écriture des logs en BDD: " . $e->getMessage());
            $this->flushQueueToFile();
        }
    }

    /**
     * Décharge la file d'attente dans un fichier de log
     */
    private function flushQueueToFile() {
        // Préparation du contenu à écrire
        $content = '';
        foreach (self::$logQueue as $log) {
            $contextStr = $log['context'] ? " | Context: {$log['context']}" : '';
            $content .= "[{$log['timestamp']}] [{$log['level']}] [{$log['user']}] [{$log['ip']}] {$log['action']}{$contextStr}\n";
        }

        // Écriture dans le fichier avec verrouillage
        file_put_contents(
            $this->logFile,
            $content,
            FILE_APPEND | LOCK_EX
        );
    }

    /**
     * Récupère les logs avec pagination et filtrage
     *
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @param array $filters Critères de filtrage (date, user, level, action)
     * @param array $sort Options de tri (field, order)
     * @return array Tableau associatif avec 'logs' et 'totalLogs'
     */
    public function getLogs($page = 1, $limit = 50, $filters = [], $sort = []) {
        $result = [
            'logs' => [],
            'totalLogs' => 0
        ];

        if ($this->useDatabase) {
            return $this->getLogsFromDatabase($page, $limit, $filters, $sort);
        } else {
            return $this->getLogsFromFile($page, $limit, $filters, $sort);
        }
    }

    /**
     * Récupère les logs depuis la base de données
     *
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @param array $filters Critères de filtrage
     * @param array $sort Options de tri
     * @return array Tableau associatif avec 'logs' et 'totalLogs'
     */
    private function getLogsFromDatabase($page = 1, $limit = 50, $filters = [], $sort = []) {
        $result = [
            'logs' => [],
            'totalLogs' => 0
        ];

        if (!$this->dbConnection) {
            return $result;
        }

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

            if (!empty($filters['action'])) {
                $whereConditions[] = "action LIKE :action";
                $params[':action'] = '%' . $filters['action'] . '%';
            }

            if (!empty($filters['type'])) {
                $whereConditions[] = "action LIKE :type";
                $params[':type'] = '%' . $filters['type'] . '%';
            }

            if (!empty($filters['level'])) {
                $whereConditions[] = "level = :level";
                $params[':level'] = strtoupper($filters['level']);
            }

            // Construction de la clause WHERE complète
            $whereClause = empty($whereConditions) ? "" : "WHERE " . implode(" AND ", $whereConditions);

            // Requête de comptage total
            $countQuery = "SELECT COUNT(*) as total FROM {$this->logTable} {$whereClause}";
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

            // Requête principale avec tri et pagination
            $query = "SELECT timestamp, user, action, ip, level 
                      FROM {$this->logTable} 
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
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des logs: " . $e->getMessage());
            return $result;
        }
    }

    /**
     * Récupère les logs depuis les fichiers
     *
     * @param int $page Numéro de page
     * @param int $limit Nombre d'éléments par page
     * @param array $filters Critères de filtrage
     * @param array $sort Options de tri
     * @return array Tableau associatif avec 'logs' et 'totalLogs'
     */
    private function getLogsFromFile($page = 1, $limit = 50, $filters = [], $sort = []) {
        $result = [
            'logs' => [],
            'totalLogs' => 0
        ];

        // Si le fichier de log n'existe pas, retourner un résultat vide
        if (!file_exists($this->logFile)) {
            return $result;
        }

        // Lire le fichier de log
        $content = file_get_contents($this->logFile);
        $lines = explode("\n", $content);
        $logs = [];

        // Parser chaque ligne
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;

            // Format attendu: [YYYY-MM-DD HH:II:SS] [LEVEL] [USER] [IP] ACTION | Context: {}
            if (preg_match('/\[(.*?)\] \[(.*?)\] \[(.*?)\] \[(.*?)\] (.*?)(?:\| Context: (.*))?$/', $line, $matches)) {
                $timestamp = $matches[1] ?? '';
                $level = $matches[2] ?? 'INFO';
                $user = $matches[3] ?? '';
                $ip = $matches[4] ?? '';
                $action = $matches[5] ?? '';
                $context = $matches[6] ?? null;

                $log = [
                    'timestamp' => $timestamp,
                    'level' => $level,
                    'user' => $user,
                    'ip' => $ip,
                    'action' => $action,
                    'context' => $context
                ];

                // Appliquer les filtres
                if ($this->filterLog($log, $filters)) {
                    $logs[] = $log;
                }
            }
        }

        // Nombre total de logs après filtrage
        $result['totalLogs'] = count($logs);

        // Trier les logs
        $this->sortLogs($logs, $sort);

        // Appliquer la pagination
        $offset = ($page - 1) * $limit;
        $result['logs'] = array_slice($logs, $offset, $limit);

        return $result;
    }

    /**
     * Filtre un log selon les critères
     *
     * @param array $log Entrée de log
     * @param array $filters Critères de filtrage
     * @return bool True si le log correspond aux filtres
     */
    private function filterLog($log, $filters) {
        // Filtre par date
        if (!empty($filters['date'])) {
            $logDate = substr($log['timestamp'], 0, 10);
            if ($logDate !== $filters['date']) {
                return false;
            }
        }

        // Filtre par utilisateur
        if (!empty($filters['user'])) {
            if (stripos($log['user'], $filters['user']) === false) {
                return false;
            }
        }

        // Filtre par action
        if (!empty($filters['action'])) {
            if (stripos($log['action'], $filters['action']) === false) {
                return false;
            }
        }

        // Filtre par type
        if (!empty($filters['type'])) {
            if (stripos($log['action'], $filters['type']) === false) {
                return false;
            }
        }

        // Filtre par niveau
        if (!empty($filters['level'])) {
            if (strtoupper($log['level']) !== strtoupper($filters['level'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Trie les logs selon les critères
     *
     * @param array &$logs Référence au tableau de logs
     * @param array $sort Options de tri
     */
    private function sortLogs(&$logs, $sort) {
        $field = !empty($sort['field']) ? $sort['field'] : 'timestamp';
        $order = !empty($sort['order']) && strtolower($sort['order']) === 'asc' ? SORT_ASC : SORT_DESC;

        // Liste des champs autorisés pour le tri
        $allowedFields = ['timestamp', 'user', 'action', 'ip', 'level'];
        if (!in_array($field, $allowedFields)) {
            $field = 'timestamp';
        }

        // Extraire la colonne de tri
        $sortColumn = array_column($logs, $field);

        // Trier les logs
        array_multisort($sortColumn, $order, $logs);
    }

    /**
     * Obtient l'adresse IP du client avec support proxy
     *
     * @return string
     */
    private function getClientIP() {
        // Vérifier les en-têtes de proxy
        $headers = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',  // Proxy standard
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'            // Adresse directe
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                // Pour X-Forwarded-For, on prend la première IP (la plus proche du client)
                if ($header === 'HTTP_X_FORWARDED_FOR') {
                    $ips = explode(',', $_SERVER[$header]);
                    $ip = trim($ips[0]);
                } else {
                    $ip = $_SERVER[$header];
                }

                // Valider l'IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback sur REMOTE_ADDR si tout a échoué
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Convertit le niveau de log en identifiant numérique pour le tri
     *
     * @param string $level Niveau de log (ERROR, WARNING, INFO, SUCCESS)
     * @return int Valeur numérique pour le tri
     */
    private function getLevelSortValue($level) {
        switch (strtoupper($level)) {
            case 'ERROR': return 4;
            case 'WARNING': return 3;
            case 'INFO': return 2;
            case 'SUCCESS': return 1;
            default: return 0;
        }
    }
}