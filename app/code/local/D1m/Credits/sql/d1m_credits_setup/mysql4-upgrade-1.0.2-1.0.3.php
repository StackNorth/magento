<?php
//优惠点数改为参数 10/1,20/3,30/5aca_sales_flat_order
//
$installer = $this;

$installer->run("

	ALTER TABLE  `aca_credits_order` ADD  `creditsparam` varchar(250) NOT NULL DEFAULT  '' AFTER  `free_credits_per_fixed_credits`

");
