<?php
class D1m_Credits_Model_Mysql4_Credits extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {       
        $this->_init('d1m_credits/credits', 'id');
    } 
    
   
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getCreatedAt()) {
            $object->setCreatedAt($this->formatDate(time()));
        }
        
        if($creditsId = $object->getId())
        {
            $oldCreditAmount = $object->getOrigData('credit_amount');
            
            $add = 0;
            $subtract = 0;
            
            if($oldCreditAmount > $object->getCreditAmount())
            {
                $subtract = $oldCreditAmount - $object->getCreditAmount();
            }
            elseif($oldCreditAmount < $object->getCreditAmount())
            {
                $add = $object->getCreditAmount() - $oldCreditAmount;
            }
            
            if($add || $subtract)
            {
                $history = Mage::getModel('d1m_credits/history'); 
                $history->credit_id = $creditsId;
                $history->add = $add;
                $history->subtract = $subtract;
                $history->description = $object->historyDesc;
                $history->order_no = $object->historyOrderNo;
                $history->save();
                
            }
            
        }
        else
        {
        	$add = $object->getCreditAmount();
            $subtract = 0;
        	$history = Mage::getModel('d1m_credits/history'); 
            $history->add = $add;
            $history->subtract = $subtract;
            $history->description = $object->historyDesc;
            $history->order_no = $object->historyOrderNo;
        	$object->_historyRec = $history;
        }
        
        
        $object->setUpdatedAt($this->formatDate(time()));
        parent::_beforeSave($object);
    }
    
    public function _afterSave(Mage_Core_Model_Abstract $object)
    {
    	
    	if($object->_historyRec && $object->_historyRec instanceof D1m_Credits_Model_History )
    	{
    			$_history = $object->_historyRec;
    			$_history->credit_id = $object->getId();
    			$_history->save();
    	}
    	
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

