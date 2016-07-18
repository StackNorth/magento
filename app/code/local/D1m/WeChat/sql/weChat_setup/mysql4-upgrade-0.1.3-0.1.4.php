<?php
/**
 *  添加微信支付所对应的表
 * @var $this Mage_Core_Model_Resource_Setup
 */
$installer = $this;

$installer->startSetup();

//创建 微信用户表
$installer->run("
 CREATE TABLE `wechat_users` (
  `openid` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `customer_id` int(11) DEFAULT '0',
  `nickname` varchar(100) COLLATE utf8_unicode_ci DEFAULT '-',
  `sex` varchar(10) COLLATE utf8_unicode_ci DEFAULT '',
  `headimgurl` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `province` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL,
  `regtime` datetime DEFAULT NULL,
  PRIMARY KEY (`openid`),
  UNIQUE KEY `uid` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC;
");

$installer->endSetup();
