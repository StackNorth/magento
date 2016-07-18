<?php
class D1m_Chef_Model_Mysql4_Chef_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {
        parent::_construct();
        $this->_init('d1m_chef/chef');
    }

}
