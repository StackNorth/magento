<?php
/**
 *  添加流水号
 */
/**
 * @var $this Mage_Core_Model_Resource_Setup
 */
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer =  new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();

//order Payment  后台的列表页
$orderTable     = $installer->getTable('sales/order');
$orderPaymentTable = $installer->getTable('sales/order_payment');

try{
    $result = $installer->getConnection()->raw_fetchRow("SHOW COLUMNS from {$orderTable} like '%pay_trade_no%'");

    if(!is_array($result) || !in_array('pay_trade_no', $result)){
        $installer->addAttribute('order', 'pay_trade_no', array('type' => 'varchar'));
    }


    $result = $installer->getConnection()->raw_fetchRow("SHOW COLUMNS from {$orderPaymentTable} like '%pay_trade_no%'");
    if(!is_array($result) || !in_array('pay_trade_no', $result))
    {
        $installer->addAttribute('order_payment', 'pay_trade_no',array('type' => 'varchar'));
    }

}catch (Mage_Core_Exception $e)
{
    Mage::logException($e);
}

$installer->endSetup();