<?php
/**
 *  添加微信支付所对应的表
 * @var $this Mage_Core_Model_Resource_Setup
 */
$installer = $this;

$installer->startSetup();

//创建 微信调试表
$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('weChat/api_debug')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('weChat/api_debug')}` (
  `debug_id` int(12) unsigned NOT NULL auto_increment,
  `order_increment_id` varchar(200) NOT NULL default '',
   `request_prepay_id` text NULL default '',
   `response_prepay_id` text  NULL default '',
  `request_packpage` text  NULL default '',
   `type` char(100) NOT NULL default 'JSAPI',
  `created_time` datetime NOT NULL,
  `change_time` datetime NOT NULL,
  PRIMARY KEY (`debug_id`),
  KEY `debug_at` (`created_time`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;
");

//创建 微信notify的信息记录表
$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('weChat/payment_notify_debug')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('weChat/payment_notify_debug')}` (
  `notify_id` int(12) unsigned NOT NULL auto_increment,
  `order_increment_id` varchar(200) NOT NULL default '',
   `notify_data` text NULL default '',
   `return_data` text  NULL default '',
  `created_time` datetime NOT NULL,
  `change_time` datetime NOT NULL,
  PRIMARY KEY (`notify_id`),
  KEY `notify_at` (`created_time`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;
");

//创建 微信订单查询的记录表
$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('weChat/payment_query_debug')}`;
CREATE TABLE IF NOT EXISTS `{$installer->getTable('weChat/payment_query_debug')}` (
  `query_id` int(12) unsigned NOT NULL auto_increment,
  `order_increment_id` varchar(200) NOT NULL default '',
   `query_data` text NULL default '',
   `return_data` text  NULL default '',
  `created_time` datetime NOT NULL,
  `change_time` datetime NOT NULL,
  PRIMARY KEY (`query_id`),
  KEY `query_at` (`created_time`)
) ENGINE=MYISAM DEFAULT CHARSET=utf8;
");

$installer->endSetup();
