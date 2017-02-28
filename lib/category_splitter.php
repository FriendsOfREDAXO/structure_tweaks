<?php
/**
 * @author Rexdude, Friends of REDAXO
 */

class structure_tweaks_category_splitter extends structure_tweaks_base
{
    /**
     * Split categories
     */
    public static function init()
    {
        rex_extension::register('PACKAGES_INCLUDED', function () {
            if (rex_addon::get('structure')->isAvailable() && rex_request('page', 'string') == 'structure') {
                $categories = self::getSplitterCategories();

                if (count($categories)) {
                    rex_extension::register('OUTPUT_FILTER', [__CLASS__, 'ep'], rex_extension::NORMAL, [
                        'categories' => $categories,
                    ]);
                }
            }
        });
    }

    /**
     * @return array
     */
    public static function getSplitterCategories()
    {
        return self::getArticles('split_category');
    }

    /**
     * EP CALLBACK
     * @param rex_extension_point $ep
     * @return string
     */
    public static function ep(rex_extension_point $ep)
    {
        $subject = $ep->getSubject();
        $categories = $ep->getParam('categories');

        foreach ($categories as $category) {
            $link_pos = strpos($subject, 'index.php?page=structure&amp;category_id='.$category.'&amp;article_id=0&amp;clang='.rex_clang::getCurrentId());

            if ($link_pos !== false) {
                // Find start of table row
                $tr_pos = strrpos(substr($subject, 0, $link_pos), '<tr>');
                // Split row
                $code_before = substr($subject, 0, $tr_pos);
                $code_after = substr($subject, $tr_pos, strlen($subject));

                $splitter = '
                        </tbody>
                    </table>
                </div>
                <div class="panel panel-default">
                    <table class="table table-striped table-hover">
                        <tbody>
                ';

                $subject = $code_before.$splitter.$code_after;
            }
        }

        return $subject;
    }
}
