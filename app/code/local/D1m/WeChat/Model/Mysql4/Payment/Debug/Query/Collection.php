<?php
/**
 * Class D1m_WeChat_Model_Mysql4_Payment_Debug_Query_Collection
 */
class D1m_WeChat_Model_Mysql4_Payment_Debug_Query_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     *  debug notify
     */
    protected function _construct()
    {
		$this->_init('weChat/payment_debug_query');
	}
}