<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('sales/order')}`
    ADD COLUMN `order_from`  VARCHAR(10) NOT NULL DEFAULT 'web'
");

$installer->endSetup();
