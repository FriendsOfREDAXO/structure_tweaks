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

        if (rex_plugin::get('structure', 'content')->isAvailable() && rex_request('page', 'string') == 'content/functions') {
            rex_extension::register('OUTPUT_FILTER', [__CLASS__, 'epFunctions']);
        }
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
     * EP CALLBACK
     * @param rex_extension_point $ep
     * @return string
     */
    public static function epFunctions(rex_extension_point $ep)
    {
        $subject = $ep->getSubject();

        // Hidden categories
        $hidden_categories = self::getHiddenCategories();
        if (!empty($hidden_categories)) {
            $subject = self::removeCategoryOptions($subject, 'category_id_new', $hidden_categories);
            $subject = self::removeCategoryOptions($subject, 'category_copy_id_new', $hidden_categories);
        }

        // Hidden non-admin categories
        $hidden_categories = self::getHiddenCategories(true);
        if (!empty($hidden_categories) && !rex::getUser()->isAdmin()) {
            $subject = self::removeCategoryOptions($subject, 'category_id_new', $hidden_categories);
            $subject = self::removeCategoryOptions($subject, 'category_copy_id_new', $hidden_categories);
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
                $(document).on("rex:ready", function() {
                    let structureTweaks_hideCategoryRows = new structureTweaks();
                    structureTweaks_hideCategoryRows.setHiddenCategoryRows(\''.json_encode($hidden_categories).'\').hideCategories();
                    structureTweaks_hideCategoryRows.hideCategories();
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
                $(document).on("rex:ready", function() {
                    let structureTweaks_hideCategoryRows = new structureTweaks();
                    structureTweaks_hideCategoryRows.setHiddenCategoryRows(\''.json_encode($hidden_categories).'\').hideCategories();
                    structureTweaks_hideCategoryRows.hideCategoriesInLinkmap();
                });
            </script>
        ';
    }

    private static function removeCategoryOptions(string $subject, string $select_id, array $hidden_categories): string
    {
        libxml_use_internal_errors(true); // Disable HTML parsing warnings @see https://stackoverflow.com/questions/9149180/domdocumentloadhtml-error

        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->loadHTML($subject);

        $element = $dom->getElementById($select_id);
        if ($element) {
            /** @var DOMElement $option */
            foreach ($element->getElementsByTagName('option') as $option) {
                if (in_array($option->getAttribute('value'), $hidden_categories)) {
                    $element->removeChild($option);
                }
            }
        }

        libxml_use_internal_errors(false); // Enable HTML parsing warnings

        return $dom->saveHTML();
    }
}
