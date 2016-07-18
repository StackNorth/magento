<?php

$installer = $this;

$installer->run("ALTER TABLE  `aca_credits_order` ADD  `financial_money` decimal(10,2)    NULL DEFAULT  '0';");

