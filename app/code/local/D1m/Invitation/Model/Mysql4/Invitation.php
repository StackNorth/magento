<?php
class D1m_Invitation_Model_Mysql4_Invitation extends Mage_Core_Model_Mysql4_Abstract
{
	
    protected function _construct()
    {       
        $this->_init('d1m_invitation/invitation', 'id');
    } 
    
   
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedAt()) {
            $object->setCreatedAt($this->formatDate(time()));
        }
        
        $object->setUpdatedAt($this->formatDate(time()));
        parent::_beforeSave($object);
        
    }
    
    public function _afterSave(Mage_Core_Model_Abstract $object)
    {
    	parent::_afterSave($object);
    	
    }
    
    public function checkCustomerIdExists($brand_id, $id)
    {
        $select = $this->_getReadAdapter()->select()->from(array('main_table'=>$this->getMainTable()), 'customer_id')
            			->where('main_table.customer_id=?', $brand_id);
        if($id)
        {
        	$select->where('main_table.id != ?', $id);
        }

        return $this->_getReadAdapter()->fetchOne($select);
    }
    

    
    public function getCustomerCredits($customer_id)
    {
        $select = $this->_getReadAdapter()->select()->from(array('main_table'=>$this->getMainTable()), 'credit_amount')
            			->where('main_table.customer_id=?', $customer_id)
            			;
   
        return $this->_getReadAdapter()->fetchOne($select);
    }
    
}

