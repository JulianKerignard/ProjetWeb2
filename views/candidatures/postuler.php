<?php
// Vue pour le formulaire de candidature à une offre
include ROOT_PATH . '/views/templates/header.php';

// Récupération des informations de l'offre de manière sécurisée
$offre_id = isset($offre_id) ? $offre_id : (isset($_GET['offre_id']) ? (int)$_GET['offre_id'] : 0);

// Variables pour éviter les notices PHP
$errors = isset($errors) ? $errors : [];
$success = isset($success) ? $success : false;
$lettre_motivation = isset($lettre_motivation) ? $lettre_motivation : "";

// Traçage visuel pour voir où le processus s'arrête
echo '<!-- POINT 1: Début du fichier postuler.php -->';
?>

    <div class="container mt-4">
        <h2 class="mb-4">Postuler à l'offre: <?php echo htmlspecialchars($offre['titre']); ?></h2>

        <?php echo '<!-- POINT 2: Avant condition $isEtudiant -->'; ?>

        <!-- Message spécial pour voir si cette condition fonctionne -->
        <div class="alert alert-info">
            <strong>État de session:</strong>
            Rôle: <?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 'non défini'; ?> |
            ROLE_ETUDIANT: <?php echo defined('ROLE_ETUDIANT') ? ROLE_ETUDIANT : 'non défini'; ?> |
            Condition: <?php echo (isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ETUDIANT) ? 'VRAIE' : 'FAUSSE'; ?>
        </div>

        <?php
        // Test force la variable à TRUE pour voir si le problème est dans la condition
        $isEtudiant = true;
        echo '<!-- POINT 3: $isEtudiant forcé à true -->';
        ?>

        <!-- Formulaire de candidature simplifié -->
        <?php if ($isEtudiant): ?>
            <?php echo '<!-- POINT 4: Dans la condition if -->'; ?>

            <div class="card">
                <div class="card-header">
                    <h5>Formulaire de candidature</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo url('candidatures', 'postuler', ['offre_id' => $offre_id]); ?>" method="post" enctype="multipart/form-data">
                        <!-- CV -->
                        <div class="mb-3">
                            <label for="cv" class="form-label">CV (PDF, DOC, DOCX)</label>
                            <input type="file" class="form-control" id="cv" name="cv" required>
                        </div>

                        <!-- Lettre de motivation -->
                        <div class="mb-3">
                            <label for="lettre_motivation" class="form-label">Lettre de motivation</label>
                            <textarea class="form-control" id="lettre_motivation" name="lettre_motivation" rows="5" required><?php echo htmlspecialchars($lettre_motivation); ?></textarea>
                        </div>

                        <!-- Boutons -->
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo url('offres', 'detail', ['id' => $offre_id]); ?>" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">Envoyer ma candidature</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <?php echo '<!-- POINT 5: Dans la condition else -->'; ?>

            <div class="alert alert-warning">
                Pour postuler à cette offre, vous devez être connecté en tant qu'étudiant.
            </div>
        <?php endif; ?>

        <?php echo '<!-- POINT 6: Après la condition if/else -->'; ?>
    </div>

<?php
echo '<!-- POINT 7: Avant inclusion du footer -->';
include ROOT_PATH . '/views/templates/footer.php';
echo '<!-- POINT 8: Fin du fichier -->';
?>