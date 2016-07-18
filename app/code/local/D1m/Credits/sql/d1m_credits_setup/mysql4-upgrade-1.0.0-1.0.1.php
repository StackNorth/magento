<?php

$installer = $this;

$installer->run("
		
	CREATE TABLE IF NOT EXISTS {$this->getTable('credits_order')} (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `status` varchar(20) NOT NULL DEFAULT 'new',
  `protect_code` varchar(255) NOT NULL,
  `remote_ip` varchar(255) NOT NULL,
  `email_sent` tinyint(1) NOT NULL DEFAULT '0',
  `billing_sent` tinyint(1) NOT NULL DEFAULT '0',
  `billing_at` datetime NOT NULL,
  `billing_shippng_method` varchar(120) NOT NULL,
  `billing_trackingno` varchar(120) NOT NULL,
  `qty` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `grand_total` decimal(12,2) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `firstname` varchar(120) NOT NULL,
  `lastname` varchar(120) NOT NULL,
  `email` varchar(255) NOT NULL,
  `city` varchar(120) NOT NULL,
  `company` varchar(255) NOT NULL,
  `zipcode` varchar(20) NOT NULL,
  `telephone` varchar(60) NOT NULL,
  `street_address` varchar(255) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_at` datetime NOT NULL,
  `total_paid` decimal(12,2) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
            
");
