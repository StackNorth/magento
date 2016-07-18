<?php
class D1m_Chef_Model_Mysql4_Chef extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct()
    {
        $this->_init('d1m_chef/chef', 'chef_id');
    }

}
