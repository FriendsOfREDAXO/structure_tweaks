<?php
/**
 * @author Friends of REDAXO
 */

$sql = rex_sql::factory();
$sql->setQuery('DROP TABLE IF EXISTS `'.rex::getTable('structure_tweaks').'`;');
