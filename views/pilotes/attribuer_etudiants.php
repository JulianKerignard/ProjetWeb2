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
                <li class="breadcrumb-item"><a href="<?php echo url('pilotes', 'etudiants', ['id' => $pilote['id']]); ?>">Étudiants assignés</a></li>
                <li class="breadcrumb-item active" aria-current="page">Gérer les attributions</li>
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

        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-plus me-2"></i>
                            Attribution d'étudiants pour <?php echo htmlspecialchars($pilote['prenom'] . ' ' . $pilote['nom']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0 list-unstyled">
                                    <?php foreach ($errors as $error): ?>
                                        <li><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo url('pilotes', 'attribuer-etudiants', ['id' => $pilote['id']]); ?>" method="post">
                            <div class="mb-4">
                                <label for="etudiant_ids" class="form-label">Sélectionner les étudiants à attribuer</label>
                                <select class="form-select" id="etudiant_ids" name="etudiant_ids[]" multiple size="15">
                                    <?php foreach ($tousLesEtudiants as $etudiant): ?>
                                        <option value="<?php echo $etudiant['id']; ?>" <?php echo in_array($etudiant['id'], $etudiantsAssignesIds) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom'] . ' (' . $etudiant['email'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    Maintenez la touche Ctrl (ou Cmd sur Mac) enfoncée pour sélectionner plusieurs étudiants.
                                    <br>Si aucun étudiant n'est sélectionné, toutes les attributions seront supprimées.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?php echo url('pilotes', 'etudiants', ['id' => $pilote['id']]); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les attributions
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Focus sur le champ de sélection multiple
            document.getElementById('etudiant_ids').focus();
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>