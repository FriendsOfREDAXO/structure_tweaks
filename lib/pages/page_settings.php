<?php
/**
 * Settings
 */

class structure_tweaks_page_settings extends structure_tweaks_base
{
    /**
     * @return string
     */
    public static function getFormPost()
    {
        $addon = self::addon();

        $message = '';

        // Process form data
        if (rex_post('submit', 'boolean')) {
            $addon->setConfig(rex_post('config', [
              ['move_meta_info_page', 'bool'],
              ['show_lastmodified_categories', 'bool'],
              ['show_lastmodified_articles', 'bool'],
              ['userwidth', 'string'],
              ['datewidth', 'string'],
              ['format', 'string'],
            ]));

            $message = rex_view::success($addon->i18n('saved'));
        }

        return $message;
    }

    /**
     * @return string
     */
    public static function getForm()
    {
        $addon = self::addon();

        // Checkboxes
        $n = [];
        $n['label'] = '<label for="structure-tweaks-move-meta-info">'.$addon->i18n('move_meta_info_page').'</label>';
        $n['field'] = '<input type="checkbox" id="structure-tweaks-move-meta-info" name="config[move_meta_info_page]" value="1" '.($addon->getConfig('move_meta_info_page') ? ' checked="checked"' : '').' />';
        $formElements[] = $n;
        //End - code
        
        $n = [];
        $n['label'] = '<h3>'.$addon->i18n('lastmodified_info_page').'</h3>';
        $n['field'] = '';
        $formElements[] = $n;
        
        // Show lastmodified
        $n = [];
        $n['label'] = '<label for="structure-tweaks-show-lastmodified-categories">'.$addon->i18n('show_lastmodified_categories').'</label>';
        $n['field'] = '<input type="checkbox" id="structure-tweaks-show-lastmodified-categories" name="config[show_lastmodified_categories]" value="1" '.($addon->getConfig('show_lastmodified_categories') ? ' checked="checked"' : '').'>';
        $formElements[] = $n;
        //End - lastmodified
        
        // Show lastmodified
        $n = [];
        $n['label'] = '<label for="structure-tweaks-show-lastmodified-articles">'.$addon->i18n('show_lastmodified_articles').'</label>';
        $n['field'] = '<input type="checkbox" id="structure-tweaks-show-lastmodified-articles" name="config[show_lastmodified_articles]" value="1" '.($addon->getConfig('show_lastmodified_articles') ? ' checked="checked"' : '').'>';
        $formElements[] = $n;
        //End - lastmodified
        
        // Userwith
        $n = [];
        $userwidth = $addon->getConfig('userwidth');
        if ($userwidth == '') $userwidth = "100px";
        
        $n['label'] = '<label for="structure-tweaks-userwidth">'.$addon->i18n('userwidth').'</label>';
        $n['field'] = '<input type="text" id="structure-tweaks-userwidth" name="config[userwidth]" value="'.$userwidth.'">';
        $formElements[] = $n;
        //End - Userwith
        
        // datewidth
        $datewidth = $addon->getConfig('datewidth');
        if ($datewidth == '') $datewidth = "130px";
        
        $n = [];
        $n['label'] = '<label for="structure-tweaks-datewidth">'.$addon->i18n('datewidth').'</label>';
        $n['field'] = '<input type="text" id="structure-tweaks-datewidth" name="config[datewidth]" value="'.$datewidth.'">';
        $formElements[] = $n;
        //End - datewidth
        
        // format
        $format = $addon->getConfig('format');
        if ($format == '') $format = "d.m.y - H:i";
        $n = [];
        $n['label'] = '<label for="structure-tweaks-format">'.$addon->i18n('format').'</label>';
        $n['field'] = '<input type="text" id="structure-tweaks-format" name="config[format]" value="'.$format.'">';
        $formElements[] = $n;
        //End - format        
        
        $fragment = new rex_fragment();
        $fragment->setVar('elements', $formElements, false);
        $content .= $fragment->parse('core/form/form.php');

        // Submit
        $submit_elements = [];
        $submit_elements[] = [
          'field' => '<button class="btn btn-save rex-form-aligned" type="submit" name="submit" value="1" '.rex::getAccesskey($addon->i18n('submit'), 'save').'>'.$addon->i18n('save').'</button>',
        ];
        $fragment = new rex_fragment();
        $fragment->setVar('flush', true);
        $fragment->setVar('elements', $submit_elements, false);
        $submit = $fragment->parse('core/form/submit.php');
        
        // Form
        $fragment = new rex_fragment();
        $fragment->setVar('class', 'edit');
        $fragment->setVar('title', $addon->i18n('page_settings'));
        $fragment->setVar('body', $content, false);
        $fragment->setVar('buttons', $submit, false);
        $form = $fragment->parse('core/page/section.php');

        return $form;
    }
}
