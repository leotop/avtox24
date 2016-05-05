CREATE TABLE IF NOT EXISTS `b_lm_analogs_simple` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `import_id` varchar(50)  NOT NULL DEFAULT 'import',
  `article_original` varchar(20)  NOT NULL,
  `brand_title_original` varchar(100)  NOT NULL,
  `article_analog` varchar(20)  NOT NULL,
  `brand_title_analog` varchar(100)  NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `group_original` varchar(10),
  `group_analog` varchar(10),
  PRIMARY KEY (`id`),
  UNIQUE KEY `articles` (`article_original`,`article_analog`,`brand_title_original`,`brand_title_analog`),
  INDEX `article_original` (`article_original`),
  INDEX `article_analog` (`article_analog`),
  INDEX `id` (`id`)
) ENGINE=InnoDB;

