<?php
require_once "Mage/Checkout/controllers/OnepageController.php";

class Robi_Checkout_OnepageController extends Mage_Checkout_OnepageController
{
	
    public function preDispatch()
    {
        parent::preDispatch();
        return $this;
    }
	
	/**
     * Check can page show for unregistered users
     *
     * @return boolean
     */
    protected function _canShowForUnregisteredUsers()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn()
            || $this->getRequest()->getActionName() == 'index'
            || !Mage::helper('checkout')->isCustomerMustBeLogged();
    }


    function billingAction()
    {
        
        if(!$this->getOnepage()->getQuote()->hasItems())
        {
        	$this->_redirect('checkout/cart');
        	return $this;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
        	

    }
    
    /**
     * Checkout page
     */
    public function indexAction()
    {
        $this->_redirect('checkout/cart');
    }
    
    
    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
    
        /**
     * Save checkout billing address
     */
    public function saveBillingAction()
    {
        if ($this->getRequest()->isPost()) {
        	
        	$session = $this->_getSession();
        	
            $data = $this->getRequest()->getPost('billing', array());
            $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);

            if (isset($data['email'])) {
                $data['email'] = trim($data['email']);
            }
            $result = $this->getOnepage()->saveBilling($data, $customerAddressId);

            if (!isset($result['error'])) {
                
                $this->_redirect('checkout/onepage/review');
            }
			else
			{
				$messages = isset($result['message']) ? $result['message'] : array() ;
                foreach ($messages as $message) {
                    $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
                }
                
                $session->setBillingFormData($data);
                
				$this->_redirect('checkout/onepage/billing');
			}
            
        }
    }
    
    /**
     * Review page action
     */
    public function reviewAction()
    {
    	if(!$this->getOnepage()->getQuote()->hasItems())
        {
        	$this->_redirect('checkout/cart');
        	return $this;
        }
    	
        $this->loadLayout();
        $this->renderLayout();
    }
    
    /**
     * Create order action
     */
    public function saveOrderAction()
    {

        if (!$this->_validateFormKey()) {
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getOnepage()->getQuote();
        $count= $quote->getItemsCount();
        if ($count==0)
        {
            $message = $this->__('网页已过期失效');
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
            $this->_redirect('checkout/cart');
            return;
        }

        $result = array();
        try {
        	
        	$defaultAddr = Mage::helper('robi_checkout')->getDefaultAddressArray();
        	
        	$result = $this->getOnepage()->saveBilling($defaultAddr, null);
        	
        	if(isset($result['error']) && $result['error'] == 1)
        	{
        		$message = $this->__('There was an error processing your order. Please contact us or try again later.');
            	$this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
        		$this->_redirect('checkout/cart');
            	return;
        	}


            $data = $this->getRequest()->getPost('payment', array('method'=>'chinapay_payment'));
            // var_dump($data); array(1) { ["method"]=> string(14) "alipay_payment" }
            if ($data)
            {
                if ($data['method']=="") $data['method']='couponRule_payment';//'chinapay_payment'; //缺省支付方式
                $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                    | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                    | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                $this->getOnepage()->getQuote()->getPayment()->importData($data);
                //echo 'import';
            }
             //echo 'method:'.$this->getOnepage()->getQuote()->getPayment()->getMethod();            die();
            $contacts = $this->getRequest()->getPost('contact', array());
			if(count($contacts))
			{
				$contactInfos = array();
				foreach($contacts as $contact)
				{
					$contactInfos[] = $contact['name'].':'.$contact['phone'];
				}
				
				if(count($contactInfos))
				{
					//echo implode('|', $contactInfos);
					$this->getOnepage()->getQuote()->setData('contact_info', implode('|', $contactInfos));
				}
			}
            //credits
            $used_amount=Mage::helper('settleaccount/event')->getPrepareUsedCredit();
            if ($used_amount>0)
            {
                $this->getOnepage()->getQuote()->setData('credit_qty',$used_amount);
            }
            //point类似
            $used_points=Mage::helper('settleaccount/event')->getRewardPoints();
            if ($used_points>0)
            {
                $this->getOnepage()->getQuote()->setData('rewardpoints_qty',$used_points);
            }


			$quote = $this->getOnepage()->getQuote();
			$quote->collectTotals();

            $this->getOnepage()->saveOrder();

			Mage::helper('settleaccount/event')->setRewardPoints(0);
			Mage::helper('settleaccount/event')->setPrepareUsedCredit(0);

			if($quote->getGrandTotal() <= 0)
			{
                //保存购物车并清空!
               $this->getOnepage()->getQuote()->save();
               $lastOrder = Mage::getModel('sales/order')->loadByIncrementId($this->getOnepage()->getLastOrderId());
                if($lastOrder->getStatus() == 'pending')
                {
                    $message = Mage::helper('chinapay')->__('Payment accepted by Chinapay');
                    $lastOrder->addStatusToHistory(Mage_Sales_Model_Order::STATE_COMPLETE, $message);
                    $lastOrder->save();
                    Mage::helper('robi_checkout')->sendOrderSuccessNotice($lastOrder);

                }
                Mage::dispatchEvent('payment_accept_notify', array('order' => $lastOrder));
                //无需支付
				$redirectUrl = Mage::getUrl('checkout/onepage/success');
                $this->_redirectUrl($redirectUrl);
                return ;
			}

            $result['success'] = true;
            $result['error']   = false;
            
        }
        catch (Mage_Payment_Model_Info_Exception $e)
        {
            $message = $e->getMessage();
            if (!empty($message))
            {
                $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
            }
            
        }
        catch (Mage_Core_Exception $e)
        {
            Mage::logException($e);
            $message = $e->getMessage();
			$this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
        }
        catch (Exception $e)
        {
            Mage::logException($e);
            $message = $this->__('There was an error processing your order. Please contact us or try again later.');
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
        }
        
        $this->getOnepage()->getQuote()->save();

/*        Zend_Debug::dump($this->getOnepage()->getLastOrderId());
        exit();*/
        /* @var $order Mage_Sales_Model_Order */
        $lastOrder = Mage::getModel('sales/order')->loadByIncrementId($this->getOnepage()->getLastOrderId());

        //$order->loadByIncrementId($this->getOnepage()->getLastOrderId());
        $redirectUrl = $lastOrder->getPayment()->getMethodInstance()->getOrderPlaceRedirectUrl();
// die($redirectUrl);
        /**
         * when there is redirect to third party, we don't want to save order yet.
         * we will save the order in return action.
         */
        if (isset($redirectUrl))
        {
            //订单提交，提示用户完成支付，如果不支付还能回到我的用户
            $go=Mage::getUrl('checkout/cart/wait').'?pid='.$this->getRequest()->getparam('savepid','').'&qty='.$this->getRequest('sveqty')->getParam('saveqty','').'&url='.urlencode($redirectUrl);
            $this->_redirectUrl($go);
        //    $this->_redirectUrl($redirectUrl);


        }
        else
        {
        	$this->_redirect('checkout/cart');
        }

    }
    
	
}


?>
