<?php

class D1m_CouponRule_Block_Adminhtml_Promo_Quote_Edit_Tab_CouponsMessageSent
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
        return Mage::helper('couponRule/data')->__('Sent Message');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {        
        return Mage::helper('couponRule/data')->__('Sent Message');
    }

   /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        $model = Mage::registry('current_promo_quote_rule');
//debug
//Mage::helper('logger/data')->info($model->getData());
        //mage rule
        if ($model->getCouponType() && ($model->getCouponType() != Mage_SalesRule_Model_Rule::COUPON_TYPE_NO_COUPON  &&  $model->getUseAutoGeneration() != 1)){
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
