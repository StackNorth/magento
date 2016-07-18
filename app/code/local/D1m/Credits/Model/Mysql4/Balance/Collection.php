<?php
/**
 * User: ahsw@qq.com
 * caeate Time: 2016/6/2715:04
 */
class D1m_Credits_Model_Mysql4_Balance_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('d1m_credits/balance');
    }
}
