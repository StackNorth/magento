<?php
$installer = $this;

$installer->run("
    DROP TABLE IF EXISTS `aca_d1m_sandcard`;
CREATE TABLE `aca_d1m_sandcard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_num` varchar(20) DEFAULT '0',
  `discount` decimal(4,2) DEFAULT '0.00',
  `card_name` varchar(100) DEFAULT '-',
  `sale_price` decimal(10,2) DEFAULT NULL,
  `info` varchar(250) DEFAULT NULL,
  `created` datetime DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `card` (`card_num`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8"
);
