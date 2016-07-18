<?php

class Robi_Chinapay_CreditpaymentController extends Mage_Core_Controller_Front_Action
{
	private $_order = null;
	
	/**
     * @return D1m_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->_order == null)
        {
            $increment_id = trim($this->getRequest()->getParam('credit_order_id'));
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('d1m_credits/order');
			
            if ($session->getLastRealCreditOrderId()){
                $this->_order->load($session->getLastRealCreditOrderId());
            } else{
                if (!empty($increment_id)){
                    $decodeOrderId = base64_decode($increment_id);
                    $this->_order->load($decodeOrderId);
                }
            }
        }        return $this->_order;
    }
    
    public function redirectAction()
    {
		$order = $this->getOrder();
		
		$_ordersTotals = $order->getGrandTotal();
		
		if ($_ordersTotals == 0) {                       
			$this->norouteAction();
			return;
		}
				
		$this->getResponse()
				->setBody($this->getLayout()
				->createBlock('chinapay/creditredirect')
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
        
        $realOrderno   = $Priv1;
        
        $order 	   = Mage::getModel('d1m_credits/order')->load($realOrderno);
        
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
			$message = '后台通知：生成支付安全验证失败，请告知后台开发人员，谢谢！';
			Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
			echo $model->getErrorResponse();	
			return;
		}
		
		//verify response by Chinapay
		$flag = verifyTransResponse($merid, $transno, $amount, $currencycode, $transdate, $transtype, $status, $checkvalue);
		if(!$flag)
		{
			$message = '后台通知：支付安全验证失败！';
			Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
			echo $model->getErrorResponse();	
			return;
		}
        
        
        //check return code
        if($status=='1001'){
        	
        	if($order->getStatus() == 'new')
        	{
        		$message = Mage::helper('chinapay')->__('Payment accepted by Chinapay');
	    		$order->setStatus(D1m_Credits_Model_Order::STATE_COMPLETE);
                $order->placedOrderCreditsToUser ($order->getQty() + $order->getGiftCredits(),$order->getId());  //购买数量+ 赠送点数
	            $order->save();
	            Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
        	}
        	else
        	{
        		$message = '支付成功，但是订单状态已经改变，订单状态不作更改处理';
        		Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
        	}
    		
      		echo $model->getSuccessResponse();	
        }
        else{
			$message = Mage::helper('chinapay')->__('Payment failed by Chinapay').'[ error:'.$status.']';
			Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
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
        
        $realOrderno   = $Priv1;
        
        $order 	   = Mage::getModel('d1m_credits/order')->load($realOrderno);
        
        if(!$order || $order->getId() <= 0 )
		{
			$message = '找不到对应的订单';
			echo $model->getErrorResponse();	
			Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
			return;
		}
		
		require_once( Mage::getBaseDir().DIRECTORY_SEPARATOR."netpay_keys".DIRECTORY_SEPARATOR ."netpayclient.php");
	  	$pgPubk = Mage::getBaseDir().DIRECTORY_SEPARATOR ."netpay_keys".DIRECTORY_SEPARATOR .$model->getConfigData('public_key');
	  	$merPrk = Mage::getBaseDir().DIRECTORY_SEPARATOR."netpay_keys".DIRECTORY_SEPARATOR .$model->getConfigData('private_key');
		
		$keyflag = buildKey($pgPubk);
		if(!$keyflag) {
			$message = '支付安全验证失败，请重试！';
			Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
		    $this->_redirect('credits/checkout/success');
			return;
		}
		
		//verify response by Chinapay
		$flag = verifyTransResponse($merid, $transno, $amount, $currencycode, $transdate, $transtype, $status, $checkvalue);
		if(!$flag)
		{
			$message = '银联支付安全验证失败，请重试！';
			Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
			Mage::getSingleton('checkout/session')->addNotice('支付失败，请重新选择新的在线支付方式。');
		    $this->_redirect('credits/checkout/success');
			return;
		}
		
        //check return code
        if($status=='1001'){
        	
        	
        	if($order->getStatus() == D1m_Credits_Model_Order::STATE_NEW)
        	{
        		$message = Mage::helper('chinapay')->__('Payment accepted by Chinapay');
	    		$order->setStatus(D1m_Credits_Model_Order::STATE_COMPLETE);
                $order->placedOrderCreditsToUser ($order->getQty() + $order->getGiftCredits(),$order->getId());  //购买数量+ 赠送点数
	            $order->save();
	            Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
        	}
        	else
        	{
        		$message = '支付成功，但是订单状态已经改变，订单状态不再作更改处理';
        		Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
        	}
        	
        	$order->save();
    		
      		$this->_redirect('credits/checkout/success');
        }
        else{
			$message = Mage::helper('chinapay')->__('Payment failed by Chinapay').'[ error:'.$status.']';
			Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
			Mage::getSingleton('checkout/session')->addNotice('支付失败，请重新选择新的在线支付方式。');
		    $this->_redirect('credits/checkout/success');
        	return;	
        }
        
	}
	


    public function errorAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $message = Mage::helper('chinapay')->__('There was an error occurred during paying process.');
		
        $transno  = $session->getLastRealCreditOrderId();
		$order 	   = Mage::getModel('d1m_credits/order')->load($transno);
		
        if($order)
        {
        	Mage::log('$transno:'.$order->getId().'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
        }
        
        $this->loadLayout();
        $this->renderLayout();
        
        $session->unsLastRealOrderId();
    }
}
