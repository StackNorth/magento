<?php
/**
 * Exception
 *
 */
class D1m_WeChat_Model_Payment_Exception extends Mage_Core_Exception
{
	public function errorMessage()
	{
		return $this->getMessage();
	}
}
