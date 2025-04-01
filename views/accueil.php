<?php
// Vérifier si on accède directement au fichier
if (!defined('ROOT_PATH')) {
    // Définir les chemins manuellement pour un accès direct
    define('ROOT_PATH', dirname(__DIR__)); // Remonte d'un niveau depuis /views

    // Charger les fichiers nécessaires
    require_once ROOT_PATH . '/config/config.php';
    require_once ROOT_PATH . '/includes/functions.php';

    // Démarrer la session si elle n'est pas déjà démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Titre de la page
$pageTitle = "Accueil";
include ROOT_PATH . '/views/templates/header.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="display-4 fw-bold mb-4">Trouvez le stage idéal pour votre carrière</h1>
                    <p class="lead mb-5">Une plateforme complète pour vous aider dans votre recherche de stage et faciliter votre entrée dans le monde professionnel.</p>
                    <?php if (!isLoggedIn()): ?>
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="<?php echo url('auth', 'login'); ?>" class="btn btn-primary btn-lg px-5 me-md-2">
                                <i class="fas fa-sign-in-alt me-2"></i>Se connecter
                            </a>
                            <a href="#features" class="btn btn-outline-primary btn-lg px-5">
                                <i class="fas fa-info-circle me-2"></i>En savoir plus
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="<?php echo url('offres'); ?>" class="btn btn-primary btn-lg px-5 me-md-2">
                                <i class="fas fa-search me-2"></i>Voir les offres
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="<?php echo URL_ROOT; ?>/public/img/hero-image.svg" alt="Stage" class="img-fluid hero-image">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5" id="features">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-md-12">
                    <h2 class="fw-bold">Pourquoi utiliser notre plateforme ?</h2>
                    <p class="text-muted mx-auto" style="max-width: 700px;">Découvrez les avantages de notre système de gestion des stages pour les étudiants et les entreprises.</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon mx-auto">
                                <i class="fas fa-search"></i>
                            </div>
                            <h5 class="card-title">Recherche simplifiée</h5>
                            <p class="card-text">Trouvez rapidement des offres de stage correspondant à votre profil et à vos compétences grâce à notre moteur de recherche avancé.</p>
                            <a href="<?php echo url('offres'); ?>" class="btn btn-sm btn-primary mt-3">
                                <i class="fas fa-arrow-right me-1"></i> Explorer
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon mx-auto">
                                <i class="fas fa-building"></i>
                            </div>
                            <h5 class="card-title">Entreprises vérifiées</h5>
                            <p class="card-text">Accédez à des entreprises partenaires de confiance avec des évaluations de stagiaires précédents pour faire le bon choix.</p>
                            <a href="<?php echo url('entreprises'); ?>" class="btn btn-sm btn-primary mt-3">
                                <i class="fas fa-arrow-right me-1"></i> Découvrir
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="feature-icon mx-auto">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h5 class="card-title">Candidature facile</h5>
                            <p class="card-text">Postulez directement depuis la plateforme et suivez l'état de vos candidatures en temps réel avec des notifications.</p>
                            <?php if (isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === ROLE_ETUDIANT): ?>
                                <a href="<?php echo url('candidatures', 'mes-candidatures'); ?>" class="btn btn-sm btn-primary mt-3">
                                    <i class="fas fa-arrow-right me-1"></i> Mes candidatures
                                </a>
                            <?php else: ?>
                                <a href="<?php echo url('auth', 'login'); ?>" class="btn btn-sm btn-primary mt-3">
                                    <i class="fas fa-arrow-right me-1"></i> Commencer
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number">200+</div>
                        <div class="stats-title">Offres de stage</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number">50+</div>
                        <div class="stats-title">Entreprises partenaires</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-number">300+</div>
                        <div class="stats-title">Étudiants satisfaits</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2 class="fw-bold mb-4">Prêt à trouver votre stage idéal ?</h2>
                <p class="mb-4">Rejoignez notre plateforme dès aujourd'hui et accédez à des centaines d'offres de stage correspondant à votre profil.</p>
                <?php if (!isLoggedIn()): ?>
                    <a href="<?php echo url('auth', 'login'); ?>" class="btn btn-cta btn-lg px-5">
                        <i class="fas fa-rocket me-2"></i>Commencer maintenant
                    </a>
                <?php else: ?>
                    <a href="<?php echo url('offres'); ?>" class="btn btn-cta btn-lg px-5">
                        <i class="fas fa-search me-2"></i>Voir les offres
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>