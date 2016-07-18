<?php

class D1m_Invitation_Block_Form extends Mage_Core_Block_Template
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
    
    
    
    public function getCustomer()
    {
    	return Mage::getSingleton('customer/session')->getCustomer();
    }
    
    public function getFormData()
    {
    	$data = Mage::getSingleton('customer/session')->getInvitationFormData();
    	return $data ? $data : array();
    }


}