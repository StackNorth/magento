<?php

class D1m_CouponRule_Block_Adminhtml_Customer_Edit_Tabs extends Mage_Adminhtml_Block_Customer_Edit_Tabs
{

    protected function _beforeToHtml()
    {

        $this->addTab('coupon', array(
            'label' => Mage::helper('couponRule/data')->__('Coupon Information'),
            'title' => Mage::helper('couponRule/data')->__('Coupon Information'),

            'content' =>  $this->getLayout()->createBlock('couponRule/adminhtml_customer_edit_tab_coupon_grid')->toHtml()
        ));

        return parent::_beforeToHtml();
    }

}
