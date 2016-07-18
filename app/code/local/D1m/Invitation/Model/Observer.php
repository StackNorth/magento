<?php
class D1m_Invitation_Model_Observer
{
	
	 const INVITATION_RULE_ID = null;
	
	
	public function getInvitationRuleID()
	{
		
		$gift_rule_id = Mage::getStoreConfig('d1m_invitation/general/gift_rule_id');
		if(!$gift_rule_id || $gift_rule_id <= 0)
		{
			$gift_rule_id =  self::INVITATION_RULE_ID;
		}
		
		return $gift_rule_id;
	}
	
	public function sendCouponToRecommender($customer)
	{
		$rid = Mage::getSingleton('core/cookie')->get('rid');
		$rid = (int)$rid;
		
		if($rid)
		{
            $inviteRec = Mage::getModel('d1m_invitation/invitation')->load($rid);

			//推荐会员送积分
            $icent = Mage::getStoreConfig('d1m_invitation/general/cent');
            settype($icent,"integer");
            if ($icent>0)
            {
                //
                $customer_id = $inviteRec->getCustomerId();
                $obj = Mage::getModel('d1m_integral/integral')->load($customer_id, 'customer_id');
                if(!$obj || $obj->getId() <= 0)
                {
                    $obj = Mage::getModel('d1m_integral/integral');
                    $obj->customer_id = $customer_id;
                    $obj->credit_amount = 0;
                }
                $obj->credit_amount = $obj->credit_amount + $icent;
                $obj->historyDesc='推荐会员获得积分';
                try {$obj->save();} catch (exception $e) {}
                
            }


            
			$gift_rule_id = $this->getInvitationRuleID();
			if($gift_rule_id <= 0)
			{
				Mage::log('not config the rule info, create coupon failed for customer id :'.$inviteRec->getCustomerId(), null, 'invitation.log', $forceLog = true);
				return false;
			}
			

		
			if(!$inviteRec->getStatus())
			{
				$rule = Mage::getModel('salesrule/rule')->load($gift_rule_id);
				
				if($rule)
				{
					
					/** @var Mage_SalesRule_Model_Coupon $coupon */
			        $coupon = Mage::getModel('salesrule/coupon');
			        $coupon->setRule($rule)
			            ->setIsPrimary(false)
			            ->setUsageLimit(1)
			            ->setType(1)
			            ->setUsagePerCustomer(1)
			            //->setExpirationDate($rule->getToDate())
			            //->setExpirationDate($rule->getToDate())
			            ;
			
			        $couponCode = Mage_SalesRule_Model_Rule::getCouponCodeGenerator()->generateCode();
			        $coupon->setCode($couponCode);
			        
			        $coupon->setCustomerId($inviteRec->getCustomerId());
					
					$saveNewlyCreated = true;
					$saveAttemptCount = 10;
			        $ok = false;
			        
		            for ($attemptNum = 0; $attemptNum < $saveAttemptCount; $attemptNum++) {
		                try {
		                    $coupon->save();
		                } catch (Exception $e) {
		                    if ($e instanceof Mage_Core_Exception || $coupon->getId()) {
		                        throw $e;
		                    }
		                    $coupon->setCode(
		                        $couponCode .
		                        Mage_SalesRule_Model_Rule::getCouponCodeGenerator()->getDelimiter() .
		                        sprintf('%04u', rand(0, 9999))
		                    );
		                    continue;
		                }
		                $ok = true;
		                break;
		            }
			        
			        if ($ok) {
			        	
			           	$coupon->save();
						$inviteRec->setStatus(1);
						$inviteRec->save();
						Mage::getSingleton('core/cookie')->delete('rid');
						return true;
			        }
					else
					{
						Mage::log('create coupon failed for customer id :'.$inviteRec->getCustomerId(), null, 'invitation.log', $forceLog = true);
					}
					
				}
				else
				{
					Mage::log('can not find the rule info, create coupon failed for customer id :'.$inviteRec->getCustomerId(), null, 'invitation.log', $forceLog = true);
				}
			}
			
		}
		
		
		
		return false;
		
	}
	
	
}
	
	
	