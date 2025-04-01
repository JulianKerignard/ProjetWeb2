</main>

<!-- Footer moderne -->
<footer>
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-5 col-md-6">
                <h5><i class="fas fa-briefcase me-2"></i><?php echo SITE_NAME; ?></h5>
                <p>Plateforme de gestion des offres de stage pour les étudiants CESI. Trouvez le stage idéal pour votre carrière professionnelle !</p>
                <div class="social-links mt-3">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <h5>Liens utiles</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo url(); ?>"><i class="fas fa-chevron-right me-2"></i>Accueil</a></li>
                    <li><a href="<?php echo url('offres'); ?>"><i class="fas fa-chevron-right me-2"></i>Offres de stage</a></li>
                    <li><a href="<?php echo url('entreprises'); ?>"><i class="fas fa-chevron-right me-2"></i>Entreprises</a></li>
                    <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Mentions légales</a></li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6">
                <h5>Contact</h5>
                <address>
                    <p><i class="fas fa-map-marker-alt me-3"></i> 1 Avenue des Stages, 75000 Paris</p>
                    <p><i class="fas fa-envelope me-3"></i> contact@web4all.fr</p>
                    <p><i class="fas fa-phone me-3"></i> +33 1 23 45 67 89</p>
                    <p><i class="fas fa-clock me-3"></i> Lun-Ven: 9h00-18h00</p>
                </address>
            </div>
        </div>

        <div class="footer-bottom text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Web4All - Tous droits réservés | Conçu avec <i class="fas fa-heart text-primary"></i> pour les étudiants CESI</p>
        </div>
    </div>
</footer>

<!-- Back to top button -->
<button type="button" class="btn btn-primary btn-floating btn-lg" id="btn-back-to-top" style="position: fixed; bottom: 20px; right: 20px; display: none; border-radius: 50%; width: 50px; height: 50px;">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Bootstrap JS via CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- Scripts personnalisés -->
<script src="<?php echo URL_ROOT; ?>/public/js/main.js"></script>

<!-- Script pour le bouton back-to-top -->
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
    });
</script>
</body>
</html>