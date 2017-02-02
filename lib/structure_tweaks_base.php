<?php
/**
 * @author Daniel Weitenauer
 */

class structure_tweaks_base
{
    /**
     * @return string
     */
    protected static function getName()
    {
        return self::getAddon()->getName();
    }

    /**
     * @return rex_addon
     */
    protected static function getAddon()
    {
        return rex_addon::get('structure_tweaks');
    }

    /**
     * @param $string
     * @return string
     */
    protected static function msg($string)
    {
        return \rex_i18n::msg(self::getName().'_'.$string);
    }

    /**
     * @param string $type
     * @return array
     */
    protected static function getArticles($type)
    {
        $sql = rex_sql::factory();
        #$sql->setDebug(true);
        $hidden_articles = $sql->getArray('SELECT `article_id` FROM '.rex::getTable(self::getName()).' WHERE `type` = ?', [ $type ]);

        $return = [];
        foreach ($hidden_articles as $article) {
            $return[] = $article['article_id'];
        }

        return $return;
    }
}
