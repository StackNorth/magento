<?php
/**
 * Class D1m_WeChat_Model_Mysql4_Payment_Debug_Query
 */
class D1m_WeChat_Model_Mysql4_Payment_Debug_Query extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('weChat/payment_query_debug', 'query_id');
    }
}