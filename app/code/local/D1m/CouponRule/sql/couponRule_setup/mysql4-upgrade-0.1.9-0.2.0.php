<?php
$installer = $this;
$installer->startSetup();

//添加发送相关的后台配置
$installer->run("
ALTER TABLE `{$this->getTable('customer_entity')}` ADD INDEX `IDX_CUSTOMER_ENTITY_EMAIL` (`email`) USING BTREE ;;
ALTER TABLE {$this->getTable('couponRule/coupon')} ADD CONSTRAINT `FK_COUPON_ENTITY_MAPPING_CUSTOMER_EMAIL_ADDRESS`
FOREIGN KEY (`customer_email`) REFERENCES `{$this->getTable('customer_entity')}` (`email`) ON DELETE NO ACTION ON UPDATE CASCADE;
");

$installer->endSetup();