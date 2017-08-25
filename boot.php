<?php
/**
 * @var rex_addon $this
 */

if (rex::isBackend() && rex::getUser() && !rex::isSetup()) {
    if (rex_addon::get('metainfo')->isAvailable() || rex_addon::get('structure')->isAvailable()) {
        rex_view::addCssFile($this->getAssetsUrl('style.css'));
        rex_view::addJsFile($this->getAssetsUrl('script.js'));

        // Tweak redaxo skin to keep structure table aligned
        if (rex_plugin::get('be_style', 'redaxo')->isAvailable() && file_exists($this->getAssetsPath('style-redaxo.cs'))) {
            rex_view::addCssFile($this->getAssetsUrl('style-redaxo.css'));
        }
    }

    // Hide startarticles
    structure_tweaks_hide_startarticle::init();

    // Hide category functions
    structure_tweaks_hide_category_functions::init();

    // Split categories
    structure_tweaks_category_splitter::init();

    // Move meta infos
    if ($this->getConfig('move_meta_info_page')) {
        structure_tweaks_move_metainfo::init();
    }
}
