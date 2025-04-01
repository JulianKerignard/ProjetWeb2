</main>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><?php echo SITE_NAME; ?></h5>
                <p>Plateforme de gestion des offres de stage pour les étudiants CESI.</p>
            </div>
            <div class="col-md-3">
                <h5>Liens utiles</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo url(); ?>" class="text-white">Accueil</a></li>
                    <li><a href="<?php echo url('offres'); ?>" class="text-white">Offres de stage</a></li>
                    <li><a href="<?php echo url('entreprises'); ?>" class="text-white">Entreprises</a></li>
                    <li><a href="#" class="text-white">Mentions légales</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Contact</h5>
                <address>
                    <p><i class="fas fa-envelope me-2"></i> contact@web4all.fr</p>
                    <p><i class="fas fa-phone me-2"></i> +33 1 23 45 67 89</p>
                </address>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Web4All - Tous droits réservés</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="<?php echo URL_ROOT; ?>/public/js/bootstrap.bundle.min.js"></script>
<!-- Scripts personnalisés -->
<script src="<?php echo URL_ROOT; ?>/public/js/main.js"></script>
</body>
</html>