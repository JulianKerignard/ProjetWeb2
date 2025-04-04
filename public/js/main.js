/**
 * Script JavaScript principal
 */

document.addEventListener('DOMContentLoaded', function() {
    // Activer les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Activer les popovers Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });

    // Fermer les alertes automatiquement après 5 secondes
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Validation des formulaires côté client
    var forms = document.querySelectorAll('.needs-validation');

    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });

    // Fonction pour confirmer une action (suppression, etc.)
    var confirmBtns = document.querySelectorAll('.confirm-action');

    confirmBtns.forEach(function(btn) {
        btn.addEventListener('click', function(event) {
            var message = this.getAttribute('data-confirm-message') || 'Êtes-vous sûr de vouloir effectuer cette action ?';

            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Gestion des filtres de recherche
    var filterForm = document.getElementById('filter-form');
    var clearFilterBtn = document.getElementById('clear-filters');

    if (clearFilterBtn && filterForm) {
        clearFilterBtn.addEventListener('click', function() {
            // CORRECTION ICI: Ne pas effacer les champs cachés qui contiennent les paramètres de navigation
            var inputs = filterForm.querySelectorAll('input:not([type="hidden"]), select');

            inputs.forEach(function(input) {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
            });

            filterForm.submit();
        });
    }

    // Prévisualisation des images uploadées
    var imageInputs = document.querySelectorAll('.image-upload');

    imageInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            var previewId = this.getAttribute('data-preview');
            var preview = document.getElementById(previewId);

            if (preview && this.files && this.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }

                reader.readAsDataURL(this.files[0]);
            }
        });
    });
});