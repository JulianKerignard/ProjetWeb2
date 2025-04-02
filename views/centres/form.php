<?php
include ROOT_PATH . '/views/templates/header.php';

// Déterminer si c'est une création ou une modification
$isEdit = isset($centre['id']);
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('centres'); ?>">Centres</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo $isEdit ? 'Modifier le centre' : 'Ajouter un centre'; ?>
                </li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $isEdit ? 'edit' : 'plus-circle'; ?> me-2"></i>
                            <?php echo $isEdit ? 'Modifier le centre' : 'Ajouter un centre'; ?>
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

                        <?php if (isset($success) && $success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Le centre a été <?php echo $isEdit ? 'mis à jour' : 'créé'; ?> avec succès.
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo $isEdit ? url('centres', 'modifier', ['id' => $centre['id']]) : url('centres', 'creer'); ?>"
                              method="post"
                              class="needs-validation"
                              novalidate>

                            <!-- Nom -->
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="nom"
                                       name="nom"
                                       value="<?php echo htmlspecialchars($centre['nom'] ?? ''); ?>"
                                       required
                                       minlength="2"
                                       maxlength="100">
                                <div class="form-text">Le nom doit contenir entre 2 et 100 caractères.</div>
                                <div class="invalid-feedback">Veuillez saisir un nom valide (2-100 caractères).</div>
                            </div>

                            <!-- Code -->
                            <div class="mb-3">
                                <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="code"
                                       name="code"
                                       value="<?php echo htmlspecialchars($centre['code'] ?? ''); ?>"
                                       required
                                       minlength="2"
                                       maxlength="20">
                                <div class="form-text">Le code doit contenir entre 2 et 20 caractères (ex: PAR, LYO).</div>
                                <div class="invalid-feedback">Veuillez saisir un code valide (2-20 caractères).</div>
                            </div>

                            <!-- Adresse -->
                            <div class="mb-3">
                                <label for="adresse" class="form-label">Adresse</label>
                                <textarea
                                    class="form-control"
                                    id="adresse"
                                    name="adresse"
                                    rows="3"><?php echo htmlspecialchars($centre['adresse'] ?? ''); ?></textarea>
                                <div class="form-text">L'adresse complète du centre.</div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?php echo url('centres'); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i><?php echo $isEdit ? 'Enregistrer les modifications' : 'Créer le centre'; ?>
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
            // Validation du formulaire
            const form = document.querySelector('.needs-validation');

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            });

            // Conversion automatique du code en majuscules
            const codeInput = document.getElementById('code');
            codeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>