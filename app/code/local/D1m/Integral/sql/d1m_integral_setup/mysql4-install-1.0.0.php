<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
		
	DROP TABLE IF EXISTS {$this->getTable('integral')};
	CREATE TABLE {$this->getTable('integral')} (
	    `id` int(11) NOT NULL AUTO_INCREMENT,
      `customer_id` int(11) NOT NULL,
      `credit_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `customer_id` (`customer_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

    DROP TABLE IF EXISTS {$this->getTable('integral_history')};
    CREATE TABLE {$this->getTable('integral_history')} (    
        `id` int(11) NOT NULL AUTO_INCREMENT,
      `credit_id` int(11) NOT NULL,
      `add` decimal(10,2) NOT NULL DEFAULT '0.00',
      `subtract` decimal(10,2) NOT NULL DEFAULT '0.00',
      `order_no` varchar(225) DEFAULT NULL,
      `description` varchar(255) DEFAULT NULL,
      `created_at` datetime NOT NULL,
      `updated_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      KEY `credit_id` (`credit_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            
");

$installer->endSetup();
