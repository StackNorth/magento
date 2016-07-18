<?php
$installer = $this;
$installer->startSetup();

$sales = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
// $sales->addAttribute('order', 'credit_qty', array('type'=>'int'));
// $sales->addAttribute('order', 'rewardpoints_qty', array('type'=>'int'));

$sales->addAttribute('quote', 'credit_qty', array('type'=>'int'));
$sales->addAttribute('quote', 'rewardpoints_qty', array('type'=>'int'));
$installer->endSetup();
?>