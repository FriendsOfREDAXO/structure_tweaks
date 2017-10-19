<?php
/**
 * @var rex_addon $this
 */

$tables = rex_sql::showTables();
if (in_array(rex::getTable('structure_tweaks'), $tables)) {

    // New column in version 1.0.0-beta
    rex_sql_table::get(rex::getTable('structure_tweaks'))
        ->ensureColumn(new rex_sql_column('label', 'varchar(100)', false))
        ->alter();

    // New column-length in version 1.1
    rex_sql_table::get(rex::getTable('structure_tweaks'))
        ->ensureColumn(new rex_sql_column('type', 'varchar(100)', false))
        ->alter();
}
