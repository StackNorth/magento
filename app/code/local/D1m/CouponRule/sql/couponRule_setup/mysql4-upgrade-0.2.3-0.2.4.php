<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('sales/order')}`

    ADD COLUMN `order_trench` VARCHAR(60) NOT NULL DEFAULT '-',
    ADD COLUMN `order_admin`  VARCHAR(60) NOT NULL DEFAULT '-',
    ADD COLUMN `order_sign`   INT(2) NOT NULL DEFAULT '0'

");

$installer->endSetup();
