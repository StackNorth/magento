<?php

/** define the different rule type
 * Class D1m_CouponRule_Model_Rule
 */

class D1m_CouponRule_Model_Rule extends Mage_Core_Model_Abstract {

    //define new coupon type
    const COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER = 4;

    const COUPON_TYPE_AUTO_GENERATE_FOR_EVENT = 5;

    const COUPON_TYPE_CREDITS_AUTO_GENERATE_WITH_CUSTOMER = 6;
    const COUPON_TYPE_CREDITS_AUTO_GENERATE_FOR_EVENT = 7;
}