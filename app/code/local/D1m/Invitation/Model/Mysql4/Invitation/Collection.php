<?php
class D1m_Invitation_Model_Mysql4_Invitation_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('d1m_invitation/invitation');
    }
}
