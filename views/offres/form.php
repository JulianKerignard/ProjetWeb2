<?php
// Vue pour le formulaire de création/modification d'offre
include ROOT_PATH . '/views/templates/header.php';

// Déterminer si c'est une création ou une modification
$isEdit = isset($offre['id']);
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('offres'); ?>">Offres de stage</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo $isEdit ? 'Modifier l\'offre' : 'Créer une offre'; ?>
                </li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-9 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $isEdit ? 'edit' : 'plus-circle'; ?> me-2"></i>
                            <?php echo $isEdit ? 'Modifier l\'offre de stage' : 'Créer une nouvelle offre de stage'; ?>
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
                                L'offre a été <?php echo $isEdit ? 'mise à jour' : 'créée'; ?> avec succès.
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo $isEdit ? url('offres', 'modifier', ['id' => $offre['id']]) : url('offres', 'creer'); ?>"
                              method="post"
                              class="needs-validation"
                              novalidate>

                            <!-- Informations générales -->
                            <h5 class="form-section-title mb-3">Informations générales</h5>

                            <!-- Titre de l'offre -->
                            <div class="mb-3">
                                <label for="titre" class="form-label">Titre de l'offre <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       id="titre"
                                       name="titre"
                                       value="<?php echo htmlspecialchars($offre['titre']); ?>"
                                       required
                                       minlength="5"
                                       maxlength="100">
                                <div class="form-text">Le titre doit être concis et descriptif (5-100 caractères).</div>
                                <div class="invalid-feedback">Veuillez saisir un titre valide (5-100 caractères).</div>
                            </div>

                            <!-- Entreprise -->
                            <div class="mb-3">
                                <label for="entreprise_id" class="form-label">Entreprise <span class="text-danger">*</span></label>
                                <select class="form-select" id="entreprise_id" name="entreprise_id" required>
                                    <option value="">Sélectionnez une entreprise</option>
                                    <?php foreach ($entreprises as $entreprise): ?>
                                        <option value="<?php echo $entreprise['id']; ?>"
                                            <?php echo ($offre['entreprise_id'] == $entreprise['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($entreprise['nom']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">
                                    Si l'entreprise n'est pas dans la liste,
                                    <a href="<?php echo url('entreprises', 'creer'); ?>" target="_blank">ajoutez-la d'abord</a>.
                                </div>
                                <div class="invalid-feedback">Veuillez sélectionner une entreprise.</div>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label">Description détaillée <span class="text-danger">*</span></label>
                                <textarea class="form-control"
                                          id="description"
                                          name="description"
                                          rows="8"
                                          required
                                          minlength="50"><?php echo htmlspecialchars($offre['description']); ?></textarea>
                                <div class="form-text">
                                    Décrivez en détail les missions, le contexte et les objectifs du stage (minimum 50 caractères).
                                </div>
                                <div class="invalid-feedback">La description doit contenir au moins 50 caractères.</div>
                            </div>

                            <!-- Période et rémunération -->
                            <h5 class="form-section-title mt-4 mb-3">Période et rémunération</h5>

                            <div class="row">
                                <!-- Date de début -->
                                <div class="col-md-4 mb-3">
                                    <label for="date_debut" class="form-label">Date de début <span class="text-danger">*</span></label>
                                    <input type="date"
                                           class="form-control"
                                           id="date_debut"
                                           name="date_debut"
                                           value="<?php echo htmlspecialchars($offre['date_debut']); ?>"
                                           required>
                                    <div class="invalid-feedback">Veuillez sélectionner une date de début.</div>
                                </div>

                                <!-- Date de fin -->
                                <div class="col-md-4 mb-3">
                                    <label for="date_fin" class="form-label">Date de fin <span class="text-danger">*</span></label>
                                    <input type="date"
                                           class="form-control"
                                           id="date_fin"
                                           name="date_fin"
                                           value="<?php echo htmlspecialchars($offre['date_fin']); ?>"
                                           required>
                                    <div class="invalid-feedback">Veuillez sélectionner une date de fin.</div>
                                </div>

                                <!-- Rémunération -->
                                <div class="col-md-4 mb-3">
                                    <label for="remuneration" class="form-label">Rémunération (€)</label>
                                    <div class="input-group">
                                        <input type="number"
                                               class="form-control"
                                               id="remuneration"
                                               name="remuneration"
                                               value="<?php echo htmlspecialchars($offre['remuneration']); ?>"
                                               min="0"
                                               step="0.01">
                                        <span class="input-group-text">€</span>
                                    </div>
                                    <div class="form-text">
                                        Laisser vide ou mettre 0 pour un stage non rémunéré.
                                    </div>
                                </div>
                            </div>

                            <!-- Calculateur de durée -->
                            <div class="alert alert-info mb-3" id="dureeCalculator">
                                <span id="dureeResult">Veuillez sélectionner des dates de début et de fin valides.</span>
                            </div>

                            <!-- Compétences requises -->
                            <h5 class="form-section-title mt-4 mb-3">Compétences requises</h5>

                            <div class="mb-3">
                                <label class="form-label">Sélectionnez les compétences requises <span class="text-danger">*</span></label>
                                <div class="form-text mb-2">
                                    Choisissez au moins une compétence requise pour ce stage.
                                </div>

                                <div class="row">
                                    <?php foreach ($competences as $index => $competence): ?>
                                        <div class="col-md-4 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input competence-checkbox"
                                                       type="checkbox"
                                                       id="competence_<?php echo $competence['id']; ?>"
                                                       name="competences[]"
                                                       value="<?php echo $competence['id']; ?>"
                                                    <?php echo in_array($competence['id'], $offre['competences']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="competence_<?php echo $competence['id']; ?>">
                                                    <?php echo htmlspecialchars($competence['nom']); ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="invalid-feedback" id="competencesValidation">
                                    Veuillez sélectionner au moins une compétence.
                                </div>
                            </div>

                            <!-- Boutons d'action -->
                            <div class="d-flex justify-content-between mt-4">
                                <a href="<?php echo $isEdit ? url('offres', 'detail', ['id' => $offre['id']]) : url('offres'); ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i><?php echo $isEdit ? 'Enregistrer les modifications' : 'Créer l\'offre'; ?>
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
            const competenceCheckboxes = document.querySelectorAll('.competence-checkbox');
            const competencesValidation = document.getElementById('competencesValidation');

            // Fonction pour calculer la durée entre deux dates
            function calculateDuration() {
                const startDate = document.getElementById('date_debut').value;
                const endDate = document.getElementById('date_fin').value;
                const dureeCalculator = document.getElementById('dureeCalculator');
                const dureeResult = document.getElementById('dureeResult');

                if (startDate && endDate) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);

                    if (end < start) {
                        dureeCalculator.className = 'alert alert-danger mb-3';
                        dureeResult.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>La date de fin doit être postérieure à la date de début.';
                        return;
                    }

                    // Calcul de la différence en jours
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                    // Calcul en mois et jours
                    const months = Math.floor(diffDays / 30);
                    const remainingDays = diffDays % 30;

                    let durationText = '<i class="fas fa-info-circle me-2"></i>Durée du stage : ';
                    if (months > 0) {
                        durationText += months + ' mois';
                        if (remainingDays > 0) {
                            durationText += ' et ';
                        }
                    }

                    if (remainingDays > 0 || months === 0) {
                        durationText += remainingDays + ' jour' + (remainingDays > 1 ? 's' : '');
                    }

                    durationText += ' (' + diffDays + ' jours)';

                    dureeCalculator.className = 'alert alert-info mb-3';
                    dureeResult.innerHTML = durationText;
                } else {
                    dureeCalculator.className = 'alert alert-info mb-3';
                    dureeResult.innerHTML = 'Veuillez sélectionner des dates de début et de fin valides.';
                }
            }

            // Évènements pour le calcul de durée
            document.getElementById('date_debut').addEventListener('change', calculateDuration);
            document.getElementById('date_fin').addEventListener('change', calculateDuration);

            // Validation à la soumission du formulaire
            form.addEventListener('submit', function(event) {
                let competenceSelected = false;

                competenceCheckboxes.forEach(function(checkbox) {
                    if (checkbox.checked) {
                        competenceSelected = true;
                    }
                });

                if (!competenceSelected) {
                    event.preventDefault();
                    event.stopPropagation();
                    competencesValidation.style.display = 'block';
                } else {
                    competencesValidation.style.display = 'none';
                }

                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            });

            // Initialisation du calcul de durée
            calculateDuration();

            // Style pour les sections du formulaire
            const style = document.createElement('style');
            style.textContent = `
        .form-section-title {
            border-left: 4px solid var(--primary);
            padding-left: 10px;
            color: var(--dark);
        }

        .was-validated .competence-checkbox:invalid ~ #competencesValidation {
            display: block;
        }

        #competencesValidation {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
    `;
            document.head.appendChild(style);
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>