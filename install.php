<?php
/**
 * @var rex_addon $this
 */

// New column in version 1.0.0-beta
$tables = rex_sql::showTables();
if (in_array(rex::getTable('structure_tweaks'), $tables)) {
    rex_sql_table::get(rex::getTable('structure_tweaks'))
        ->ensureColumn(new rex_sql_column('label', 'varchar(100)', false))
        ->alter();
}
