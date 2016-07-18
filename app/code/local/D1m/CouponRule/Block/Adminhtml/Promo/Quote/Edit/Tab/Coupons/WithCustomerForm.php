<?php

class D1m_CouponRule_Block_Adminhtml_Promo_Quote_Edit_Tab_Coupons_WithCustomerForm
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
//Mage::helper('logger/data')->info($model->getData());

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


        $fieldset->addField('length', 'text', array(
            'name'     => 'length',
            'label'    => Mage::helper('salesrule')->__('Code Length'),
            'title'    => Mage::helper('salesrule')->__('Code Length'),
            'required' => true,
            'note'     => Mage::helper('salesrule')->__('Excluding prefix, suffix and separators.'),
            'value'    => $couponHelper->getDefaultLength(),
            'class'    => 'validate-digits validate-greater-than-zero'
        ));

        $fieldset->addField('format', 'select', array(
            'label'    => Mage::helper('salesrule')->__('Code Format'),
            'name'     => 'format',
            'options'  => $couponHelper->getFormatsList(),
            'required' => true,
            'value'    => $couponHelper->getDefaultFormat()
        ));

        $fieldset->addField('prefix', 'text', array(
            'name'  => 'prefix',
            'label' => Mage::helper('salesrule')->__('Code Prefix'),
            'title' => Mage::helper('salesrule')->__('Code Prefix'),
            'value' => $couponHelper->getDefaultPrefix()
        ));

        $fieldset->addField('suffix', 'text', array(
            'name'  => 'suffix',
            'label' => Mage::helper('salesrule')->__('Code Suffix'),
            'title' => Mage::helper('salesrule')->__('Code Suffix'),
            'value' => $couponHelper->getDefaultSuffix()
        ));

        $fieldset->addField('dash', 'text', array(
            'name'  => 'dash',
            'label' => Mage::helper('salesrule')->__('Dash Every X Characters'),
            'title' => Mage::helper('salesrule')->__('Dash Every X Characters'),
            'note'  => Mage::helper('salesrule')->__('If empty no separation.'),
            'value' => $couponHelper->getDefaultDashInterval(),
            'class' => 'validate-digits'
        ));


        $customerGroups = Mage::getResourceModel('customer/group_collection')->load()->toOptionArray();
        $found = false;

        foreach ($customerGroups as $group) {
            if ($group['value']==0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array(
                    'value' => 0,
                    'label' => Mage::helper('salesrule')->__('NOT LOGGED IN'))
            );
        }

        $fieldset->addField('coupon_customer_group_ids', 'multiselect', array(
            'name'      => 'coupon_customer_group_ids[]',
            'label'     => Mage::helper('salesrule')->__('Customer Groups'),
            'title'     => Mage::helper('salesrule')->__('Customer Groups'),
            'required'  => false,
            'values'    => Mage::getResourceModel('customer/group_collection')->toOptionArray(),
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
