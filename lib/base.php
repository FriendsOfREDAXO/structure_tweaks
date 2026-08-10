<?php
/**
 * @author Daniel Weitenauer
 */

namespace FriendsOfREDAXO\StructureTweaks;

use rex;
use rex_addon;
use rex_addon_interface;
use rex_i18n;
use rex_sql;

class structure_tweaks_base
{
    /**
     * @return non-empty-string
     */
    protected static function name(): string
    {
        return self::addon()->getName();
    }

    /**
     * @return rex_addon_interface
     */
    protected static function addon(): rex_addon_interface
    {
        return rex_addon::get('structure_tweaks');
    }

    /**
     * @param $string
     * @return string
     */
    protected static function msg(string $string): string
    {
        return \rex_i18n::msg(self::name().'_'.$string);
    }

    /**
     * @param string $type
     * @return array<int, int>
     */
    protected static function getArticles(string $type): array
    {
        $sql = rex_sql::factory();
        $articles = $sql->getArray('SELECT * FROM '.rex::getTable(self::name()).' WHERE `type` = ?', [$type]);

        $return = [];
        foreach ($articles as $article) {
            $return[] = (int) $article['article_id'];
        }

        return $return;
    }
}
