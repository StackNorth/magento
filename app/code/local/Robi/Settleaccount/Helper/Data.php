<?php

class Robi_Settleaccount_Helper_Data extends Mage_Core_Helper_Abstract {
	
	const POINTS_TO_MOENY_RATE = 100;
	
    public function getReferalUrl()
    {
        return $this->_getUrl('settleaccount/');
    }
    

    public function convertMoneyToPoints($money){
        $points_to_get_money = Robi_Settleaccount_Helper_Data::POINTS_TO_MOENY_RATE;
        $money_amount = floor($money*$points_to_get_money);
        return $money_amount;
    }

    public function convertPointsToMoney($points_to_be_used){
        
        $customerId = Mage::getModel('customer/session')->getCustomerId();
        $current = Mage::helper('settleaccount/points')->getCurrentPoints();

        if ($current < $points_to_be_used) {
            Mage::getSingleton('checkout/session')->addError('积分余额不足。');
            Mage::helper('settleaccount/event')->setCreditPoints(0);
            return 0;
        }
        
        $points_to_get_money = Robi_Settleaccount_Helper_Data::POINTS_TO_MOENY_RATE;
       
        $discount_amount = floor($points_to_be_used/$points_to_get_money);
        
        return $discount_amount;
    }

    public function getPointsOnOrder(){
        $cartHelper = Mage::helper('checkout/cart');
        $items = $cartHelper->getCart()->getItems();
        $rewardPoints = 0;


        $rules = Mage::getModel('rewardpoints/rules')->getPointsByRule();
        $cart_amount = 0;

        foreach ($items as $_item){
            $_product = Mage::getModel('catalog/product')->load($_item->getProductId());
            $product_points = $_product->getData('reward_points');

            if ($product_points > 0){
                if ($_item->getQty() > 0){
                    $rewardPoints += (int)$product_points * $_item->getQty();

                }
            } else {
                $price = $_item->getRowTotal() + $_item->getTaxAmount() - $_item->getDiscountAmount();
                $rewardPoints += (int)Mage::getStoreConfig('rewardpoints/default/money_points', Mage::app()->getStore()->getId()) * $price;
            }
            $cart_amount += $_item->getRowTotal() + $_item->getTaxAmount() - $_item->getDiscountAmount();

            if ($rules != array()){
                foreach ($rules as $rule){
                    if ($rule['type'] == RewardPoints_Model_Rules::TARGET_SKU){
                        if ($_product->getSku() == $rule['test_value']){
                            $rewardPoints += (int)$rule['points'] * $_item->getQty();
                        }
                    }
                }
            }
        }

        if ($cart_amount > 0){
            if ($rules != array()){
                foreach ($rules as $rule){
                    if ($rule['type'] == RewardPoints_Model_Rules::TARGET_CART){
                        switch ($rule['operator']){
                            case RewardPoints_Model_Rules::OPERATOR_1: // =
                                if ($cart_amount == $rule['test_value']){
                                    $rewardPoints += (int)$rule['points'];
                                }
                                break;
                            case RewardPoints_Model_Rules::OPERATOR_2: // <
                                if ($cart_amount < $rule['test_value']){
                                    $rewardPoints += (int)$rule['points'];
                                }
                                break;
                            case RewardPoints_Model_Rules::OPERATOR_3: // <=
                                if ($cart_amount <= $rule['test_value']){
                                    $rewardPoints += (int)$rule['points'];
                                }
                                break;
                            case RewardPoints_Model_Rules::OPERATOR_4: // >
                                if ($cart_amount > $rule['test_value']){
                                    $rewardPoints += (int)$rule['points'];
                                }
                                break;
                            case RewardPoints_Model_Rules::OPERATOR_5: // >=
                                if ($cart_amount >= $rule['test_value']){
                                    $rewardPoints += (int)$rule['points'];
                                }
                                break;
                            case RewardPoints_Model_Rules::OPERATOR_6: // Between
                                $test_values = explode(";",$rule['test_value']);
                                if ($cart_amount >= (int)$test_values[0] && $cart_amount <= (int)$test_values[1]){
                                    $rewardPoints += (int)$rule['points'];
                                }
                                break;
                        }

                    }
                }
            }
        }
        
        //added by robin at 2011/7/14 ,try to get the giftcert amount
        $giftcertAmount = 0;
        $addresses = $cartHelper->getCart()->getQuote()->getAllAddresses();
        foreach($addresses as $address)
        {
        	$addressType = $address->getAddressType();
	        if ($addressType=='billing' && !$cartHelper->getCart()->getQuote()->isVirtual()) {
	           continue;
	        }
	        $items = $address->getAllItems($address);
	        if (!count($items)) {
	            continue;
	        }
	        
	        $giftcertAmount = $address->getGiftcertAmount();
	        $pointsAmount   = $address->getRewardpointsAmount();
	        $discountAmount = $address->getDiscountAmount();
        }
        
        /**
        $points_apply = (int) Mage::helper('rewardpoints/event')->getCreditPoints();
        if($points_apply > 0 )
        	$points_apply_amount = Mage::helper('rewardpoints/data')->convertPointsToMoney($points_apply);
        else
        	$points_apply_amount = 0;
        **/
        
        $rewardPoints = $rewardPoints  - abs($giftcertAmount) - abs($pointsAmount) ;
        
        if($rewardPoints < 0 )
        	$rewardPoints = 0;

        if (Mage::getStoreConfig('rewardpoints/default/math_method', Mage::app()->getStore()->getId()) == 1){
            $rewardPoints = round($rewardPoints);
        } else {
            $rewardPoints = floor($rewardPoints);
        }


        return $rewardPoints;
    }
    
    
    public function getPointsTypes()
    {
    	
    	$customer = Mage::getSingleton('customer/session')->getCustomer();
    	
    	$points = Mage::getResourceModel('rewardpoints/stats_collection')
            		->addClientFilter(Mage::getSingleton('customer/session')->getCustomer()->getId())
            		->addGroupByIntro();
    	
    	$result = array();
    	foreach($points as $point)
    	{
    		$result[] = $point->getPointsContent();
    	}
    	
    	return $result;
    }


}
