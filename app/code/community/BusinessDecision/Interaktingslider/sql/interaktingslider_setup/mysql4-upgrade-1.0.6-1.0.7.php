<?php
/* @var $this Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$slideTable = $installer->getTable('interaktingslider_slide');
if (!$installer->getConnection()->fetchOne("SHOW COLUMNS FROM `{$slideTable}` LIKE 'group'")) {
    $installer->run("ALTER TABLE `{$slideTable}` ADD COLUMN `group` VARCHAR(255) NOT NULL DEFAULT '';");
}
$installer->run("UPDATE `{$slideTable}` SET `group` = 'home';");

$installer->endSetup();
