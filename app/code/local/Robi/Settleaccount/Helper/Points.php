<?php

class Robi_Settleaccount_Helper_Points extends Mage_Core_Helper_Abstract
{
    
    public function getCustomer()
    {
    	return Mage::getSingleton('customer/session')->getCustomer();
    }


    public function getCreditsByCustomer()
    {
        
    }
    
    public function getCurrentPoints()
    {
    	$customer = $this->getCustomer();
        return Mage::helper('d1m_integral')->getCreditAmountByCustomerId($customer->getId());
    }


    
}