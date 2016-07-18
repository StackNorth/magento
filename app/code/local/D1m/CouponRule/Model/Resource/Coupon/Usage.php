<?php

/**
 *  Rewrite
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @author      D1M
 */
class D1m_CouponRule_Model_Resource_Coupon_Usage extends Mage_SalesRule_Model_Resource_Coupon_Usage
{
    /**
     * @param $customerId
     * @param $couponId
     */
    public function cancelCouponUsage($customerId, $couponId){
        $read = $this->_getReadAdapter();
        $select = $read->select();
        $select->from($this->getMainTable(), array('times_used'))
            ->where('coupon_id = :coupon_id')
            ->where('customer_id = :customer_id');

        $timesUsed = $read->fetchOne($select, array(':coupon_id' => $couponId, ':customer_id' => $customerId));

        //delete
        if (max($timesUsed - 1, 0) == 0){
            $this->_getWriteAdapter()->delete(
                $this->getMainTable(),
                array(
                    'coupon_id = ?' => $couponId,
                    'customer_id = ?' => $customerId,
                )
            );
        } else {
            $this->_getWriteAdapter()->update(
                $this->getMainTable(),
                array(
                    'times_used' => $timesUsed - 1
                ),
                array(
                    'coupon_id = ?' => $couponId,
                    'customer_id = ?' => $customerId,
                )
            );
        }
    }
}
