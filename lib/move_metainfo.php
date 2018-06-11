<?php
/**
 * @author Friends of REDAXO
 */

class structure_tweaks_move_metainfo extends structure_tweaks_base
{
    /**
     * Move meta page
     */
    public static function init()
    {
        rex_extension::register('PACKAGES_INCLUDED', function () {
            if (rex_addon::get('metainfo')->isAvailable()) {
                // Remove meta info tab
                rex_addon::get('metainfo')->removeProperty('pages');

                // Metainfo js
                if (rex_be_controller::getCurrentPagePart(1) == 'content') {
                    rex_view::addJsFile(rex_url::addonAssets('metainfo', 'metainfo.js'));
                }

                // Redirect meta info into sidebar
                rex_extension::register('STRUCTURE_CONTENT_SIDEBAR', [__CLASS__, 'getMetaPage']);
            }
        });
    }

    /**
     * EP CALLBACK
     * @param rex_extension_point $ep
     * @return string
     */
    public static function getMetaPage(rex_extension_point $ep)
    {
        $params = $ep->getParams();
        $subject = $ep->getSubject();

        $panel = self::getStructure().self::getMetaInfo();

        $fragment = new rex_fragment();
        $fragment->setVar('title', '<i class="rex-icon rex-icon-info"></i> '.self::msg('metadata'), false);
        $fragment->setVar('body', $panel, false);
        $fragment->setVar('article_id', $params['article_id'], false);
        $fragment->setVar('clang', $params['clang'], false);
        $fragment->setVar('ctype', $params['ctype'], false);
        $fragment->setVar('collapse', true);
        $fragment->setVar('collapsed', false);
        $content = $fragment->parse('core/page/section.php');

        return $content.$subject;
    }

    /**
     * Substituted metadata panel
     * @see redaxo/src/addons/metainfo/extensions/extension_content_sidebar.php
     * @return string
     */
    protected static function getStructure()
    {
        $article_id = self::getArticleId();
        $clang_id = self::getClangId();
        $article = rex_article::get($article_id, $clang_id);
        $article_status = self::getArticleStatus($article_id, $clang_id);
        $article_template = self::getArticleTemplate($article_id, $clang_id);

        return '
            <dl class="dl-horizontal text-left structure-tweaks-metainfos">
                <dt>'.rex_i18n::msg('created_by').'</dt><dd>'.$article->getValue('createuser').'</dd>
                <dt>'.rex_i18n::msg('created_on').'</dt><dd>'.rex_formatter::strftime($article->getValue('createdate'), 'date').'</dd>
                <dt>'.rex_i18n::msg('updated_by').'</dt><dd>'.$article->getValue('updateuser').'</dd>
                <dt>'.rex_i18n::msg('updated_on').'</dt><dd>'.rex_formatter::strftime($article->getValue('updatedate'), 'date').'</dd>
                <dt>'.rex_i18n::msg('status').'</dt><dd class="structure-tweaks-status">'.$article_status.'</dd>
                <dt>'.rex_i18n::msg('template').'</dt><dd class="structure-tweaks-template">'.$article_template.'</dd>
            </dl>
        ';
    }

    /**
     * @return string
     */
    protected static function getMetaInfo()
    {
        $return = '';

        /**
         * @see redaxo/src/addons/structure/plugins/content/pages/content.php
         */
        $article_id = self::getArticleId();
        $clang = self::getClangId();

        $article = rex_sql::factory();
        $article->setQuery('
            SELECT
                article.*, template.attributes as template_attributes
            FROM
                '.rex::getTablePrefix().'article as article
            LEFT JOIN '.rex::getTablePrefix()."template as template
                ON template.id=article.template_id
            WHERE
                article.id='$article_id'
                AND clang_id=$clang"
        );

        if ($article->getRows() == 1) {
            // ----- ctype holen
            $template_attributes = $article->getArrayValue('template_attributes');

            // Für Artikel ohne Template
            if (!is_array($template_attributes)) {
                $template_attributes = [];
            }

            $ctypes = isset($template_attributes['ctype']) ? $template_attributes['ctype'] : []; // ctypes - aus dem template

            $ctype = rex_request('ctype', 'int', 1);
            if (!array_key_exists($ctype, $ctypes)) {
                $ctype = 1;
            } // default = 1

            $context = new rex_context([
                'page' => rex_be_controller::getCurrentPage(),
                'article_id' => $article_id,
                'clang' => $clang,
                'ctype' => $ctype,
            ]);

            /**
             * @see redaxo/src/addons/metainfo/pages/content.metainfo.php
             */
            $metainfoHandler = new rex_metainfo_article_handler();
            $form = $metainfoHandler->getForm([
                'id' => $article_id,
                'clang' => $clang,
                'article' => $article,
            ]);

            $formElements = [];
            $formElements[] = [
                'label' => '<label for="rex-id-meta-article-name">'.rex_i18n::msg('header_article_name').'</label>',
                'field' => '<input class="form-control" type="text" id="rex-id-meta-article-name" name="meta_article_name" value="'.htmlspecialchars(rex_article::get($article_id, $clang)->getValue('name')).'" />',
            ];
            $fragment = new rex_fragment();
            $fragment->setVar('elements', $formElements, false);
            $form = $fragment->parse('core/form/form.php').$form;

            $return = '
                <form class="moved-metainfo" action="'.$context->getUrl().'" method="post" enctype="multipart/form-data">
                    '.(rex_post('savemeta', 'boolean') ? rex_view::success(rex_i18n::msg('minfo_metadata_saved')) : '').'
                    <fieldset>
                        <legend>'.self::msg('metainfo_fields').'</legend>
                        <input type="hidden" name="save" value="1" />
                        <input type="hidden" name="ctype" value="'.$ctype.'" />
                        '.$form.'
                        <button class="btn btn-primary pull-right" type="submit" name="savemeta"'.rex::getAccesskey(rex_i18n::msg('update_metadata'), 'save').' value="1">'.rex_i18n::msg('update_metadata').'</button>
                    </fieldset>
                </form>
            ';
        }

        return $return;
    }


        /**
         * Make article template switchable
         * @see redaxo/src/addons/structure/plugins/content/pages/templates.php#L72
         * @param int $article_id
         * @param int $clang_id
         * @return string
         */
        protected static function getArticleTemplate($article_id, $clang_id)
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

            $article_template_id = $article->getTemplateId();

            if ($perm && rex::getUser()->hasPerm('publishArticle[]')) {

              $ooArt = rex_article::get($article_id, $clang);
              $data['category_id'] = $ooArt->getCategoryId();

              $legend = rex_i18n::msg('edit_template') . ' <small class="rex-primary-id">' . rex_i18n::msg('id') . ' = ' . $template_id . '</small>';

              if (rex_plugin::get('structure', 'content')->isAvailable()) {
                $templates = rex_template::getTemplatesForCategory($data['category_id']);

                $select = new rex_select();
                $select->setName('tweak_template');
                $select->setAttribute('class', 'form-control tweak_template');
                $select->setAttributes([]);
                foreach ($templates as $tid => $tname) {
                  $select->addOption($tname,$tid);
                }
                $select->setSelected($article_template_id);
                $return = $select->get();
              }
            } else {
                $return = '<span class="'.$article_class.' text-muted"><i class="rex-icon '.$article_icon.'"></i> '.$article_template.'</span>';
            }

            return $return;
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
