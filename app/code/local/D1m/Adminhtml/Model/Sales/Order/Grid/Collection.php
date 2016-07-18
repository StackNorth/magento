<?php
class D1m_Adminhtml_Model_Sales_Order_Grid_Collection extends Mage_Sales_Model_Resource_Order_Grid_Collection
{

    protected $d1m_special  = false;
    public function setD1mSpecial($value)
    {
        $this->d1m_special = (bool)$value;
        return $this;
    }
    public function getD1mSpecial()
    {
        return $this->d1m_special;
    }

    public function getSelectCountSql()
    {
        if (!$this->d1m_special) return $this->_getSelectCountSql();
        //do special
        $this->_renderFilters();
        $groupSelect = clone $this->getSelect();
        $groupSelect->reset(Zend_Db_Select::ORDER);
        $groupSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $groupSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect = clone $this->getSelect();
        $countSelect->reset();
        $countSelect->from(array('a' => $groupSelect), 'COUNT(*)');
        // echo 'abcdefg';
        return $countSelect;

    }
    public function getPaymentList(){
     return array(
            'alipay_payment'=>'支付宝',
            'chinapay_payment'=>'银联',
            'sandpay_payment'=>'杉德卡',
            'couponRule_payment'=>'预收款结账',
            'weChat_payment'=>'微信付款',
        //    'banktransfer'=>'现金',
          //  'banktransfer'=>'银行转账',  
        );
    }
    public function getStatuses(){

        return array(
            'processing'=>'processing',
            'pending'=>'pending',
            'canceled'=>'canceled',
            'complete'=>'complete',
            'refund'=>'refund',
            //  'banktransfer'=>'银行转账',
        );
    }
    public function updateOrderStatus($orderId,$status){

        /* 获取资源模型 */
        $order = Mage::getModel('sales/order')->load($orderId);
        $resource = Mage::getSingleton('core/resource');

        $writeConnection = $resource->getConnection('core_write');
        $query = "UPDATE `aca_sales_flat_order_grid` SET `status` ='{$status}' WHERE entity_id=" .$orderId;
        $writeConnection->query($query);
        if($status=='refund'){
            $tableName = $resource->getTableName('sales/order');
             $query = "UPDATE `{$tableName}` SET `status` ='refund',state='refund' WHERE entity_id=" .$orderId;
            $writeConnection->query($query);
            return $this->updateProductQty($order);

        }


    }
    public function updateProductQty($order){
        $productItem= $order->getAllItems();
        foreach($productItem as $item){
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($item->getProductId());
            if ( $stockItem->getId()) {
                $newQty= $stockItem->getQty()+$item->getQtyOrdered();
                $stockItem->setQty($newQty);
                $stockItem->save();
              //  echo $stockItem->getId(),':';
              //  echo $newQty;
            }


        }
    }
}
