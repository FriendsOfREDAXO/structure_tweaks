<?php
/**
 * @author Friends of REDAXO
 */

namespace FriendsOfREDAXO\StructureTweaks;

use rex;
use rex_addon;
use rex_extension;
use rex_extension_point;
use rex_i18n;
use rex_request;
use rex_response;
use rex_sql;

class structure_tweaks_category_splitter extends structure_tweaks_base
{
    /**
     * Split categories
     */
    public static function init(): void
    {
        rex_extension::register('PACKAGES_INCLUDED', function () {
            if (rex_addon::get('structure')->isAvailable() && rex_request('page', 'string') == 'structure') {
                rex_extension::register('PAGE_HEADER', [__CLASS__, 'ep']);
            }
        });
    }

    /**
     * @return array<int, array{article_id: int, label: string}>
     */
    public static function getSplitterCategories(): array
    {
        $sql = rex_sql::factory();
        $articles = $sql->getArray('SELECT * FROM '.rex::getTable(self::name()).' WHERE `type` = "split_category"');

        $return = [];
        foreach ($articles as $article) {
            $item = [
                'article_id' => (int) $article['article_id'],
                'label' => rex_i18n::translate((string) $article['label']),
            ];

            $return[] = $item;
        }

        return $return;
    }

    /**
     * @param rex_extension_point<mixed> $ep
     */
    public static function ep(rex_extension_point $ep): string
    {
        $subject = $ep->getSubject();

        // Pass splitting categories to JavaScript
        $split_categories = self::getSplitterCategories();
        if ($split_categories !== []) {
            $subject .= '
                <script nonce="' . rex_response::getNonce() . '">
                    $(document).on("rex:ready", function() {
                        const structureTweaksSplitCategories = new structureTweaks();
                        structureTweaksSplitCategories.setSplitterCategories(\'' . json_encode($split_categories) . '\').splitCategories();
                    });
                </script>
            ';
        }

        return $subject;
    }
}
