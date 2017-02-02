<?php
/**
 * @var rex_addon $this
 */

if (rex::isBackend() && !rex::isSetup() && rex_addon::get('structure')->isAvailable()) {
    // Workaround forcing page reload to be able to insert new content to head
    $page_property = rex_addon::get('structure')->getProperty('page');
    $page_property['pjax'] = false;
    rex_addon::get('structure')->setProperty('page', $page_property);

    structure_tweaks_hide_startarticle::hideStartArticle();
    structure_tweaks_category_splitter::splitCategories();
}


