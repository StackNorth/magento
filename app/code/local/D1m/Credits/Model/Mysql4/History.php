<?php
class D1m_Credits_Model_Mysql4_History extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {       
        $this->_init('d1m_credits/history', 'id');
    } 
    
   
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedAt()) {
            $object->setCreatedAt($this->formatDate(time()));
        }
        
        $object->setUpdatedAt($this->formatDate(time()));
        parent::_beforeSave($object);
    }
    
    
    
    
}

