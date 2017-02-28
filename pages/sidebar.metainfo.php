<?php
/*
 * see redaxo/src/addons/structure/plugins/content/pages/content.php
 */
$article_id = rex_request('article_id', 'int');
$clang = rex_request('clang', 'int');
$article_id = rex_article::get($article_id) ? $article_id : 0;
$clang = rex_clang::exists($clang) ? $clang : rex_clang::getStartId();

$article = rex_sql::factory();
$article->setQuery('
        SELECT
            article.*, template.attributes as template_attributes
        FROM
            ' . rex::getTablePrefix() . 'article as article
        LEFT JOIN ' . rex::getTablePrefix() . "template as template
            ON template.id=article.template_id
        WHERE
            article.id='$article_id'
            AND clang_id=$clang");

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


    /*
     * see redaxo/src/addons/metainfo/pages/content.metainfo.php
     */

    $panel = '';

    if (rex_post('savemeta', 'boolean')) {
        $panel .= rex_view::success(rex_i18n::msg('minfo_metadata_saved'));
    }

    $panel .= '<fieldset>
            <input type="hidden" name="save" value="1" />
            <input type="hidden" name="ctype" value="' . $ctype . '" />
            ';

    $metainfoHandler = new rex_metainfo_article_handler();
    $form = $metainfoHandler->getForm([
        'id' => $article_id,
        'clang' => $clang,
        'article' => $article,
    ]);

    $n = [];
    $n['label'] = '<label for="rex-id-meta-article-name">' . rex_i18n::msg('header_article_name') . '</label>';
    $n['field'] = '<input class="form-control" type="text" id="rex-id-meta-article-name" name="meta_article_name" value="' . htmlspecialchars(rex_article::get($article_id, $clang)->getValue('name')) . '" />';
    $formElements = [$n];

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $panel .= $fragment->parse('core/form/form.php');

    $panel .= $form . '<button class="btn btn-primary rex-form-aligned" type="submit" name="savemeta"' . rex::getAccesskey(rex_i18n::msg('update_metadata'), 'save') . ' value="1">' . rex_i18n::msg('update_metadata') . '</button></fieldset>';

    return '
    <form action="' . $context->getUrl() . '" method="post" enctype="multipart/form-data">
        ' . $panel . '
    </form>';
}