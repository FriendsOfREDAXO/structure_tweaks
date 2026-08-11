<?php
/**
 * @var rex_addon $this
 */

use FriendsOfREDAXO\StructureTweaks\structure_tweaks_page_settings;

?>
<?= structure_tweaks_page_settings::getFormPost(); ?>

<form action="<?=rex_url::currentBackendPage();?>" method="post">
    <?= structure_tweaks_page_settings::getForm(); ?>
</form>
