<?php
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

    <link rel="stylesheet" type="text/css" href="/public/css/styles.css">

    <div class="container mt-4">
        <p>**Mentions Légales**

            **1. Éditeur du site**
            Nom du site : [Nom du site]
            Propriétaire : [Nom de l'entreprise / Nom du responsable]
            Adresse : [Adresse complète]
            Téléphone : [Numéro de téléphone]
            Email : [Adresse e-mail]
            Numéro SIRET : [Si applicable]

            **2. Hébergement**
            Hébergeur : [Nom de l'hébergeur]
            Adresse : [Adresse de l'hébergeur]
            Téléphone : [Numéro de contact de l'hébergeur]

            **3. Propriété intellectuelle**
            Tout le contenu présent sur ce site (textes, images, graphismes, logos, vidéos, etc.) est la propriété exclusive de [Nom de l'entreprise / Nom du propriétaire], sauf mention contraire. Toute reproduction, modification ou distribution sans autorisation écrite est interdite.

            **4. Données personnelles**
            Les informations collectées via ce site sont traitées conformément à la réglementation en vigueur sur la protection des données personnelles. Vous disposez d’un droit d’accès, de rectification et de suppression de vos données en nous contactant à : [Adresse e-mail de contact].

            **5. Cookies**
            Ce site utilise des cookies pour améliorer l’expérience utilisateur. Vous pouvez modifier vos préférences en matière de cookies à tout moment via les paramètres de votre navigateur.

            **6. Responsabilité**
            [Nom du site] ne saurait être tenu responsable des dommages directs ou indirects résultant de l’utilisation de son site.

            **7. Contact**
            Pour toute question, vous pouvez nous contacter à : [Adresse e-mail de contact].

        </p>
    </div>


<?php include ROOT_PATH . '/views/templates/footer.php';