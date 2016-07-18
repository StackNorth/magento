<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
		
	DROP TABLE IF EXISTS {$this->getTable('invitation')};
	CREATE TABLE {$this->getTable('invitation')} (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
      `customer_id` int(11) NOT NULL,
      `status` tinyint(1) NOT NULL default '0',
      `coupon_code` varchar(255) DEFAULT NULL,
      `name` varchar(255) DEFAULT NULL,
      `email` varchar(255) DEFAULT NULL,
      `phone` varchar(255) DEFAULT NULL,
      `note` text DEFAULT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

            
");

$installer->endSetup();
