<?php

class D1m_CouponRule_Block_Adminhtml_Customer_Edit_Tab_Coupon_Grid_Column_Renderer_RuleName
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $rule_data = $row->getData();
        $rule_id   = $rule_data['rule_id'];
        return empty($rule_id) ? Mage::helper('adminhtml')->__('No Data') :  Mage::getModel('salesrule/rule')->load($rule_id)->getName();
    }
}
