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
     * @param bool $non_admin
     * @return array
     */
    protected static function getHiddenArticles($non_admin = false)
    {
        if ($non_admin) {
            $type = 'hide_startarticle_non_admin';
        } else {
            $type = 'hide_startarticle';
        }

        return self::getArticles($type);
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
        $hidden_articles = self::getHiddenArticles();
            if (!empty($hidden_articles)) {
                $subject .= self::getScript($hidden_articles);
        }

        // Pass hidden non-admin articles to JavaScript
        $hidden_articles = self::getHiddenArticles(true);
        if (!empty($hidden_articles) && !rex::getUser()->isAdmin()) {
            $subject .= self::getScript($hidden_articles);
        }

        return $subject;
    }

    /**
     * @param array $hidden_articles
     * @return string
     */
    protected static function getScript($hidden_articles)
    {
        return '
            <script>
                $(function() {
                    var structureTweaks_hideArticles = new structureTweaks();
                    structureTweaks_hideArticles.setHiddenArticles(\''.json_encode($hidden_articles).'\').hideArticles();
                    $(document).on("pjax:end", function() {
                        structureTweaks_hideArticles.hideArticles();
                    });
                });
            </script>
        ';
    }
}
