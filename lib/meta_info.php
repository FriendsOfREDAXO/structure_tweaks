<?php
/**
 * @author Friends of REDAXO
 */

class structure_tweaks_meta_info extends structure_tweaks_base
{
    /**
     * Set EP
     */
    public static function init()
    {
        rex_extension::register('PACKAGES_INCLUDED', function () {
            if (rex_addon::get('metainfo')->isAvailable()) {
                // Remove meta info tab
                rex_addon::get('metainfo')->removeProperty('pages');

                // Redirect meta info into sidebar
                rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', [__CLASS__, 'getMetaPage']);
            }
        });

    }

    /**
     * @param rex_extension_point $ep
     * @return string
     */
    public static function getMetaPage(rex_extension_point $ep)
    {
        $params = $ep->getParams();
        $subject = $ep->getSubject();

        $panel = include(self::getAddon()->getPath('pages/sidebar.metainfo.php'));

        $fragment = new rex_fragment();
        $fragment->setVar('title', '<i class="rex-icon rex-icon-info"></i> Einstellungen', false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('article_id', $params['article_id'], false);
        $fragment->setVar('clang', $params['clang'], false);
        $fragment->setVar('ctype', $params['ctype'], false);
        $fragment->setVar('collapse', true);
        $fragment->setVar('collapsed', false);
        $content = $fragment->parse('core/page/section.php');

        return $content.$subject;
    }
}
