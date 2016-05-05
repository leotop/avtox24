CREATE TABLE IF NOT EXISTS `b_lm_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `article` varchar(100) NOT NULL,
  `original_article` varchar(100) NOT NULL,
  `brand_title` varchar(100) NOT NULL,
  `price` float(8,2) DEFAULT NULL,
  `quantity` float(8,2) DEFAULT NULL,
  `group_id` varchar(50) DEFAULT NULL,
  `weight` float(8,2) DEFAULT NULL,
  `supplier_id` varchar(100) NOT NULL DEFAULT '0',
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `article` (`article`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS `b_lm_wordforms` (
  `brand_title` varchar(255) COLLATE utf8_bin NOT NULL,
  `group` varchar(100) COLLATE utf8_bin NOT NULL,
  UNIQUE KEY `brand_title` (`brand_title`),
  KEY `group` (`group`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `b_lm_api_modifications` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `set_id` varchar(50) NOT NULL DEFAULT 'default',
 `type` varchar(50) NOT NULL,
 `source_id` varchar(180) NOT NULL,
 `parent_id` varchar(50) NOT NULL,
 `data` text NOT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `unique_modification` (`set_id`,`type`,`source_id`,`parent_id`),
 KEY `set_id` (`set_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `b_lm_notepad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `article` varchar(50) NOT NULL,
  `brand_title` varchar(100) DEFAULT NULL,
  `extra` varchar(255) DEFAULT NULL,
  `auto` varchar(255) DEFAULT NULL,
  `auto_id` int(11) DEFAULT NULL,
  `quantity` int(4) DEFAULT NULL,
  `notes` text,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `b_lm_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `title` varchar(255) NOT NULL,
  `supplier_id` varchar(255) NOT NULL,
  `protocol` varchar(25) NOT NULL,
  `connection` text NOT NULL,
  `conversion` text NOT NULL,
  `mode` char NULL,
  `email` varchar(100) NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `b_lm_tasks_shedule` (
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

CREATE TABLE IF NOT EXISTS `b_lm_search_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article` varchar(50) NOT NULL,
  `brand_title` varchar(100) DEFAULT NULL,
  `supplier_id` varchar(50) DEFAULT NULL,
  `branch_id` int(12) DEFAULT NULL,
  `variants` int(12) DEFAULT NULL,
  `analogs` int(12) DEFAULT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `article` (`article`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `b_lm_price_lists_user_rights` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned DEFAULT NULL,
  `privilege` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accessible suppliers` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `b_lm_spares_user_rights` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned DEFAULT NULL,
  `privilege` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `accessible suppliers` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `b_lm_transactions` (
  `ID` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `ID_BITRIX_TRANSACTION` int(12) unsigned NOT NULL,
  `BASKET_ID` int(12) unsigned NOT NULL,
  `REFUSED_BY` int(11) DEFAULT NULL,
  `USER_IP` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TYPE` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `MODIFIED_DATE` datetime DEFAULT NULL,
  `MODIFIED_BY` int(11) DEFAULT NULL,
  `DELETED` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `b_lm_rights` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `GROUP_CODE` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ENTITY_TYPE` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `ENTITY_ID` int(11) NOT NULL,
  `TASK_ID` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB COMMENT='Доступы к объектам linemedia';

CREATE TABLE IF NOT EXISTS `b_lm_import_history` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `TASK_ID` int(11) NOT NULL,
  `SUPPLIER_ID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `PARTS_COUNT` int(11) NOT NULL,
  `SUM_PRICE` decimal(10,2) NOT NULL,
  `PARTS_DIFF` int(11) DEFAULT NULL,
  `SUM_DIFF` int(11) DEFAULT NULL,
  `DATE` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `b_lm_sale_basket_props` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `BASKET_ID` int(11) NOT NULL,
  `NAME` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `VALUE` text COLLATE utf8_unicode_ci DEFAULT NULL,
  `CODE` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `SORT` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`ID`),
  KEY `IXS_BASKET_PROPS_BASKET` (`BASKET_ID`),
  KEY `IXS_BASKET_PROPS_CODE` (`CODE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE OR REPLACE VIEW b_lm_orders_view AS
SELECT
  b.ID,
  b.FUSER_ID,
  b.ORDER_ID,
  b.PRODUCT_ID,
  b.PRODUCT_PRICE_ID,
  b.PRICE,
  b.CURRENCY,
  b.DATE_INSERT,
  b.DATE_UPDATE,
  b.WEIGHT,
  b.QUANTITY,
  b.LID,
  b.DELAY,
  b.NAME,
  b.CAN_BUY,
  (b.PRICE * b.QUANTITY)              AS AMOUNT,

  o.PERSON_TYPE_ID                    AS PERSON_TYPE,
  o.PAY_SYSTEM_ID                     AS PAYSYSTEM,
  o.USER_ID                           AS USER_ID,
  o.COMMENTS                          AS COMMENTS,
  o.USER_DESCRIPTION                  AS USER_DESCRIPTION,
  o.STATUS_ID                         AS STATUS_ID,
  o.PAYED                             AS ORDER_PAYED,
  o.CANCELED                          AS ORDER_CANCELED,
  o.DATE_INSERT                       AS ORDER_CREATED,

  bp_payed.VALUE                      AS PAYED,
  bp_canceled.VALUE                   AS CANCELED,
  bp_delivery_time.VALUE              AS DELIVERY_TIME,
  bp_status.VALUE                     AS STATUS,
  bp_article.VALUE                    AS ARTICLE,
  bp_original_article.VALUE           AS ORIGINAL_ARTICLE,
  bp_brand_title.VALUE                AS BRAND,
  bp_supplier_id.VALUE                AS SUPPLIER,
  bp_delivery.VALUE                   AS DELIVERY,
  bp_base_price.VALUE                 AS BASEPRICE,
  (bp_base_price.VALUE * b.QUANTITY)  AS BASEPRICE_AMOUNT,
  bp_to_branch_id.VALUE               AS BRANCH_ID,
  bp_retail_chain.VALUE               AS RETAIL_CHAIN,
  bp_child_basket_id.VALUE            AS CHILD_BASKET_ID,

  ua.CURRENT_BUDGET                   AS USER_ACCOUNT,

  u.LOGIN                             AS LOGIN,
  u.EMAIL                             AS EMAIL


FROM b_sale_basket b

LEFT JOIN b_sale_order o ON o.ID=b.ORDER_ID

LEFT JOIN b_sale_basket_props bp_supplier_id ON bp_supplier_id.BASKET_ID=b.ID AND bp_supplier_id.CODE='supplier_id'
LEFT JOIN b_sale_basket_props bp_article ON bp_article.BASKET_ID=b.ID AND bp_article.CODE='article'
LEFT JOIN b_sale_basket_props bp_original_article ON bp_original_article.BASKET_ID=b.ID AND bp_original_article.CODE='original_article'
LEFT JOIN b_sale_basket_props bp_brand_title ON bp_brand_title.BASKET_ID=b.ID AND bp_brand_title.CODE='brand_title'
LEFT JOIN b_sale_basket_props bp_base_price ON bp_base_price.BASKET_ID=b.ID AND bp_base_price.CODE='base_price'
LEFT JOIN b_sale_basket_props bp_payed ON bp_payed.BASKET_ID=b.ID AND bp_payed.CODE='payed'
LEFT JOIN b_sale_basket_props bp_canceled ON bp_canceled.BASKET_ID=b.ID AND bp_canceled.CODE='canceled'
LEFT JOIN b_sale_basket_props bp_status ON bp_status.BASKET_ID=b.ID AND bp_status.CODE='status'
LEFT JOIN b_sale_basket_props bp_delivery ON bp_delivery.BASKET_ID=b.ID AND bp_delivery.CODE='delivery'
LEFT JOIN b_sale_basket_props bp_delivery_time ON bp_delivery_time.BASKET_ID=b.ID AND bp_delivery_time.CODE='delivery_time'
LEFT JOIN b_sale_basket_props bp_to_branch_id ON bp_to_branch_id.BASKET_ID=b.ID AND bp_to_branch_id.CODE='to_branch_id'
LEFT JOIN b_sale_basket_props bp_retail_chain ON bp_retail_chain.BASKET_ID=b.ID AND bp_retail_chain.CODE='retail_chain'
LEFT JOIN b_sale_basket_props bp_child_basket_id ON bp_child_basket_id.BASKET_ID=b.ID AND bp_child_basket_id.CODE='child_basket_id'

LEFT JOIN b_sale_user_account ua ON ua.USER_ID=o.USER_ID AND ua.CURRENCY='RUB'

LEFT JOIN b_user u ON u.ID=o.USER_ID