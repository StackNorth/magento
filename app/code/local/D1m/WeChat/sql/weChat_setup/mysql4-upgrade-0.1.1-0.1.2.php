<?php
/**
 *  添加 订单的支付时间
 */
/**
 * @var $this Mage_Core_Model_Resource_Setup
 */
/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer =  new Mage_Eav_Model_Entity_Setup('core_setup');

$installer->startSetup();


//order grid  后台的列表页
$orderGridTable = $installer->getTable('sales/order_grid');
$orderTable     = $installer->getTable('sales/order');

try{
    $result = $installer->getConnection()->raw_fetchRow("SHOW COLUMNS from {$orderGridTable} like '%paid_at%'");

    if(!is_array($result) || !in_array('paid_at', $result)){
        //order admin grid
        $installer->getConnection()->addColumn($orderGridTable, 'paid_at', 'datetime');
        $installer->getConnection()->addKey($orderGridTable, 'IDX_PAID_AT', 'paid_at');
    }


    $result = $installer->getConnection()->raw_fetchRow("SHOW COLUMNS from {$orderTable} like '%paid_at%'");
    if(!is_array($result) || !in_array('paid_at', $result)){
        //order admin grid
        $installer->getConnection()->addColumn($orderTable, 'paid_at', 'datetime');
        $installer->getConnection()->addKey($orderTable, 'IDX_PAID_AT', 'paid_at');
    }

}catch (Mage_Core_Exception $e)
{
    Mage::logException($e);
}

$installer->endSetup();