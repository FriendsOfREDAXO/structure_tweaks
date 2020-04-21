<?php
/**
 * @var rex_addon $this
 */

if (rex::isBackend() && rex::getUser() && !rex::isSetup()) {
    if (rex_addon::get('metainfo')->isAvailable() || rex_addon::get('structure')->isAvailable()) {
        rex_view::addCssFile($this->getAssetsUrl('style.css'));
        rex_view::addJsFile($this->getAssetsUrl('script.js'));
    }

    // Hide startarticles
    structure_tweaks_hide_startarticle::init();

    // Hide category functions
    structure_tweaks_hide_category_functions::init();

    // Hide categories
    structure_tweaks_hide_categories::init();

    // Split categories
    structure_tweaks_category_splitter::init();
    
    // Last modified categories / articles
    if ($this->getConfig('show_lastmodified_categories') || $this->getConfig('show_lastmodified_articles') ) {
      structure_tweaks_category_last_modified::init();
    }

    if (rex_string::versionCompare(rex::getVersion(), '5.10.0-dev', '<')) {
        // load settings page
        $page = $this->getProperty('page');
        $page['subpages']['settings'] = ['title' => $this->i18n('structure_tweaks_page_settings')];
        $this->setProperty('page', $page);
        // Move meta infos
        if ($this->getConfig('move_meta_info_page')) {
            structure_tweaks_move_metainfo::init();
        }
     }
}
