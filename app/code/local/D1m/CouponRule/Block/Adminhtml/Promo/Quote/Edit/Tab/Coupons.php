<?php
/**
 *  rewrite
 * Class D1m_CouponRule_Block_Adminhtml_Promo_Quote_Edit_Tab_Coupons
 */
class  D1m_CouponRule_Block_Adminhtml_Promo_Quote_Edit_Tab_Coupons extends Mage_Adminhtml_Block_Promo_Quote_Edit_Tab_Coupons
{

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        $priceRule = Mage::registry('current_promo_quote_rule');

      /*  Mage::helper('logger/data')->info($priceRule->getData());*/
        if ($priceRule->getCouponType() == Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO || $priceRule->getUseAutoGeneration() == 1){
            return true;
        }
        return false;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

}
