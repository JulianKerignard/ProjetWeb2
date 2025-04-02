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
$pageTitle = "Mentions Légales";
include ROOT_PATH . '/views/templates/header.php';
?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h1 class="h2 mb-0">
                            <i class="fas fa-gavel me-2"></i>Mentions Légales
                        </h1>
                    </div>
                    <div class="card-body">
                        <h2>Éditeur du site</h2>
                        <p>Le site <?php echo SITE_NAME; ?> est édité par:</p>
                        <p>
                            <strong>Web4All</strong><br>
                            4 Allée du Web<br>
                            75000 Paris<br>
                            France<br>
                            Téléphone : +33 1 23 45 67 89<br>
                            Email : contact@web4all.fr
                        </p>

                        <h2>Hébergement</h2>
                        <p>
                            Ce site est hébergé par:<br>
                            <strong>Microsoft Azure</strong><br>
                            37/45 37 QUAI DU PRESIDENT ROOSEVELT<br>
                            92130 ISSY-LES-MOULINEAUX<br>
                            France
                        </p>

                        <h2>Propriété intellectuelle</h2>
                        <p>
                            L'ensemble de ce site relève de la législation française et internationale sur le droit d'auteur et la propriété intellectuelle. Tous les droits de reproduction sont réservés, y compris pour les documents téléchargeables et les représentations iconographiques et photographiques.
                        </p>

                        <h2>Protection des données personnelles</h2>
                        <p>
                            Conformément à la loi « Informatique et Libertés » du 6 janvier 1978 modifiée, vous disposez d'un droit d'accès, de modification, de rectification et de suppression des données qui vous concernent. Pour exercer ce droit, veuillez nous contacter par email à l'adresse : contact@web4all.fr
                        </p>

                        <h2>Cookies</h2>
                        <p>
                            Ce site utilise des cookies pour améliorer l'expérience utilisateur. En naviguant sur ce site, vous acceptez l'utilisation de cookies conformément à notre politique de confidentialité.
                        </p>

                        <h2>Limitations de responsabilité</h2>
                        <p>
                            <?php echo SITE_NAME; ?> ne pourra être tenu responsable des dommages directs et indirects causés au matériel de l'utilisateur, lors de l'accès au site, et résultant soit de l'utilisation d'un matériel ne répondant pas aux spécifications indiquées, soit de l'apparition d'un bug ou d'une incompatibilité.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include ROOT_PATH . '/views/templates/footer.php'; ?>