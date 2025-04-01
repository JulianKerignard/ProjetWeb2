<?php
// Vue pour le formulaire de création/modification d'entreprise
include ROOT_PATH . '/views/templates/header.php';

// Déterminer si c'est une création ou une modification
$isEdit = isset($entreprise['id']);
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('entreprises'); ?>">Entreprises</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo $isEdit ? 'Modifier l\'entreprise' : 'Ajouter une entreprise'; ?>
                </li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $isEdit ? 'edit' : 'plus-circle'; ?> me-2"></i>
                            <?php echo $isEdit ? 'Modifier l\'entreprise' : 'Ajouter une nouvelle entreprise'; ?>
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

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                L'entreprise a été <?php echo $isEdit ? 'mise à jour' : 'créée'; ?> avec succès.
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo $isEdit ? url('entreprises', 'modifier', ['id' => $entreprise['id']]) : url('entreprises', 'creer'); ?>"
                              method="post"
                              class="needs-validation"
                              novalidate>

                            <!-- Nom de l'entreprise -->
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="nom"
                                       name="nom"
                                       value="<?php echo htmlspecialchars($entreprise['nom']); ?>"
                                       required
                                       minlength="2"
                                       maxlength="100">
                                <div class="form-text">Le nom doit être concis et descriptif (2-100 caractères).</div>
                                <div class="invalid-feedback">Veuillez saisir un nom valide (2-100 caractères).</div>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control"
                                          id="description"
                                          name="description"
                                          rows="5"><?php echo htmlspecialchars($entreprise['description']); ?></textarea>
                                <div class="form-text">
                                    Décrivez l'entreprise, son secteur d'activité, sa taille, etc.
                                </div>
                            </div>

                            <!-- Email de contact -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email de contact</label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       name="email"
                                       value="<?php echo htmlspecialchars($entreprise['email']); ?>">
                                <div class="form-text">
                                    Email de contact principal pour les candidatures.
                                </div>
                                <div class="invalid-feedback">Veuillez saisir un email valide.</div>
                            </div>

                            <!-- Téléphone de contact -->
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone de contact</label>
                                <input type="tel"
                                       class="form-control"
                                       id="telephone"
                                       name="telephone"
                                       value="<?php echo htmlspecialchars($entreprise['telephone']); ?>"
                                       pattern="[0-9+\(\)\s.-]{6,20}">
                                <div class="form-text">
                                    Numéro de téléphone au format international (ex: +33 1 23 45 67 89).
                                </div>
                                <div class="invalid-feedback">Veuillez saisir un numéro de téléphone valide.</div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?php echo $isEdit ? url('entreprises', 'detail', ['id' => $entreprise['id']]) : url('entreprises'); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i><?php echo $isEdit ? 'Enregistrer les modifications' : 'Créer l\'entreprise'; ?>
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
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>