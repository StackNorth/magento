<?php
class Robi_Msgnotice_Model_Mysql4_Failedaction extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {       
        $this->_init('msgnotice/failedaction','id');
        
        $this->_read = $this->_getReadAdapter();
        $this->_write = $this->_getWriteAdapter();
        
    } 
    
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedOn()) {
            $object->setCreatedOn($this->formatDate(time()));
        }
        
        $object->setUpdatedOn($this->formatDate(time()));
        parent::_beforeSave($object);
    }
    
    
    
}

