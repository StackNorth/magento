<?php

class Robi_Checkout_Block_Billing extends Mage_Core_Block_Template
{
        
     public function __construct()
    {
        parent::__construct();
    }
    
    
        /**
     * Add meta information from product to head block
     *
     * @return Mage_Catalog_Block_Product_View
     */
    protected function _prepareLayout()
    {
        
        $headBlock = $this->getLayout()->getBlock('head');
      
         return parent::_prepareLayout();
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
    
 	public function getBillingPostData()
 	{
 		$_session = $this->_getSession();
 		return $_session->getBillingFormData();
 	}
    
    public function getCreditCheckoutData()
    {
    	return Mage::getSingleton('checkout/session')->getCreditCheckoutData();
    }
    
    public function getCustomer()
    {
    	return Mage::getSingleton('customer/session')->getCustomer();
    }
    
    public function getDefaultBillingAddress()
    {
    	$customer = $this->getCustomer();
    	$billingaddress = $customer->getPrimaryBillingAddress();
    	$address = array();
    	if($billingaddress)
    	{
    		$address['has_address'] = true;
    		
    		$address['data']['firstname'] = $billingaddress->getFirstname();
    		$address['data']['lastname'] =  $billingaddress->getLastname();
    		$address['data']['company'] =  $billingaddress->getCompany();
    		$address['data']['city'] =  $billingaddress->getCity();
    		$address['data']['email'] =  $customer->getEmail();
    		$address['data']['zipcode'] =  $billingaddress->getPostcode();
    		$address['data']['telephone'] =  $billingaddress->getTelephone();
    		$streets =  $billingaddress->getStreet();
    		$address['data']['street_address'] =  isset($streets[0]) ? $streets[0] : '';
    	}
    	else
    	{
    		$address['has_address'] = false;
    		$address['data'] = array();
    	}
    	
    	return $address;
    }
    
    public function getPostBillingAddress()
    {
    	$creditCheckoutData = Mage::getSingleton('checkout/session')->getCreditCheckoutData();
    	
    	$billing =  isset($creditCheckoutData['billing']) ? $creditCheckoutData['billing'] : array();
    	
    	$data = array();
    	$data['firstname'] = isset($billing['firstname']) ? $billing['firstname'] : '';
    	$data['lastname'] = isset($billing['lastname']) ? $billing['lastname'] : '';
    	$data['company'] = isset($billing['company']) ? $billing['company'] : '';
    	$data['city'] = isset($billing['city']) ? $billing['city'] : '';
    	$data['email'] = isset($billing['email']) ? $billing['email'] : '';
    	$data['zipcode'] = isset($billing['zipcode']) ? $billing['zipcode'] : '';
    	$data['telephone'] = isset($billing['telephone']) ? $billing['telephone'] : '';
    	$data['street_address'] = isset($billing['street_address']) ? $billing['street_address'] : '';
    	
    	return $data;
    }


    public function getCreditsByCustomer()
    {
    	
        $customer = $this->getCustomer();
        return Mage::helper('robi_checkout')->getCreditAmountByCustomerId($customer->getId());
        
    }
}