<?php
class D1m_Credits_Model_Credits extends Mage_Core_Model_Abstract 
{
     public $historyDesc = null;
     public $historyOrderNo = null;
     
     public $_historyRec = null;
    
    protected function _construct()
    {
    	
        $this->_init('d1m_credits/credits');
    }
    
    public function checkCustomerIdExists($customer_id, $id)
    {
        return $this->_getResource()->checkCustomerIdExists($customer_id, $id);
    }
    

    public function getCustomerCredits($customer_id)
    {
    	
         $credit =  $this->_getResource()->getCustomerCredits($customer_id);
         if($credit)
         {
         	return $credit;
         }
         
         return 0;
    }
}
