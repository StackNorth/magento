<?php
/**
 * Class D1m_WeChat_Model_Mysql4_Payment_Debug_Pay_Collection
 */
class D1m_WeChat_Model_Mysql4_Payment_Debug_Pay_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     *
     */
    protected function _construct()
    {
		$this->_init('weChat/payment_debug_pay');
	}
}