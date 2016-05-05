CREATE TABLE IF NOT EXISTS `b_lm_tecdoc_api_modifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `set_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default' COMMENT ' -    ',
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '   (, , )',
  `source_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT ' ID  ',
  `parent_id` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'ID  ',
  `data` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'diff ',
  PRIMARY KEY (`id`),
  UNIQUE KEY `u_idx` (`set_id`,`type`,`source_id`,`parent_id`),
  KEY `set_id` (`set_id`)
) ENGINE=InnoDB;