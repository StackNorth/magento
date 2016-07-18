<?php
/**
 * User: ahsw@qq.com
 * caeate Time: 2016/6/2715:02
 */

class D1m_Credits_Model_Balance extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {

        $this->_init('d1m_credits/balance');
    }
    public function getCustomerMoney($customer_id,$month='')
    {
        if($month==''){
            $month = Mage::getModel('core/date')->gmtDate('Y-m-01');
        }

        $money =  $this->_getResource()->getCustomerMoney($customer_id,$month);
        if($money)
        {
            return $money;
        }

        return 0;
    }
}
