<?php
$installer = $this;
$installer->startSetup();

$sales = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

$installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'is_reviewed', 'TINYINT(1)');

$sales->addAttribute('order', 'contact_info', array('type'=>'varchar'));

$sales->addAttribute('quote', 'contact_info', array('type'=>'varchar'));

$installer->endSetup();
?>