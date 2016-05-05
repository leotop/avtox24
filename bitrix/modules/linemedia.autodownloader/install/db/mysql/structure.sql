
CREATE TABLE IF NOT EXISTS `b_lm_downloader_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `title` varchar(255) NOT NULL,
  `supplier_id` varchar(255) NOT NULL,
  `protocol` varchar(25) NOT NULL,
  `connection` text NOT NULL,
  `conversion` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `b_lm_downloader_tasks_shedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `interval` int(11) DEFAULT NULL,
  `days` varchar(15) DEFAULT NULL,
  `start_day` int(2) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `last_exec` timestamp NULL DEFAULT NULL,
  `force_run_now` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`)
) ENGINE=InnoDB;
