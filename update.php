<?php
/**
 * @author Friends of REDAXO
 */

// Update wird in einer temporären Update-Umgebung ausgeführt, deshalb immer __DIR__ nutzen.
rex_autoload::addDirectory(__DIR__ . '/lib');

foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/lib', FilesystemIterator::SKIP_DOTS)) as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    require_once $file->getPathname();
}

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
