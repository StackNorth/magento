<?php

class Robi_Settleaccount_Helper_Event extends Mage_Core_Helper_Abstract
{
    
    public function setRewardPoints($points_value){
        
        if (Mage::app()->getStore()->isAdmin()) 
        {
         	Mage::getSingleton('adminhtml/session_quote')->setPrepareRewardPoints($points_value);
        }
        else
        {
        	Mage::getSingleton('customer/session')->setRewardPoints($points_value);
        }
    }
    
    public function getRewardPoints(){
       
		if (Mage::app()->getStore()->isAdmin()) 
		{
			return Mage::getSingleton('adminhtml/session_quote')->getPrepareRewardPoints();
		}
		else
		{
			return Mage::getSingleton('customer/session')->getRewardPoints();
		}
    }
    

   
    public function setPrepareUsedCredit($value){
        
        if (Mage::app()->getStore()->isAdmin()) 
		{
			Mage::getSingleton('adminhtml/session_quote')->setPrepareUsedCredit($value);
		}
		else
		{
        	Mage::getSingleton('customer/session')->setPrepareUsedCredit($value);  
		}
		
    }
    
    
    public function getPrepareUsedCredit(){
        
        if (Mage::app()->getStore()->isAdmin()) 
		{
			return Mage::getSingleton('adminhtml/session_quote')->getPrepareUsedCredit();
		}
		else
		{
        	return Mage::getSingleton('customer/session')->getPrepareUsedCredit();
		}
    }


    
}