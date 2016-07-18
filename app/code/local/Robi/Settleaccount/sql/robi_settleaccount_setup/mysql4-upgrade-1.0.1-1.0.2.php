<?php
/* @var $this Mage_Sales_Model_Mysql4_Setup  */
$installer = $this;
$installer->startSetup();

$sales = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

//发票对应的金额
$sales->addAttribute('order', 'credit_amount_invoiced', array('type'=>'decimal'));
$sales->addAttribute('order', 'base_credit_amount_invoiced', array('type'=>'decimal'));
$sales->addAttribute('order', 'rewardpoints_amount_invoiced', array('type'=>'decimal'));
$sales->addAttribute('order', 'base_rewardpoints_amount_invoiced', array('type'=>'decimal'));

$installer->endSetup();
?>