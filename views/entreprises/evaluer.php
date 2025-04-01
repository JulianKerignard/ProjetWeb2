<?php
// Vue pour le formulaire d'évaluation d'entreprise
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('entreprises'); ?>">Entreprises</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('entreprises', 'detail', ['id' => $entreprise['id']]); ?>"><?php echo htmlspecialchars($entreprise['nom']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Évaluer</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-star me-2"></i>
                            Évaluer l'entreprise : <?php echo htmlspecialchars($entreprise['nom']); ?>
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
                                Votre évaluation a été enregistrée avec succès.
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo url('entreprises', 'evaluer', ['id' => $entreprise['id']]); ?>"
                              method="post"
                              class="needs-validation"
                              novalidate>

                            <!-- Note -->
                            <div class="mb-4">
                                <label class="form-label">Note <span class="text-danger">*</span></label>
                                <div class="rating-stars mb-2">
                                    <div class="btn-group" role="group" aria-label="Notation">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <input type="radio" class="btn-check" name="note" id="star<?php echo $i; ?>" value="<?php echo $i; ?>" autocomplete="off" <?php echo isset($_POST['note']) && $_POST['note'] == $i ? 'checked' : ''; ?> required>
                                            <label class="btn btn-outline-warning" for="star<?php echo $i; ?>">
                                                <i class="fas fa-star"></i> <?php echo $i; ?>
                                            </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="form-text">Évaluez l'entreprise de 1 à 5 étoiles.</div>
                                <div class="invalid-feedback">Veuillez attribuer une note.</div>
                            </div>

                            <!-- Commentaire -->
                            <div class="mb-3">
                                <label for="commentaire" class="form-label">Commentaire <span class="text-danger">*</span></label>
                                <textarea class="form-control"
                                          id="commentaire"
                                          name="commentaire"
                                          rows="5"
                                          required
                                          minlength="10"><?php echo isset($_POST['commentaire']) ? htmlspecialchars($_POST['commentaire']) : ''; ?></textarea>
                                <div class="form-text">
                                    Partagez votre expérience avec cette entreprise. Votre avis sera utile aux autres étudiants.
                                </div>
                                <div class="invalid-feedback">Veuillez saisir un commentaire (10 caractères minimum).</div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?php echo url('entreprises', 'detail', ['id' => $entreprise['id']]); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Soumettre l'évaluation
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

            // Style pour les étoiles
            const stars = document.querySelectorAll('.rating-stars input[type="radio"]');

            stars.forEach(function(star) {
                star.addEventListener('change', function() {
                    // Réinitialiser toutes les étoiles
                    stars.forEach(function(s) {
                        const label = document.querySelector(`label[for="${s.id}"]`);
                        label.classList.remove('active');
                    });

                    // Activer les étoiles jusqu'à celle sélectionnée
                    const selectedValue = parseInt(this.value);
                    stars.forEach(function(s) {
                        const value = parseInt(s.value);
                        if (value <= selectedValue) {
                            const label = document.querySelector(`label[for="${s.id}"]`);
                            label.classList.add('active');
                        }
                    });
                });
            });

            // Activer les étoiles pour la valeur initiale (si présente)
            const checkedStar = document.querySelector('.rating-stars input[type="radio"]:checked');
            if (checkedStar) {
                const event = new Event('change');
                checkedStar.dispatchEvent(event);
            }
        });
    </script>

    <style>
        .rating-stars .btn-outline-warning.active {
            background-color: #ffc107;
            color: #212529;
        }
    </style>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>