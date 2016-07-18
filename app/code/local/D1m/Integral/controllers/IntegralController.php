<?php
class D1m_Integral_IntegralController extends Mage_Core_Controller_Front_Action
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
        
        $this->renderLayout();
    }
    
    function savecreditsAction()
    {
    	
         if ($this->getRequest()->isPost()) {
            
            $credit_qty = $this->getRequest()->getParam('credit_qty', 10);
            $payment_method = $this->getRequest()->getParam('payment_method', 'alipay_payment');
            
            
            $data = array();
            $data['qty'] = $credit_qty;
            $data['payment_method'] = $payment_method;
            
            Mage::getSingleton('checkout/session')->setCreditCheckoutData($data);
            
            $this->_redirect('credits/checkout/billing');
            
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
        	$message = Mage::helper('d1m_credits')->__('Please input the credit checkout information.');
         	$this->_getCheckoutSession()->addError($message);
         	$this->_redirect('credits/checkout/view');
        }

    }
    
    function overviewAction(){
        
        
        $creditCheckoutData = Mage::getSingleton('checkout/session')->getCreditCheckoutData();
        
        if($creditCheckoutData && isset($creditCheckoutData['qty']) && $creditCheckoutData['qty'] 
        && isset($creditCheckoutData['payment_method']) && $creditCheckoutData['payment_method']
        && isset($creditCheckoutData['billing']) && $creditCheckoutData['billing']
        )
        {
	        $this->loadLayout();
	        $this->_initLayoutMessages('checkout/session');
	        $this->renderLayout();
        	
        }
        else
        {
        	$message = Mage::helper('d1m_credits')->__('Please input the credit checkout information.');
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
    	if($creditCheckoutData && isset($creditCheckoutData['qty']) && $creditCheckoutData['qty'] 
        		&& isset($creditCheckoutData['payment_method']) && $creditCheckoutData['payment_method']
        		&& isset($creditCheckoutData['billing']) && $creditCheckoutData['billing']
        )
        {
        	$errors = $this->validate($creditCheckoutData['billing']);
        	
        	if(count($errors))
            {
            	$message = Mage::helper('d1m_credits')->__('Please input the credit checkout billing information.');
         		$this->_getCheckoutSession()->addError(implode($errors, ','));
         		$this->_redirect('credits/checkout/billing');
            	
            }
            else
            {
            	
            	try
            	{
            		$order = Mage::getModel('d1m_credits/order');
            	
	            	$customer = $this->_getSession()->getCustomer();
	            	$order->setCustomer($customer);
	            	$order->initOrderData($creditCheckoutData);
	            	
	            	$order->save();
	            	
	            	$redirectUrl = $order->getPaymentPlacedUrl();
	            	
	            	$this->_getCheckoutSession()->setCreditLastOrderId($order->getId())
		                 ->setCreditRedirectUrl($redirectUrl)
		                ;
	            	
	            	$this->_redirectUrl($redirectUrl);
	            	
            	}
            	catch(Execption $e)
            	{
            		Mage::logException($e);
            		
            		$message = Mage::helper('d1m_credits')->__('save error, please try again.');
         			$this->_getCheckoutSession()->addError($message);
         			$this->_redirect('credits/checkout/overview');
            		
            	}
            	
            }
        	
        	
        	
        }
        else
        {
        	$message = Mage::helper('d1m_credits')->__('Please input the credit checkout information.');
         	$this->_getCheckoutSession()->addError($message);
         	$this->_redirect('credits/checkout/view');
        }
    }
    
    
}