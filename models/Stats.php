<?php
/**
 * Modèle pour la gestion des statistiques globales de l'application
 *
 * Ce modèle implémente des requêtes SQL optimisées pour l'agrégation
 * et l'analyse des données à des fins statistiques.
 */
class Stats {
    private $conn;
    private $cacheExpiry = 300; // Durée de vie du cache en secondes (5 minutes)
    private $cacheEnabled = true; // Activation du cache

    /**
     * Constructeur - Initialise la connexion à la BDD et le système de cache
     */
    public function __construct() {
        // Initialisation de la connexion à la base de données
        require_once ROOT_PATH . '/config/database.php';
        $database = new Database();
        $this->conn = $database->getConnection();

        // Désactiver le cache en mode développement si nécessaire
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
            $this->cacheEnabled = false;
        }
    }

    /**
     * Récupère les statistiques générales pour le tableau de bord
     *
     * Utilise des requêtes agrégées pour réduire la charge sur la base de données
     * et implémente un système de mise en cache basique pour les données statistiques.
     *
     * @return array Tableau associatif contenant diverses métriques
     */
    public function getGeneralStats() {
        // Vérification du cache si activé
        $cacheFile = ROOT_PATH . '/cache/general_stats.cache';
        if ($this->cacheEnabled && file_exists($cacheFile) && (time() - filemtime($cacheFile) < $this->cacheExpiry)) {
            return unserialize(file_get_contents($cacheFile));
        }

        try {
            $stats = [];

            // Requête optimisée pour récupérer plusieurs compteurs en une seule opération
            $query = "SELECT 
                     (SELECT COUNT(*) FROM offres) AS total_offres,
                     (SELECT COUNT(*) FROM offres WHERE date_fin >= CURDATE()) AS offres_actives,
                     (SELECT COUNT(*) FROM entreprises) AS total_entreprises,
                     (SELECT COUNT(*) FROM etudiants) AS total_etudiants,
                     (SELECT COUNT(*) FROM pilotes) AS total_pilotes,
                     (SELECT COUNT(*) FROM candidatures) AS total_candidatures";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Conversion explicite en entiers pour garantir le typage correct
            $stats['total_offres'] = (int)$row['total_offres'];
            $stats['offres_actives'] = (int)$row['offres_actives'];
            $stats['total_entreprises'] = (int)$row['total_entreprises'];
            $stats['total_etudiants'] = (int)$row['total_etudiants'];
            $stats['total_pilotes'] = (int)$row['total_pilotes'];
            $stats['total_candidatures'] = (int)$row['total_candidatures'];

            // Requête pour le taux d'évaluation des entreprises
            $query = "SELECT 
                     (SELECT COUNT(DISTINCT entreprise_id) FROM evaluations_entreprises) AS entreprises_evaluees,
                     (SELECT AVG(note) FROM evaluations_entreprises) AS note_moyenne";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $stats['entreprises_evaluees'] = (int)$row['entreprises_evaluees'];
            $stats['note_moyenne'] = round((float)$row['note_moyenne'], 1);

            // Mise en cache des résultats si le cache est activé
            if ($this->cacheEnabled) {
                // Création du répertoire de cache si nécessaire
                if (!file_exists(ROOT_PATH . '/cache')) {
                    mkdir(ROOT_PATH . '/cache', 0755, true);
                }
                file_put_contents($cacheFile, serialize($stats));
            }

            return $stats;
        } catch (PDOException $e) {
            error_log("[Stats::getGeneralStats] Erreur PDO: " . $e->getMessage());
            return [
                'total_offres' => 0,
                'offres_actives' => 0,
                'total_entreprises' => 0,
                'total_etudiants' => 0,
                'total_pilotes' => 0,
                'total_candidatures' => 0,
                'entreprises_evaluees' => 0,
                'note_moyenne' => 0
            ];
        }
    }

    /**
     * Récupère les données pour le graphique de candidatures par mois
     *
     * @param int $limit Nombre maximum de mois à récupérer
     * @return array Données formatées pour Chart.js
     */
    public function getMonthlyApplications($limit = 12) {
        try {
            // Requête optimisée avec indexation sur date_candidature
            $query = "SELECT 
                        DATE_FORMAT(date_candidature, '%Y-%m') as month,
                        DATE_FORMAT(date_candidature, '%b %Y') as month_label, 
                        COUNT(*) as count 
                      FROM candidatures 
                      GROUP BY month, month_label
                      ORDER BY month DESC 
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = $row;
            }

            // Inverser le tableau pour l'ordre chronologique
            return array_reverse($result);
        } catch (PDOException $e) {
            error_log("[Stats::getMonthlyApplications] Erreur PDO: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère la distribution des compétences dans les offres
     *
     * @param int $limit Nombre maximum de compétences à récupérer
     * @return array Données formatées pour Chart.js
     */
    public function getSkillDistribution($limit = 10) {
        try {
            $query = "SELECT 
                        c.nom, 
                        COUNT(oc.offre_id) as count 
                      FROM competences c
                      LEFT JOIN offres_competences oc ON c.id = oc.competence_id
                      GROUP BY c.id, c.nom
                      ORDER BY count DESC, c.nom ASC
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = [
                    'nom' => $row['nom'],
                    'count' => (int)$row['count']
                ];
            }

            return $result;
        } catch (PDOException $e) {
            error_log("[Stats::getSkillDistribution] Erreur PDO: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère le top des entreprises par nombre d'offres
     *
     * @param int $limit Nombre maximum d'entreprises à récupérer
     * @return array Données formatées pour Chart.js
     */
    public function getTopCompanies($limit = 10) {
        try {
            $query = "SELECT 
                        e.nom, 
                        COUNT(o.id) as count 
                      FROM entreprises e
                      INNER JOIN offres o ON e.id = o.entreprise_id
                      GROUP BY e.id, e.nom
                      ORDER BY count DESC
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $result = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result[] = [
                    'nom' => $row['nom'],
                    'count' => (int)$row['count']
                ];
            }

            return $result;
        } catch (PDOException $e) {
            error_log("[Stats::getTopCompanies] Erreur PDO: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère la distribution des évaluations des entreprises
     *
     * @return array Données formatées pour Chart.js
     */
    public function getRatingDistribution() {
        try {
            $query = "SELECT 
                        note,
                        COUNT(*) as count 
                      FROM evaluations_entreprises
                      GROUP BY note
                      ORDER BY note DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            // Création d'un tableau avec toutes les notes de 1 à 5
            $ratings = [
                '5★' => 0,
                '4★' => 0,
                '3★' => 0,
                '2★' => 0,
                '1★' => 0
            ];

            // Remplissage avec les données réelles
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $ratings[$row['note'] . '★'] = (int)$row['count'];
            }

            // Transformation en format pour Chart.js
            $result = [];
            foreach ($ratings as $label => $count) {
                $result[] = [
                    'label' => $label,
                    'count' => $count
                ];
            }

            return $result;
        } catch (PDOException $e) {
            error_log("[Stats::getRatingDistribution] Erreur PDO: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcule le taux de placement des étudiants (ratio étudiants ayant postulé / total)
     *
     * @return array Données formatées pour Chart.js
     */
    public function getPlacementRate() {
        try {
            $query = "SELECT 
                      (SELECT COUNT(DISTINCT etudiant_id) FROM candidatures) AS places,
                      (SELECT COUNT(*) FROM etudiants) AS total";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $placed = (int)$row['places'];
            $total = (int)$row['total'];
            $searching = $total - $placed;

            // Éviter la division par zéro
            $placementRate = ($total > 0) ? round(($placed / $total) * 100) : 0;

            return [
                'placed' => $placed,
                'searching' => $searching,
                'total' => $total,
                'rate' => $placementRate
            ];
        } catch (PDOException $e) {
            error_log("[Stats::getPlacementRate] Erreur PDO: " . $e->getMessage());
            return [
                'placed' => 0,
                'searching' => 0,
                'total' => 0,
                'rate' => 0
            ];
        }
    }

    /**
     * Récupère le nombre moyen de candidatures par étudiant
     *
     * @return float
     */
    public function getAverageApplicationsPerStudent() {
        try {
            $query = "SELECT 
                      (SELECT COUNT(*) FROM candidatures) AS total_applications,
                      (SELECT COUNT(DISTINCT etudiant_id) FROM candidatures) AS students_with_applications";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $totalApplications = (int)$row['total_applications'];
            $studentsWithApplications = (int)$row['students_with_applications'];

            // Éviter la division par zéro
            return ($studentsWithApplications > 0) ? round($totalApplications / $studentsWithApplications, 1) : 0;
        } catch (PDOException $e) {
            error_log("[Stats::getAverageApplicationsPerStudent] Erreur PDO: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère les dernières offres publiées
     *
     * @param int $limit Nombre maximum d'offres à récupérer
     * @return array Liste des dernières offres
     */
    public function getLatestOffers($limit = 5) {
        try {
            $query = "SELECT o.id, o.titre, o.created_at, e.nom as entreprise_nom
                      FROM offres o
                      INNER JOIN entreprises e ON o.entreprise_id = e.id
                      ORDER BY o.created_at DESC
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $offers = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $offers[] = $row;
            }

            return $offers;
        } catch (PDOException $e) {
            error_log("[Stats::getLatestOffers] Erreur PDO: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les dernières entreprises ajoutées
     *
     * @param int $limit Nombre maximum d'entreprises à récupérer
     * @return array Liste des dernières entreprises
     */
    public function getLatestCompanies($limit = 5) {
        try {
            $query = "SELECT e.id, e.nom, e.created_at,
                      (SELECT COUNT(*) FROM offres o WHERE o.entreprise_id = e.id) as nb_offres
                      FROM entreprises e
                      ORDER BY e.created_at DESC
                      LIMIT :limit";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $companies = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $companies[] = $row;
            }

            return $companies;
        } catch (PDOException $e) {
            error_log("[Stats::getLatestCompanies] Erreur PDO: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les journaux d'activité récents
     * Note: Dans une implémentation réelle, cette fonction récupérerait des données
     * d'une table de logs dans la base de données
     *
     * @param int $limit Nombre maximum d'entrées à récupérer
     * @return array Journal d'activités
     */
    public function getActivityLogs($limit = 20) {
        // Dans une implémentation réelle, cette fonction récupérerait des données
        // d'une table de logs dans la base de données.
        // Pour l'instant, nous retournons des données fictives pour illustration.

        $logs = [];
        $actions = [
            'Connexion au système',
            'Création d\'une offre de stage',
            'Modification d\'une entreprise',
            'Suppression d\'une offre',
            'Ajout d\'un pilote',
            'Évaluation d\'une entreprise',
            'Modification d\'un compte étudiant'
        ];

        $users = [
            'admin@web4all.fr',
            'pilote@web4all.fr',
            'etudiant@web4all.fr'
        ];

        for ($i = 0; $i < $limit; $i++) {
            $logs[] = [
                'timestamp' => date('Y-m-d H:i:s', time() - ($i * 3600)),
                'user' => $users[array_rand($users)],
                'action' => $actions[array_rand($actions)],
                'ip' => '192.168.1.' . rand(1, 254)
            ];
        }

        return $logs;
    }
}