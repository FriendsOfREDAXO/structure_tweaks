CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%structure_tweaks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) NOT NULL,
  `type` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;
