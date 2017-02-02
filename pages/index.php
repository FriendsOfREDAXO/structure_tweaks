<?php
/**
 * @var rex_addon $this
 */

echo rex_view::title($this->i18n($this->getName()));

include rex_be_controller::getCurrentPageObject()->getSubPath();
