<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();


$installer->run("
DROP TABLE IF EXISTS {$this->getTable('msgnotice_failedaction')};
CREATE TABLE {$this->getTable('msgnotice_failedaction')} (
  `id` int(11) unsigned NOT NULL auto_increment,
  `actionname` varchar(255) NOT NULL default '',
  `fulldata` text NULL default Null,
  `returndata` text NULL default Null,
  `trytimes` int(11) NOT NULL default '0',
  `status` tinyint(2) NOT NULL default '0',
  `created_on` datetime,
  `updated_on` datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");


$installer->endSetup();
