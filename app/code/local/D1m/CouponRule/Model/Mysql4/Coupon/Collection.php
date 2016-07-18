<?php

/**
 * Firstsecond Coupon Collection Model specialized for MySQL4
 *
 * @category   Firstsecond
 * @package    D1m_CouponRule
 */
class D1m_CouponRule_Model_Mysql4_Coupon_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('couponRule/coupon');
    }
    
    
    public function getCouponCodeBycustomerId($customerId,$code, $rule_id){
        if (!empty($customerId) && !empty($rule_id)){
             $result_data = $this->addFieldToFilter('customer_id',$customerId)->addFieldToFilter('coupon',$code)->addFieldToFilter('rule_id',$rule_id)->getData();

             if ($result_data && count($result_data)>=1){
                  return true;
             } else {
                  return false;
             }
        }
    }
 
}