<?php

class Robi_Settleaccount_Model_Quote_Total_Rewardpoints extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    
     public function __construct()
    {
        $this->setCode('rewardpoints');
    }
    
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
    	parent::collect($address);
    	
    	$this->_setAmount(0)
            ->_setBaseAmount(0);
            
         $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }
        
        $address->setRewardpointsAmount(0);
		$address->setBaseRewardpointsAmount(0);
    	
        $quote = $address->getQuote();
        $this->_quote = $address->getQuote();
        
        $this->_quote->setUsedRewardpointsAmount(0);
		
		if (!Mage::app()->getStore()->isAdmin()) 
		{
			if (!Mage::getSingleton('customer/session')->isLoggedIn())
        		return false;
		}
		
        $points_apply = (int) Mage::helper('settleaccount/event')->getRewardPoints();
        
        $customer = $quote->getCustomer();
        $customerId = $customer->getId();
        
		$amount = 0;
		
		//echo '$points_apply:'.$points_apply.'<br/>';
		
        if ($points_apply > 0 && $customerId != null){
            
            $test_points = $this->checkMaxPointsToApply($points_apply);
            
            if ($points_apply > $test_points){
                
                $points_apply = $test_points;
                Mage::helper('settleaccount/event')->setRewardPoints($points_apply);
                
            }
            
            $points_apply_amount = Mage::helper('settleaccount/data')->convertPointsToMoney($points_apply);
            
            if ($points_apply > Mage::helper('settleaccount/points')->getCurrentPoints()){
                Mage::helper('settleaccount/event')->setRewardPoints(null);
                return false;
            } else {
                $amount = $points_apply_amount;
            }
         
         }
         
        //$address->setRewardpointsAmount($amount);
		//$address->setBaseRewardpointsAmount($amount);
		
 		if ( $amount > 0) {
			$address->addTotalAmount($this->getCode(),-$amount );
        	$address->addBaseTotalAmount($this->getCode(), -$amount);	
        	$this->_quote->setUsedRewardpointsAmount($amount);	
		}
	
        
        return $this;
    }
    
    public function checkMaxPointsToApply($points){
        
        $totalPrices = $this->_quote->getTotals();

        $tax = 0;
        if (isset($totalPrices['tax'])){
            $tax_val = $totalPrices['tax'];
            $tax = $tax_val->getData('value');
        }
		
		$discountPrice  = isset($totalPrices['discount']) ? $totalPrices['discount'] : null;
		$order_discount = $discountPrice ? $discountPrice->getData('value') : 0;
		
		$credit = isset($totalPrices['credit']) ? $totalPrices['credit'] : null;
		$creditAmount = $credit ? $credit->getData('value') : 0;
		
        $subtotalPrice = $totalPrices['subtotal'];
        $order_details = $subtotalPrice->getData('value') + $tax + $order_discount + $creditAmount;
		
        $cart_amount = floor($order_details);
        
        $pointsToMoenyAmount = Mage::helper('settleaccount/data')->convertMoneyToPoints($cart_amount);
        
        $maxpoints = min($pointsToMoenyAmount, $points);
		
        //return $points;
        return $maxpoints;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
         
        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }
         
         $amount = $address->getRewardpointsAmount();
         
         if($amount != 0)
         {
         	$title =  Mage::helper('settleaccount')->__('Points');
         	
            $address->addTotal(array(
                'code'=>  $this->getCode(),
                'title'=> $title,
                'value'=> $amount
            ));
         }
        
        return $this;
    }

}
