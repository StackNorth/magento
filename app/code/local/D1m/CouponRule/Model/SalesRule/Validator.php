<?php

class D1m_CouponRule_Model_SalesRule_Validator extends Mage_SalesRule_Model_Validator
{
 
	//key: item_id, value: item price
	protected $_itemPriceCache=array();
	
	//key:rule_id,value: array() for the cheapest item ids for this rule
	protected $_cheapestItemIdsInRule=array();
	
	//all available items for one rules
	protected $_availableItemsForRule=array();
	
	//cache the rule of rule action validate
	protected $_ruleActionValidate=array();
	
	//key: rule_id, value: array of categoryItemIdsResult
	protected $_categoryItemIdsResultCache=array();
	
	//key: rule id, value: fixed product price of this rule
	protected $_productsFixedPrice=array();
	
    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param   Mage_SalesRule_Model_Rule $rule
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  bool
     */
    protected function _canProcessRule($rule, $address)
    {
    	Mage::unregister("current_validate_salerule");
    	Mage::register("current_validate_salerule", $rule->getId());
    	
        if ($rule->hasIsValidForAddress($address) && !$address->isObjectNew()) {
            return $rule->getIsValidForAddress($address);
        }

        /**
         * check per coupon usage limit
         */
        if ($rule->getCouponType() != Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON) {
            $couponCode = $address->getQuote()->getCouponCode();
            if ($couponCode) {
                $coupon = Mage::getModel('salesrule/coupon');
                $coupon->load($couponCode, 'code');
                if ($coupon->getId()) {
                    // check entire usage limit
                    if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
                        $rule->setIsValidForAddress($address, false);
                        return false;
                    }
                    // check per customer usage limit
                    $customerId = $address->getQuote()->getCustomerId();
                    if ($customerId && $coupon->getUsagePerCustomer()) {
                        $couponUsage = new Varien_Object();
                        Mage::getResourceModel('salesrule/coupon_usage')->loadByCustomerCoupon(
                            $couponUsage, $customerId, $coupon->getId());
                        if ($couponUsage->getCouponId() &&
                            $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()
                        ) {
                            $rule->setIsValidForAddress($address, false);
                            return false;
                        }
                    }
                }
            }
        }

        /**
         * check per rule usage limit
         */
        $ruleId = $rule->getId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId     = $address->getQuote()->getCustomerId();
            $ruleCustomer   = Mage::getModel('salesrule/rule_customer');
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    $rule->setIsValidForAddress($address, false);
                    return false;
                }
            }
        }
        $rule->afterLoad();
        /**
         * quote does not meet rule's conditions
         */
        if (!$rule->validate($address)) {
            $rule->setIsValidForAddress($address, false);
            return false;
        }
        /**
         * passed all validations, remember to be valid
         */
        $rule->setIsValidForAddress($address, true);
        return true;
    }
	
	
    /**
     * Quote item discount calculation process
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_SalesRule_Model_Validator
     */
    public function process(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $item->setDiscountAmount(0);
        $item->setBaseDiscountAmount(0);
        $item->setDiscountPercent(0);
        $quote      = $item->getQuote();
        $address    = $this->_getAddress($item);

        $itemPrice              = $this->_getItemPrice($item);
        $baseItemPrice          = $this->_getItemBasePrice($item);
        $itemOriginalPrice      = $item->getOriginalPrice();
        $baseItemOriginalPrice  = $item->getBaseOriginalPrice();

        
        if ($itemPrice <= 0) {
            return $this;
        }

        $appliedRuleIds = array();
        foreach ($this->_getRules() as $rule) {
            /* @var $rule Mage_SalesRule_Model_Rule */
        	
        	Mage::unregister("current_validate_salerule");
    		Mage::register("current_validate_salerule", $rule->getId());
        	
            if (!$this->_canProcessRule($rule, $address)) {
                continue;
            }

            if ($this->checkActionValidate( $item,$rule )==false) {
                continue;
            }

            //if this rule is set to only be available for original price and the product's price is discount
            if( $rule->getData('just_for_original_price')==D1m_CouponRule_Model_Improvesalerule::AVAILABLE_ORIGINAL_PRICE
              && Mage::getSingleton('couponRule/improvesalerule')->isOriginalPrice( $item->getProductId() )==false ){
            	
            	continue;
            }
            
            $qty = $this->_getItemQty($item, $rule);

            
            $rulePercent = min(100, $rule->getDiscountAmount());

            $discountAmount = 0;
            $baseDiscountAmount = 0;
            //discount for original price
            $originalDiscountAmount = 0;
            $baseOriginalDiscountAmount = 0;
            
//			if( $this->checkIsCheaperItem( $item,$rule )==false ){
//				continue;
//			}
            

            switch ($rule->getSimpleAction()) {
                case Mage_SalesRule_Model_Rule::TO_PERCENT_ACTION:
                    $rulePercent = max(0, 100-$rule->getDiscountAmount());
                //no break;
                case Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION:
                    $step = $rule->getDiscountStep();
                    if ($step) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $_rulePct = $rulePercent/100;
                    $discountAmount    = ($qty*$itemPrice - $item->getDiscountAmount()) * $_rulePct;
                    $baseDiscountAmount= ($qty*$baseItemPrice - $item->getBaseDiscountAmount()) * $_rulePct;
                    //get discount for original price
                    $originalDiscountAmount    = ($qty*$itemOriginalPrice - $item->getDiscountAmount()) * $_rulePct;
                    $baseOriginalDiscountAmount= ($qty*$baseItemOriginalPrice - $item->getDiscountAmount()) * $_rulePct;

                    if (!$rule->getDiscountQty() || $rule->getDiscountQty()>$qty) {
                        $discountPercent = min(100, $item->getDiscountPercent()+$rulePercent);
                        $item->setDiscountPercent($discountPercent);
                    }
                    break;
                case Mage_SalesRule_Model_Rule::TO_FIXED_ACTION:
                    $quoteAmount = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount    = $qty*($itemPrice-$quoteAmount);
                    $baseDiscountAmount= $qty*($baseItemPrice-$rule->getDiscountAmount());
                    //get discount for original price
                    $originalDiscountAmount    = $qty*($itemOriginalPrice-$quoteAmount);
                    $baseOriginalDiscountAmount= $qty*($baseItemOriginalPrice-$rule->getDiscountAmount());
                    break;

                case Mage_SalesRule_Model_Rule::BY_FIXED_ACTION:
                    $step = $rule->getDiscountStep();
                    if ($step) {
                        $qty = floor($qty/$step)*$step;
                    }
                    $quoteAmount        = $quote->getStore()->convertPrice($rule->getDiscountAmount());
                    $discountAmount     = $qty*$quoteAmount;
                    $baseDiscountAmount = $qty*$rule->getDiscountAmount();
                    break;

                case Mage_SalesRule_Model_Rule::CART_FIXED_ACTION:
                    if (empty($this->_rulesItemTotals[$rule->getId()])) {
                        Mage::throwException(Mage::helper('salesrule')->__('Item totals are not set for rule.'));
                    }

                    /**
                     * prevent applying whole cart discount for every shipping order, but only for first order
                     */
                    if ($quote->getIsMultiShipping()) {
                        $usedForAddressId = $this->getCartFixedRuleUsedForAddress($rule->getId());
                        if ($usedForAddressId && $usedForAddressId != $address->getId()) {
                            break;
                        } else {
                            $this->setCartFixedRuleUsedForAddress($rule->getId(), $address->getId());
                        }
                    }
                    $cartRules = $address->getCartFixedRules();
                    if (!isset($cartRules[$rule->getId()])) {
                        $cartRules[$rule->getId()] = $rule->getDiscountAmount();
                    }

                    if ($cartRules[$rule->getId()] > 0) {
                        if ($this->_rulesItemTotals[$rule->getId()]['items_count'] <= 1) {
                            $quoteAmount = $quote->getStore()->convertPrice($cartRules[$rule->getId()]);
                            $baseDiscountAmount = min($baseItemPrice * $qty, $cartRules[$rule->getId()]);
                        } else {
                            $discountRate = $baseItemPrice * $qty /
                                            $this->_rulesItemTotals[$rule->getId()]['base_items_price'];
                            $maximumItemDiscount = $rule->getDiscountAmount() * $discountRate;
                            $quoteAmount = $quote->getStore()->convertPrice($maximumItemDiscount);

                            $baseDiscountAmount = min($baseItemPrice * $qty, $maximumItemDiscount);
                            $this->_rulesItemTotals[$rule->getId()]['items_count']--;
                        }

                        $discountAmount = min($itemPrice * $qty, $quoteAmount);
                        $discountAmount = $quote->getStore()->roundPrice($discountAmount);
                        $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);

                        //get discount for original price
                        $originalDiscountAmount = min($itemOriginalPrice * $qty, $quoteAmount);
                        $baseOriginalDiscountAmount = $quote->getStore()->roundPrice($originalDiscountAmount);
                        $baseOriginalDiscountAmount = $quote->getStore()->roundPrice($baseItemOriginalPrice);

                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }
                    $address->setCartFixedRules($cartRules);

                    break;                	
                	
                /*
                 * there are two conditions for this rules:
                 * 1. Priority must be highest
                 * 2. catagory can just apply for this rule
                 */    
                case "fixed_price_for_products":
                    if (empty($this->_rulesItemTotals[$rule->getId()])) {
                        Mage::throwException(Mage::helper('salesrule')->__('Item totals are not set for rule.'));
                    }

                    /**
                     * prevent applying whole cart discount for every shipping order, but only for first order
                     */
                    if ($quote->getIsMultiShipping()) {
                        $usedForAddressId = $this->getCartFixedRuleUsedForAddress($rule->getId());
                        if ($usedForAddressId && $usedForAddressId != $address->getId()) {
                            break;
                        } else {
                            $this->setCartFixedRuleUsedForAddress($rule->getId(), $address->getId());
                        }
                    }
                    $cartRules = $address->getCartFixedRules();
                    
                    if (!isset($cartRules[$rule->getId()])) {
                    		
                    		//all available items price
                    		$allAvailableItemsAmount=$this->getAllItemsPriceForFixedPrice( $item, $rule );
                    		if( $allAvailableItemsAmount>$rule->getDiscountAmount() ){
                    			$cartRules[$rule->getId()]=$allAvailableItemsAmount-$rule->getDiscountAmount();
                    			$this->_productsFixedPrice[$rule->getId()]=$allAvailableItemsAmount-$rule->getDiscountAmount();
                    		}
                    		else{
                    			break;
                    		}
                    		
                    }

                    if ($cartRules[$rule->getId()] > 0) {
                        if ($this->_rulesItemTotals[$rule->getId()]['items_count'] <= 1) {
                            $quoteAmount = $quote->getStore()->convertPrice($cartRules[$rule->getId()]);
                            $baseDiscountAmount = min($baseItemPrice * $qty, $cartRules[$rule->getId()]);
                        } else {
                            $discountRate = $baseItemPrice * $qty /
                                            $this->_rulesItemTotals[$rule->getId()]['base_items_price'];
                            $maximumItemDiscount = $this->_productsFixedPrice[$rule->getId()] * $discountRate;
                            $quoteAmount = $quote->getStore()->convertPrice($maximumItemDiscount);

                            $baseDiscountAmount = min($baseItemPrice * $qty, $maximumItemDiscount);
                            $this->_rulesItemTotals[$rule->getId()]['items_count']--;
                        }

                        $discountAmount = min($itemPrice * $qty, $quoteAmount);
                        $discountAmount = $quote->getStore()->roundPrice($discountAmount);
                        $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);

                        //get discount for original price
                        $originalDiscountAmount = min($itemOriginalPrice * $qty, $quoteAmount);
                        $baseOriginalDiscountAmount = $quote->getStore()->roundPrice($originalDiscountAmount);
                        $baseOriginalDiscountAmount = $quote->getStore()->roundPrice($baseItemOriginalPrice);

                        $cartRules[$rule->getId()] -= $baseDiscountAmount;
                    }
                    $address->setCartFixedRules($cartRules);

                    break;

                case Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION:
                    $x = $rule->getDiscountStep();
                    $y = $rule->getDiscountAmount();
                    if (!$x || $y>=$x) {
                        break;
                    }
                    $buyAndDiscountQty = $x + $y;

                    $fullRuleQtyPeriod = floor($qty / $buyAndDiscountQty);
                    $freeQty  = $qty - $fullRuleQtyPeriod * $buyAndDiscountQty;

                    $discountQty = $fullRuleQtyPeriod * $y;
                    if ($freeQty > $x) {
                        $discountQty += $freeQty - $x;
                    }

                    $discountAmount    = $discountQty * $itemPrice;
                    $baseDiscountAmount= $discountQty * $baseItemPrice;
                    //get discount for original price
                    $originalDiscountAmount    = $discountQty * $itemOriginalPrice;
                    $baseOriginalDiscountAmount= $discountQty * $baseItemOriginalPrice;
                    break;
            }

            $result = new Varien_Object(array(
                'discount_amount'      => $discountAmount,
                'base_discount_amount' => $baseDiscountAmount,
            ));
            Mage::dispatchEvent('salesrule_validator_process', array(
                'rule'    => $rule,
                'item'    => $item,
                'address' => $address,
                'quote'   => $quote,
                'qty'     => $qty,
                'result'  => $result,
            ));

            $discountAmount = $result->getDiscountAmount();
            $baseDiscountAmount = $result->getBaseDiscountAmount();

            $percentKey = $item->getDiscountPercent();
            /**
             * Process "delta" rounding
             */
            if ($percentKey) {
                $delta      = isset($this->_roundingDeltas[$percentKey]) ? $this->_roundingDeltas[$percentKey] : 0;
                $baseDelta  = isset($this->_baseRoundingDeltas[$percentKey])
                        ? $this->_baseRoundingDeltas[$percentKey]
                        : 0;
                $discountAmount+= $delta;
                $baseDiscountAmount+=$baseDelta;

                $this->_roundingDeltas[$percentKey]     = $discountAmount -
                                                          $quote->getStore()->roundPrice($discountAmount);
                $this->_baseRoundingDeltas[$percentKey] = $baseDiscountAmount -
                                                          $quote->getStore()->roundPrice($baseDiscountAmount);
                $discountAmount = $quote->getStore()->roundPrice($discountAmount);
                $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
            } else {
                $discountAmount     = $quote->getStore()->roundPrice($discountAmount);
                $baseDiscountAmount = $quote->getStore()->roundPrice($baseDiscountAmount);
            }

            /**
             * We can't use row total here because row total not include tax
             * Discount can be applied on price included tax
             */

            $itemDiscountAmount = $item->getDiscountAmount();
            $itemBaseDiscountAmount = $item->getBaseDiscountAmount();

            $discountAmount     = min($itemDiscountAmount + $discountAmount, $itemPrice * $qty);
            $baseDiscountAmount = min($itemBaseDiscountAmount + $baseDiscountAmount, $baseItemPrice * $qty);

            $item->setDiscountAmount($discountAmount);
            $item->setBaseDiscountAmount($baseDiscountAmount);

            $item->setOriginalDiscountAmount($originalDiscountAmount);
            $item->setBaseOriginalDiscountAmount($baseOriginalDiscountAmount);

            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();
//			if( $this->checkIsAllAdvancedConditionsInRule( $rule ) ){
//				if( $result->getDiscountAmount()==0 && $result->getBaseDiscountAmount() ){
//					// not save this rule id to item, so do no thing
//				}
//			}else{
//				$appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();
//			}

            $this->_maintainAddressCouponCode($address, $rule);
            $this->_addDiscountDescription($address, $rule);

            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }

        $item->setAppliedRuleIds(join(',',$appliedRuleIds));
        $address->setAppliedRuleIds($this->mergeIds($address->getAppliedRuleIds(), $appliedRuleIds));
        $quote->setAppliedRuleIds($this->mergeIds($quote->getAppliedRuleIds(), $appliedRuleIds));

        return $this;
    }

    /**
     * Calculate quote totals for each rule and save results
     *
     * @param mixed $items
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Mage_SalesRule_Model_Validator
     */
    public function initTotals($items, Mage_Sales_Model_Quote_Address $address)
    {
        $address->setCartFixedRules(array());

        if (!$items) {
            return $this;
        }

        foreach ($this->_getRules() as $rule) {
             if (Mage_SalesRule_Model_Rule::CART_FIXED_ACTION == $rule->getSimpleAction()
                && $this->_canProcessRule($rule, $address)) {

                $ruleTotalItemsPrice = 0;
                $ruleTotalBaseItemsPrice = 0;
                $validItemsCount = 0;

                foreach ($items as $item) {
                    //Skipping child items to avoid double calculations
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if (!$rule->getActions()->validate($item)) {
                        continue;
                    }
                    $qty = $this->_getItemQty($item, $rule);
                    $ruleTotalItemsPrice += $this->_getItemPrice($item) * $qty;
                    $ruleTotalBaseItemsPrice += $this->_getItemBasePrice($item) * $qty;
                    $validItemsCount++;
                }

                $this->_rulesItemTotals[$rule->getId()] = array(
                    'items_price' => $ruleTotalItemsPrice,
                    'base_items_price' => $ruleTotalBaseItemsPrice,
                    'items_count' => $validItemsCount,
                );
            }
            if ( "fixed_price_for_products"== $rule->getSimpleAction()
                && $this->_canProcessRule($rule, $address)) {

                $ruleTotalItemsPrice = 0;
                $ruleTotalBaseItemsPrice = 0;
                $validItemsCount = 0;

                foreach ($items as $item) {
                    //Skipping child items to avoid double calculations
                    if ($item->getParentItemId()) {
                        continue;
                    }
                    if (!$rule->getActions()->validate($item)) {
                        continue;
                    }
                    $qty = $this->_getItemQty($item, $rule);
                    if( Mage::helper("couponRule")->enableCategoryItemQty($rule)==true && 0==$qty){
            			continue;
            		}
                    $ruleTotalItemsPrice += $this->_getItemPrice($item) * $qty;
                    $ruleTotalBaseItemsPrice += $this->_getItemBasePrice($item) * $qty;
                    $validItemsCount++;
                }

                $this->_rulesItemTotals[$rule->getId()] = array(
                    'items_price' => $ruleTotalItemsPrice,
                    'base_items_price' => $ruleTotalBaseItemsPrice,
                    'items_count' => $validItemsCount,
                );
            }            
            
        }

        return $this;
    }    
    
    /*
     * because if condition is advanced condition(from extension "AdvancedPromotions"), function 
     * $this->_canProcessRule($rule, $address) will always return true, so it will alway save rule id
     * to item.
     * 
     * check all conditions in rule, if all of them are advanced rules(from extension "AdvancedPromotions"),
     * then return true, else return false
     * 
     * @return  bool
     */
    public function checkIsAllAdvancedConditionsInRule( $rule ){
    	
    	$conditionField=unserialize($rule->getData('conditions_serialized'));

		$advancedConditionNum=0; // all advanced condition number for this rule
		$conditionNum=0;
		if( isset( $conditionField[conditions] ) ){  // there aremore than one condition in rule
	
			$conditionNum=count( $conditionField['conditions'] );   //all condition number for this rule
			foreach( $conditionField['conditions'] as $tempcondition){
				if( 'fooman_advancedpromotions/salesRule_rule_condition_product_groupSimple'==$tempcondition['type']
				||	'fooman_advancedpromotions/salesRule_rule_condition_product_groupIndependant'==$tempcondition['type']
				||	'fooman_advancedpromotions/salesRule_rule_condition_product_subtotal'==$tempcondition['type']
				||	'fooman_advancedpromotions/salesRule_rule_condition_product_groupSubselectQty'==$tempcondition['type']
				)
				{
					$advancedConditionNum++;
				}
	
			}
		}
	
		if( $advancedConditionNum===$conditionNum ){
			return true;
		}else{
			return false;
		}    	
    	
    	
    }
    
    /*
     * check is it the most cheapest item by qty
     * for example: buy 3 shirts get 10% off, in shop cart it get 4 shirts, but discount is just available
     *  for 3 shirt(which is the cheapest)
     */
    protected function checkIsCheaperItem( $item, $rule ){
    	$address    = $this->_getAddress($item);
    	$availableItemsForThisRule=array();
    	$itemPriceArray=array();
    	$cheapestItemIds=array();
    	
    	foreach( $address->getAllNonNominalItems() as $tempItem ){
    		
    		$itemPrice = $this->getItemPriceInCache($tempItem);
	    	if ($itemPrice <= 0) {
	            continue;
	        }
	        
    	    if (!$this->_canProcessRule($rule, $address)) {
                continue;
            }

            if (!$rule->getActions()->validate($tempItem)) {
                continue;
            }
            
            $availableItems[]=$tempItem;
    	}
    	
    	foreach( $availableItems as $tempItem ){
    		$itemPriceArray[]=$this->getItemPriceInCache($tempItem);
    	}
    	
    	//sort all price by DESC
    	sort($itemPriceArray);
    	
    	foreach( $itemPriceArray as $itemPrice ){
    		foreach( $availableItems as $tempItem ){
    			if( $this->getItemPriceInCache($tempItem)==$itemPrice ){
    				
    				for($i=0; $i<$tempItem->getTotalQty();$i++){
    					$cheapestItemIds[]=$tempItem->getId();
    				}
    				
    			}
    		}
    	}
    	
    	//$cheapestQty should come from a filed in database, this field mean the most qty can get discount
    	$cheapestQty=2;
    	for( $i=0;$i<$cheapestQty;$i++ ){
    		if( $item->getId()==$cheapestItemIds[$i] ){
    			return true;
    		}
    	}
    	
    	return false;
    	
    }
    

    
    protected function getItemPriceInCache( $item ){
    	if(!isset( $this->_itemPriceCache[$item->getId()] )){
    		$this->_itemPriceCache[$item->getId()]=$this->_getItemPrice($item);
    	}
    	return $this->_itemPriceCache[$item->getId()];
    }

    /*
     * get all item available for  rule
     * @return item object
     */
    protected function getAllAvailableItemsForRule( $item, $rule ){
    	$address    = $this->_getAddress($item);
    	$addressId= $address->getId();
    	$key=$addressId." ".$rule->getId();
    	if( !isset( $this->_availableItemsForRule[$key] ) ){
    		
    		$availableItems=array();
	    	foreach( $address->getAllNonNominalItems() as $tempItem ){
	    		
	    		$itemPrice = $this->getItemPriceInCache($tempItem);
		    	if ($itemPrice <= 0) {
		            continue;
		        }
		        
	    	    if (!$this->_canProcessRule($rule, $address)) {
	                continue;
	            }
	
	            if ($this->checkActionValidate( $tempItem,$rule )==false) {
	                continue;
	            }

	            
	            $availableItems[]=$tempItem;
	    	}//end foreach    		
    		
	    	$this->_availableItemsForRule[$key]=$availableItems;
	    	
    	}//end if 
    	
    	return $this->_availableItemsForRule[$key];
    }
    

    protected function checkActionValidate( $item, $rule ){
    	$key=$rule->getId()."_".$item->getId();
    	if( !isset( $this->_ruleActionValidate[$key] ) ){
    		$this->_ruleActionValidate[$key]=$rule->getActions()->validate($item);
    	}
    	return $this->_ruleActionValidate[$key];
    }

}
