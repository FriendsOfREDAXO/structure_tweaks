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
                $hidden_articles = self::getHiddenArticles();
                $category_id = rex_request::request('category_id', 'int');

                if (in_array($category_id, $hidden_articles)) {
                    rex_extension::register('PAGE_HEADER', [__CLASS__, 'ep'], rex_extension::NORMAL, [
                        'page' => $page,
                    ]);
                }
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
        $subject  = $ep->getSubject();

        $subject .= '
            <style type="text/css"> 
                .rex-startarticle { display: none !important; } 
            </style>
        ';
        // Add missing article class in linkmap
        if ($ep->getParam('page') == 'linkmap') {
            $subject .= '
                <script type="text/javascript">
                    jQuery(document).ready(function() {
                        jQuery(".rex-icon-startarticle").parents("li").addClass("rex-startarticle");
                    });
                </script>
            ';
        }

        return $subject;
    }
}
