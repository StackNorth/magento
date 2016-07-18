<?php
class D1m_Credits_CheckoutController extends Mage_Core_Controller_Front_Action
{
    
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        // a brute-force protection here would be nice

        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        if (!$this->_getSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
         
    }
    
   
   /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    
    function viewAction(){
        
        $this->loadLayout();
        
        $this->_initLayoutMessages('checkout/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('我的课点'));
        $this->renderLayout();
    }


    function savecreditsAction()
    {
    	
         if ($this->getRequest()->isPost())
         {

             //统一以 other_qty
            $credit_qty = $this->getRequest()->getParam('other_qty', 0);
            settype($credit_qty,"integer");

            if ($credit_qty<=0)
            {
                $message = Mage::helper('d1m_credits')->__('请输入或选择要购买的课点数');
                $this->_getCheckoutSession()->addError($message);
                $this->_redirect('credits/checkout/view');
                return;
            }


            $payment_method = $this->getRequest()->getParam('payment_method');

             // die($payment_method);
            
            $data = array();
            $data['qty'] = $credit_qty;
            $data['payment_method'] = $payment_method;

            
            Mage::getSingleton('checkout/session')->setCreditCheckoutData($data);

            $this->_redirect('credits/checkout/overview');
            
         }
         else
         {
         	$message = Mage::helper('d1m_credits')->__('Data saving problem');
         	$this->_getCheckoutSession()->addError($message);
         	$this->_redirect('credits/checkout/view');
         	
         }
         
    }
    
    function billingAction(){

        $creditCheckoutData = Mage::getSingleton('checkout/session')->getCreditCheckoutData();
        
        if($creditCheckoutData && isset($creditCheckoutData['qty']) && $creditCheckoutData['qty'] && isset($creditCheckoutData['payment_method']) && $creditCheckoutData['payment_method'])
        {
	        $this->loadLayout();
	        $this->_initLayoutMessages('checkout/session');
	        $this->renderLayout();
        	
        }
        else
        {
        	$message = Mage::helper('d1m_credits')->__('请输入正确的点数');
         	$this->_getCheckoutSession()->addError($message);
         	$this->_redirect('credits/checkout/view');
        }

    }
    
    function overviewAction(){

        $creditCheckoutData = Mage::getSingleton('checkout/session')->getCreditCheckoutData();


        if($creditCheckoutData && isset($creditCheckoutData['qty']) && $creditCheckoutData['qty'] 
        && isset($creditCheckoutData['payment_method']) && $creditCheckoutData['payment_method']
        )
        {
	        $this->loadLayout();
	        $this->_initLayoutMessages('checkout/session');
	        $this->renderLayout();
        	
        }
        else
        {
        	$message = Mage::helper('d1m_credits')->__('请输入购买课点数量，选择支付方式');
         	$this->_getCheckoutSession()->addError($message);
         	$this->_redirect('credits/checkout/view');
        }

    }
    
    public function validate($data)
    {
        $errors = array();
        if (!Zend_Validate::is( trim($data['firstname']) , 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The first name cannot be empty.');
        }

        if (!Zend_Validate::is( trim($data['lastname']) , 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The last name cannot be empty.');
        }

        if (!Zend_Validate::is($data['email'], 'EmailAddress')) {
            $errors[] = Mage::helper('customer')->__('Invalid email address "%s".', $data['email']);
        }
        
        if (!Zend_Validate::is( trim($data['city']) , 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The city cannot be empty.');
        }
        
        if (!Zend_Validate::is( trim($data['company']) , 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The company cannot be empty.');
        }
        
        if (!Zend_Validate::is( trim($data['zipcode']) , 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The zipcode cannot be empty.');
        }
        
        if (!Zend_Validate::is( trim($data['telephone']) , 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The telephone cannot be empty.');
        }
        
        if (!Zend_Validate::is( trim($data['street_address']) , 'NotEmpty')) {
            $errors[] = Mage::helper('customer')->__('The street address cannot be empty.');
        }
        
        return $errors;
        
        
    }
    
    function savebillingAction()
    {
    	
         if ($this->getRequest()->isPost()) {
            
            $billing = $this->getRequest()->getParam('billing', array());
            
            $errors = $this->validate($billing);
            
            $creditCheckoutData = Mage::getSingleton('checkout/session')->getCreditCheckoutData();
            
            $creditCheckoutData['billing'] = $billing;
            Mage::getSingleton('checkout/session')->setCreditCheckoutData($creditCheckoutData);
            
            if(count($errors))
            {
            	$message = Mage::helper('d1m_credits')->__('Please input the credit checkout billing information.');
         		$this->_getCheckoutSession()->addError(implode($errors, ','));
         		$this->_redirect('credits/checkout/billing');
            	
            }
            else
            {
            	$this->_redirect('credits/checkout/overview');
            }
    
         }
		else
        {
        	$message = Mage::helper('d1m_credits')->__('Please input the credit checkout information.');
         	$this->_getCheckoutSession()->addError($message);
         	$this->_redirect('credits/checkout/billing');
        }
        
    }
    
    
    public function saveorderAction()
    {
    	
    	$creditCheckoutData = Mage::getSingleton('checkout/session')->getCreditCheckoutData();
        $qty=$creditCheckoutData['qty'];
        settype($qty,"integer");
        if ($qty<=0)
        {
            $message = Mage::helper('d1m_credits')->__('购买数量必须大于0');
            $this->_getCheckoutSession()->addError($message);
            $this->_redirect('credits/checkout/overview');
            return ;


        }

    	if($creditCheckoutData && isset($creditCheckoutData['qty']) && $creditCheckoutData['qty'] 
        		&& isset($creditCheckoutData['payment_method']) && $creditCheckoutData['payment_method']
        )
        {
        	
            	
            	try
            	{
            		$order = Mage::getModel('d1m_credits/order');
            	
	            	$customer = $this->_getSession()->getCustomer();
	            	$order->setCustomer($customer);
                    //var_dump($creditCheckoutData);die();
                    /* @var $order D1m_Credits_Model_Order */
	            	$order->initOrderData($creditCheckoutData);




	            	$order->save();

                    // var_dump($order);                    die();
	            	
	            	Mage::getSingleton('checkout/session')->setLastRealCreditOrderId($order->getId());
	            	
	            	$redirectUrl = $order->getPaymentPlacedUrl();
                    // die($redirectUrl);

                    $this->_getCheckoutSession()->setCreditLastOrderId($order->getId())
                        ->setCreditRedirectUrl($redirectUrl)		                ;

                    //订单提交，提示用户完成支付，如果不支付还能回到我的用户
                    $go=Mage::getUrl('credits/checkout/wait').'?url='.urlencode($redirectUrl);
                    $this->_redirectUrl($go);

	            	//$this->_redirectUrl($redirectUrl);
	            	
            	}
            	catch(Execption $e)
            	{
            		Mage::logException($e);
            		
            		$message = Mage::helper('d1m_credits')->__('save error, please try again.');
         			$this->_getCheckoutSession()->addError($message);
         			$this->_redirect('credits/checkout/overview');
            		
            	}
            	
            }
            else
            {
            	$this->_redirect('credits/checkout/view');
            }
        	
        
    }


    //gao 课点支付成功
    function successAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('课点支付成功'));
        $this->renderLayout();
    }

    public function waitAction()
    {
        //等待支付
        $this->loadLayout()
            ->_initLayoutMessages('checkout/session')
            ->getLayout()->getBlock('head')->setTitle($this->__('等待支付'));
        $this->renderLayout();


    }
   
    
}