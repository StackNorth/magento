<?php
/* @var $this Mage_Sales_Model_Mysql4_Setup  */
$installer = $this;
$installer->startSetup();

$sales = new Mage_Sales_Model_Mysql4_Setup('sales_setup');
//订单退款对应的金额
$sales->addAttribute('order', 'credit_amount_refunded', array('type'=>'decimal'));
$sales->addAttribute('order', 'base_credit_amount_refunded', array('type'=>'decimal'));
$sales->addAttribute('order', 'rewardpoints_amount_refunded', array('type'=>'decimal'));
$sales->addAttribute('order', 'base_rewardpoints_amount_refunded', array('type'=>'decimal'));

//课点和积分 数量 暂时没用上
// $this->_conn->addColumn($this->getTable('sales_flat_order'), 'credit_qty', 'int(11) DEFAULT "0"');
// $this->_conn->addColumn($this->getTable('sales_flat_order'), 'rewardpoints_qty', 'int(11) DEFAULT "0"');

$sales->addAttribute('order', 'credit_qty', array('type'=>'int'));
$sales->addAttribute('order', 'rewardpoints_qty', array('type'=>'int'));


$installer->endSetup();
?>