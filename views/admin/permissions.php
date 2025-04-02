<?php
/**
 * Vue de gestion des permissions pour le panel administrateur
 *
 * Permet de configurer les accès des différents types d'utilisateurs
 * aux fonctionnalités du système.
 */
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">
                <i class="fas fa-user-shield me-2"></i>
                <?php echo $pageTitle; ?>
            </h1>

            <div>
                <a href="<?php echo url('admin'); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                </a>
            </div>
        </div>

        <!-- Matrice des permissions -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i>Matrice des rôles et permissions</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                        <tr>
                            <th>Fonctionnalité</th>
                            <th class="text-center">Administrateur</th>
                            <th class="text-center">Pilote</th>
                            <th class="text-center">Étudiant</th>
                        </tr>
                        </thead>
                        <tbody>
                        <!-- Entreprises -->
                        <tr>
                            <td colspan="4" class="table-secondary"><strong>Gestion des entreprises</strong></td>
                        </tr>
                        <tr>
                            <td>Consulter les entreprises</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td>Créer une entreprise</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>
                        <tr>
                            <td>Modifier une entreprise</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>
                        <tr>
                            <td>Supprimer une entreprise</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>
                        <tr>
                            <td>Évaluer une entreprise</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>

                        <!-- Offres -->
                        <tr>
                            <td colspan="4" class="table-secondary"><strong>Gestion des offres</strong></td>
                        </tr>
                        <tr>
                            <td>Consulter les offres</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td>Créer une offre</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>
                        <tr>
                            <td>Modifier une offre</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>
                        <tr>
                            <td>Supprimer une offre</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>

                        <!-- Candidatures -->
                        <tr>
                            <td colspan="4" class="table-secondary"><strong>Gestion des candidatures</strong></td>
                        </tr>
                        <tr>
                            <td>Postuler à une offre</td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td>Gérer sa wishlist</td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td>Voir toutes les candidatures</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>

                        <!-- Utilisateurs -->
                        <tr>
                            <td colspan="4" class="table-secondary"><strong>Gestion des utilisateurs</strong></td>
                        </tr>
                        <tr>
                            <td>Gérer les pilotes</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>
                        <tr>
                            <td>Gérer les étudiants</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>

                        <!-- Administration -->
                        <tr>
                            <td colspan="4" class="table-secondary"><strong>Administration système</strong></td>
                        </tr>
                        <tr>
                            <td>Accès au tableau de bord</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>
                        <tr>
                            <td>Consulter les statistiques</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>
                        <tr>
                            <td>Gérer les permissions</td>
                            <td class="text-center"><i class="fas fa-check text-success"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                            <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Note informative -->
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            <strong>Note:</strong> Cette matrice des permissions est actuellement en lecture seule.
            La modification dynamique des permissions n'est pas implémentée dans cette version de l'application.
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>