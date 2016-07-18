<?php
class D1m_Producttool_Model_Mysql4_Downloadfile_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('d1m_producttool/downloadfile');
        // $model=Mage::getModel('d1m_customfile/log');        var_dump($model);
        // die('1123');

    }
}