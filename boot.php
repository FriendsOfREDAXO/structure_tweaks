<?php
/**
 * @var rex_addon $this
 */

if (rex::isBackend() && rex::getUser() && !rex::isSetup()) {
    // Workaround forcing page reload to be able to insert new content to head
    $page_property = rex_addon::get('structure')->getProperty('page');
    $page_property['pjax'] = false;
    rex_addon::get('structure')->setProperty('page', $page_property);

    // Hide startarticles
    structure_tweaks_hide_startarticle::init();

    // Split categories
    structure_tweaks_category_splitter::init();

    // Move meta infos
    if ($this->getConfig('move_meta_info_page')) {
        structure_tweaks_move_metainfo::init();
    }
}
