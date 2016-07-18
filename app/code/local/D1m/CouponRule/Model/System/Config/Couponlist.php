<?php
class D1m_CouponRule_Model_System_Config_Couponlist
{
    public function toOptionArray()
    {
        $rules = Mage::getResourceModel('salesrule/rule_collection')->load();
        $list = array(
            '' => Mage::helper('adminhtml')->__('Please choose rule')
        );
        if ($rules) {
            foreach ($rules as $rule) {
                   if ($rule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT){
                        $list[$rule->getId()] = $rule->getName();
                   }
                   
            }
        }
        return $list;
    }
}