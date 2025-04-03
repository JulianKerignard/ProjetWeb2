<?php
/**
 * Vue de gestion des permissions pour le panel administrateur
 *
 * Permet de configurer les accès des différents types d'utilisateurs
 * aux fonctionnalités du système.
 */
include ROOT_PATH . '/views/templates/header.php';

// Liste des fonctionnalités par catégorie
$permissionCategories = [
    'Gestion des entreprises' => [
        'entreprise_creer' => 'Créer une entreprise',
        'entreprise_modifier' => 'Modifier une entreprise',
        'entreprise_supprimer' => 'Supprimer une entreprise',
        'entreprise_evaluer' => 'Évaluer une entreprise'
    ],
    'Gestion des offres' => [
        'offre_creer' => 'Créer une offre',
        'offre_modifier' => 'Modifier une offre',
        'offre_supprimer' => 'Supprimer une offre'
    ],
    'Gestion des candidatures' => [
        'offre_postuler' => 'Postuler à une offre',
        'wishlist_ajouter' => 'Ajouter à la wishlist',
        'wishlist_retirer' => 'Retirer de la wishlist',
        'wishlist_afficher' => 'Afficher la wishlist',
        'candidatures_afficher' => 'Voir ses candidatures'
    ],
    'Gestion des utilisateurs' => [
        'pilote_creer' => 'Créer un pilote',
        'pilote_modifier' => 'Modifier un pilote',
        'pilote_supprimer' => 'Supprimer un pilote',
        'etudiant_creer' => 'Créer un étudiant',
        'etudiant_modifier' => 'Modifier un étudiant',
        'etudiant_supprimer' => 'Supprimer un étudiant'
    ]
];

// Rôles disponibles
$roles = [ROLE_ADMIN, ROLE_PILOTE, ROLE_ETUDIANT];

// Récupérer les permissions actuelles
require_once ROOT_PATH . '/models/Permission.php';
$permissionModel = new Permission();
$currentPermissions = $permissionModel->getAllPermissions();

// Message de confirmation
$message = '';
$messageType = '';

?>

    <div class="container mt-4">
        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">
                <i class="fas fa-user-shield me-2"></i>
                <?php echo $pageTitle; ?>
            </h1>

            <div>
                <a href="<?php echo url('admin', 'reset-permissions'); ?>" class="btn btn-warning">
                    <i class="fas fa-sync me-2"></i>Réinitialiser les permissions
                </a>
                <a href="<?php echo url('admin'); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form action="<?php echo url('admin', 'save-permissions'); ?>" method="post">
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
                                <?php foreach ($roles as $role): ?>
                                    <th class="text-center"><?php echo ucfirst($role); ?></th>
                                <?php endforeach; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($permissionCategories as $category => $permissions): ?>
                                <tr>
                                    <td colspan="<?php echo count($roles) + 1; ?>" class="table-secondary">
                                        <strong><?php echo $category; ?></strong>
                                    </td>
                                </tr>
                                <?php foreach ($permissions as $permission => $label): ?>
                                    <tr>
                                        <td><?php echo $label; ?></td>
                                        <?php foreach ($roles as $role): ?>
                                            <td class="text-center">
                                                <div class="form-check form-check-inline d-flex justify-content-center">
                                                    <?php if ($role === ROLE_ADMIN): ?>
                                                        <!-- L'admin a toujours toutes les permissions -->
                                                        <input class="form-check-input" type="checkbox"
                                                               checked disabled>
                                                        <input type="hidden" name="permissions[<?php echo $role; ?>][]"
                                                               value="<?php echo $permission; ?>">
                                                    <?php else: ?>
                                                        <input class="form-check-input" type="checkbox"
                                                               name="permissions[<?php echo $role; ?>][]"
                                                               value="<?php echo $permission; ?>"
                                                            <?php echo (isset($currentPermissions[$role]) && in_array($permission, $currentPermissions[$role])) ? 'checked' : ''; ?>>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </div>
        </form>

        <!-- Aide -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informations sur les permissions</h5>
            </div>
            <div class="card-body">
                <p>
                    <strong>Note:</strong> Modifiez les permissions en cochant ou décochant les cases correspondantes.
                    Les modifications ne seront appliquées qu'après avoir cliqué sur le bouton "Enregistrer les modifications".
                </p>
                <p>
                    <strong>Attention:</strong> L'administrateur a automatiquement toutes les permissions,
                    ces cases ne peuvent pas être décochées.
                </p>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>