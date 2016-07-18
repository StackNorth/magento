<?php
class D1m_Credits_Model_Mysql4_Order_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
      
        $this->_init('d1m_credits/order');
    }
}
