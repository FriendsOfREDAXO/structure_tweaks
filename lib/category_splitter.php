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
        $sql = rex_sql::factory();
        $articles = $sql->getArray('SELECT * FROM '.rex::getTable(self::name()).' WHERE `type` = "split_category"');

        $return = [];
        foreach ($articles as $article) {
            $item = [
                'article_id' => $article['article_id'],
                'label' => rex_i18n::translate($article['label']),
            ];

            $return[] = $item;
        }

        return $return;
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
        $split_categories = self::getSplitterCategories();
        if (!empty($split_categories)) {
            $subject .= '
                <script>
                    let structureTweaks_splitCategories = new structureTweaks();
                    structureTweaks_splitCategories.setSplitterCategories(\''.json_encode($split_categories).'\').splitCategories();
                    $(document).on(\'rex:ready\', function() {
                        structureTweaks_splitCategories.splitCategories();
                    });
                </script>
            ';
        }

        return $subject;
    }
}
