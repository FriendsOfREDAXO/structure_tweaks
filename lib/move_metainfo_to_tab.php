<?php
/**
 * @author Friends of REDAXO
 */

class structure_tweaks_move_metainfo_to_tab extends structure_tweaks_base
{
    /**
     * Move meta page
     */
    public static function init()
    {
        if (rex_addon::get('metainfo')->isAvailable() && rex_plugin::get('structure', 'content')->isAvailable()) {
            // Remove meta info from sidebar
            rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', [__CLASS__, 'removeMetaPage']);

            rex_extension::register('PAGES_PREPARED', function () {
                $page = new rex_be_page('metainfo', rex_i18n::msg('metadata'));
                $page->setSubPath(rex_addon::get('structure_tweaks')->getPath('pages/content.metainfo.php'));
                $page_controller = rex_be_controller::getPageObject('content');
                $page_controller->addSubpage($page);
            });
        }
    }

    /**
     * EP CALLBACK
     * @param rex_extension_point $ep
     * @return string
     */
    public static function removeMetaPage(rex_extension_point $ep)
    {
        $subject = $ep->getSubject();

        libxml_use_internal_errors(true); // Disable HTML parsing warnings @see https://stackoverflow.com/questions/9149180/domdocumentloadhtml-error
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->loadHTML($subject);
        $metadata = $dom->getElementById('rex-page-sidebar-metainfo');
        if ($metadata) {
            $metadata->parentNode->removeChild($metadata);
            libxml_use_internal_errors(false); // Enable HTML parsing warnings

            // @see https://stackoverflow.com/questions/9924261/removing-doctype-while-saving-domdocument
            $subject = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML());
        }

        return $subject;
    }

    /**
     * Substituted metadata panel
     * @see redaxo/src/addons/structure/plugins/content/boot.php
     * @return string
     */
    protected static function getStructure()
    {
        $article_id = self::getArticleId();
        $clang_id = self::getClangId();
        $article = rex_article::get($article_id, $clang_id);
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
    protected static function getArticleStatus($article_id, $clang_id)
    {
        $article = rex_article::get($article_id, $clang_id);
        $artstart = rex_request('artstart', 'int');
        $catstart = rex_request('catstart', 'int');

        $perm = rex::getUser()->getComplexPerm('structure')->hasCategoryPerm($article_id);

        $context = new rex_context([
            'page' => 'content/edit',
            'category_id' => $article->getCategoryId(),
            'article_id' => $article_id,
            'clang' => $clang_id,
        ]);

        $article_status_types = rex_article_service::statusTypes();
        $article_status = $article_status_types[$article->getValue('status')][0];
        $article_class = $article_status_types[$article->getValue('status')][1];
        $article_icon = $article_status_types[$article->getValue('status')][2];

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

        if ($perm && rex::getUser()->hasPerm('publishArticle[]')) {
            $return = '<a class="'.$article_class.'" href="'.$article_link.'"><i class="rex-icon '.$article_icon.'"></i> '.$article_status.'</a>';
        } else {
            $return = '<span class="'.$article_class.' text-muted"><i class="rex-icon '.$article_icon.'"></i> '.$article_status.'</span>';
        }

        return $return;
    }

    /**
     * @return int
     */
    protected static function getArticleId()
    {
        $article_id = rex_request('article_id', 'int');
        $article_id = rex_article::get($article_id) ? $article_id : 0;

        return $article_id;
    }

    /**
     * @return int
     */
    protected static function getClangId()
    {
        $clang_id = rex_request('clang', 'int');
        $clang_id = rex_clang::exists($clang_id) ? $clang_id : rex_clang::getStartId();

        return $clang_id;
    }
}
