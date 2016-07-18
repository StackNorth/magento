<?php

class D1m_Adminhtml_Model_Observer{

    public function salesOrderSaveBefore($observer){
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        if ($order->getCustomerId() && $order->getStatus() == 'pending') {
            $dv = abs(abs($order->getDiscountAmount()) - $order->getSubtotal());
            if($dv >= 0 &&  $dv <= 0.009){
                $order->setStatus('complete');
                $order->addStatusToHistory('complete', 'DiscountAmount equal to subtotal');
            }
        }
    }
}
