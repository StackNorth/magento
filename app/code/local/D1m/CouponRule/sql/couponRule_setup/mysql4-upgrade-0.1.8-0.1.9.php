<?php
 $installer = $this;
 $installer->startSetup();
 
//添加发送相关的后台配置
$installer->run("
ALTER TABLE `aca_salesrule` ADD COLUMN `sent_method_type`  tinyint(1)  NULL  AFTER `uses_per_coupon`;
ALTER TABLE `aca_salesrule` ADD COLUMN `send_date` datetime NULL   AFTER `sent_method_type`;
ALTER TABLE `aca_salesrule` ADD COLUMN `email_notice_template` int(10)  NULL  AFTER `send_date`;
ALTER TABLE `aca_salesrule` ADD COLUMN `sms_notice_template`int(10)  NULL  AFTER `email_notice_template`;
ALTER TABLE `aca_salesrule` ADD COLUMN `mesage_box_notice_template` int(10)  NULL  AFTER `sms_notice_template`;
");
 
 $installer->endSetup();