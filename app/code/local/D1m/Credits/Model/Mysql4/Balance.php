<?php
/**
 * User: ahsw@qq.com
 * caeate Time: 2016/6/2715:03
 */
class D1m_Credits_Model_Mysql4_Balance extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('d1m_credits/balance', 'id');
    }
    public function getCustomerMoney($customer_id,$month='2016-06-01')
    {
        $select = $this->_getReadAdapter()->select()->from(array('main_table'=>$this->getMainTable()))
            ->where('main_table.uid=?', $customer_id)
            ->where('main_table.created_date=?', $month)
        ;

        $info= $this->_getReadAdapter()->fetchRow($select);
        if($info){
            return $info['order_money']+$info['credits'];
        }
        return 0;
    }
}