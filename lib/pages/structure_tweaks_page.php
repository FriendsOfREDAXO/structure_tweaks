<?php

class structure_tweaks_page extends structure_tweaks_base
{
    /**
     * @return string
     */
    public static function getPage()
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
    protected static function delete()
    {
        $sql = rex_sql::factory();
        #$sql->setDebug(true);
        $sql->setTable(rex::getTable(self::getName()));
        $sql->setWhere('id='.self::getArticleId().' LIMIT 1');

        if ($sql->delete()) {
            $message = rex_view::info(self::msg('deleted'));
        } else {
            $message = rex_view::warning($sql->getError());
        }

        return $message;
    }

    /**
     * @return string
     */
    protected static function getTable()
    {
        $list = rex_list::factory('SELECT * FROM '.rex::getTable(self::getName()).' ORDER BY id');

        $head = '<a href="'.$list->getUrl(['func' => 'add']).'"><i class="rex-icon rex-icon-add"></i></a>';
        $body = '<a href="'.$list->getUrl(['func' => 'edit']).'"><i class="rex-icon rex-icon-table"></i></a>';

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
    protected static function getForm()
    {
        $article_id = self::getArticleId();

        $form = rex_form::factory(rex::getTable(self::getName()), self::msg('select'), 'id='.$article_id);

        if (self::getFunc() == 'edit') {
            $form->addParam('article_id', $article_id);
        }
        $form->addErrorMessage(REX_FORM_ERROR_VIOLATE_UNIQUE_KEY, self::msg('article_exists'));

        $field = $form->addLinkmapField('article_id');
        $field->setLabel(self::msg('article_linkmap'));

        $field = $form->addSelectField('type');
        $field->setLabel(self::msg('type'));
        $select = $field->getSelect();
        $select->setSize(1);
        $select->addOption('---', '');
        $select->addOption(self::msg('hide_startarticle'), 'hide_startarticle');
        $select->addOption(self::msg('split_category'), 'split_category');

        return $form->get();
    }

    /**
     * @return string
     */
    protected static function getFunc()
    {
        return \rex_request::request('func', 'string');
    }

    /**
     * @return int
     */
    protected static function getArticleId()
    {
        return \rex_request::request('article_id', 'int');
    }

    /**
     * EP CALLBACK
     * @param array $p
     * @return string
     */
    public static function getArticleName($p)
    {
        /** @var rex_list $list */
        $list = $p["list"];

        $article = rex_article::get($list->getValue("article_id"));

        if ($article instanceof rex_article) {
            $return = $article->getName();
        } else {
            $return = self::msg('article_not_found');
        }

        return $return;
    }

    /**
     * EP CALLBACK
     * @param array $p
     * @return string
     */
    public static function getType($p)
    {
        /** @var rex_list $list */
        $list = $p["list"];
        $type = $list->getValue("type");

        $text = [
            '' => '---',
            'hide_startarticle' => self::msg('hide_startarticle'),
            'split_category' => self::msg('split_category'),
        ];

        return $text[$type];
    }
}
