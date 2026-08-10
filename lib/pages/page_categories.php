<?php

namespace FriendsOfREDAXO\StructureTweaks;

use rex;
use rex_article;
use rex_category_select;
use rex_form;
use rex_fragment;
use rex_list;
use rex_request;
use rex_view;
use rex_sql;

class structure_tweaks_page_categories extends structure_tweaks_base
{
    /**
     * @return string
     */
    public static function getPage(): string
    {
        $return = '';

        if (self::getFunc() == 'delete' && self::getArticleId() > 0) {
            $return .= self::delete();
        }

        $fragment = new rex_fragment();
        $fragment->setVar('title', self::msg('page_settings'));
        if (in_array(self::getFunc(), ['add', 'edit'])) {
            $fragment->setVar('class', 'edit', false);
            $fragment->setVar('body', self::getForm(), false);
        } else {
            $fragment->setVar('content', self::getTable(), false);
        }
        $return .= $fragment->parse('core/page/section.php');

        return $return;
    }

    /**
     * @return string
     */
    protected static function delete(): string
    {
        $sql = rex_sql::factory();
        #$sql->setDebug(true);
        $sql->setTable(rex::getTable(self::name()));
        $sql->setWhere('id='.self::getArticleId().' LIMIT 1');

        /** @phpstan-ignore-next-line delete() returns a rex_sql control object in REDAXO */
        if ($sql->delete()) {
            $message = rex_view::info(self::msg('deleted'));
        } else {
            $message = rex_view::warning((string) $sql->getError());
        }

        return $message;
    }

    /**
     * @return string
     */
    protected static function getTable(): string
    {
        $list = rex_list::factory('SELECT * FROM '.rex::getTable(self::name()).' ORDER BY id');

        $head = '<a href="'.$list->getUrl(['func' => 'add']).'"><i class="rex-icon rex-icon-add"></i></a>';
        $body = '<i class="rex-icon rex-icon-table"></i>';

        $list->setNoRowsMessage(self::msg('list_rowmsg'));

        // Icon
        $list->addColumn($head, $body, 0, [
                '<th class="rex-table-icon">###VALUE###</th>',
                '<td class="rex-table-icon">###VALUE###</td>'
        ]);
        $list->setColumnParams($head, ['func' => 'edit', 'article_id' => '###id###']);

        // Name & ID
        $list->addColumn(self::msg('article_name'), '', 2, array('<th>###VALUE###</th>','<td>###VALUE###</td>'));
        $list->setColumnFormat(self::msg('article_name'), 'custom', __CLASS__.'::getArticleName');
        $list->setColumnLabel('article_id', self::msg('article_id'));

        // Type
        $list->setColumnFormat('type', 'custom', __CLASS__.'::getType');
        $list->setColumnLabel('type', self::msg('type'));

        // Label
        $list->setColumnLabel('label', self::msg('splitter_label'));

        // Functions
        $list->addColumn(self::msg('functions'), self::msg('edit'), -1, [
            '<th colspan="2">###VALUE###</th>',
            '<td>###VALUE###</td>'
        ]);
        $list->setColumnParams(self::msg('functions'), ['func' => 'edit', 'article_id' => '###id###']);
        $list->addColumn('delete_column', self::msg('delete'), -1, [
            '',
            '<td>###VALUE###</td>'
        ]);
        $list->setColumnParams('delete_column', ['func' => 'delete', 'article_id' => '###id###']);
        $list->addLinkAttribute('delete_column', 'onclick', 'return confirm(\''.self::msg('delete').' ?\')');

        return $list->get();
    }

    /**
     * @return string
     */
    protected static function getForm(): string
    {
        $article_id = self::getArticleId();

        $form = rex_form::factory(rex::getTable(self::name()), self::msg('select'), 'id='.$article_id);

        if (self::getFunc() == 'edit') {
            $form->addParam('article_id', $article_id);
        }
        $form->addErrorMessage(\REX_FORM_ERROR_VIOLATE_UNIQUE_KEY, self::msg('article_exists'));

        $field = $form->addSelectField('article_id');
        $field->setLabel(self::msg('article_linkmap'));
        $field->setAttribute('class', 'form-control selectpicker show-menu-arrow');
        $field->setAttribute('data-live-search', 'true');
        $field->setAttribute('data-size', '15');
        $categorySelect = new rex_category_select(false, false, true, true);
        $field->setSelect($categorySelect);

        $field = $form->addSelectField('type');
        $field->setLabel(self::msg('type'));
        $field->setAttribute('class', 'form-control');
        $select = $field->getSelect();
        $select->setSize(1);
        $select->addOption('---', '');
        $select->addOption(self::msg('hide_startarticle'), 'hide_startarticle');
        $select->addOption(self::msg('hide_startarticle_non_admin'), 'hide_startarticle_non_admin');
        $select->addOption(self::msg('hide_category_functions'), 'hide_cat_functions');
        $select->addOption(self::msg('hide_category_functions_non_admin'), 'hide_cat_functions_non_admin');
        $select->addOption(self::msg('hide_category_functions_all'), 'hide_cat_functions_all');
        $select->addOption(self::msg('hide_category_functions_all_non_admin'), 'hide_cat_functions_all_non_admin');
        $select->addOption(self::msg('hide_categories'), 'hide_categories');
        $select->addOption(self::msg('hide_categories_non_admin'), 'hide_categories_non_admin');
        $select->addOption(self::msg('split_category'), 'split_category');

        $field = $form->addTextField('label');
        $field->setLabel(self::msg('splitter_label'));

        return $form->get();
    }

    /**
     * @return string
     */
    protected static function getFunc(): string
    {
        return \rex_request::request('func', 'string');
    }

    /**
     * @return int
     */
    protected static function getArticleId(): int
    {
        return \rex_request::request('article_id', 'int');
    }

    /**
     * @param array{list: rex_list} $p
     */
    public static function getArticleName(array $p): string
    {
        /** @var rex_list $list */
        $list = $p["list"];

        $article = rex_article::get((int) $list->getValue('article_id'));

        if (!$article instanceof rex_article) {
            return self::msg('article_not_found');
        }

        return $article->getName();
    }

    /**
     * @param array{list: rex_list} $p
     */
    public static function getType(array $p): string
    {
        /** @var rex_list $list */
        $list = $p["list"];
        $type = (string) $list->getValue('type');

        $text = [
            '' => '---',
            'hide_startarticle' => self::msg('hide_startarticle'),
            'hide_startarticle_non_admin' => self::msg('hide_startarticle_non_admin'),
            'hide_cat_functions' => self::msg('hide_category_functions'),
            'hide_cat_functions_non_admin' => self::msg('hide_category_functions_non_admin'),
            'hide_cat_functions_all' => self::msg('hide_category_functions_all'),
            'hide_cat_functions_all_non_admin' => self::msg('hide_category_functions_all_non_admin'),
            'hide_categories' => self::msg('hide_categories'),
            'hide_categories_non_admin' => self::msg('hide_categories_non_admin'),
            'split_category' => self::msg('split_category'),
        ];

        return $text[$type] ?? '---';
    }
}
