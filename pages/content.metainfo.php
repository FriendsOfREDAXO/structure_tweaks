<?php

$articleId = rex_request('article_id', 'int');
$categoryId = rex_request('category_id', 'int');
$clang = rex_request('clang', 'int');
$ctype = rex_request('ctype', 'int');
$article = rex_sql::factory();
$article->setQuery('
    SELECT article.*, template.attributes as template_attributes
    FROM ' . rex::getTablePrefix() . 'article as article
    LEFT JOIN ' . rex::getTablePrefix() . 'template as template ON template.id = article.template_id
    WHERE article.id = ? AND clang_id = ?', [
        $articleId,
        $clang
]);

$context = new rex_context([
    'page' => rex_be_controller::getCurrentPage(),
    'article_id' => $articleId,
    'category_id' => $categoryId,
    'clang' => $clang,
    'ctype' => $ctype,
]);

$content = '';

if (rex_post('savemeta', 'boolean')) {
    $content = rex_view::success(rex_i18n::msg('minfo_metadata_saved'));
}

$panel = '<fieldset>
            <input type="hidden" name="save" value="1" />
            <input type="hidden" name="ctype" value="' . $ctype . '" />
            ';

$metainfoHandler = new rex_metainfo_article_handler();
$form = $metainfoHandler->getForm([
    'id' => $articleId,
    'clang' => $clang,
    'article' => $article,
]);

$n = [];
$n['label'] = '<label for="rex-id-meta-article-name">' . rex_i18n::msg('header_article_name') . '</label>';
$n['field'] = '<input class="form-control" type="text" id="rex-id-meta-article-name" name="meta_article_name" value="' . rex_escape(rex_article::get($articleId, $clang)->getValue('name')) . '" />';
$formElements = [$n];

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$panel .= $fragment->parse('core/form/form.php');

$panel .= $form . '</fieldset>';

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="savemeta"' . rex::getAccesskey(rex_i18n::msg('update_metadata'), 'save') . ' value="1">' . rex_i18n::msg('update_metadata') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('general'), false);
$fragment->setVar('body', $panel, false);
$fragment->setVar('buttons', $buttons, false);
$content .= $fragment->parse('core/page/section.php');

return '
    <form action="' . $context->getUrl() . '" method="post" enctype="multipart/form-data">
        ' . $content . '
    </form>';
