<?php

class D1m_CouponRule_Model_SalesRule_Condition_Product extends Mage_SalesRule_Model_Rule_Condition_Product
{

 

    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
 
    	/*
    	 * check this rule condition is available for original
    	 */
    	if( Mage::helper('couponRule')->enableSaleRuleOriginalPrice( Mage::registry("current_validate_salerule")  )==true ){
    		
    		if( Mage::getSingleton('couponRule/improvesalerule')->isOriginalPrice( $object->getProductId() )==false ){
    			return false;
    		}
    	}
    	
        
        return parent::validate($object);
    }
}
