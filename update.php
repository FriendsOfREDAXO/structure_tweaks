<?php
/**
 * @var rex_addon $this
 */

// New column in beta version
if (rex_string::versionCompare($this->getVersion(), '1.0.0-beta', '<')) {
    rex_sql_table::get(rex::getTable('structure_tweaks'))
        ->ensureColumn(new rex_sql_column('label', 'varchar(100)', false))
        ->alter();
}
