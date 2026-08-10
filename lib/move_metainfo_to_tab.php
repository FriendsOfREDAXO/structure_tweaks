<?php
/**
 * @author Friends of REDAXO
 */

namespace FriendsOfREDAXO\StructureTweaks;

use Dom\HTMLDocument;
use rex;
use rex_addon;
use rex_article;
use rex_article_service;
use rex_be_controller;
use rex_clang;
use rex_context;
use rex_extension;
use rex_extension_point;
use rex_formatter;
use rex_api_article_status;
use rex_api_category_status;
use rex_be_page;
use rex_fragment;
use rex_i18n;
use rex_metainfo_article_handler;
use rex_plugin;
use rex_request;
use rex_url;
use rex_sql;
use rex_view;

class structure_tweaks_move_metainfo_to_tab extends structure_tweaks_base
{
    /**
     * Move meta page
     */
    public static function init(): void
    {
        if (rex_addon::get('metainfo')->isAvailable() && rex_plugin::get('structure', 'content')->isAvailable()) {
            // Remove meta info from sidebar
            rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', [__CLASS__, 'removeMetaPage']);

            rex_extension::register('PAGES_PREPARED', function () {
                $page = new rex_be_page('metainfo', rex_i18n::msg('metadata'));
                $page->setSubPath(rex_addon::get('structure_tweaks')->getPath('pages/content.metainfo.php'));
                $page_controller = rex_be_controller::getPageObject('content');
                if ($page_controller !== null) {
                    $page_controller->addSubpage($page);
                }
            });
        }
    }

    /**
     * @param rex_extension_point<mixed> $ep
     */
    public static function removeMetaPage(rex_extension_point $ep): string
    {
        $subject = $ep->getSubject();

        $wrappedSubject = '<!doctype html><html><body>'.$subject.'</body></html>';
        /** @phpstan-ignore-next-line PHP 8.4 DOM API is available at runtime */
        $document = @HTMLDocument::createFromString($wrappedSubject);
        if (!is_object($document)) {
            return $subject;
        }
        /** @phpstan-ignore-next-line PHP 8.4 DOM API is available at runtime */
        $metadata = $document->getElementById('rex-page-sidebar-metainfo');
        if ($metadata) {
            $metadata->parentNode->removeChild($metadata);

            // @see https://stackoverflow.com/questions/9924261/removing-doctype-while-saving-domdocument
            /** @phpstan-ignore-next-line PHP 8.4 DOM API is available at runtime */
            $subject = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $document->saveHtml());
        }

        return $subject;
    }

    /**
     * Substituted metadata panel
     * @see redaxo/src/addons/structure/plugins/content/boot.php
     * @return string
     */
    protected static function getStructure(): string
    {
        $article_id = self::getArticleId();
        $clang_id = self::getClangId();
        $article = rex_article::get($article_id, $clang_id);
        if (!$article instanceof rex_article) {
            return '';
        }
        $article_status = self::getArticleStatus($article_id, $clang_id);

        return '
            <dl class="dl-horizontal text-left structure-tweaks-metainfos">
                <dt>'.rex_i18n::msg('created_by').'</dt><dd>'.$article->getValue('createuser').'</dd>
                <dt>'.rex_i18n::msg('created_on').'</dt><dd>'.rex_formatter::strftime($article->getValue('createdate'), 'date').'</dd>
                <dt>'.rex_i18n::msg('updated_by').'</dt><dd>'.$article->getValue('updateuser').'</dd>
                <dt>'.rex_i18n::msg('updated_on').'</dt><dd>'.rex_formatter::strftime($article->getValue('updatedate'), 'date').'</dd>
                <dt>'.rex_i18n::msg('status').'</dt><dd class="structure-tweaks-status">'.$article_status.'</dd>
            </dl>
        ';
    }

    /**
     * Make article status switchable
     * @see redaxo/src/addons/structure/pages/index.php
     * @param int $article_id
     * @param int $clang_id
     * @return string
     */
    protected static function getArticleStatus(int $article_id, int $clang_id): string
    {
        $article = rex_article::get($article_id, $clang_id);
        if (!$article instanceof rex_article) {
            return '';
        }
        $artstart = rex_request('artstart', 'int');
        $catstart = rex_request('catstart', 'int');

        $user = rex::requireUser();
        $perm = $user->getComplexPerm('structure')->hasCategoryPerm($article_id);

        $context = new rex_context([
            'page' => 'content/edit',
            'category_id' => $article->getCategoryId(),
            'article_id' => $article_id,
            'clang' => $clang_id,
        ]);

        /** @var array<int, array{0: string, 1: string, 2: string}> $article_status_types */
        $article_status_types = rex_article_service::statusTypes();
        $status = (int) $article->getValue('status');
        $article_status = $article_status_types[$status][0] ?? '';
        $article_class = $article_status_types[$status][1] ?? '';
        $article_icon = $article_status_types[$status][2] ?? '';

        if (version_compare(rex::getVersion(), '5.5.0', '<')) {
            if ($article->isStartArticle()) {
                $article_link = $context->getUrl([
                    'rex-api-call' => 'category_status',
                    'catstart' => $catstart,
                    'category-id' => $article->getCategoryId(),
                ]);
            } else {
                $article_link = $context->getUrl([
                    'rex-api-call' => 'article_status',
                    'artstart' => $artstart
                ]);
            }
        } else {
            if ($article->isStartArticle()) {
                $article_link = $context->getUrl([
                    'catstart' => $catstart,
                    'category-id' => $article->getCategoryId(),
                ] + rex_api_category_status::getUrlParams());
            } else {
                $article_link = $context->getUrl([
                    'artstart' => $artstart
                ] + rex_api_article_status::getUrlParams());
            }
        }

        if ($perm && $user->hasPerm('publishArticle[]')) {
            $return = '<a class="'.$article_class.'" href="'.$article_link.'"><i class="rex-icon '.$article_icon.'"></i> '.$article_status.'</a>';
        } else {
            $return = '<span class="'.$article_class.' text-muted"><i class="rex-icon '.$article_icon.'"></i> '.$article_status.'</span>';
        }

        return $return;
    }

    /**
     * @return int
     */
    protected static function getArticleId(): int
    {
        $article_id = rex_request('article_id', 'int');
        $article_id = rex_article::get($article_id) ? $article_id : 0;

        return $article_id;
    }

    /**
     * @return int
     */
    protected static function getClangId(): int
    {
        $clang_id = rex_request('clang', 'int');
        $clang_id = rex_clang::exists($clang_id) ? $clang_id : rex_clang::getStartId();

        return $clang_id;
    }
}
