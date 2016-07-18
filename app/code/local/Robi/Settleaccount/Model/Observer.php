<?php

class Robi_Settleaccount_Model_Observer
{

    public function sales_order_payment_place_start($observer)
    {
        $order = $observer->getEvent()->getPayment()->getOrder();

        if (!$order) {
            return $this;
        }

        $totalAmount = $order->getCreditAmount();
        $totalAmount = floor($totalAmount/Mage::helper('d1m_credits')->getFeeByCredit());
        $totalAmount = abs($totalAmount);
		if($totalAmount)
		{
			$creditObj   = Mage::getModel('d1m_credits/credits')->load($order->getCustomerId(), 'customer_id');
			
			
			if (!$creditObj || $creditObj->getCreditAmount() < $totalAmount )
            {
	            Mage::throwException(Mage::helper('settleaccount')->__('Credits applied to this order have changed.
	                Unable to proceed, please return to shopping cart to see the changes.'));
	        }
			
			$newAmount = $creditObj->getCreditAmount() - $totalAmount;
			
			$creditObj->setCreditAmount($newAmount);
			$creditObj->historyOrderNo  = $order->getIncrementId();
			$creditObj->historyDesc  = Mage::helper('settleaccount')->__('Placed order');
			
			//finished the order
			if($order->getBaseGrandTotal() <= 0 )
			{
        		$message = Mage::helper('chinapay')->__('Payment accepted by Credits');
	    		$order->addStatusToHistory(Mage::getModel("chinapay/payment")->getConfigData('order_status_payment_accepted'), $message);
	            if($this->saveInvoice($order))
	            {
	                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
	            }
	            $order->save();
	            
	            Mage::helper('robi_checkout')->sendOrderSuccessNotice($order);
			}
			
			$creditObj->save();
		}
		
    }
    
    /**
     * Save invoice for order
     *
     * @param    Mage_Sales_Model_Order $order
     * @return	  boolean Can save invoice or not
     */
    protected function saveInvoice(Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) {
            /** @var Mage_Sales_Model_Convert_Order $convertor */
            $convertor = Mage::getModel('sales/convert_order');
            $invoice = $convertor->toInvoice($order);
            foreach ($order->getAllItems() as $orderItem) {
                if (! $orderItem->getQtyToInvoice()) {
                    continue;
                }
                $item = $convertor->itemToInvoiceItem($orderItem);
                $item->setQty($orderItem->getQtyToInvoice());
                $invoice->addItem($item);
            }
            $invoice->collectTotals();
            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
            return true;
        }

        return false;
    }
    
    
    public function sales_order_save_after_customergroup($observer)
    {
        $order = $observer->getEvent()->getOrder();
        Mage::log('sales_order_save_after_customergroup_start',7,'userGroup.log');
        $storeId = $order->getStoreId();
        $baseMoney=88;
        $totalPaid= $order->getTotalPaid();//实际付款
        $creditAmount= $order->getCreditAmount();//课点付款
         $subtotal= $order->getSubtotal();//原价

        if($order->getTotalQtyOrdered()){
            $baseMoney=  $baseMoney*$order->getTotalQtyOrdered();
        }
        if($subtotal>$baseMoney){
            $birdie= abs($creditAmount)+$totalPaid;
            Mage::log('sales_order_save_after_customergroup_birdie:'.$birdie,7,'userGroup.log');
            if($birdie==$baseMoney){//早鸟价

                $customerId = $order->getCustomerId();
                $customer = Mage::getModel('customer/customer')->load($customerId);

                if($customer->getGroupId() == '1' OR  $customer->getGroupId() == '4' )
                {
                    $customer->setGroupId(3);
                    Mage::log('sales_order_save_after_customergroup_birdie_ok',7,'userGroup.log');
                    $customer->save();
                }
            }
        }

    	
    }
    public function sales_order_cancel_after_customergroup($order){
     //   $order = $observer->getEvent()->getOrder();
        Mage::log('sales_order_cancel_after_customergroup_start',7,'userGroup.log');
        $storeId = $order->getStoreId();
        $baseMoney=88;
        $totalPaid= $order->getTotalPaid();//实际付款
        $creditAmount= $order->getCreditAmount();//课点付款
        $subtotal= $order->getSubtotal();//原价

        if($order->getTotalQtyOrdered()){
            $baseMoney=  $baseMoney*$order->getTotalQtyOrdered();
        }
        if($subtotal>$baseMoney){
            $birdie= abs($creditAmount)+$totalPaid;
            Mage::log('sales_order_cancel_after_customergroup_birdie:'.$birdie,7,'userGroup.log');
            if($birdie==$baseMoney){//早鸟价

                $customerId = $order->getCustomerId();
                $customer = Mage::getModel('customer/customer')->load($customerId);

                if($customer->getGroupId() == '3' )
                {
                    $customer->setGroupId(4);
                    Mage::log('sales_order_cancel_after_customergroup_birdie_ok',7,'userGroup.log');
                    $customer->save();
                }
            }
        }

    }


    /**
    * On payment/order cancel refund GCs
    *
    * @param mixed $observer
    */
    public function sales_order_payment_cancel($observer)
    {
        $payment = $observer->getEvent()->getPayment();
        $order = $payment->getOrder();
        
        $totalAmount = $order->getCreditAmount();
        $totalAmount = floor($totalAmount/Mage::helper('d1m_credits')->getFeeByCredit());
        $totalAmount = abs($totalAmount);
        $qty=$order->getCreditQty(); //有可能为空 初始时
        settype($qty,"integer");
        if (($qty>0) and ($totalAmount!=$qty)) $totalAmount=$qty; //当比例改变时，以原始数量为准

		if($totalAmount)
		{
			$creditObj   = Mage::getModel('d1m_credits/credits')->load($order->getCustomerId(), 'customer_id');
			
			$newAmount = $creditObj->getCreditAmount() + $totalAmount;
			
			$creditObj->setCreditAmount($newAmount);
			$creditObj->historyOrderNo  = $order->getIncrementId();
			$creditObj->historyDesc  = Mage::helper('settleaccount')->__('Canceled order');
			
			$creditObj->save();
		}
        
    }

    public function sales_order_payment_place_start_rewardpoint($observer)
    {
        $order = $observer->getEvent()->getPayment()->getOrder();

        if (!$order) {
            return $this;
        }

        $totalAmount = $order->getRewardpointsAmount();
        $totalAmount = abs($totalAmount);
		if($totalAmount)
		{
			$pointsAmount = Mage::helper('settleaccount/data')->convertMoneyToPoints($totalAmount);
			$creditObj   = Mage::getModel('d1m_integral/integral')->load($order->getCustomerId(), 'customer_id');
			
			if (!$creditObj || $creditObj->getCreditAmount() < $totalAmount ) {
	            Mage::throwException(Mage::helper('settleaccount')->__('Credits applied to this order have changed.
	Unable to proceed, please return to shopping cart to see the changes.'));
	        }
			
			$newAmount = $creditObj->getCreditAmount() - $pointsAmount;
			
			$creditObj->setCreditAmount($newAmount);
			$creditObj->historyOrderNo  = $order->getIncrementId();
			$creditObj->historyDesc  = Mage::helper('settleaccount')->__('Placed order');
			
			$creditObj->save();
		}
		
    }


    /**
    * On payment/order cancel refund GCs
    *
    * @param mixed $observer
    */
    public function sales_order_payment_cancel_rewardpoint($observer)
    {
        $payment = $observer->getEvent()->getPayment();
        $order = $payment->getOrder();
        
        $totalAmount = $order->getRewardpointsAmount();
        $totalAmount = abs($totalAmount);
		if($totalAmount)
		{
			$creditObj   = Mage::getModel('d1m_integral/integral')->load($order->getCustomerId(), 'customer_id');
			
			$pointsAmount = Mage::helper('settleaccount/data')->convertMoneyToPoints($totalAmount);
			//未取订单中的实际数量rewaredpoints_qty todo
			$newAmount = $creditObj->getCreditAmount() + $pointsAmount;
			
			$creditObj->setCreditAmount($newAmount);
			$creditObj->historyOrderNo  = $order->getIncrementId();
			$creditObj->historyDesc  = Mage::helper('settleaccount')->__('Canceled order');
			
			$creditObj->save();
		}
        
        
    }


    public function invoiceSaveAfter(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        if ($invoice->getBaseCreditAmount()) 
        {
            $order = $invoice->getOrder();
            $order->setCreditAmountInvoiced($order->getCreditAmountInvoiced() + $invoice->getCreditAmount());
            $order->setBaseCreditAmountInvoiced($order->getBaseCreditAmountInvoiced() + $invoice->getBaseCreditAmount());
        }
        if ($invoice->getBaseRewardpointsAmount())
        {
            $order = $invoice->getOrder();
            $order->setRewardpointsAmountInvoiced($order->getRewardpointsAmountInvoiced() + $invoice->getRewardpointsAmount());
            $order->setBaseRewardpointsAmountInvoiced($order->getBaseRewardpointsAmountInvoiced() + $invoice->getBaseRewardpointsAmount());
        }
        return $this;
    }
    public function creditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if ($creditmemo->getCreditAmount()) //第一次如有课点不为0，第二次为0
        {

            $order = $creditmemo->getOrder();
            //mage::log('** order creditamount:'.$order->getCreditAmount());
            //mage::log('** order creditamountrefunded:'.$order->getCreditAmountRefunded());
            $order->setCreditAmountRefunded($order->getCreditAmountRefunded() + $creditmemo->getCreditAmount());
            //mage::log('** set a+b');
            $order->setBaseCreditAmountRefunded($order->getBaseCreditAmountRefunded() + $creditmemo->getBaseCreditAmount());

            // 退用户课点

            $totalAmount = abs($order->getCreditAmountRefunded());
            $totalAmount = floor($totalAmount/Mage::helper('d1m_credits')->getFeeByCredit());
            $qty=$order->getCreditQty(); //有可能为空 初始时
            settype($qty,"integer");
            if (($qty>0) and ($totalAmount!=$qty)) $totalAmount=$qty; //当比例改变时，以原始数量为准
            if($totalAmount)
            {

                $creditObj   = Mage::getModel('d1m_credits/credits')->load($order->getCustomerId(), 'customer_id');
                $newAmount = $creditObj->getCreditAmount() + $totalAmount;
                $creditObj->setCreditAmount($newAmount);
                $creditObj->historyOrderNo  = $order->getIncrementId();
                $creditObj->historyDesc  = Mage::helper('settleaccount')->__('退款退课点');
                $creditObj->save();
            }



        }
        if ($creditmemo->getRewardpointsAmount())
        {
            $order = $creditmemo->getOrder();
            $order->setRewardpointsAmountRefunded($order->getRewardpointsAmountRefunded() + $creditmemo->getRewardpointsAmount());
            $order->setBaseRewardpointsAmountRefunded($order->getBaseRewardpointsAmountRefunded() + $creditmemo->getBaseRewardpointsAmount());

            // 退用户积分
            $totalAmount = abs($order->getRewardpointsAmountRefunded());
            if($totalAmount)
            {
                $creditObj   = Mage::getModel('d1m_integral/integral')->load($order->getCustomerId(), 'customer_id');
                //未取订单中的实际数量rewaredpoints_qty todo
                $pointsAmount = Mage::helper('settleaccount/data')->convertMoneyToPoints($totalAmount);
                $newAmount = $creditObj->getCreditAmount() + $pointsAmount;
                $creditObj->setCreditAmount($newAmount);
                $creditObj->historyOrderNo  = $order->getIncrementId();
                $creditObj->historyDesc  = Mage::helper('settleaccount')->__('退款退积分');
                $creditObj->save();
            }

        }        
        return $this;
    }
    
}
