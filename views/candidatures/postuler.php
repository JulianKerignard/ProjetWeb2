<?php
// Vue pour le formulaire de candidature à une offre
include ROOT_PATH . '/views/templates/header.php';

// Récupération des informations de l'offre de manière sécurisée
$offre_id = isset($offre_id) ? $offre_id : (isset($_GET['offre_id']) ? (int)$_GET['offre_id'] : 0);

// Variables pour éviter les notices PHP
$errors = isset($errors) ? $errors : [];
$success = isset($success) ? $success : false;
$lettre_motivation = isset($lettre_motivation) ? $lettre_motivation : "";
?>

    <div class="container mt-4">
        <h2 class="mb-4">Postuler à l'offre: <?php echo htmlspecialchars($offre['titre']); ?></h2>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0 list-unstyled">
                    <?php foreach ($errors as $error): ?>
                        <li><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                Votre candidature a été envoyée avec succès.
            </div>
        <?php endif; ?>

        <!-- Formulaire de candidature -->
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
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>