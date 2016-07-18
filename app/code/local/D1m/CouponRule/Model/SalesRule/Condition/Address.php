<?php

class D1m_CouponRule_Model_SalesRule_Condition_Address extends Mage_SalesRule_Model_Rule_Condition_Address
{
 
    /**
     * Validate Address Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $address = $object;
        if (!$address instanceof Mage_Sales_Model_Quote_Address) {
            if ($object->getQuote()->isVirtual()) {
                $address = $object->getQuote()->getBillingAddress();
            }
            else {
                $address = $object->getQuote()->getShippingAddress();
            }
        }

        if ('payment_method' == $this->getAttribute() && ! $address->hasPaymentMethod()) {
            $address->setPaymentMethod($object->getQuote()->getPayment()->getMethod());
        }

        if( Mage::helper('couponRule')->enableSaleRuleOriginalPrice( Mage::registry("current_validate_salerule")  ) ){
        	
	        $base_subtotal=0.0;
	        $total_qty=0;
	        $weight=0.0;
	        $items=$address->getAllNonNominalItems();
	        foreach( $items as $item ){
	        	if (!$item->isDeleted() && !$item->getParentItemId()) {
		        	if( Mage::getSingleton('couponRule/improvesalerule')->isOriginalPrice( $item->getProductId() )==true ){
		        		$base_subtotal+= $item->getRowTotal();
		        		$total_qty+= $item->getData("qty");
		        		$weight+=$item->getData("weight");
		        	}
	        	}
	        }
	        
	        $address->setData( "base_subtotal",$base_subtotal );
	        $address->setData( "total_qty",$total_qty );
	        $address->setData( "weight",$weight );
	        
        }
        
        return parent::validate($address);
    }
}
