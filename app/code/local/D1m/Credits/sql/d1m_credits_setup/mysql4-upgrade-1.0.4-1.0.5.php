<?php
$installer = $this;

$installer->run("
    DROP TABLE IF EXISTS `aca_d1m_balance`;
CREATE TABLE `aca_d1m_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `created_date` date NOT NULL,
  `order_money` decimal(8,2) NOT NULL DEFAULT '0.00',
  `credits` decimal(8,2) NOT NULL DEFAULT '0.00',

  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
");
