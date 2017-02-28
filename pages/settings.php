<?php
/**
 * @var rex_addon $this
 */

?>
<?= structure_tweaks_page_settings::getFormPost(); ?>

<form action="<?=rex_url::currentBackendPage();?>" method="post">
    <?= structure_tweaks_page_settings::getForm(); ?>
</form>
