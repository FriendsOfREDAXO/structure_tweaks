<?php
/**
 * @author Friends of REDAXO
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

    // Update engine and character set
    rex_sql::factory()
        ->setQuery('ALTER TABLE `'.rex::getTable('structure_tweaks').'` ENGINE = InnoDB;')
        ->setQuery('ALTER TABLE `'.rex::getTable('structure_tweaks').'` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
}
