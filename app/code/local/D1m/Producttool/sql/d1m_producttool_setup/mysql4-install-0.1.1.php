<?php
//die('abcdd');
$installer = $this;
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('d1m_producttool/downloadfile')};
CREATE TABLE {$this->getTable('d1m_producttool/downloadfile')}
(
  `id` int(11) unsigned NOT NULL auto_increment,
  `pname` varchar(255) NOT NULL default '',
  `fname` varchar(255) NOT NULL default '',
  PRIMARY KEY (`id`),
  KEY `pname` (`pname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();