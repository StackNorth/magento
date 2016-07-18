<?php

class D1m_CouponRule_Model_Validator  extends Mage_SalesRule_Model_Validator
{

   const INNER_PURCHASE_COUPON_LIMIT = 10;

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


    //验证与用户之间的绑定关系
    public function validateRuleCode(Mage_SalesRule_Model_Rule $rule,Mage_SalesRule_Model_Coupon $coupon)
    {
        //init
        $customer_session = Mage::getSingleton('customer/session');
        $current_time     = Mage::app()->getLocale()->date()->toString('yyyy-MM-dd HH:mm:ss');
        $errors = array();

        //return $errors;
        if($rule && $rule->getRuleId())
        {
            
            if($rule->getIsActive())
			{
				
				if (Mage::app()->getStore()->isAdmin()) {
		         	$_session = Mage::getSingleton('adminhtml/session_quote');
					$customer = $_session->getCustomer();
					$customerGroupId = $customer->getGroupId();
				}
				else
				{
				 	$customer = Mage::getSingleton('customer/session')->getCustomer();
				 	$customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
				}
				
				if($customerGroupIds = $rule->getData('customer_group_ids'))
				{
					if(!in_array($customerGroupId, $customerGroupIds))
					{
						$errors[] = '该优惠券只有指定用户等级方可使用,如果您还尚未登录，请先登录后再试！';
						return $errors;
					}
				}

				// 2)  如果是优惠规则与用户相关，则验证是否为当前用户,并且是在可用时间内的
	            if ($rule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER || $rule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT)
	            {
	                //是否登录
	                if (!$customer->getId()){
	                    $errors[] = Mage::helper('couponRule')->__('you can use it before you login!');
	                    return $errors;
	                }
	
	                // 验证当前用户是否为此COUPON所绑定的用户
	                $coupon_mapping_id = Mage::getModel('couponRule/coupon')->loadCouponRuleByCoupon($coupon);
	                if ($coupon_mapping_id && $mapping_coupon_data = Mage::getModel('couponRule/coupon')->load($coupon_mapping_id) ){
	                    if ($mapping_coupon_data->getCustomerId() != $customer->getId()){
	                        $errors[] = Mage::helper('couponRule')->__('此优惠券不属于您,您不可以使用');//you can not use this coupon. it not belong to you ');
	                        return $errors;
	                    }
	                }
	
	            }
                
                $now  = Mage::getModel('core/date')->date('Y-m-d H:i:s');
				if( (is_null($rule->getFromDate()) || $rule->getFromDate() <= $now) && (is_null($rule->getToDate()) || $rule->getToDate() >= $now) )
				{	

					if(!is_null($coupon->getExpirationDate()) && $coupon->getExpirationDate() < $now)
					{
						$errors[] = '该优惠券已经过期。';
					}
					
					if(!is_null($coupon->getBeginUseDate()) && $coupon->getBeginUseDate() > $now)
					{
						$errors[] = '该优惠券生效日期为：'.$coupon->getBeginUseDate().'。';
					}
					
	                if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
	                    $errors[] = '该优惠券的使用总次数已经超过总限次数。';
						return $errors;
	                }
	                
	                $customerId = $customer->getId();
	                if ($customerId && $coupon->getUsagePerCustomer()) {
		                $couponUsage = new Varien_Object();
		                Mage::getResourceModel('salesrule/coupon_usage')->loadByCustomerCoupon(
		                    $couponUsage, $customerId, $coupon->getId());
		                if ($couponUsage->getCouponId() &&
		                    $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()
		                ) {
		                    $errors[] = '每一用户只能使用该优惠券'.$coupon->getUsagePerCustomer().'次。';
							return $errors;
		                }
		            }
		            
				}
		       elseif(!is_null($rule->getFromDate()) && $rule->getFromDate() > $now)
			   {
					$errors[] = '该优惠券现在尚未起用。';
			   }
			   elseif(!is_null($rule->getToDate()) && $rule->getToDate() < $now)
			   {
				 	$errors[] = '该优惠规则已经过期。';
			   }
	            
	            
			}
            else
			{
				$errors[] = '该优惠券已经禁用。';
			}
            
            
            
            
        }

        return $errors;
    }


    /**
     * Check if rule can be applied for specific address/quote/customer
     *
     * @param   Mage_SalesRule_Model_Rule $rule
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  bool
     */
    protected function _canProcessRule($rule, $address)
    {
        /*
         * and Bysoft_Improvesalerule_Model_SalesRule_Condition_Product
         */
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
     * Quote item free shipping ability check
     * This process not affect information about applied rules, coupon code etc.
     * This information will be added during discount amounts processing
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_SalesRule_Model_Validator
     */
    public function processFreeShipping(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $address = $this->_getAddress($item);
        $item->setFreeShipping(false);

        foreach ($this->_getRules() as $rule) {
            /* @var $rule Mage_SalesRule_Model_Rule */
            if (!$this->_canProcessRule($rule, $address)) {
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                continue;
            }

            switch ($rule->getSimpleFreeShipping()) {
                case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ITEM:
                    $item->setFreeShipping($rule->getDiscountQty() ? $rule->getDiscountQty() : true);
                    break;

                case Mage_SalesRule_Model_Rule::FREE_SHIPPING_ADDRESS:
                    $address->setFreeShipping(true);
                    break;
            }
            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        return $this;
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

        // 如果优惠券规则有排它性，则不起作用
        $appliedRuleIds = array();

        $other_discount_count = 0;
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
            // 启用排斥条件
            if( $rule->getData('condition_for_original_price')==D1m_CouponRule_Model_Improvesalerule::AVAILABLE_ORIGINAL_PRICE
                && Mage::getSingleton('couponRule/improvesalerule')->isOriginalPrice( $item->getProductId() )==false ){
                
                if(!Mage::registry("original_price_rule_validated"))
            	{
            		Mage::register("original_price_rule_validated", true);
            	}
                
                continue;
            }

           //处理内购的促销规则
            if ($rule->getRuleBaseOnOriginalPrice() == D1m_CouponRule_Model_SalesRule_PriceType::RULE_BASE_ON_ORIGINAL_PRICE) {
                $otherDiscountCount = 0;
                foreach ($quote->getItemsCollection() as $quoteItem){
                    $parentItemId = $quoteItem->getParentItemId();
                    $saleRules    = $quoteItem->getAppliedRuleIds();
                   if (empty($parentItemId) && !empty($saleRules)){

                       //如果购物车中的项均没有任何变化,则可以直接获得discount
                       if ($quoteItem->getId() != $item->getId()){
                           $otherDiscountCount += $quoteItem->getDiscountQty();
                       }
                    }
                }

                if ((self::INNER_PURCHASE_COUPON_LIMIT-$otherDiscountCount) != 0){
                    if ((self::INNER_PURCHASE_COUPON_LIMIT-$otherDiscountCount) < ($item->getQty())){
                        $discount_qty = self::INNER_PURCHASE_COUPON_LIMIT-$otherDiscountCount;
                    } else {
                        $discount_qty = $item->getQty();
                    }
                } else {
                    $discount_qty = $item->getDiscountQty();
                }

                $item->setData('discount_qty',$discount_qty);
                $qty = $discount_qty;
            } else {
                $qty = $this->_getItemQty($item, $rule);
            }


            //$qty = $this->_getItemQty($item, $rule);
          //  $item->setData('discount_qty',$qty);

            $rulePercent = min(100, $rule->getDiscountAmount());

            $discountAmount = 0;
            $baseDiscountAmount = 0;
            //discount for original price
            $originalDiscountAmount = 0;
            $baseOriginalDiscountAmount = 0;

            // --------排斥性 over
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

                    if ($rule->getRuleBaseOnOriginalPrice() == D1m_CouponRule_Model_SalesRule_PriceType::RULE_BASE_ON_ORIGINAL_PRICE){
                        $itemPrice                      =       $item->getProduct()->getPrice();
                        $baseItemPrice                  =       $item->getProduct()->getPrice();
                        $itemOriginalPrice              =       $item->getProduct()->getPrice();
                        $baseItemOriginalPrice          =       $item->getProduct()->getPrice();
                    }

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

                case Mage_SalesRule_Model_Rule::BUY_X_GET_Y_ACTION:
                    $x = $rule->getDiscountStep();
                    $y = $rule->getDiscountAmount();
                    if (!$x || $y > $x) {
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




    protected function checkActionValidate( $item, $rule ){
        $key=$rule->getId()."_".$item->getId();
        if( !isset( $this->_ruleActionValidate[$key] ) ){
            $this->_ruleActionValidate[$key]=$rule->getActions()->validate($item);
        }
        return $this->_ruleActionValidate[$key];
    }



    /**
     * Reset quote and address applied rules
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @return Mage_SalesRule_Model_Validator
     */
    public function reset(Mage_Sales_Model_Quote_Address $address)
    {
        if ($this->_isFirstTimeResetRun) {
            $address->setAppliedRuleIds('');
            $address->getQuote()->setAppliedRuleIds('');
            $this->_isFirstTimeResetRun = false;
        }

        return $this;
    }


}