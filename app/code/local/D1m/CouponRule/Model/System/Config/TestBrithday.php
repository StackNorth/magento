<?php
/**
 * Description of TestBrithday
 *
 * @author d1miao
 */
class D1m_CouponRule_Model_System_Config_TestBrithday{
    
 public function toOptionArray()
 {
     for ($i=1;$i<=12;$i++){
         if ($i <=9){
               $date_month_item[] =  array('value' => '0'.$i, 'label'=>$i.Mage::helper('couponRule/data')->__('month'));
         } else {
               $date_month_item[] =  array('value' => $i, 'label'=>$i.Mage::helper('couponRule/data')->__('month'));
         }
     }
     
     return $date_month_item;
   }  
 }

