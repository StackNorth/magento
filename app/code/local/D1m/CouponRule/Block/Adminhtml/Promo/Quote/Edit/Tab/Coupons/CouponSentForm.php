<?php

class D1m_CouponRule_Block_Adminhtml_Promo_Quote_Edit_Tab_Coupons_CouponSentForm
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

//Mage::helper('logger/data')->info($model->getData());

        //如果规则TYPE为自动,则自动隋隐藏
        if ($model->getCouponType() && ($model->getCouponType() != D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT)){

            $fieldset = $form->addFieldset('sent_coupon_methods_information_fieldset', array('legend'=>Mage::helper('couponRule/data')->__('Sent Method Options')));
            $fieldset->addClass('ignore-validate');

            $sent_method_type = $fieldset->addField('sent_method_type', 'select', array(
                'label'    => Mage::helper('couponRule/data')->__('send method'),
                'name'     => 'sent_method_type',
                'options'  => Mage::helper('couponRule/data')->sendCouponType(),
                'required' => true,
                'value'    =>  $model->getSentMethodType(),
                'class'    => 'validate-select'
            ));

            $send_date       = $fieldset->addField('send_date', 'date', array(
                'name'      => 'send_date',
                'time'      => true,
                'format'    => 'yyyy-MM-dd HH:mm:ss',
                "input_format" => 'yyyy-MM-dd HH:mm:ss',
                'label'     => Mage::helper('couponRule/data')->__('Send Date'),
                'value'    =>  $model->getSendDate(),
                'image'    => $this->getSkinUrl('images/grid-cal.gif')
            ));
        }

        //不管是什么类型，均是可以发送的
        $templates_fieldset = $form->addFieldset('sent_coupon_templates_information_fieldset', array('legend'=>Mage::helper('couponRule/data')->__('Sent Message Templates Options')));
        $templates_fieldset->addClass('ignore-validate');

        $templates_fieldset->addField('rule_id', 'hidden', array(
            'name'     => 'rule_id',
            'value'    => $ruleId
        ));


        $is_sent_email   =  $templates_fieldset->addField('is_sent_email', 'select', array(
            'label'     => Mage::helper('couponRule/data')->__('send email to notice customer?'),
            'title'     => Mage::helper('couponRule/data')->__('send email to notice customer?'),
            'name'      => 'is_sent_email',
            'value'    =>   ($model->getEmailNoticeTemplate())?1:0,
            'required' => true,
            'options'    => array(
                D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_NO => Mage::helper('couponRule/data')->__('No'),
                D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_YES => Mage::helper('couponRule/data')->__('Yes'),
            ),
        ));

        $email_notice_template =  $templates_fieldset->addField('email_notice_template', 'select', array(
            'label'    => Mage::helper('couponRule/data')->__('send Email Template'),
            'name'     => 'email_notice_template',
            'options'  =>  Mage::helper('couponRule/data')->getAllemailTemplates(),
            'required' => true,
            'value'    =>  $model->getEmailNoticeTemplate(),
            'class'    => 'validate-select'

        ));

        $is_send_sms    =  $templates_fieldset->addField('is_sent_message', 'select', array(
        'label'     => Mage::helper('couponRule/data')->__('send SMS to notice customer?'),
        'title'     => Mage::helper('couponRule/data')->__('send SMS to notice customer?'),
        'name'      => 'is_sent_message',
        'required' => true,
        'value'    =>   ($model->getSmsNoticeTemplate())?1:0,
        'options'    => array(
            D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_NO  => Mage::helper('couponRule/data')->__('No'),
            D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_YES => Mage::helper('couponRule/data')->__('Yes'),
        ),
    ));

        $sms_notice_template =  $templates_fieldset->addField('sms_notice_template', 'select', array(
            'label'    => Mage::helper('couponRule/data')->__('send Message Template'),
            'name'     => 'sms_notice_template',
            'required' => true,
            'class'    => 'validate-select',
            'value'    =>  $model->getSmsNoticeTemplate(),
            'options'  =>  Mage::helper('d1m_messageTemplate/data')->getAllActiveSmsTemplates()
        ));

        $is_send_inboxMessage    =  $templates_fieldset->addField('is_sent_inboxMessage', 'select', array(
            'label'     => Mage::helper('couponRule/data')->__('send Inbox Message to  notice customer?'),
            'title'     => Mage::helper('couponRule/data')->__('send Inbox Message to  notice customer?'),
            'name'      => 'is_sent_inboxMessage',
            'value'    =>   ($model->getMesageBoxNoticeTemplate())?1:0,
            'required' => true,
            'options'    => array(
                D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_NO  => Mage::helper('couponRule/data')->__('No'),
                D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_YES => Mage::helper('couponRule/data')->__('Yes'),
            ),
        ));

        $inboxMessage_notice_template =  $templates_fieldset->addField('mesage_box_notice_template', 'select', array(
            'label'    => Mage::helper('couponRule/data')->__('send Message Template'),
            'name'     => 'mesage_box_notice_template',
            'required' => true,
            'class'    => 'validate-select',
            'value'    =>  $model->getMesageBoxNoticeTemplate(),
            'options'  =>  Mage::getModel('advancemsg/system_config_source_templateOptions')->toOptionArray()
        ));


        if (!$model->getId()) {
            $model->setData('is_sent_email', '1');
        }


        $templates_fieldset->addField('send_message_button', 'note', array(
            'text' => $this->getButtonHtml(
                Mage::helper('couponRule/data')->__('Send Message Or Save It'),
                "sendMessageOrSave('{$this->getUrl('*/*/dealmessage')}')",
                'scalable save'
            )
        ));

        $this->setForm($form);

        // field dependencies
        if ($model->getCouponType() && ($model->getCouponType() != D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT)){

            $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                    ->addFieldMap($sent_method_type->getHtmlId(), $sent_method_type->getName())
                    ->addFieldMap($send_date->getHtmlId(), $send_date->getName())
                    ->addFieldMap($is_sent_email->getHtmlId(), $is_sent_email->getName())
                    ->addFieldMap($email_notice_template->getHtmlId(), $email_notice_template->getName())
                    ->addFieldMap($is_send_sms->getHtmlId(), $is_send_sms->getName())
                    ->addFieldMap($sms_notice_template->getHtmlId(), $sms_notice_template->getName())
                    ->addFieldMap($is_send_inboxMessage->getHtmlId(), $is_send_inboxMessage->getName())
                    ->addFieldMap($inboxMessage_notice_template->getHtmlId(), $inboxMessage_notice_template->getName())
                    ->addFieldDependence(
                        $send_date->getName(),
                        $sent_method_type->getName(),
                        D1m_CouponRule_Helper_Data::COUPON_SENT_METHOD_CRONTAB)
                    ->addFieldDependence(
                        $email_notice_template->getName(),
                        $is_sent_email->getName(),
                        D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_YES)
                    ->addFieldDependence(
                        $sms_notice_template->getName(),
                        $is_send_sms->getName(),
                        D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_YES)
                    ->addFieldDependence(
                        $inboxMessage_notice_template->getName(),
                        $is_send_inboxMessage->getName(),
                        D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_YES)
            );
        } else {
            // field dependencies
            $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                    ->addFieldMap($is_sent_email->getHtmlId(), $is_sent_email->getName())
                    ->addFieldMap($email_notice_template->getHtmlId(), $email_notice_template->getName())
                    ->addFieldMap($is_send_sms->getHtmlId(), $is_send_sms->getName())
                    ->addFieldMap($sms_notice_template->getHtmlId(), $sms_notice_template->getName())
                    ->addFieldMap($is_send_inboxMessage->getHtmlId(), $is_send_inboxMessage->getName())
                    ->addFieldMap($inboxMessage_notice_template->getHtmlId(), $inboxMessage_notice_template->getName())
                    ->addFieldDependence(
                        $email_notice_template->getName(),
                        $is_sent_email->getName(),
                        D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_YES)
                    ->addFieldDependence(
                        $sms_notice_template->getName(),
                        $is_send_sms->getName(),
                        D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_YES)
                    ->addFieldDependence(
                        $inboxMessage_notice_template->getName(),
                        $is_send_inboxMessage->getName(),
                        D1m_CouponRule_Helper_Data::COUPON_SENT_TAG_YES)
            );
        }

        Mage::dispatchEvent('adminhtml_promo_quote_edit_tab_coupons_form_prepare_form', array('form' => $form));

        return parent::_prepareForm();
    }


}
