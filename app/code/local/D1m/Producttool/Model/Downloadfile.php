<?php
class D1m_Producttool_Model_Downloadfile extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        // $resourceMode
        $this->_init('d1m_producttool/downloadfile');
    }
}