CREATE TABLE IF NOT EXISTS `b_lm_suppliers_requests` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `supplier_id` varchar(255) NOT NULL,
    `user_id` int(6) NOT NULL,
    `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `status` varchar(1) NOT NULL,
    `step` varchar(10) NOT NULL,
    `closed` varchar(1) NOT NULL DEFAULT 'N',
    `note` text NULL,
    PRIMARY KEY (`id`),
    KEY `supplier_id` (`supplier_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `b_lm_suppliers_requests_baskets` (
  `request_id` int(7) NOT NULL,
  `basket_id` int(11) NOT NULL,
  KEY `request_id` (`request_id`)
);

