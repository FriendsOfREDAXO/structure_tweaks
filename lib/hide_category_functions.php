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
            if (
                (rex_addon::get('structure')->isAvailable() && rex_request('page', 'string') == 'structure') ||
                (rex_plugin::get('structure', 'content')->isAvailable() && rex_request('page', 'string') == 'content/edit')
            ) {
                rex_extension::register('PAGE_HEADER', [__CLASS__, 'ep']);
            }
        });
    }

    /**
     * @param bool $non_admin
     * @return array
     */
    protected static function getHiddenCategories($non_admin = false)
    {
        if ($non_admin) {
            $type = 'hide_cat_functions_non_admin';
        } else {
            $type = 'hide_cat_functions';
        }

        return self::getArticles($type);
    }

    /**
     * @param bool $non_admin
     * @return array
     */
    protected static function getHiddenCategoriesAll($non_admin = false)
    {
        if ($non_admin) {
            $type = 'hide_cat_functions_all_non_admin';
        } else {
            $type = 'hide_cat_functions_all';
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

        // Pass hidden categories to JavaScript
        $hidden_categories = self::getHiddenCategories();
        if (!empty($hidden_categories)) {
            $subject .= self::getScript($hidden_categories);
        }

        // Pass hidden non-admin categories to JavaScript
        $hidden_categories = self::getHiddenCategories(true);
        if (!empty($hidden_categories) && !rex::getUser()->isAdmin()) {
            $subject .= self::getScript($hidden_categories);
        }

        // Pass hidden categories to JavaScript
        $hidden_categories = self::getHiddenCategoriesAll();
        if (!empty($hidden_categories)) {
            $subject .= self::getScriptAll($hidden_categories);
        }

        // Pass hidden non-admin categories to JavaScript
        $hidden_categories = self::getHiddenCategoriesAll(true);
        if (!empty($hidden_categories) && !rex::getUser()->isAdmin()) {
            $subject .= self::getScriptAll($hidden_categories);
        }

        return $subject;
    }

    /**
     * @param array $hidden_categories
     * @return string
     */
    protected static function getScript($hidden_categories)
    {
        $deprecated_traversing = 'false';
        if (version_compare(rex::getVersion(), '5.5.0', '<')) {
            $deprecated_traversing = 'true';
        }

        return '
            <script>
                $(document).on("rex:ready", function() {
                    let structureTweaks_hideCategories = new structureTweaks();
                    structureTweaks_hideCategories.setHiddenCategories(\''.json_encode($hidden_categories).'\').hideCategoryFunctions('.$deprecated_traversing.');
                    structureTweaks_hideCategories.hideCategoryFunctions();
                });
            </script>
        ';
    }

    /**
     * @param array $hidden_categories
     * @return string
     */
    protected static function getScriptAll($hidden_categories)
    {
        $deprecated_traversing = 'false';
        if (version_compare(rex::getVersion(), '5.5.0', '<')) {
            $deprecated_traversing = 'true';
        }

        return '
            <script>
                $(document).on("rex:ready", function() {
                   let structureTweaks_hideCategoriesAll = new structureTweaks();
                    structureTweaks_hideCategoriesAll.setHiddenCategories(\''.json_encode($hidden_categories).'\').hideCategoryFunctionsAll('.$deprecated_traversing.');
                    structureTweaks_hideCategoriesAll.hideCategoryFunctions();
                });
            </script>
        ';
    }
}
