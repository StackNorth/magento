<?php

class D1m_Credits_Block_Billing extends Mage_Core_Block_Template
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
    
    public function getGiftCredits()
    {
    	$qty = $this->getQty();
    	return Mage::helper('d1m_credits')->getGiftCredits($qty);
    }
    
    public function getGiftCreditsAmount()
    {
    	$freeQty = $this->getGiftCredits() * $this->getFeeByCredit();
    	return $freeQty;
    }
    
    public function getCreditGrandTotal()
    {
    	return $this->getQtySubtotal() - $this->getGiftCreditsAmount();
    }
    
    
    public function getFeeByCredit()
    {
        return Mage::helper('d1m_credits')->getFeeByCredit();
        
    }
    
    public function getQty()
    {
    	$creditCheckoutData = $this->getCreditCheckoutData();
    	$qty =  isset($creditCheckoutData['qty']) ? (int)$creditCheckoutData['qty'] : 10;
    	if($qty <= 0 )
    		$qty = 10;
    	return $qty;
    }
    
    public function getQtySubtotal()
    {
    	return $this->getQty() * $this->getFeeByCredit();
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
        return Mage::helper('d1m_credits')->getCreditAmountByCustomerId($customer->getId());
        
    }
}