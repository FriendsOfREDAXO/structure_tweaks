<?php
/**
 * @author Friends of REDAXO
 */

namespace FriendsOfREDAXO\StructureTweaks;

use Dom\HTMLDocument;
use Dom\Element;
use rex;
use rex_addon;
use rex_extension;
use rex_extension_point;
use rex_plugin;
use rex_request;
use rex_response;
use rex_sql;

class structure_tweaks_hide_categories extends structure_tweaks_base
{
    /**
     * Check page and category, hide if necessary
     */
    public static function init(): void
    {
        rex_extension::register('PACKAGES_INCLUDED', function () {
            if (rex_addon::get('structure')->isAvailable() &&
                (rex_request('page', 'string') == 'structure' || rex_request('page', 'string') == 'linkmap')
            ) {
                rex_extension::register('PAGE_HEADER', [__CLASS__, 'ep']);
            }
        });

        if (rex_plugin::get('structure', 'content')->isAvailable() && rex_request('page', 'string') == 'content/functions') {
            rex_extension::register('OUTPUT_FILTER', [__CLASS__, 'epFunctions']);
        }
    }

    /**
     * @return array<int, int>
     */
    protected static function getHiddenCategories(bool $non_admin = false): array
    {
        if ($non_admin) {
            $type = 'hide_categories_non_admin';
        } else {
            $type = 'hide_categories';
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
            if (rex_request('page', 'string') == 'structure') {
                $subject .= self::getScript($hidden_categories);
            }
            if (rex_request('page', 'string') == 'linkmap') {
                $subject .= self::getScriptInLinkmap($hidden_categories);
            }
        }

        // Pass hidden non-admin categories to JavaScript
        $hidden_categories = self::getHiddenCategories(true);
        $user = rex::requireUser();
        if ($hidden_categories !== [] && !$user->isAdmin()) {
            if (rex_request('page', 'string') == 'structure') {
                $subject .= self::getScript($hidden_categories);
            }
            if (rex_request('page', 'string') == 'linkmap') {
                $subject .= self::getScriptInLinkmap($hidden_categories);
            }
        }

        return $subject;
    }

    /**
     * @param rex_extension_point<mixed> $ep
     */
    public static function epFunctions(rex_extension_point $ep): string
    {
        $subject = $ep->getSubject();

        // Hidden categories
        $hidden_categories = self::getHiddenCategories();
        if ($hidden_categories !== []) {
            $subject = self::removeCategoryOptions($subject, 'category_id_new', $hidden_categories);
            $subject = self::removeCategoryOptions($subject, 'category_copy_id_new', $hidden_categories);
        }

        // Hidden non-admin categories
        $hidden_categories = self::getHiddenCategories(true);
        $user = rex::requireUser();
        if ($hidden_categories !== [] && !$user->isAdmin()) {
            $subject = self::removeCategoryOptions($subject, 'category_id_new', $hidden_categories);
            $subject = self::removeCategoryOptions($subject, 'category_copy_id_new', $hidden_categories);
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
                    const structureTweaksHideCategoryRows = new structureTweaks();
                    structureTweaksHideCategoryRows.setHiddenCategoryRows(\'' . json_encode($hidden_categories) . '\').hideCategories();
                });
            </script>
        ';
    }

    /**
     * @param array<int, int> $hidden_categories
     */
    protected static function getScriptInLinkmap(array $hidden_categories): string
    {
        return '
            <script nonce="' . rex_response::getNonce() . '">
                $(document).on("rex:ready", function() {
                    const structureTweaksHideCategoryRows = new structureTweaks();
                    structureTweaksHideCategoryRows.setHiddenCategoryRows(\'' . json_encode($hidden_categories) . '\').hideCategoriesInLinkmap();
                });
            </script>
        ';
    }

    /**
     * @param array<int, int> $hidden_categories
     */
    private static function removeCategoryOptions(string $subject, string $select_id, array $hidden_categories): string
    {
        /** @phpstan-ignore-next-line PHP 8.4 DOM API is available at runtime */
        $document = HTMLDocument::createFromString($subject);

        $element = $document->getElementById($select_id);
        if ($element) {
            foreach ($element->getElementsByTagName('option') as $option) {
                /** @phpstan-ignore-next-line PHP 8.4 DOM API is available at runtime */
                if (in_array($option->getAttribute('value'), $hidden_categories, true)) {
                    $element->removeChild($option);
                }
            }
        }

        return $document->saveHtml();
    }
}
