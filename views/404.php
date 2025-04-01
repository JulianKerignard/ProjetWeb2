<?php
// Titre de la page
$pageTitle = "Page non trouvée";
include ROOT_PATH . '/views/templates/header.php';
?>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <div class="error-page">
                        <h1 class="display-1 text-primary">404</h1>
                        <h2 class="mb-4">Oups ! Page non trouvée</h2>
                        <p class="lead mb-5">La page que vous recherchez n'existe pas ou a été déplacée.</p>
                        <a href="<?php echo url(); ?>" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>