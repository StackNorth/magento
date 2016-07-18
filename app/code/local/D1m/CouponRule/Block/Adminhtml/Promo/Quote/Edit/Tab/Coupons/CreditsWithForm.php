<?php

/** 添加事件与用户绑定的功能
 * Class D1m_CouponRule_Block_Adminhtml_Promo_Quote_Edit_Tab_Coupons_CreditsWithForm
 */
class D1m_CouponRule_Block_Adminhtml_Promo_Quote_Edit_Tab_Coupons_CreditsWithForm
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Prepare coupon codes generation parameters form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        /**
         * @var Mage_SalesRule_Helper_Coupon $couponHelper
         */
        $couponHelper = Mage::helper('salesrule/coupon');

        $model = Mage::registry('current_promo_quote_rule');
        $ruleId = $model->getId();

        $form->setHtmlIdPrefix('coupons_');

        $gridBlock = $this->getLayout()->getBlock('promo_quote_edit_tab_coupons_grid');
        $gridBlockJsObject = '';
        if ($gridBlock) {
            $gridBlockJsObject = $gridBlock->getJsObjectName();
        }

        $fieldset = $form->addFieldset('information_fieldset', array('legend'=>Mage::helper('salesrule')->__('Coupons Information')));
        $fieldset->addClass('ignore-validate');

        $fieldset->addField('rule_id', 'hidden', array(
            'name'     => 'rule_id',
            'value'    => $ruleId
        ));

        $fieldset->addField('event_uses_per_coupon', 'text', array(
            'name' => 'event_uses_per_coupon',
            'label' => Mage::helper('salesrule')->__('Uses per Customer'),
        ));


        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('event_from_date', 'date', array(
            'name'   => 'event_from_date',
            'label'  => Mage::helper('salesrule')->__('From Date'),
            'title'  => Mage::helper('salesrule')->__('From Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));
        $fieldset->addField('event_to_date', 'date', array(
            'name'   => 'event_to_date',
            'label'  => Mage::helper('salesrule')->__('To Date'),
            'title'  => Mage::helper('salesrule')->__('To Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));



        $fieldset->addField('customer_email_collection', 'textarea', array(
            'name'  => 'customer_email_collection',
            'label' => Mage::helper('couponRule/data')->__('customer email'),
            'title' => Mage::helper('couponRule/data')->__('customer email'),
            'note'  => Mage::helper('couponRule/data')->__('It May be Empty. Or please use comma to seperate customer email.')
        ));

        $idPrefix = $form->getHtmlIdPrefix();
        $generateUrl = $this->getGenerateUrl();

        $fieldset->addField('generate_button', 'note', array(
            'text' => $this->getButtonHtml(
                Mage::helper('salesrule')->__('Generate'),
                "generateCouponCodes('{$idPrefix}' ,'{$generateUrl}', '{$gridBlockJsObject}')",
                'generate'
            )
        ));

        $this->setForm($form);

        Mage::dispatchEvent('adminhtml_promo_quote_edit_tab_coupons_form_prepare_form', array('form' => $form));

        return parent::_prepareForm();
    }

    /**
     * Retrieve URL to Generate Action
     *
     * @return string
     */
    public function getGenerateUrl()
    {
        return $this->getUrl('*/*/generate');
    }
}
