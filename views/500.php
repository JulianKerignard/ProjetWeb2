<?php
// Page d'erreur serveur 500
$pageTitle = isset($pageTitle) ? $pageTitle : 'Erreur interne du serveur';
$errorMessage = isset($errorMessage) ? $errorMessage : 'Une erreur inattendue est survenue sur le serveur.';

// Inclusion conditionnelle du header selon disponibilité
if (file_exists(ROOT_PATH . '/views/templates/header.php')) {
    include ROOT_PATH . '/views/templates/header.php';
} else {
    // Header minimaliste de secours
    echo '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $pageTitle . ' - ' . (defined('SITE_NAME') ? SITE_NAME : 'Application') . '</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
    <main class="py-4">';
}
?>

    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <div class="error-page">
                        <h1 class="display-1 text-danger">500</h1>
                        <h2 class="mb-4">Erreur interne du serveur</h2>
                        <div class="alert alert-danger mb-4">
                            <p class="mb-0"><?php echo $errorMessage; ?></p>
                        </div>
                        <p class="lead mb-5">Nous nous excusons pour ce désagrément. Notre équipe technique a été informée du problème.</p>
                        <div class="d-grid gap-2 d-md-flex justify-content-center">
                            <a href="<?php echo defined('URL_ROOT') ? URL_ROOT : '/'; ?>" class="btn btn-primary btn-lg">
                                <i class="fas fa-home me-2"></i>Retour à l'accueil
                            </a>
                            <button onclick="window.location.reload()" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-sync-alt me-2"></i>Rafraîchir la page
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php
// Inclusion conditionnelle du footer selon disponibilité
if (file_exists(ROOT_PATH . '/views/templates/footer.php')) {
    include ROOT_PATH . '/views/templates/footer.php';
} else {
    // Footer minimaliste de secours
    echo '</main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>';
}
?>