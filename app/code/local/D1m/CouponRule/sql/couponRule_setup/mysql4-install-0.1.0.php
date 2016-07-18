<?php
$this->startSetup();

$this->run("
DROP TABLE IF EXISTS {$this->getTable('couponRule/coupon')};
CREATE TABLE IF NOT EXISTS {$this->getTable('couponRule/coupon')} (
 `coupon_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `coupon` varchar(100) NOT NULL DEFAULT '',
  `rule_id` int(10) NOT NULL DEFAULT '0',
  `customer_id` int(10) NOT NULL DEFAULT '0',
  `customer_email` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`coupon_id`),
  KEY `IDX_CUSTOMER_ID` (`customer_id`) USING BTREE,
  KEY `IDX_COUPON_CODE` (`coupon`) USING BTREE,
  KEY `FK_COUPON_ENTITY_MAPPING_CUSTOMER_EMAIL_ADDRESS` (`customer_email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='couponRule mapping customer';
");
$this->endSetup();
?>