<?php
/**
 *  添加是否基于原价的优惠券促销判断
 *
 */
$installer = $this;
 $installer->startSetup();
 
$installer->run("
ALTER TABLE `aca_salesrule` ADD COLUMN `rule_base_on_original_price`  int(10) NOT NULL DEFAULT '1' AFTER `available_category_item_qty_info`;
");
 
 $installer->endSetup();