<?php

class D1m_Credits_Block_Overview extends Mage_Core_Block_Template
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
    
    
    public function getFeeByCredit()
    {
        return Mage::helper('d1m_credits')->getFeeByCredit();
        
    }
    
    public function getQty()
    {
    	$creditCheckoutData = $this->getCreditCheckoutData();
    	return isset($creditCheckoutData['qty']) ? (int)$creditCheckoutData['qty'] : 10;
    }

    public function getPaymentMethod()
    {
        $creditCheckoutData = $this->getCreditCheckoutData();
        return isset($creditCheckoutData['payment_method']) ? $creditCheckoutData['payment_method'] : 'chinapay_payment';
    }
    
    public function getQtySubtotal()
    {
    	return $this->getQty() * $this->getFeeByCredit();
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
    
    
    public function getCreditCheckoutData()
    {
    	return Mage::getSingleton('checkout/session')->getCreditCheckoutData();
    }
    
    public function getCustomer()
    {
    	return Mage::getSingleton('customer/session')->getCustomer();
    }
    

    
    public function getBillingAddress()
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