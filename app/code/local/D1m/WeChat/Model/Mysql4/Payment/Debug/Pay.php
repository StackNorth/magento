<?php
/**
 * Class D1m_WeChat_Model_Mysql4_Payment_Debug_Pay
 */
class D1m_WeChat_Model_Mysql4_Payment_Debug_Pay extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('weChat/api_debug', 'debug_id');
    }
}
