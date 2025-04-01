<?php
// Vue pour le formulaire de candidature à une offre
include ROOT_PATH . '/views/templates/header.php';

// Récupération des informations de l'offre
$offre_id = isset($offre_id) ? $offre_id : (isset($_GET['offre_id']) ? (int)$_GET['offre_id'] : 0);

// Détection du rôle étudiant pour affichage conditionnel
$isEtudiant = isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ETUDIANT;
?>

    <div class="container mt-4">
        <!-- Fil d'Ariane -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo url(); ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('offres'); ?>">Offres de stage</a></li>
                <li class="breadcrumb-item"><a href="<?php echo url('offres', 'detail', ['id' => $offre['id']]); ?>"><?php echo htmlspecialchars($offre['titre']); ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Postuler</li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-paper-plane me-2"></i>
                            Postuler à l'offre : <?php echo htmlspecialchars($offre['titre']); ?>
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
                                Votre candidature a été envoyée avec succès.
                            </div>
                        <?php endif; ?>

                        <!-- Message contextuel pour les utilisateurs non-étudiants -->
                        <?php if (!$isEtudiant): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Information :</strong> Pour postuler à cette offre, vous devez être connecté avec un compte étudiant.
                                <?php if (!isLoggedIn()): ?>
                                    <div class="mt-3">
                                        <a href="<?php echo url('auth', 'login'); ?>" class="btn btn-primary">
                                            <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Récapitulatif de l'offre -->
                        <div class="card mb-4 bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <h6 class="card-title mb-2"><?php echo htmlspecialchars($offre['entreprise_nom']); ?></h6>
                                    <span class="badge bg-primary"><?php echo (new DateTime($offre['date_debut']))->format('d/m/Y'); ?> - <?php echo (new DateTime($offre['date_fin']))->format('d/m/Y'); ?></span>
                                </div>
                                <p class="card-text small"><?php echo mb_substr(strip_tags($offre['description']), 0, 200) . '...'; ?></p>
                            </div>
                        </div>

                        <!-- Formulaire de candidature conditionnel -->
                        <?php if ($isEtudiant): ?>
                            <form action="<?php echo url('candidatures', 'postuler', ['offre_id' => $offre_id]); ?>" method="post" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <input type="hidden" name="offre_id" value="<?php echo $offre_id; ?>">

                                <!-- CV -->
                                <div class="mb-4">
                                    <label for="cv" class="form-label">CV (PDF, DOC, DOCX) <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" id="cv" name="cv" accept=".pdf,.doc,.docx" required>
                                    <div class="form-text">Format accepté : PDF, DOC, DOCX. Taille maximum : 5 Mo.</div>
                                    <div class="invalid-feedback">Veuillez téléverser votre CV.</div>
                                </div>

                                <!-- Lettre de motivation -->
                                <div class="mb-4">
                                    <label for="lettre_motivation" class="form-label">Lettre de motivation <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="lettre_motivation" name="lettre_motivation" rows="10" required minlength="100"><?php echo isset($lettre_motivation) ? htmlspecialchars($lettre_motivation) : ''; ?></textarea>
                                    <div class="form-text">
                                        Présentez-vous, expliquez votre parcours et votre motivation pour ce stage (minimum 100 caractères).
                                    </div>
                                    <div class="invalid-feedback">Veuillez rédiger une lettre de motivation d'au moins 100 caractères.</div>
                                </div>

                                <!-- Boutons d'action -->
                                <div class="d-flex justify-content-between mt-4">
                                    <a href="<?php echo url('offres', 'detail', ['id' => $offre_id]); ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Envoyer ma candidature
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="mb-3">Pour postuler à cette offre, vous devez disposer d'un compte étudiant.</p>
                                <a href="<?php echo url('offres', 'detail', ['id' => $offre_id]); ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Retour à l'offre
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validation du formulaire
            const form = document.querySelector('.needs-validation');
            if (form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated');
                });

                // Validation du format et de la taille du fichier
                const cvInput = document.getElementById('cv');
                if (cvInput) {
                    const maxFileSize = 5 * 1024 * 1024; // 5 Mo en octets
                    const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

                    cvInput.addEventListener('change', function() {
                        if (this.files.length > 0) {
                            const fileSize = this.files[0].size;
                            const fileType = this.files[0].type;

                            let errorMessage = '';

                            if (fileSize > maxFileSize) {
                                errorMessage = 'Le fichier est trop volumineux. La taille maximale est de 5 Mo.';
                            } else if (!allowedTypes.includes(fileType)) {
                                errorMessage = 'Format de fichier non supporté. Veuillez téléverser un fichier PDF, DOC ou DOCX.';
                            }

                            if (errorMessage) {
                                this.setCustomValidity(errorMessage);
                                this.nextElementSibling.nextElementSibling.textContent = errorMessage;
                            } else {
                                this.setCustomValidity('');
                            }
                        }
                    });
                }
            }
        });
    </script>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>