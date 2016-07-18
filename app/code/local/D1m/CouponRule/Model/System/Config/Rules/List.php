<?php
class D1m_CouponRule_Model_System_Config_Rules_List
{
     protected $_options;
     
    public function toOptionArray()
    {
        
        $rules = Mage::getResourceModel('salesrule/rule_collection')->load();

        $list = array();
        if ($rules && $rules->getSize()>0) {
            foreach ($rules->getData() as $rule) {
                if ($rule['is_active'] == 1){
                    $list[] = array('value'=>$rule['rule_id'], 'label'=>$rule['name']);
                }
            }
        }
        
        $_options =  $list;
        
        if(!$_options){
            array_unshift($_options, array('value'=>'', 'label'=> Mage::helper('adminhtml')->__('--Please Select--')));
        }
        return $list;
    }
}