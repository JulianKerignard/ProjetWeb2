<?php
// Titre de la page
$pageTitle = "Accueil";
include 'views/templates/header.php';
?>

    <!-- Hero Section -->
    <section class="hero bg-light py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Trouvez le stage idéal pour votre carrière</h1>
                    <p class="lead mb-4">Une plateforme complète pour vous aider dans votre recherche de stage et faciliter votre entrée dans le monde professionnel.</p>
                    <?php if (!isLoggedIn()): ?>
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="<?php echo url('auth', 'login'); ?>" class="btn btn-primary btn-lg px-4 me-md-2">Se connecter</a>
                        </div>
                    <?php else: ?>
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="<?php echo url('offres'); ?>" class="btn btn-primary btn-lg px-4 me-md-2">Voir les offres</a>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <img src="<?php echo URL_ROOT; ?>/public/img/hero-image.svg" alt="Stage" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-md-12">
                    <h2 class="fw-bold">Pourquoi utiliser notre plateforme ?</h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                                <i class="fas fa-search fa-2x p-3"></i>
                            </div>
                            <h5 class="card-title">Recherche simplifiée</h5>
                            <p class="card-text">Trouvez rapidement des offres de stage correspondant à votre profil et à vos compétences.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                                <i class="fas fa-building fa-2x p-3"></i>
                            </div>
                            <h5 class="card-title">Entreprises vérifiées</h5>
                            <p class="card-text">Accédez à des entreprises partenaires de confiance avec des évaluations de stagiaires précédents.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="feature-icon bg-primary bg-gradient text-white rounded-circle mb-3">
                                <i class="fas fa-file-alt fa-2x p-3"></i>
                            </div>
                            <h5 class="card-title">Candidature facile</h5>
                            <p class="card-text">Postulez directement depuis la plateforme et suivez l'état de vos candidatures.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4">
                    <h2 class="fw-bold text-primary">200+</h2>
                    <p class="lead">Offres de stage</p>
                </div>
                <div class="col-md-4">
                    <h2 class="fw-bold text-primary">50+</h2>
                    <p class="lead">Entreprises partenaires</p>
                </div>
                <div class="col-md-4">
                    <h2 class="fw-bold text-primary">300+</h2>
                    <p class="lead">Étudiants satisfaits</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="fw-bold mb-4">Prêt à trouver votre stage idéal ?</h2>
                    <p class="lead mb-4">Rejoignez notre plateforme dès aujourd'hui et accédez à des centaines d'offres de stage correspondant à votre profil.</p>
                    <?php if (!isLoggedIn()): ?>
                        <a href="<?php echo url('auth', 'login'); ?>" class="btn btn-primary btn-lg px-5">Commencer maintenant</a>
                    <?php else: ?>
                        <a href="<?php echo url('offres'); ?>" class="btn btn-primary btn-lg px-5">Voir les offres</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

<?php include 'views/templates/footer.php'; ?>