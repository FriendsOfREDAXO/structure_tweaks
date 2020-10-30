<?php
/**
 * @author Friends of REDAXO
 */

$table = rex_sql_table::get(rex::getTable('structure_tweaks'));
$table
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('article_id', 'int(11)', false), 'id')
    ->ensureColumn(new rex_sql_column('type', 'varchar(100)', false), 'article_id')
    ->ensureColumn(new rex_sql_column('label', 'varchar(100)', false), 'type')
    ->alter()
;

// Ensure that all updates are also executed on re-install
include ('update.php');
