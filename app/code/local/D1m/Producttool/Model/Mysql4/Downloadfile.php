<?php

class D1m_Producttool_Model_Mysql4_Downloadfile extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the web_id refers to the key field in your database table.
        //这个地方很重要! VERY IMPORTANT!
        $this->_init('d1m_producttool/downloadfile', 'id');
    }
}