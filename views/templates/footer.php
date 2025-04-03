<?php
// Vérifier si on accède directement au fichier
if (!defined('ROOT_PATH')) {
    // Définir les chemins manuellement pour un accès direct
    define('ROOT_PATH', dirname(dirname(__DIR__))); // Remonte de deux niveaux depuis /views/templates

    // Charger les fichiers nécessaires
    require_once ROOT_PATH . '/config/config.php';
    require_once ROOT_PATH . '/includes/functions.php';

    // Démarrer la session si elle n'est pas déjà démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
?>
</main>

<!-- Élément de séparation pour garantir le bon flux du document -->
<div class="clearfix" style="visibility: visible; clear: both; height: 60px;"></div>

<!-- Footer moderne -->
<footer class="mt-5" style="position: relative; z-index: 10; clear: both;">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-5 col-md-6">
                <h5><i class="fas fa-briefcase me-2"></i><?php echo SITE_NAME; ?></h5>
                <p>Plateforme de gestion des offres de stage pour les étudiants CESI. Trouvez le stage idéal pour votre carrière professionnelle !</p>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Liens utiles</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo url(); ?>"><i class="fas fa-chevron-right me-2"></i>Accueil</a></li>
                    <li><a href="<?php echo url('offres'); ?>"><i class="fas fa-chevron-right me-2"></i>Offres de stage</a></li>
                    <li><a href="<?php echo url('entreprises'); ?>"><i class="fas fa-chevron-right me-2"></i>Entreprises</a></li>
                    <li><a href="<?php echo url('mentions-legales'); ?>"><i class="fas fa-chevron-right me-2"></i>Mentions légales</a></li>
                    <?php if (isLoggedIn() && $_SESSION['role'] === ROLE_ETUDIANT): ?>
                        <li><a href="<?php echo url('candidatures', 'mes-candidatures'); ?>"><i class="fas fa-chevron-right me-2"></i>Mes candidatures</a></li>
                        <li><a href="<?php echo url('candidatures', 'afficher-wishlist'); ?>"><i class="fas fa-chevron-right me-2"></i>Ma liste de souhaits</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h5>Contact</h5>
                <address>
                    <p><i class="fas fa-map-marker-alt me-3"></i>6 Rue Bois du Chêne le Loup, 54500 Vandœuvre-lès-Nancy</p>
                    <p><i class="fas fa-envelope me-3"></i> contact@web4all.fr</p>
                    <p><i class="fas fa-phone me-3"></i> +33 7 66 85 63 90</p>
                    <p><i class="fas fa-clock me-3"></i> Lun-Ven: 9h00-18h00</p>
                </address>

                <h5 class="mt-4">Suivez-nous</h5>
                <div class="social-links mt-3">
                    <a href="https://www.facebook.com/" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://x.com/" class="social-link" title="X"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.linkedin.com/" class="social-link" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://www.instagram.com/" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>

        <div class="footer-bottom text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Web4All - Tous droits réservés | Conçu avec <i class="fas fa-heart text-primary"></i> pour les étudiants CESI</p>
        </div>
    </div>
</footer>

<!-- Back to top button -->
<button type="button" class="btn btn-primary btn-floating btn-lg" id="btn-back-to-top" style="position: fixed; bottom: 20px; right: 20px; display: none; border-radius: 50%; width: 50px; height: 64px; z-index: 1000;">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Bootstrap JS via CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Scripts personnalisés -->
<script src="<?php echo URL_ROOT; ?>/public/js/main.js"></script>

<!-- Script pour le bouton back-to-top et gestion du z-index -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Bouton retour en haut
        const backToTopButton = document.getElementById('btn-back-to-top');

        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopButton.style.display = 'flex';
                backToTopButton.style.alignItems = 'center';
                backToTopButton.style.justifyContent = 'center';
            } else {
                backToTopButton.style.display = 'none';
            }
        });

        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Animation des éléments au scroll
        const animateElements = document.querySelectorAll('.card, .feature-icon, .btn-lg');

        function checkAnimation() {
            animateElements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight;

                if (elementPosition < screenPosition * 0.9) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        }

        // Initialisation des éléments
        animateElements.forEach(element => {
            element.style.opacity = '0';
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        });

        // Vérification initiale
        checkAnimation();

        // Vérification au scroll
        window.addEventListener('scroll', checkAnimation);

        // Correctif pour le problème de chevauchement
        // Forcer la position du footer
        const footer = document.querySelector('footer');
        if (footer) {
            footer.style.position = 'relative';
            footer.style.zIndex = '10';
            footer.style.clear = 'both';
        }

        // Forcer la visibilité du séparateur
        const footerSpacer = document.querySelector('.footer-spacer');
        if (footerSpacer) {
            footerSpacer.style.display = 'block';
            footerSpacer.style.visibility = 'visible';
            footerSpacer.style.height = '80px';
        }

        // Correctif pour le conteneur de vue détaillée
        const detailView = document.querySelector('.detail-view');
        if (detailView) {
            detailView.style.marginBottom = '120px';
        }
    });
</script>
</body>
</html>