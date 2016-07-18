<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Model_Mysql4_Placeholder_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('easybanner/placeholder');
    }
    
    public function toOptionArray()
    {
        return $this->_toOptionArray('placeholder_id');
    }
}