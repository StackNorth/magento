<?php
class CosmoCommerce_Alipay_Model_Observer{
    
    /*
     * cancel order that do not paid ( neither online payment or check on money ) in a perid time.
    */
    public function orderCancelationActive(){
        /* @var $date Mage_Core_Model_Date */
        $date = Mage::getModel('core/date');
        $gmtTimestamp = $date->gmtTimestamp($date->timestamp(time()));
        
        $cancel_pending_orders = Mage::getStoreConfig('payment/alipay_payment/cancel_pending_orders');
        if($cancel_pending_orders){
            /* @var $_orderList Mage_Sales_Model_Resource_Order_Collection */
            $_orderList = Mage::getModel('sales/order')->getCollection();
            $_orderList->addFieldToFilter('status', array('eq'=>'pending'));
            $_orderList->getSelect()->where("DATE_ADD(updated_at, INTERVAL $cancel_pending_orders MINUTE) < '".date(Varien_Date::DATETIME_PHP_FORMAT, $gmtTimestamp)."' ");
            if ($_orderList->getSize() > 0) {
                $orderIds = array();
                foreach($_orderList as $_order){
                    /* @var $order Mage_Sales_Model_Order */
                    $order = Mage::getModel("sales/order")->load($_order->getId());
                    if($order->canCancel()) {
                        $order->cancel()->save();
                    }
                    $orderIds[] = $order->getIncrementId();
                }
                Mage::log(sprintf( "%d orders was canceled.", $_orderList->getSize() ).'-'.implode(',', $orderIds), null, "order-cancelation-active.log");
            } else {
                Mage::log("0 orders was canceled.", null, "order-cancelation-active.log");
            }
        }
    
    }

}