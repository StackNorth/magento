<?php
 $installer = $this;
 $installer->startSetup();
 
//boolean, if this value is 1, in check out process, all rule's condition are just for original price
//this filed is add in Bysoft_Specialgiftcard_Block_Adminhtml_Promo_Quote_Edit_Tab_Main
$installer->run("
ALTER TABLE `aca_salesrule` ADD COLUMN `link_send_coupon_rule_id`  int(10) NOT NULL DEFAULT '0';
");
 
 $installer->endSetup();