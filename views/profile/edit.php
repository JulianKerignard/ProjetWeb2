<?php
// Vue pour la modification du profil utilisateur
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('profile'); ?>">Mon Profil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Modifier</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Modifier mon profil
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
                                Votre profil a été mis à jour avec succès.
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo url('profile', 'edit'); ?>" method="post" class="needs-validation" novalidate>
                            <!-- Nom -->
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="nom"
                                       name="nom"
                                       value="<?php echo htmlspecialchars($userProfile['nom']); ?>"
                                       required
                                       minlength="2"
                                       maxlength="50">
                                <div class="form-text">Votre nom de famille.</div>
                                <div class="invalid-feedback">Veuillez saisir un nom valide (2-50 caractères).</div>
                            </div>

                            <!-- Prénom -->
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="prenom"
                                       name="prenom"
                                       value="<?php echo htmlspecialchars($userProfile['prenom']); ?>"
                                       required
                                       minlength="2"
                                       maxlength="50">
                                <div class="form-text">Votre prénom.</div>
                                <div class="invalid-feedback">Veuillez saisir un prénom valide (2-50 caractères).</div>
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       name="email"
                                       value="<?php echo htmlspecialchars($userProfile['email']); ?>"
                                       required>
                                <div class="form-text">Votre adresse email (qui servira également d'identifiant de connexion).</div>
                                <div class="invalid-feedback">Veuillez saisir une adresse email valide.</div>
                            </div>

                            <!-- Mot de passe -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Nouveau mot de passe</label>
                                <input type="password"
                                       class="form-control"
                                       id="password"
                                       name="password"
                                       minlength="6">
                                <div class="form-text">Laissez vide pour conserver votre mot de passe actuel.</div>
                                <div class="invalid-feedback">Le mot de passe doit contenir au moins 6 caractères.</div>
                            </div>

                            <!-- Confirmation du mot de passe -->
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                <input type="password"
                                       class="form-control"
                                       id="confirm_password"
                                       name="confirm_password"
                                       minlength="6">
                                <div class="invalid-feedback">Les mots de passe ne correspondent pas.</div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?php echo url('profile'); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Enregistrer les modifications
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
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            // Vérification de la correspondance des mots de passe
            confirmPassword.addEventListener('input', function() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            });

            password.addEventListener('input', function() {
                if (password.value !== confirmPassword.value && confirmPassword.value !== '') {
                    confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            });

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