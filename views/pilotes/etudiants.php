<?php
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('pilotes'); ?>">Pilotes</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('pilotes', 'detail', ['id' => $pilote['id']]); ?>"><?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Étudiants assignés</li>
            </ol>
        </nav>

        <!-- Messages flash -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['flash_message']['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['flash_message']['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <!-- En-tête et actions -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2"><?php echo $pageTitle; ?></h1>
            <?php if (isAdmin()): ?>
                <a href="<?php echo url('pilotes', 'attribuer-etudiants', ['id' => $pilote['id']]); ?>" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>Gérer les attributions
                </a>
            <?php endif; ?>
        </div>

        <!-- Liste des étudiants assignés -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i>Étudiants assignés
                    <span class="text-muted">(<?php echo count($etudiants); ?> étudiants)</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($etudiants)): ?>
                    <div class="alert alert-info m-3">
                        Aucun étudiant n'est assigné à ce pilote.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Email</th>
                                <th>Centre</th>
                                <th>Date d'attribution</th>
                                <?php if (isAdmin()): ?>
                                    <th class="text-end">Actions</th>
                                <?php endif; ?>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($etudiants as $etudiant): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($etudiant['nom']); ?></td>
                                    <td><?php echo htmlspecialchars($etudiant['prenom']); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($etudiant['email']); ?>">
                                            <?php echo htmlspecialchars($etudiant['email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if (!empty($etudiant['centre_nom'])): ?>
                                            <?php echo htmlspecialchars($etudiant['centre_nom']); ?>
                                            (<?php echo htmlspecialchars($etudiant['centre_code']); ?>)
                                        <?php else: ?>
                                            <span class="text-muted">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo (new DateTime($etudiant['date_attribution']))->format('d/m/Y H:i'); ?></td>
                                    <?php if (isAdmin()): ?>
                                        <td class="text-end">
                                            <div class="btn-group">
                                                <a href="<?php echo url('etudiants', 'detail', ['id' => $etudiant['id']]); ?>" class="btn btn-sm btn-outline-primary" title="Détails">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?php echo url('pilotes', 'retirer-etudiant', ['pilote_id' => $pilote['id'], 'etudiant_id' => $etudiant['id']]); ?>" class="btn btn-sm btn-outline-danger" title="Retirer l'étudiant" onclick="return confirm('Êtes-vous sûr de vouloir retirer cet étudiant du pilote?');">
                                                    <i class="fas fa-user-minus"></i>
                                                </a>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bouton de retour -->
        <div class="mt-4">
            <a href="<?php echo url('pilotes', 'detail', ['id' => $pilote['id']]); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour au détail du pilote
            </a>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>