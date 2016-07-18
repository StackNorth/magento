<?php
class D1m_CouponRule_Model_System_Config_Customepromotion_Giftlist
{
     protected $_options;
     
    public function toOptionArray()
    {
        
        $giftlist = Mage::getModel('giftpromo/giftpromo')->getCollection()->addFieldToFilter('status',1)->load();
        $list = array();
        if ($giftlist && $giftlist->getSize()>0) {
            foreach ($giftlist as $gift) {
                $list[] = array('value'=>$gift->getGiftId(), 'label'=>$gift->getGiftName());
            }
        }
        
        $_options =  $list;
        
        if(!$_options){
            array_unshift($_options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }
        return $list;
    }
}