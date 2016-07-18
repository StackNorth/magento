<?php

$installer = $this;

$installer->run("ALTER TABLE  `aca_credits_order` ADD  `sales_promotion` varchar(100)  NULL DEFAULT  '-';");

