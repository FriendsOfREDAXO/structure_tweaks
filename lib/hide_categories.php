<?php
/**
 * @author Friends of REDAXO
 */

class structure_tweaks_hide_categories extends structure_tweaks_base
{
    /**
     * Check page and category, hide if necessary
     */
    public static function init()
    {
        rex_extension::register('PACKAGES_INCLUDED', function () {
            if (rex_addon::get('structure')->isAvailable() &&
                (rex_request('page', 'string') == 'structure' || rex_request('page', 'string') == 'linkmap')
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
            $type = 'hide_categories_non_admin';
        } else {
            $type = 'hide_categories';
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
            if (rex_request('page', 'string') == 'structure') {
            $subject .= self::getScript($hidden_categories);
        }
            if (rex_request('page', 'string') == 'linkmap') {
                $subject .= self::getScriptInLinkmap($hidden_categories);
            }
        }

        // Pass hidden non-admin categories to JavaScript
        $hidden_categories = self::getHiddenCategories(true);
        if (!empty($hidden_categories) && !rex::getUser()->isAdmin()) {
            if (rex_request('page', 'string') == 'structure') {
            $subject .= self::getScript($hidden_categories);
        }
            if (rex_request('page', 'string') == 'linkmap') {
                $subject .= self::getScriptInLinkmap($hidden_categories);
            }
        }

        return $subject;
    }

    /**
     * @param array $hidden_categories
     * @return string
     */
    protected static function getScript($hidden_categories)
    {
        return '
            <script>
                $(function() {
                    var structureTweaks_hideCategoryRows = new structureTweaks();
                    structureTweaks_hideCategoryRows.setHiddenCategoryRows(\''.json_encode($hidden_categories).'\').hideCategories();
                    $(document).on("pjax:end", function() {
                        structureTweaks_hideCategoryRows.hideCategories();
                    });
                });
            </script>
        ';
    }

    /**
     * @param array $hidden_categories
     * @return string
     */
    protected static function getScriptInLinkmap($hidden_categories)
    {
        return '
            <script>
                $(function() {
                    var structureTweaks_hideCategoryRows = new structureTweaks();
                    structureTweaks_hideCategoryRows.setHiddenCategoryRows(\''.json_encode($hidden_categories).'\').hideCategories();
                    $(document).on("ready pjax:end", function() {
                        structureTweaks_hideCategoryRows.hideCategoriesInLinkmap();
                    });
                });
            </script>
        ';
    }
}
