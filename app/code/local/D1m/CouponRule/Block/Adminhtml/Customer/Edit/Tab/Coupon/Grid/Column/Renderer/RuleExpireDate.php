<?php

class D1m_CouponRule_Block_Adminhtml_Customer_Edit_Tab_Coupon_Grid_Column_Renderer_RuleExpireDate  extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $rule_id = $row->getRuleId();
        $coupond_expire_date = $row->getExpirationDate();
        return empty($coupond_expire_date) ? Mage::getModel('salesrule/rule')->load($rule_id)->getToDate(): $coupond_expire_date ;
    }
}
