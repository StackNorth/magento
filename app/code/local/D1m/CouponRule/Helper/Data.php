<?php
/*
 * @author song
 * @copyright Copyright (c) 2013 D1M.
 */
class D1m_CouponRule_Helper_Data extends Mage_Core_Helper_Abstract {

    //send message crontab
    const COUPON_SENT_METHOD_RIGHT_NOW = 1;
    const COUPON_SENT_METHOD_CRONTAB   = 2;

    //是否需要发送
    const COUPON_SENT_TAG_YES  = 1;
    const COUPON_SENT_TAG_NO   = 0;


    //是否已经发送
    const  MESSAGE_SENT_FLAG_YES = 1;
    const  MESSAGE_SENT_FLAG_NO = 0;



    //统一过期时间
    const      COUPON_EXPIRE_DATE_SETTING = 7;

    /**
     *  确定发送COUPON的类型
     */
    public function sendCouponType(){
        return array(
            '' => $this->__('Please Sent Method'),
            self::COUPON_SENT_METHOD_RIGHT_NOW   => $this->__('right now'),
            self::COUPON_SENT_METHOD_CRONTAB      => $this->__('cron'),
        );
    }

    /**
     *  获得所有的EMAIL 模板
     */
    public function getAllemailTemplates(){
        $email_collection = Mage::getResourceModel('core/email_template_collection')
            ->load();

        $email_templates = array(
            '' => Mage::helper('d1m_messageTemplate/data')->__('Please choose the Email template')
        );

        $email_collection = $email_collection->toOptionArray();
        if ($email_templates) {
            foreach ($email_collection as $emailTemplate) {
               $email_templates[$emailTemplate['value']] = $emailTemplate['label'];
            }
        }


        return $email_templates;
    }


    /**
     *  得到当前用户所属组可以的优惠规则
     */
    public function getActiveRuleForCustomerGroup(){

      $aviable_rule_ids   = array();

        //get current account group id
        $customer_group_id  = Mage::getModel('customer/customer')->load($this->getSession()->getId())->getGroupId();
        $rule_for_groupid   = Mage::getResourceModel('salesrule/rule_collection')->addFieldToFilter('is_active',1);
        foreach ($rule_for_groupid as $key => $rule){
            if (in_array($customer_group_id, $rule->getCustomerGroupIds())) {
                $aviable_rule_ids[$key] = $rule->getRuleId();
            }
        }

        return  $aviable_rule_ids;
    }



    /**
     *  验证 COUPON的可用性
     *  会检测优惠券的用户绑定关系
     * @param $rule
     * @param $code
     * @return bool
     */
    public function validatorCoupon($rule,$code) {
        $coupon = Mage::getModel('salesrule/coupon');
        $coupon->load($code, 'code');
        $ruleId = $rule->getId();
        
        if ($coupon->getId()) {
            
            if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
                return false;
            }
            
            $customerId = $this->getSession()->getCustomerId();
            
            //会员判断
            if ($customerId && Mage::getModel('couponRule/coupon')->getCollection()->getCouponCodeBycustomerId($customerId,$code,$ruleId) == false){
                   return false;
            } elseif ($customerId && $coupon->getUsagePerCustomer()) {
            // coupon useage
                $couponUsage = new Varien_Object();
                Mage::getResourceModel('salesrule/coupon_usage')->loadByCustomerCoupon(
                        $couponUsage, $customerId, $coupon->getId());
                if ($couponUsage->getCouponId() &&
                        $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()
                ) {
                    return false;
                }
            } 
        }
        

        if ($ruleId && $rule->getUsesPerCustomer()) {
            $customerId     = $this->getSession()->getCustomerId();
            $ruleCustomer   = Mage::getModel('salesrule/rule_customer');
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    return false;
                }
            }
        }
        
        return true;
    }


    /**
     * 验证优惠券是否有效
     * 不会检测用户与优惠券的绑定关系
     * @param $rule
     * @param $code
     * @return bool
     */
    public function validatorCouponForUse($rule,$code,$customerId) {
        $coupon = Mage::getModel('salesrule/coupon');
        $coupon->load($code, 'code');
        if ($coupon->getId()) {
            if ($coupon->getUsageLimit() && $coupon->getTimesUsed() >= $coupon->getUsageLimit()) {
                return false;
            }
            if ($customerId && $coupon->getUsagePerCustomer()) {
                $couponUsage = new Varien_Object();
                Mage::getResourceModel('salesrule/coupon_usage')->loadByCustomerCoupon(
                    $couponUsage, $customerId, $coupon->getId());
                if ($couponUsage->getCouponId() &&
                    $couponUsage->getTimesUsed() >= $coupon->getUsagePerCustomer()
                ) {
                    return false;
                }
            }
        }

        $ruleId = $rule->getId();
        if ($ruleId && $rule->getUsesPerCustomer()) {
            $ruleCustomer   = Mage::getModel('salesrule/rule_customer');
            $ruleCustomer->loadByCustomerRule($customerId, $ruleId);
            if ($ruleCustomer->getId()) {
                if ($ruleCustomer->getTimesUsed() >= $rule->getUsesPerCustomer()) {
                    return false;
                }
            }
        }

        return true;
    }

    /** 获取咱俩唯一的email
     * @param array $customer_collections
     */
    public function getUnqiueEmail(array $customer_collections){
        $customer_emails = array();
        foreach ($customer_collections as $customer){
            $customer_emails[$customer['entity_id']] = $customer['email'];
        }
        return $customer_emails;
    }


    public function getSession(){
        return Mage::getSingleton('customer/session');
    }

    /**
     *   返回COUPON已经使用的次数
     * @param $coupon_per_use
     * @param $coupon_id
     * @param $ruleId
     * @return null
     */
    public function hasAllUseForCoupon($coupon_per_use,$coupon_id,$ruleId){
        $customer_id = Mage::helper('couponRule')->getSession()->getCustomerId();
        $rule_coupon = Mage::getModel('salesrule/coupon')->load($coupon_id);

        if ($rule_coupon->getUsagePerCustomer()){
            $ruleCustomer          = Mage::getModel('salesrule/rule_customer')->loadByCustomerRule($customer_id, $ruleId);
            $rule_coupon_time_used = $ruleCustomer->getTimesUsed();
        }

        if (!empty($rule_coupon_time_used)) {
            return $rule_coupon_time_used;
        }
        return null;
    }


    /**
     *  获得当前用户即将过期的优惠券数量
     * @return mixed
     */
    public function   getCurrentCustomerExpireCouponList(){
         return Mage::getModel('couponRule/coupon')->getBeExpiredCouponAmount();
    }
    /**
     * 得到优惠券的过期时间
     * @param type $couponExpireDate
     * @param type $RuleExpireDate
     */
    public function formatExpireDate($couponExpireDate,$ruleExpireDate){

        if (!empty($couponExpireDate) && !empty($ruleExpireDate)) {
            return Mage::app()->getLocale()->date(strtotime($couponExpireDate))->toString('yyyy-M-d');
        } elseif (!empty($couponExpireDate) && empty($ruleExpireDate)){
            return  Mage::app()->getLocale()->date(strtotime($couponExpireDate))->toString('yyyy-M-d');
        } elseif (empty($couponExpireDate) && !empty($ruleExpireDate)){
            return $ruleExpireDate;
        } else{
            return Mage::helper('couponRule/data')->__('-');
        }
    }

    /**
     *  判断是否已经过期
     * @param $couponExpireDate
     * @param $ruleExpireDate
     * @return bool
     */
    public function isExpireForCoupon($couponExpireDate,$ruleExpireDate){
        if ($this->formatExpireDate($couponExpireDate,$ruleExpireDate) != '-'){

            if (strtotime($this->formatExpireDate($couponExpireDate,$ruleExpireDate)) < strtotime(Mage::app()->getLocale()->date()->toString('yyyy-MM-dd'))){
                return true;
            }
        }
    }

    /**
     * 如果COUPON有定义expire date，则使用自己的expire date
     * @param $couponExpireDate
     * @param $ruleExpireDate
     * @return bool   true 即将过期
     */
    public function compareExpire($couponExpireDate,$ruleExpireDate){

        //测试后台配置是否已经开启
        if (Mage::helper('couponRule/config')->getExpireDateSettingActive()){
            $couponSettingExpireDate = Mage::helper('couponRule/config')->getExpireDate();
        } else {
            $couponSettingExpireDate  = self::COUPON_EXPIRE_DATE_SETTING;
        }

        if ($this->formatExpireDate($couponExpireDate,$ruleExpireDate) != '-'){

            $compare_time = strtotime($this->formatExpireDate($couponExpireDate,$ruleExpireDate))-time();

            if ($compare_time<($couponSettingExpireDate*24*60*60)){
                return true;
            }
        }
    }

    /**
     *  保存并且对应客户与优惠券的关系
     * @param type $observer
     */
    public function addCustomerAndCoupon(array $couponInfo) {

        $error      = false;
        $errr_info  = array();
        $customerInfo = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('email', trim($couponInfo['user_email']))->load()->getData();


        if (!Zend_Validate::is(trim($couponInfo['user_email']), 'EmailAddress')) {
            $error = true;
            $errr_info[] = Mage::helper('couponRule/data')->__('the email address format is incorrect');
        }

        if (!isset($customerInfo[0]['entity_id']) ||  empty($customerInfo[0]['entity_id'])){
            $error = true;
            $errr_info[] = Mage::helper('couponRule/data')->__('the customer not exists');
        }

        if ($error == false && count($errr_info) == 0) {
            try {
                $customerCoupon = Mage::getModel('couponRule/coupon');

                $binds = array(
                    'id'              => null,
                    'coupon'         => trim($couponInfo['coupon_code']),
                    'rule_id'        => intval($couponInfo['rule_id']),
                    'customer_id'    => intval($customerInfo[0]['entity_id']),
                    'customer_email' => $couponInfo['user_email']
                );

                if ($customerCoupon->checkIsexists(array('coupon'=>$binds['coupon'])) == true){

                    $customerCoupon = Mage::getModel('couponRule/coupon');
                    $customerCoupon->setData($binds);
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        } else {
            //log error data to log file
            foreach ($errr_info as  $error){
                Mage::getSingleton('adminhtml/session')->addWarning('the data has error-- customer coupon->:'.$couponInfo['coupon_code'].'   the email->: '.$couponInfo['user_email'].' '.Mage::helper('fooman_advancedpromotions')->__($error));
            }
            //log error data to log file
            Mage::log('-----------------------------DEBUG START-----------------------------------',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::log('error customer info: coupon code is '.$couponInfo['coupon_code'],null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::log('error customer info: customer address is '.$couponInfo['user_email'],null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::log($errr_info,7,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::log('-----------------------------DEBUG END-----------------------------------',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
        }
    }

    /**
     *  获得当前用户可以使用的优惠券
     */
    public function getCanUseCouponAmount(){

        //init
        $is_all_used = false;
        $available_coupon_amount = 0 ;
        $customer_id = (int)Mage::getSingleton('customer/session')->getId();

        // 1) 获得当前用户可以使用的coupon
           $available_rule_ids   =  Mage::helper('couponRule')->getActiveRuleForCustomerGroup();
           $rule_ids = join(',', $available_rule_ids);
           $coupon_collections = Mage::getResourceModel('couponRule/coupon')->getAllCouponListForCustomer($customer_id,$rule_ids);

        // 2）对满足条件的coupon进行加1
       if ($coupon_collections && $coupon_collections->getSize()){
              foreach ($coupon_collections as $coupon_info){
                  $coupon_expire_flag    = Mage::helper('couponRule')->isExpireForCoupon($coupon_info->getExpirationDate(),$coupon_info->getToDate());

                  if ($coupon_info->getUsagePerCustomer()){
                      $ruleCustomer          = Mage::getModel('salesrule/rule_customer')->loadByCustomerRule($customer_id, $coupon_info->getRuleId());
                      $rule_coupon_time_used = $ruleCustomer->getTimesUsed();
                      $coupon_per_use =  $coupon_info->getUsagePerCustomer();
                      if ($rule_coupon_time_used > $coupon_per_use){
                            $is_all_used = true;
                      }
                  }

                  //compare
                  if (!$coupon_expire_flag && $is_all_used == false){
                        $available_coupon_amount ++;
                  }
              }
        }

        return $available_coupon_amount;
    }


    /**
     *  判断规则是否排斥
     * @param $ruleId
     * @return bool
     */
    public function enableSaleRuleOriginalPrice( $ruleId ){

        $saleRule=Mage::getModel("salesrule/rule")->load( $ruleId );
        if( $saleRule  ){
            if( $saleRule->getData('condition_for_original_price')
                ==D1m_CouponRule_Model_Improvesalerule::CONDITION_AVAILABLE_ORIGINAL_PRICE )
            {
                return true;
            }
            return false;
        }

        return false;
    }



}