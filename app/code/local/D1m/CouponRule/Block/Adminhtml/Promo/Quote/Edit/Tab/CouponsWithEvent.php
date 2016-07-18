<?php

class D1m_CouponRule_Block_Adminhtml_Promo_Quote_Edit_Tab_CouponsWithEvent
    extends Mage_Adminhtml_Block_Text_List
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('salesrule')->__('Manage Coupon Codes');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {        
        return Mage::helper('salesrule')->__('Manage Coupon Codes');
    }

   /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        $model = Mage::registry('current_promo_quote_rule');
/*Mage::helper('logger/data')->info('current rule type is '.$model->getCouponType());
Mage::helper('logger/data')->info('auto defined rule id is '.D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER);
Mage::helper('logger/data')->info($model->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER);
Mage::helper('logger/data')->info($model->getData());*/
        if ($model->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT){
            return true;
        } else {
            return false;
        }

    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
       return false;
    }

    /**
     * Check whether we edit existing rule or adding new one
     *
     * @return bool
     */
    protected function _isEditing()
    {
        $priceRule = Mage::registry('current_promo_quote_rule');
        return !is_null($priceRule->getRuleId());
    }
}
