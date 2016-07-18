<?php
/**
 * @author song
 * @copyright Copyright (c) 2013 D1M.
 */
class D1m_CouponRule_Model_Observer extends Mage_Core_Model_Abstract {

    /***
     *  通过传值，来格式化MAGENTO的时间
     */
    private function _formatDatetime($format,$timestamp=NULL){
        return empty($timestamp)?Mage::app()->getLocale()->date()->toString($format):Mage::app()->getLocale()->date($timestamp)->toString($format);
    }

    /**
     * 添加与客户绑定的优惠券以及与事件绑定的类型
     * on Magento >= 1.4.1.0
     *
     * @param   Varien_Event_Observer $observer
     */
    public function enableAutoCouponType ($observer)
    {
        $transport = $observer->getEvent()->getTransport();
        //$transport->setIsCouponTypeAutoVisible(true);
        $types = $transport->getCouponTypes();
        $types[D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER] = Mage::helper('couponRule/data')->__('绑定用户的优惠券');
      //  $types[D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT] = Mage::helper('couponRule/data')->__('优惠券不绑定用户');
       // $types[D1m_CouponRule_Model_Rule::COUPON_TYPE_CREDITS_AUTO_GENERATE_WITH_CUSTOMER] = Mage::helper('couponRule/data')->__('课点优惠券:绑定用户');
      //  $types[D1m_CouponRule_Model_Rule::COUPON_TYPE_CREDITS_AUTO_GENERATE_FOR_EVENT] = Mage::helper('couponRule/data')->__('课点优惠券:不指定用户');
        $types = $transport->setCouponTypes($types);
    }

    /**
     *
     *  添加排除正价商品的字段
     * @param $observer
     */
    public function  addOriginalPriceField($observer){

        $form = $observer->getEvent()->getForm();
        $current_rule = Mage::registry('current_promo_quote_rule');

        $prefix = $form->getHtmlIdPrefix();
        $fieldset = $form->getElement('base_fieldset');

        $fieldset->addField('condition_for_original_price', 'select', array(
            'label'     => Mage::helper('couponRule')->__('this rule condition is just available for original price'),
            'title'     => Mage::helper('couponRule')->__('this rule condition is just available for original price'),
            'name'      => 'condition_for_original_price',
            'value'     =>  $current_rule?$current_rule->getConditionForOriginalPrice():'',
            'options'   => array(
                '1' => Mage::helper('salesrule')->__('Yes'),
                '0' => Mage::helper('salesrule')->__('No'),
            ),
        ));




        $fieldset->addField('just_for_original_price', 'select', array(
            'label'     => Mage::helper('couponRule')->__('this rule price is just available for original price'),
            'title'     => Mage::helper('couponRule')->__('this rule price is just available for original price'),
            'name'      => 'just_for_original_price',
            'value'     =>  $current_rule?$current_rule->getJustForOriginalPrice():'',
            'options'    => array(
                '1' => Mage::helper('salesrule')->__('Yes'),
                '0' => Mage::helper('salesrule')->__('No'),
            ),
        ));

/*
 *      临时取消
        $fieldset->addField('rule_base_on_original_price', 'select', array(
            'label'     =>  Mage::helper('couponRule')->__('rule base on price type'),
            'title'     =>  Mage::helper('couponRule')->__('rule base on price type'),
            'name'      =>  'rule_base_on_original_price',
            'value'     =>  $current_rule->getRuleBaseOnOriginalPrice(),
            'options'   =>  Mage::getModel('couponRule/salesRule_PriceType')->toOptionArray()
        ));*/

     }

    /**
     * 添加用户注册之后,生成优惠券
     * @param $observer  $observer
     */
    public function newCustomerCouponGenerator(Varien_Event_Observer $observer) {

        Mage::log('##################################### the Register Customer Generate Coupon code START #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

        // 1) generate coupon code
        $customer        = $observer->getEvent()->getCustomer();
        $coupon_code     = Mage::getModel('couponRule/coupon')->generateNewCustomerCoupon($customer);

        // 2) send message to notice customer
        if ($coupon_code){
            $oCoupon = Mage::getModel('salesrule/coupon')->load($coupon_code, 'code');
            $oRule  =  Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());

            $variables = array(
                'customer' =>   $customer,
                'coupon'    =>  $oCoupon,
                'rule'     =>   $oRule
            );

            if (Mage::helper('couponRule/message')->sendMessageForNewCustomer($oRule,$oRule->getSmsNoticeTemplate(),$oRule->getEmailNoticeTemplate(),$oRule->getMesageBoxNoticeTemplate(),$variables)){
                Mage::log('New Customer Coupon  sent right now! it send success!',null,D1m_Core_Helper_Log::SALE_COUPON_SEND_MESSAGE_LOG_FILE);
            }
        }

        Mage::log('##################################### the Register Customer Generate Coupon code END #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
    }

    /**
     *  订单取消时自动释放优惠券
     * Release used coupon after order cancelation
     *
     * @param Varien_Event_Observer $observer
     */
    public function releaseUsedCoupon(Varien_Event_Observer $observer)
    {
        Mage::log('##################################### release coupon for cancel order START #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

        //是否启用
        if (!Mage::helper('couponRule/config')->getCancelorderToReleaseCouponActive()){
            Mage::log(' debug:: NOT enable release coupon for enable cancel order',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return ;
        }

        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();

        if ($couponCode = $order->getCouponCode()) {
            // Release salesrule_coupon
            $coupon = Mage::getModel('salesrule/coupon');
            $coupon->load($couponCode, 'code');
            $customerId = $order->getCustomerId();
            if ($coupon->getId()) {
                $coupon->setTimesUsed(max($coupon->getTimesUsed() - 1, 0));
                $coupon->save();

                // Release salesrule
                $ruleId = $coupon->getRuleId();
                $rule = Mage::getModel('salesrule/rule');
                $rule->load($ruleId);
                if ($rule->getId()) {
                    $rule->setTimesUsed(max($rule->getTimesUsed() - 1, 0));
                    $rule->save();
                }

                if ($customerId) {
                    // Release salesrule_coupon_usage
                    /* @var $couponUsage Mage_SalesRule_Model_Mysql4_Coupon_Usage */
                    $couponUsage = Mage::getResourceModel('salesrule/coupon_usage');
                    $couponUsage->cancelCouponUsage($customerId,$coupon->getId());

                    // Release salesrule_customer
                    $ruleCustomer = Mage::getModel('salesrule/rule_customer');
                    $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
                    if ($ruleCustomer->getId()) {
                        if (max($ruleCustomer->getTimesUsed() - 1, 0) == 0){
                            $ruleCustomer->delete();
                        }else {
                            $ruleCustomer->setTimesUsed(max($ruleCustomer->getTimesUsed() - 1, 0));
                            $ruleCustomer->save();
                        }
                    }
                }
            }
        }

        Mage::log('debug ::the cancel  coupon code is '. $order->getCouponCode(),null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

        Mage::log('##################################### release coupon for cancel order END #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

        return $this;
    }

    /**
     *  退款时自动释放优惠券
     * Release used coupon after creditmemo created
     *
     * @param Varien_Event_Observer $observer
     */
    public function releaseUsedCouponCreditmemoRefund(Varien_Event_Observer $observer)
    {
        Mage::log('##################################### release coupon basing Creditmemo Refund START #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

        //是否启用
        if (!Mage::helper('couponRule/config')->getcreditmemoRefundToReleaseCouponActive()){
            Mage::log(' debug:: NOT enable release coupon for creditmemoRefund',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return ;
        }

        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $observer->getEvent()->setOrder($creditmemo->getOrder());


        return $this->releaseUsedCoupon($observer);
    }


    /**
     *  保存数据
     */
    public function beforeSaveCustomerData(Varien_Event_Observer $observer){

        if (Mage::helper('couponRule/config')->isUpgradeVipCouponActive() ||
            Mage::helper('couponRule/config')->isUpgradeSilverVipCouponActive() ||
            Mage::helper('couponRule/config')->isUpgradeGoldVipCouponActive())
        {
            $customer = $observer->getEvent()->getCustomer();

            if ($customer->getIsActive())
            {
                $customer  = $customer->load();
                $this->setData('customer_group_id', $customer->getGroupId());
            }
        }

    }

    /**  如果是用户组等级变更，则生成coupon code 并且发送信息
     *   目前不涉及到用户的降级，即只要用户是这个级别，则自动发送
     * @param Varien_Event_Observer $observer
     */
    public function generateCodeAndSendMessage(Varien_Event_Observer $observer){
        $customer = $observer->getEvent()->getCustomer();

        //如果用户等级有变量，则生成coupon code以及发送信息
      if (!is_null($this->getData('customer_group_id')) && !strlen($this->getData('customer_group_id')) &&  $this->getData('customer_group_id') != $customer->getGroupId())
      {

            if ($customer->getGroupId() == D1m_Vip_Helper_Data::GENERAL_VIP_GROUP_ID){
                if (Mage::helper('couponRule/config')->isUpgradeVipCouponActive() && Mage::helper('couponRule/config')->getUpgradeVipCouponCouponRoleId()){
                    $coupon_code = Mage::getModel('couponRule/coupon')->generateUpgradeVipCoupon($customer);
                    $this->_sendMessageBaseCouponCode($coupon_code,$customer);
                }
            }elseif ($customer->getGroupId() == D1m_Vip_Helper_Data::SILVER_VIP_GROUP_ID){
                if (Mage::helper('couponRule/config')->isUpgradeSilverVipCouponActive() && Mage::helper('couponRule/config')->getUpgradeSilverVipCouponCouponRoleId()){
                    $coupon_code = Mage::getModel('couponRule/coupon')->generateUpgradeSilverVipCoupon($customer);
                    $this->_sendMessageBaseCouponCode($coupon_code,$customer);
                }
            }elseif($customer->getGroupId() == D1m_Vip_Helper_Data::GOLD_VIP_GROUP_ID){
                if (Mage::helper('couponRule/config')->isUpgradeGoldVipCouponActive() && Mage::helper('couponRule/config')->getUpgradeGoldVipCouponCouponRoleId()){
                    $coupon_code = Mage::getModel('couponRule/coupon')->generateUpgradeGoldVipCoupon($customer);

                    $this->_sendMessageBaseCouponCode($coupon_code,$customer);
                }
            }

            //unset  var
             Mage::unregister('group_id');
        }
    }

    /**
     *  发送COUPON CODE的信息
     * @param $coupon_code
     * @param Mage_Customer_Model_Customer $customer
     */
    protected function _sendMessageBaseCouponCode($coupon_code,Mage_Customer_Model_Customer $customer){
        if ($coupon_code){
            $oCoupon = Mage::getModel('salesrule/coupon')->load($coupon_code, 'code');
            $oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());

            if ($oRule->getEmailNoticeTemplate() || $oRule->getSmsNoticeTemplate() || $oRule->getMesageBoxNoticeTemplate()){
                $this->_sendMessageForVip($customer,$oRule,$oCoupon);
            }
        }
    }

    /**
     *  发送优惠券相关的信息
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_SalesRule_Model_Rule $oRule
     * @param Mage_SalesRule_Model_Coupon $oCoupon
     */
    protected function _sendMessageForVip(Mage_Customer_Model_Customer $customer,Mage_SalesRule_Model_Rule $oRule,Mage_SalesRule_Model_Coupon $oCoupon){
        //1) email
        if ($oRule->getEmailNoticeTemplate()){
            Mage::helper('d1m_mail')->sendReviewsMail($customer,$oRule,$oCoupon);
        }

        // 2) sms message
        if ($oRule->getSmsNoticeTemplate()){
            Mage::helper('d1m_messageTemplate/sendSms')->sendReviewsPhoneText($customer,$oRule,$oCoupon);
        }

        // 3) inbox message
        if ($oRule->getMesageBoxNoticeTemplate()){
            Mage::helper('advancemsg')->sendReviewsInMessage($customer,$oRule,$oCoupon);
        }
    }

}
