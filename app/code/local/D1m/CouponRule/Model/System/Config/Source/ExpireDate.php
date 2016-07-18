<?php
/**
 * Setting Expire Date
 *
 * @author d1miao
 */
class D1m_CouponRule_Model_System_Config_Source_ExpireDate extends  Varien_Object {
    
 public function toOptionArray()
 {
     $expire_Date = array(
         '' => Mage::helper('couponRule/data')->__('Please choose expire date')
     );
     for ($i=1;$i<=30;$i++){
        $expire_Date[$i] = $i.Mage::helper('couponRule/data')->__('day');
     }


     return $expire_Date;
   }  
 }

