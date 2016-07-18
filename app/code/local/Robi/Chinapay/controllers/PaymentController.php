<?php

class Robi_Chinapay_PaymentController extends Mage_Core_Controller_Front_Action
{
	private $_order = null;
	
	/**
     * @return D1m_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null)
        {
            $increment_id = trim($this->getRequest()->getParam('increment_id'));
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
			
            if ($session->getLastRealOrderId()){
                $this->_order->loadByIncrementId($session->getLastRealOrderId());
            } else{
                if (!empty($increment_id)){
                    $decodeOrderId = base64_decode($increment_id);
                    $this->_order->loadByIncrementId($decodeOrderId);
                }
            }
        }
        return $this->_order;
    }
    
    public function redirectAction()
    {
		$order = $this->getOrder();
		
		$_ordersTotals = $order->getGrandTotal();
		$_ordersGrandTotal = $order->getBaseGrandTotal();
		
		if ($_ordersTotals == 0 || $_ordersGrandTotal == 0) {                       
			$this->norouteAction();
			return;
		}
				
		$this->getResponse()
				->setBody($this->getLayout()
				->createBlock('chinapay/redirect')
				->setPayment($order)
				->toHtml());
			
    }

    public function ueronotifyAction()
    {

		$model = Mage::getModel("chinapay/payment");
		
        if($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();
            $method = 'post';
        }else if($this->getRequest()->isGet())
        {
            $postData = $this->getRequest()->getQuery();
            $method = 'get';
        }else
        {
            echo $model->getErrorResponse();	
			return;
        }
        
        $merid 		= isset($postData['merid']) ? $postData['merid'] : '';
        $transno 	= isset($postData['orderno']) ?  $postData['orderno']: '';
        $transdate 	= isset($postData['transdate']) ?  $postData['transdate']: '';
        $amount 	= isset($postData['amount']) ?  $postData['amount']: '';
        $currencycode 	= isset($postData['currencycode']) ?  $postData['currencycode']: '';
        $transtype 		= isset($postData['transtype']) ?  $postData['transtype']: '';
        $status 		= isset($postData['status']) ?  $postData['status']: '';
        $checkvalue 	= isset($postData['checkvalue']) ?  $postData['checkvalue']: '';
        $gateId 		= isset($postData['GateId']) ?  $postData['GateId']: '';
        $Priv1 			= isset($postData['Priv1']) ?  $postData['Priv1']: '';
        
        $realOrderNo   = $Priv1;
        
        $order 	   = Mage::getModel('sales/order')->loadByIncrementId($realOrderNo);
        
        if(!$order && $order->getId() <= 0 )
		{
			echo $model->getErrorResponse();	
			return;
		}
        
		require_once( Mage::getBaseDir().DIRECTORY_SEPARATOR."netpay_keys".DIRECTORY_SEPARATOR ."netpayclient.php");
	  	$pgPubk = Mage::getBaseDir().DIRECTORY_SEPARATOR ."netpay_keys".DIRECTORY_SEPARATOR .$model->getConfigData('public_key');
	  	$merPrk = Mage::getBaseDir().DIRECTORY_SEPARATOR."netpay_keys".DIRECTORY_SEPARATOR .$model->getConfigData('private_key');
		
		$keyflag = buildKey($pgPubk);
		if(!$keyflag) {
			$message = '后台通知：生成支付安全验证失败，请告知后台开发人员，谢谢！';
			$order->addStatusHistoryComment($message);
			$order->save();
			echo $model->getErrorResponse();	
			return;
		}
		
		//verify response by Chinapay
		$flag = verifyTransResponse($merid, $transno, $amount, $currencycode, $transdate, $transtype, $status, $checkvalue);
		if(!$flag)
		{
			$message = '后台通知：支付安全验证失败！';
			$order->addStatusHistoryComment($message);
			$order->save();
			echo $model->getErrorResponse();	
			return;
		}
        
        //check return code
        if($status=='1001'){
        	
        	if($order->getStatus() == 'pending')
        	{
        		$message = Mage::helper('chinapay')->__('Payment accepted by Chinapay');
	    		$order->addStatusToHistory($model->getConfigData('order_status_payment_accepted'), $message);
	            if($this->saveInvoice($order))
	            {
	                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
	            }
	            Mage::helper('robi_checkout')->sendOrderSuccessNotice($order);
                Mage::dispatchEvent('payment_accept_notify', array('order' => $order));
        	}
        	else
        	{
        		$message = '支付成功，但是订单状态已经改变，订单状态不作更改处理';
				$order->addStatusHistoryComment($message);
        	}
    		
      		echo $model->getSuccessResponse();	
        }
        else{
        	
			$message = Mage::helper('chinapay')->__('Payment failed by Chinapay').'[ error:'.$status.']';
			$order->addStatusHistoryComment($message);
			echo $model->getErrorResponse();
        }
        
        $order->save();
        
        
    }


    /**
     *  Success payment page
     *
     *  @param    none
     *  @return	  void
     */
    public function successAction()
    {
    	$model = Mage::getModel("chinapay/payment");
    	
        if($this->getRequest()->isPost())
        {
            $postData = $this->getRequest()->getPost();
            $method = 'post';
        }else if($this->getRequest()->isGet())
        {
            $postData = $this->getRequest()->getQuery();
            $method = 'get';
        }else
        {
            Mage::getSingleton('checkout/session')->addNotice('支付失败，请重新选择新的在线支付方式。');
		    $this->_redirect('onlinepay/payment/redirect');
        	return;
        }
        
        $merid 		= isset($postData['merid']) ? $postData['merid'] : '';
        $transno 	= isset($postData['orderno']) ?  $postData['orderno']: '';
        $transdate 	= isset($postData['transdate']) ?  $postData['transdate']: '';
        $amount 	= isset($postData['amount']) ?  $postData['amount']: '';
        $currencycode 	= isset($postData['currencycode']) ?  $postData['currencycode']: '';
        $transtype 		= isset($postData['transtype']) ?  $postData['transtype']: '';
        $status 		= isset($postData['status']) ?  $postData['status']: '';
        $checkvalue 	= isset($postData['checkvalue']) ?  $postData['checkvalue']: '';
        $gateId 		= isset($postData['GateId']) ?  $postData['GateId']: '';
        $Priv1 			= isset($postData['Priv1']) ?  $postData['Priv1']: '';
        
        $realOrderNo   = $Priv1;
        
        $order 	   = Mage::getModel('sales/order')->loadByIncrementId($realOrderNo);
        
        if(!$order || $order->getId() <= 0 )
		{
			echo $model->getErrorResponse();	
			return;
		}
		
		require_once( Mage::getBaseDir().DIRECTORY_SEPARATOR."netpay_keys".DIRECTORY_SEPARATOR ."netpayclient.php");
	  	$pgPubk = Mage::getBaseDir().DIRECTORY_SEPARATOR ."netpay_keys".DIRECTORY_SEPARATOR .$model->getConfigData('public_key');
	  	$merPrk = Mage::getBaseDir().DIRECTORY_SEPARATOR."netpay_keys".DIRECTORY_SEPARATOR .$model->getConfigData('private_key');
		
		$keyflag = buildKey($pgPubk);
		if(!$keyflag) {
			$message = '支付安全验证失败，请重试！';
			$order->addStatusHistoryComment($message);
			$order->save();
			Mage::getSingleton('checkout/session')->addNotice('支付失败，请重新选择新的在线支付方式。');
		    $this->_redirect('checkout/onepage/success');
			return;
		}
		
		//verify response by Chinapay
		$flag = verifyTransResponse($merid, $transno, $amount, $currencycode, $transdate, $transtype, $status, $checkvalue);
		if(!$flag)
		{
			$message = '银联支付安全验证失败，请重试！';
			$order->addStatusHistoryComment($message);
			$order->save();
			Mage::getSingleton('checkout/session')->addNotice('支付失败，请重新选择新的在线支付方式。');
		    //$this->_redirect('checkout/onepage/success');
			return;
		}
		
        //check return code
        if($status=='1001'){
        	
        	if($order->getStatus() == 'pending')
        	{
        		$message = Mage::helper('chinapay')->__('Payment accepted by Chinapay');
	    		$order->addStatusToHistory($model->getConfigData('order_status_payment_accepted'), $message);
	            if($this->saveInvoice($order))
	            {
	                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
	            }
	            
	            Mage::helper('robi_checkout')->sendOrderSuccessNotice($order);
        	}
        	else
        	{
        		$message = '支付成功，但是订单状态已经改变，订单状态不再作更改处理';
				$order->addStatusHistoryComment($message);
        	}
        	
        	$order->save();
        	
      		$this->_redirect('checkout/onepage/success');
        }
        else{
			$message = Mage::helper('chinapay')->__('Payment failed by Chinapay').'[ error:'.$status.']';
			$order->addStatusHistoryComment($message)->save();
			Mage::getSingleton('checkout/session')->addNotice('支付失败，请重新选择新的在线支付方式。');
		    $this->_redirect('checkout/onepage/success');
        	return;	
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
	

    public function errorAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $message = Mage::helper('chinapay')->__('There was an error occurred during paying process.');
		
        $transno  = $session->getLastRealOrderId();
		$order 	   = Mage::getModel('sales/order')->loadByIncrementId($transno);
        
        if($order && $order->getId() )
		{
			$order->addStatusHistoryComment($message);
			$order->save();
		}

        $this->loadLayout();
        $this->renderLayout();
        
        $session->unsLastRealOrderId();
    }
}
