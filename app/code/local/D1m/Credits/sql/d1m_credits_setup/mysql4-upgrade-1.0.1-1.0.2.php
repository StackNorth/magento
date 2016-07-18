<?php

$installer = $this;

$installer->run("
		
	ALTER TABLE  `aca_credits_order` ADD  `free_credits_per_fixed_credits` INT( 11 ) NOT NULL DEFAULT  '0' AFTER  `unit_price` ,
ADD  `gift_credits` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0' AFTER  `free_credits_per_fixed_credits` ,
ADD  `gift_total` DECIMAL( 10, 2 ) NOT NULL DEFAULT  '0' AFTER  `gift_credits`
            
");
