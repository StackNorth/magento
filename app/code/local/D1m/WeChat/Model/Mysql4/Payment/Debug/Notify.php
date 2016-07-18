<?php
/**
 * Class D1m_WeChat_Model_Mysql4_Payment_Debug_Notify
 */
class D1m_WeChat_Model_Mysql4_Payment_Debug_Notify extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('weChat/payment_notify_debug', 'notify_id');
    }
}
