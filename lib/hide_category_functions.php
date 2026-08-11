<?php
/**
 * @author Friends of REDAXO
 */

namespace FriendsOfREDAXO\StructureTweaks;

use rex;
use rex_addon;
use rex_extension;
use rex_extension_point;
use rex_plugin;
use rex_request;
use rex_response;
use rex_sql;

class structure_tweaks_hide_category_functions extends structure_tweaks_base
{
    /**
     * Check page and category, hide if necessary
     */
    public static function init(): void
    {
        rex_extension::register('PACKAGES_INCLUDED', function () {
            if (
                (rex_addon::get('structure')->isAvailable() && rex_request('page', 'string') == 'structure') ||
                (rex_plugin::get('structure', 'content')->isAvailable() && rex_request('page', 'string') == 'content/edit')
            ) {
                rex_extension::register('PAGE_HEADER', [__CLASS__, 'ep']);
            }
        });
    }

    /**
     * @return array<int, int>
     */
    protected static function getHiddenCategories(bool $non_admin = false): array
    {
        if ($non_admin) {
            $type = 'hide_cat_functions_non_admin';
        } else {
            $type = 'hide_cat_functions';
        }

        return self::getArticles($type);
    }

    /**
     * @return array<int, int>
     */
    protected static function getHiddenCategoriesAll(bool $non_admin = false): array
    {
        if ($non_admin) {
            $type = 'hide_cat_functions_all_non_admin';
        } else {
            $type = 'hide_cat_functions_all';
        }

        return self::getArticles($type);
    }

    /**
     * @param rex_extension_point<mixed> $ep
     */
    public static function ep(rex_extension_point $ep): string
    {
        $subject = $ep->getSubject();

        // Pass hidden categories to JavaScript
        $hidden_categories = self::getHiddenCategories();
        if ($hidden_categories !== []) {
            $subject .= self::getScript($hidden_categories);
        }

        // Pass hidden non-admin categories to JavaScript
        $hidden_categories = self::getHiddenCategories(true);
        $user = rex::requireUser();
        if ($hidden_categories !== [] && !$user->isAdmin()) {
            $subject .= self::getScript($hidden_categories);
        }

        // Pass hidden categories to JavaScript
        $hidden_categories = self::getHiddenCategoriesAll();
        if ($hidden_categories !== []) {
            $subject .= self::getScriptAll($hidden_categories);
        }

        // Pass hidden non-admin categories to JavaScript
        $hidden_categories = self::getHiddenCategoriesAll(true);
        $user = rex::requireUser();
        if ($hidden_categories !== [] && !$user->isAdmin()) {
            $subject .= self::getScriptAll($hidden_categories);
        }

        return $subject;
    }

    /**
     * @param array<int, int> $hidden_categories
     */
    protected static function getScript(array $hidden_categories): string
    {
        return '
            <script nonce="' . rex_response::getNonce() . '">
                $(document).on("rex:ready", function() {
                    const structureTweaksHideCategories = new structureTweaks();
                    structureTweaksHideCategories.setHiddenCategories(\'' . json_encode($hidden_categories) . '\').hideCategoryFunctions(false);
                });
            </script>
        ';
    }

    /**
     * @param array<int, int> $hidden_categories
     */
    protected static function getScriptAll(array $hidden_categories): string
    {
        return '
            <script nonce="' . rex_response::getNonce() . '">
                $(document).on("rex:ready", function() {
                   const structureTweaksHideCategoriesAll = new structureTweaks();
                    structureTweaksHideCategoriesAll.setHiddenCategories(\'' . json_encode($hidden_categories) . '\').hideCategoryFunctionsAll(false);
                });
            </script>
        ';
    }
}
