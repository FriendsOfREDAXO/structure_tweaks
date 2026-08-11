<?php
/**
 * @author Rexdude, Friends of REDAXO
 */

namespace FriendsOfREDAXO\StructureTweaks;

use rex;
use rex_addon;
use rex_extension;
use rex_extension_point;
use rex_i18n;
use rex_request;
use rex_response;
use rex_sql;

class structure_tweaks_hide_startarticle extends structure_tweaks_base
{
    /**
     * Check page and category, hide if necessary
     */
    public static function init(): void
    {
        rex_extension::register('PACKAGES_INCLUDED', function () {
            $pages = ['structure', 'linkmap']; // Pages, where articles are shown
            $page = rex_request::request('page', 'string');

            if (rex_addon::get('structure')->isAvailable() && in_array($page, $pages, true)) {
                rex_extension::register('PAGE_HEADER', [__CLASS__, 'ep']);
            }
        });
    }

    /**
     * @return array<int, int>
     */
    protected static function getHiddenArticles(bool $non_admin = false): array
    {
        if ($non_admin) {
            $type = 'hide_startarticle_non_admin';
        } else {
            $type = 'hide_startarticle';
        }

        return self::getArticles($type);
    }

    /**
     * @param rex_extension_point<mixed> $ep
     */
    public static function ep(rex_extension_point $ep): string
    {
        $subject = $ep->getSubject();

        // Pass hidden articles to JavaScript
        $hidden_articles = self::getHiddenArticles();
        if ($hidden_articles !== []) {
            $subject .= self::getScript($hidden_articles);
        }

        // Pass hidden non-admin articles to JavaScript
        $hidden_articles = self::getHiddenArticles(true);
        $user = rex::requireUser();
        if ($hidden_articles !== [] && !$user->isAdmin()) {
            $subject .= self::getScript($hidden_articles);
        }

        return $subject;
    }

    /**
     * @param array<int, int> $hidden_articles
     */
    protected static function getScript(array $hidden_articles): string
    {
        $hiddenArticlesJson = json_encode($hidden_articles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        if (!is_string($hiddenArticlesJson)) {
            $hiddenArticlesJson = '[]';
        }

        return '
            <script nonce="' . rex_response::getNonce() . '">
                $(document).on("rex:ready", function() {
                    const structureTweaksHideArticles = new structureTweaks();
                    structureTweaksHideArticles.setHiddenArticles(\'' . $hiddenArticlesJson . '\').hideArticles();
                });
            </script>
        ';
    }
}
