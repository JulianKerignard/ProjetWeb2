<?php
// Vérifier si on accède directement au fichier (pour débogage uniquement)
if (!defined('ROOT_PATH')) {
    // Démarrer la session si elle n'est pas déjà démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Définir les chemins manuellement pour un accès direct
    define('ROOT_PATH', dirname(dirname(__DIR__))); // Remonte de deux niveaux depuis /views/auth

    // Charger les fichiers nécessaires
    require_once ROOT_PATH . '/config/config.php';
    require_once ROOT_PATH . '/includes/functions.php';
}

// Titre de la page
$pageTitle = "Connexion";
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card shadow rounded-lg">
                    <div class="card-header bg-gradient-primary text-white p-4 text-center">
                        <h3 class="mb-0"><i class="fas fa-user-circle me-2"></i>Connexion</h3>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0 list-unstyled">
                                    <?php foreach ($errors as $error): ?>
                                        <li><i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo url('auth', 'login'); ?>" method="post" class="needs-validation" novalidate>
                            <div class="mb-4">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                           placeholder="Entrez votre email" required>
                                </div>
                                <div class="invalid-feedback">Veuillez saisir un email valide.</div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="Entrez votre mot de passe" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="far fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Veuillez saisir votre mot de passe.</div>
                            </div>
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Se souvenir de moi</label>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer bg-light p-3 text-center">
                        <p class="mb-0">
                            <a href="#" class="text-primary">Mot de passe oublié ?</a>
                        </p>
                    </div>
                </div>

                <!-- Quick Info Card -->
                <div class="card mt-4 shadow-sm">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-info-circle text-primary me-2 fs-4"></i>
                            <h5 class="mb-0">Informations de connexion</h5>
                        </div>
                        <p class="mb-0 small">Pour vous connecter à la démo, utilisez les identifiants suivants :</p>
                        <ul class="small mb-0 mt-2">
                            <li>Admin : admin@web4all.fr / admin123</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Afficher/masquer mot de passe
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');

            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });

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