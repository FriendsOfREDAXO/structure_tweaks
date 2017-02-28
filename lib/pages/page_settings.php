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
        $checkbox_elements = [];
        $checkbox_elements[] = [
            'label' => '<label for="structure-tweaks-move-meta-info">'.$addon->i18n('move_meta_info_page').'</label>',
            'field' => '<input type="checkbox" id="structure-tweaks-move-meta-info" name="config[move_meta_info_page]" value="1" '.($addon->getConfig('move_meta_info_page') ? ' checked="checked"' : '').' />',
        ];
        $fragment = new rex_fragment();
        $fragment->setVar('elements', $checkbox_elements, false);
        $checkboxes = $fragment->parse('core/form/checkbox.php');

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
        $fragment->setVar('body', $checkboxes, false);
        $fragment->setVar('buttons', $submit, false);
        $form = $fragment->parse('core/page/section.php');

        return $form;
    }
}
