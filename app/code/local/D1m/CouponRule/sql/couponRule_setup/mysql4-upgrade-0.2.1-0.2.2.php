<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer->startSetup();

$installer->run("
ALTER TABLE `{$this->getTable('salesrule')}`
    ADD COLUMN `is_credits`  tinyint(1) NOT NULL DEFAULT '0'
");

$installer->endSetup();
