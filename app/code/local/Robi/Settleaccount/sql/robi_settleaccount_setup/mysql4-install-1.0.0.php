<?php

$installer = $this;
$installer->startSetup();

$sales = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

$this->_conn->addColumn($this->getTable('sales_flat_order'), 'is_sync_erp', 'TINYINT(1) UNSIGNED NULL DEFAULT "0"');
$sales->addAttribute('order', 'is_sync_erp', array('type'=>'TINYINT(1)'));

$this->_conn->addColumn($this->getTable('sales_flat_quote_address'), 'credit_amount', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('sales_flat_quote_address'), 'base_credit_amount', 'decimal(12,4)');

$sales = new Mage_Sales_Model_Mysql4_Setup('sales_setup');

$sales->addAttribute('order', 'credit_amount', array('type'=>'decimal'));
$sales->addAttribute('order', 'base_credit_amount', array('type'=>'decimal'));

$sales->addAttribute('invoice', 'credit_amount', array('type' => 'decimal'));
$sales->addAttribute('invoice', 'base_credit_amount', array('type' => 'decimal'));

$sales->addAttribute('creditmemo', 'credit_amount', array('type' => 'decimal'));
$sales->addAttribute('creditmemo', 'base_credit_amount', array('type' => 'decimal'));

$this->_conn->addColumn($this->getTable('sales_flat_quote_address'), 'rewardpoints_amount', 'decimal(12,4)');
$this->_conn->addColumn($this->getTable('sales_flat_quote_address'), 'base_rewardpoints_amount', 'decimal(12,4)');

$sales->addAttribute('order', 'rewardpoints_amount', array('type'=>'decimal'));
$sales->addAttribute('order', 'base_rewardpoints_amount', array('type'=>'decimal'));

$sales->addAttribute('invoice', 'rewardpoints_amount', array('type' => 'decimal'));
$sales->addAttribute('invoice', 'base_rewardpoints_amount', array('type' => 'decimal'));
$sales->addAttribute('creditmemo', 'rewardpoints_amount', array('type' => 'decimal'));
$sales->addAttribute('creditmemo', 'base_rewardpoints_amount', array('type' => 'decimal'));

$installer->endSetup();
?>