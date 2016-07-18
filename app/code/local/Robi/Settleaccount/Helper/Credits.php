<?php

class Robi_Settleaccount_Helper_Credits extends Mage_Core_Helper_Abstract
{
    
    public function getCustomer()
    {
    	return Mage::getSingleton('customer/session')->getCustomer();
    }


    public function getCreditsByCustomer()
    {
    	
        $customer = $this->getCustomer();
        return Mage::helper('d1m_credits')->getCreditAmountByCustomerId($customer->getId());
        
    }
	


    
}