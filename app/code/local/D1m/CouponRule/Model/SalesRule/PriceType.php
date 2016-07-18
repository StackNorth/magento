<?php
/**
 *  优惠券的条件使用是否基于原价
 * Class D1m_CouponRule_Model_SalesRule_PriceType
 */
class D1m_CouponRule_Model_SalesRule_PriceType extends Mage_Core_Model_Abstract
{
    const  RULE_BASE_ON_SPECIAL_PRICE  =1;
    const  RULE_BASE_ON_ORIGINAL_PRICE = 2;

    public function toOptionArray()
    {
        return  array(
                self::RULE_BASE_ON_SPECIAL_PRICE    =>  Mage::helper('couponRule')->__('Special Price'),
                self::RULE_BASE_ON_ORIGINAL_PRICE   =>  Mage::helper('couponRule')->__('Original Price')
        );
    }
}
