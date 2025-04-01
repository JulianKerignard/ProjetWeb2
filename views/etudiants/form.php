<?php
// Vue pour le formulaire de création/modification d'étudiant
include ROOT_PATH . '/views/templates/header.php';

// Déterminer si c'est une création ou une modification
$isEdit = isset($etudiant['id']);
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('etudiants'); ?>">Étudiants</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo $isEdit ? 'Modifier l\'étudiant' : 'Ajouter un étudiant'; ?>
                </li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $isEdit ? 'edit' : 'plus-circle'; ?> me-2"></i>
                            <?php echo $isEdit ? 'Modifier l\'étudiant' : 'Ajouter un nouvel étudiant'; ?>
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
                                L'étudiant a été <?php echo $isEdit ? 'mis à jour' : 'créé'; ?> avec succès.
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo $isEdit ? url('etudiants', 'modifier', ['id' => $etudiant['id']]) : url('etudiants', 'creer'); ?>"
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
                                       value="<?php echo htmlspecialchars($etudiant['nom']); ?>"
                                       required
                                       minlength="2"
                                       maxlength="50">
                                <div class="form-text">Le nom doit contenir entre 2 et 50 caractères.</div>
                                <div class="invalid-feedback">Veuillez saisir un nom valide (2-50 caractères).</div>
                            </div>

                            <!-- Prénom -->
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="prenom"
                                       name="prenom"
                                       value="<?php echo htmlspecialchars($etudiant['prenom']); ?>"
                                       required
                                       minlength="2"
                                       maxlength="50">
                                <div class="form-text">Le prénom doit contenir entre 2 et 50 caractères.</div>
                                <div class="invalid-feedback">Veuillez saisir un prénom valide (2-50 caractères).</div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       name="email"
                                       value="<?php echo htmlspecialchars($etudiant['email']); ?>"
                                       required>
                                <div class="form-text">Adresse email de l'étudiant.</div>
                                <div class="invalid-feedback">Veuillez saisir une adresse email valide.</div>
                            </div>

                            <!-- Mot de passe -->
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    Mot de passe <?php echo $isEdit ? '' : '<span class="text-danger">*</span>'; ?>
                                </label>
                                <input type="password"
                                       class="form-control"
                                       id="password"
                                       name="password"
                                    <?php echo $isEdit ? '' : 'required'; ?>
                                       minlength="6">
                                <div class="form-text">
                                    <?php echo $isEdit ? 'Laissez vide pour conserver le mot de passe actuel.' : 'Le mot de passe doit contenir au moins 6 caractères.'; ?>
                                </div>
                                <div class="invalid-feedback">Veuillez saisir un mot de passe valide (minimum 6 caractères).</div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?php echo url('etudiants'); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i><?php echo $isEdit ? 'Enregistrer les modifications' : 'Créer l\'étudiant'; ?>
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