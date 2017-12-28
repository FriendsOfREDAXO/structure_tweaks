<?php
/**
 * @author Friends of REDAXO
 */

class structure_tweaks_hide_category_functions extends structure_tweaks_base
{
    /**
     * Check page and category, hide if necessary
     */
    public static function init()
    {
        rex_extension::register('PACKAGES_INCLUDED', function () {
            if (rex_addon::get('structure')->isAvailable() && rex_request('page', 'string') == 'structure') {
                rex_extension::register('PAGE_HEADER', [__CLASS__, 'ep']);
            }
        });
    }

    /**
     * @return array
     */
    protected static function getHiddenCategories()
    {
        return self::getArticles('hide_cat_functions');
    }

    /**
     * EP CALLBACK
     * @param rex_extension_point $ep
     * @return string
     */
    public static function ep(rex_extension_point $ep)
    {
        $subject = $ep->getSubject();

        // Pass hidden categories to JavaScript
        $subject .= '
            <script>
                $(function() {
                    var structureTweaks_hideCategories = new structureTweaks();
                    structureTweaks_hideCategories.setHiddenCategories(\''.json_encode(self::getHiddenCategories()).'\').hideCategoryFunctions();
                    $(document).on("pjax:end", function() {
                        structureTweaks_hideCategories.hideCategoryFunctions();
                    });
                });
            </script>
        ';

        return $subject;
    }
}
