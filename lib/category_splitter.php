<?php
/**
 * @author Friends of REDAXO
 */

class structure_tweaks_category_splitter extends structure_tweaks_base
{
    /**
     * Split categories
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
    public static function getSplitterCategories()
    {
        return self::getArticles('split_category');
    }

    /**
     * EP CALLBACK
     * @param rex_extension_point $ep
     * @return string
     */
    public static function ep(rex_extension_point $ep)
    {
        $subject = $ep->getSubject();

        // Pass splitting categories to JavaScript
        $subject .= '
            <script>
                $(function() {
                    var structureTweaks_splitCategories = new structureTweaks();
                    structureTweaks_splitCategories.setSplitterCategories(\''.json_encode(self::getSplitterCategories()).'\').splitCategories();
                    $(document).on("pjax:end", function() {
                        structureTweaks_splitCategories.splitCategories();
                    });
                });
            </script>
        ';

        return $subject;
    }
}
