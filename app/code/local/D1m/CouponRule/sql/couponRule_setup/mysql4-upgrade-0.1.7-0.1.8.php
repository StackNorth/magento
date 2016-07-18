<?php
 $installer = $this;
 $installer->startSetup();
 
//boolean, if this value is 1, then it can default in every category, how many qty of product is available 
$installer->run("
ALTER TABLE `aca_salesrule` ADD COLUMN `available_category_item_qty`  int(10) NOT NULL DEFAULT '0';
");

/*
 * for example 
 * "471:2"->categoryid:471,qty of products which are available:2
   "471:2;472:2"
 */
$installer->run("
ALTER TABLE `aca_salesrule` ADD COLUMN `available_category_item_qty_info`  VARCHAR(200) DEFAULT NULL;
");
 
 $installer->endSetup();