<?php
/**
 * @author Rexdude, Friends of REDAXO
 */

class structure_tweaks_hide_startarticle extends structure_tweaks_base
{
    /**
     * Check page and category, hide if necessary
     */
    public static function init()
    {
        rex_extension::register('PACKAGES_INCLUDED', function () {
            $pages = ['structure', 'linkmap']; // Pages, where articles are shown
            $page =  rex_request::request('page', 'string');

            if (rex_addon::get('structure')->isAvailable() && in_array($page, $pages)) {
                rex_extension::register('PAGE_HEADER', [__CLASS__, 'ep']);
            }
        });
    }

    /**
     * @return array
     */
    protected static function getHiddenArticles()
    {
        return self::getArticles('hide_startarticle');
    }

    /**
     * EP CALLBACK
     * @param rex_extension_point $ep
     * @return string
     */
    public static function ep(rex_extension_point $ep)
    {
        $subject = $ep->getSubject();

        // Pass hidden articles to JavaScript
        $subject .= '
            <script>
                $(function() {
                    var structureTweaks_hideArticles = new structureTweaks();
                    structureTweaks_hideArticles.setHiddenArticles(\''.json_encode(self::getHiddenArticles()).'\').hideArticles();
                    $(document).on("pjax:end", function() {
                        structureTweaks_hideArticles.hideArticles();
                    });
                });
            </script>
        ';

        return $subject;
    }
}
