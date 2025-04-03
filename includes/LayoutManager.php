<?php
/**
 * Gestionnaire de mise en page pour prévenir les problèmes de chevauchement
 *
 * Cette classe gère l'intégration correcte des différentes parties des templates
 * en assurant des espacements corrects et la gestion des z-index.
 *
 * @version 1.0
 */
class LayoutManager {
    /**
     * Chemins des templates
     */
    private $viewsPath;
    private $templatePath;

    /**
     * État du layout actuel
     */
    private $hasWidgetZone = false;
    private $contentType = 'default';
    private $pageTitle = '';
    private $metaTags = [];

    /**
     * Constructeur
     */
    public function __construct($viewsPath = null) {
        if ($viewsPath === null) {
            $this->viewsPath = defined('VIEWS_PATH') ? VIEWS_PATH : ROOT_PATH . '/views';
        } else {
            $this->viewsPath = $viewsPath;
        }

        $this->templatePath = $this->viewsPath . '/templates';
    }

    /**
     * Définit les métadonnées du layout
     */
    public function setLayoutMeta($pageTitle, $contentType = 'default', $hasWidgetZone = false) {
        $this->pageTitle = $pageTitle;
        $this->contentType = $contentType;
        $this->hasWidgetZone = $hasWidgetZone;

        return $this;
    }

    /**
     * Ajoute une balise meta
     */
    public function addMetaTag($name, $content) {
        $this->metaTags[$name] = $content;

        return $this;
    }

    /**
     * Génère le header avec les bons métadonnées
     */
    public function renderHeader() {
        // Extraction des variables pour les rendre disponibles dans la vue
        $pageTitle = $this->pageTitle;
        $metaTags = $this->metaTags;
        $contentType = $this->contentType;

        // Inclusion du header
        include $this->templatePath . '/header.php';
    }

    /**
     * Génère le footer avec les ajustements appropriés
     */
    public function renderFooter() {
        // Si la page a une zone de widgets, on ajoute un espace supplémentaire
        if ($this->hasWidgetZone) {
            echo '<div class="clearfix" style="margin-bottom: 80px;"></div>';
        }

        // Inclusion du footer
        include $this->templatePath . '/footer.php';
    }

    /**
     * Méthode pratique pour encapsuler une vue entre le header et le footer
     */
    public function renderView($viewPath, $data = []) {
        // Extraction des variables pour les rendre disponibles dans la vue
        extract($data);

        // Configuration du layout en fonction des données
        $this->setLayoutMeta(
            isset($pageTitle) ? $pageTitle : 'Page',
            isset($contentType) ? $contentType : 'default',
            isset($hasWidgetZone) ? $hasWidgetZone : false
        );

        // Rendu du header
        $this->renderHeader();

        // Inclusion du contenu principal
        include $this->viewsPath . '/' . $viewPath . '.php';

        // Rendu du footer
        $this->renderFooter();
    }
}