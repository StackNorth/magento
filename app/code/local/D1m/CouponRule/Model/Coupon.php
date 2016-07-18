<?php
/**
 * @author Song
 * @copyright Copyright (c) 2013 FirstSecond.
 */

class D1m_CouponRule_Model_Coupon extends Mage_Core_Model_Abstract {

    //put your code here
    protected $_generated_count = 0;


    public function __construct() {
        parent::__construct();
        $this->_init('couponRule/coupon');
    }
    
    /**
     *  如果规则ID，邮箱名以及coupon相同，则不进行修改
     *  如果通过 过滤条件 找到存在记录，返回false, 否则返回true
     * @param array $couponInfo
     */
    public function checkIsexists(array $couponInfo){
        
        $customer_coupon_collections = Mage::getResourceModel('couponRule/coupon_collection');
        if (!empty($couponInfo['coupon']) && isset($couponInfo['coupon'])){
            $customer_coupon_collections = $customer_coupon_collections->addFieldToFilter('coupon', $couponInfo['coupon']);
        }
        if (!empty($couponInfo['rule_id']) && isset($couponInfo['rule_id'])){
            $customer_coupon_collections->addFieldToFilter('rule_id', $couponInfo['rule_id']);
        } 
        if (!empty($couponInfo['customer_email']) && isset($couponInfo['customer_email'])){
            $customer_coupon_collections->addFieldToFilter('customer_email', $couponInfo['customer_email']);
        }

        if (count($customer_coupon_collections->getData()) == 0){
            return true;
        } else {
            return false;
        }
    }
    
   /**
    *  根据所生成的CODE是否已经存在
    * @param type $code
    */
   public function checkCouponExist($code){
       $read_resource  = Mage::getSingleton('core/resource')->getConnection('core_read');
       $tableName      = Mage::getSingleton('core/resource')->getTableName('salesrule/coupon');
       $select = $read_resource->select();
       $select->from($tableName, 'code');
       $select->where('code = :code');

       if ($read_resource->fetchOne($select, array('code' => $code)) === false) {
            return false;
        }
        return true;
   }

    /**
     * @ 三种VIP的升级有着大量的共用代码，因此可以直接重用
     */
    protected function _getRuleCouponData(array $data,$code){
        $rule                         =  Mage::getModel('salesrule/rule')->load($data['rule_id']);
        $couponUsageDate             =   Mage::helper('couponRule/config')->getVipUsageDate();
        $expire_date_unixStamp = strtotime('+'.$couponUsageDate.' day');

        $ruleCouponData  = array(
            'id'                    =>  NULL,
            'rule_id'              =>  $data['rule_id'],
            'code'                 =>  $code,
            'usage_per_customer' =>  $rule->getUsesPerCustomer(),
            'begin_use_date'      =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss'),
            'expiration_date'     =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss',$expire_date_unixStamp),
            'created_at'           =>  Mage::helper('d1m_core/date')->formatDatetime(),
            'type'                  =>  D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT,
        );
        return $ruleCouponData;
    }
   
   /**
    *  根据 Coupon来获取
    */
   public function loadCouponRuleByCoupon(Mage_SalesRule_Model_Coupon $rule_coupon){
       
       if ($rule_coupon instanceof Mage_SalesRule_Model_Coupon){
           $coupon_mapping_id = Mage::getResourceModel('couponRule/coupon')->loadByCouponcode($rule_coupon->getRuleId(),$rule_coupon->getCode());

             if (!empty($coupon_mapping_id)){
                  return $coupon_mapping_id;
             }
       }
   }
   

    /**
     * 新用户注册时 发送COUPON CODE
     */
    public function generateNewCustomerCoupon(Mage_Customer_Model_Customer $customer){

        // 1)安全性检测
        if (!Mage::helper('couponRule/config')->isNewCustomerCouponActive()){
            Mage::log(' debug:: generate new customer configuration  NOT INACTIVE',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return ;
        }

        if (!Mage::helper('couponRule/config')->getNewCustomerCouponCouponRoleId()){
            /*  echo Mage::helper('couponRule/data')->__('no base new customer rule choose!Please Enable it!');*/
            Mage::log('debug:: no base new customer rule choose. or the rule is not active or exists!',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return;
        }

        // 2) 生成新用户注册优惠券
        $rule = Mage::getModel('salesrule/rule')->load(Mage::helper('couponRule/config')->getNewCustomerCouponCouponRoleId());
        $data     =  Mage::helper('couponRule/config')->getNewCustomerGenerateConfig();
        $code = Mage::helper('couponRule/generate')->generateCouponCode($data);

        if ($code) {
            //如果此规则不存在，并且邮箱已经存在
            if (Mage::getModel('couponRule/coupon')->checkIsexists(array('rule_id'=>Mage::helper('couponRule/config')->getNewCustomerCouponCouponRoleId(),'customer_email'=>trim($customer->getEmail()))) == true){

                //set from date and expire date
                if (Mage::helper('couponRule/config')->isNewCustomerCouponSettingUseageDateIsactive()){
                    $couponUsageDate = Mage::helper('couponRule/config')->getBrithdayCouponSettingUsageDate();
                } else {
                    $couponUsageDate  = 7;
                }

                //not contain the lastest day
               // $expire_date_uninxStamp = mktime(0,0,0,(int)$this->_formatDatetime('MM',strtotime('+'.$couponUsageDate.' day')),$this->_formatDatetime('d',strtotime('+'.$couponUsageDate.' day')),$this->_formatDatetime('yyyy',strtotime('+'.$couponUsageDate.' day')));
                $expire_date_unixStamp = strtotime('+'.$couponUsageDate.' day');

                try {
                    $ruleCouponData  = array(
                        'id'                    =>  NULL,
                        'rule_id'              =>  $data['rule_id'],
                        'code'                 =>  $code,
                        'usage_per_customer' =>  $rule->getUsesPerCustomer(),
                        'begin_use_date'      =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss'),
                        'expiration_date'     =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss',$expire_date_unixStamp),
                        'created_at'           =>  Mage::helper('d1m_core/date')->formatDatetime(),
                        'type'                  =>  D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT,
                    );

                    //save it
                    if (Mage::helper('couponRule/generate')->saveCouponCode($ruleCouponData,$customer,'newCustomer')){
                        return $code;
                    }

                    //generate success information
                    Mage::log('the new customer coupon information! customer email is '.$customer->getEmail().' the coupon code is '.$code ,null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

                } catch (Exception $e) {
                    Mage::log('debug: new customer register has error occur .the customer infomation as following',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
                    Mage::log($customer->getData(),7,'D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE');
                    Mage::logException($e);
                }
            } else {
                Mage::log('debug: this customer email has exists in mapping customer. the customer information ,email address is '.$customer->getEmail(),null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            }
        }


    }

    /**
     *  生成评论的优惠券
     */
    public function generateReviewCoupon(Mage_Customer_Model_Customer $customer){

        Mage::log('##################################### the Reviews Coupon Generate  code START #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

        // 1) 安全性检测
        $config_data_flag = Mage::helper('couponRule/generate')->checkSecurity(array(
            'is_active' => Mage::helper('couponRule/config')->isReviewsCouponActive(),
            'rule_id'   => Mage::helper('couponRule/config')->getReviewsCouponRoleId()
        ));

        //2) 生成 评论优惠券
        $data                         = Mage::helper('couponRule/config')->getReviewsCouponGenerateConfig();
        if ($config_data_flag) $code = Mage::helper('couponRule/generate')->generateCouponCode($data);


        // 3) 保存优惠券的信息
        if ($code) {
                $rule                         =  Mage::getModel('salesrule/rule')->load($data['rule_id']);

                    //set from date and expire date
                    if (Mage::helper('couponRule/config')->isReviewsCouponUseageDateIsactive()){
                        $couponUsageDate = Mage::helper('couponRule/config')->getReviewsCouponSettingUsageDate();
                    } else {
                        $couponUsageDate  = 15;
                    }
                    //not contain the day
                    $expire_date_unixStamp = strtotime('+'.$couponUsageDate.' day');

                    $ruleCouponData  = array(
                        'id'                    =>  NULL,
                        'rule_id'              =>  $data['rule_id'],
                        'code'                 =>  $code,
                        'usage_per_customer' =>  $rule->getUsesPerCustomer(),
                        'begin_use_date'      =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss'),
                        'expiration_date'     =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss',$expire_date_unixStamp),
                        'created_at'           =>  Mage::helper('d1m_core/date')->formatDatetime(),
                        'type'                  =>  D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT,
                    );

                //save it
               if (Mage::helper('couponRule/generate')->saveCouponCode($ruleCouponData,$customer,'reviews')){
                    return $code;
                }

            Mage::log('##################################### the Reviews Coupon Generate code END #####################',null,'coupon-error.log');
        }
              return;
     }


    /**
     *  进行普通VIP的升级,然后生成COUPON CODE
     * @param Mage_Customer_Model_Customer $customer
     */
    public function generateUpgradeVipCoupon(Mage_Customer_Model_Customer $customer){
        Mage::log('##################################### the Upgrade VIP Coupon Generate  code START #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

        // 1) 安全性检测
        $config_data_flag = Mage::helper('couponRule/generate')->checkSecurity(array(
            'is_active' => Mage::helper('couponRule/config')->isUpgradeVipCouponActive(),
            'rule_id'   => Mage::helper('couponRule/config')->getUpgradeVipCouponCouponRoleId()
        ));

        //2) 生成 升级到VIP的优惠券
        $data                         = Mage::helper('couponRule/config')->getUpgradeVipGenerateConfig();
        if ($config_data_flag) $code = Mage::helper('couponRule/generate')->generateCouponCode($data);

        // 3) 保存优惠券的信息
        if ($code) {

            //获得要保存的数据
            $ruleCouponData  = $this->_getRuleCouponData($data,$code);

            //save it
            if (Mage::helper('couponRule/generate')->saveCouponCode($ruleCouponData,$customer,'upgrade vip')){
                return $code;
            }

        }

        Mage::log('##################################### the Upgrade VIP Coupon Generate  code END #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
    }



    /**
     *  进行升级到银卡VIP，生成COUPON CODE
     * @param Mage_Customer_Model_Customer $customer
     */
    public function generateUpgradeSilverVipCoupon(Mage_Customer_Model_Customer $customer){
        Mage::log('##################################### the Upgrade Silver VIP Coupon Generate  code START #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

        // 1) 安全性检测
        $config_data_flag = Mage::helper('couponRule/generate')->checkSecurity(array(
            'is_active' => Mage::helper('couponRule/config')->isUpgradeSilverVipCouponActive(),
            'rule_id'   => Mage::helper('couponRule/config')->getUpgradeSilverVipCouponCouponRoleId()
        ));

        //2) 生成 升级到银卡VIP的优惠券
        $data                         = Mage::helper('couponRule/config')->getUpgradeSilverVipGenerateConfig();
        if ($config_data_flag) $code = Mage::helper('couponRule/generate')->generateCouponCode($data);

        // 3) 保存优惠券的信息
        if ($code) {

            //获得要保存的数据
            $ruleCouponData  = $this->_getRuleCouponData($data,$code);

            //save it
            if (Mage::helper('couponRule/generate')->saveCouponCode($ruleCouponData,$customer,'upgrade silver vip')){
                return $code;
            }

        }

        Mage::log('##################################### the Upgrade SILVER VIP Coupon Generate  code END #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
    }

    /**
     *  进行升级到金卡VIP，生成COUPON CODE
     * @param Mage_Customer_Model_Customer $customer
     */
    public function generateUpgradeGoldVipCoupon(Mage_Customer_Model_Customer $customer){
        Mage::log('##################################### the Upgrade Silver VIP Coupon Generate  code START #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

        // 1) 安全性检测
        $config_data_flag = Mage::helper('couponRule/generate')->checkSecurity(array(
            'is_active' => Mage::helper('couponRule/config')->isUpgradeGoldVipCouponActive(),
            'rule_id'   => Mage::helper('couponRule/config')->getUpgradeGoldVipCouponCouponRoleId()
        ));

        //2) 生成 升级到金卡VIP的优惠券
        $data                         = Mage::helper('couponRule/config')->getUpgradeGoldVipGenerateConfig();
        if ($config_data_flag) $code = Mage::helper('couponRule/generate')->generateCouponCode($data);

        // 3) 保存优惠券的信息
        if ($code) {

            //获得要保存的数据
            $ruleCouponData  = $this->_getRuleCouponData($data,$code);

            //save it
            if (Mage::helper('couponRule/generate')->saveCouponCode($ruleCouponData,$customer,'upgrade gold vip')){
                return $code;
            }
        }

        Mage::log('##################################### the Upgrade GOLD VIP Coupon Generate  code END #####################',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
    }

    /**
     *  发送优惠券过期提示
     */
    public function  sendExpireNoticeAlert(){

        Mage::log('############################# expire notice message debug start!############################################# ',null,'sms_send.log');
        Mage::log('expire notice message send time  '.date('Y-M-d',time()),null,'sms_send.log');

        if (Mage::getStoreConfig('couponRule/coupon_expire_alert/enable_coupon_expire_alert') ==1 && Mage::getStoreConfig('couponRule/coupon_expire_alert/expire_date')){

            //得到所有已经激活并且有特定coupon的规则
            $allAviableRules = Mage::getResourceModel('couponRule/coupon')->getAllAvaibleRuleCollections();
            if ($allAviableRules->getSize()>0){
                foreach ($allAviableRules as $rule){

                    if (Mage::helper('couponRule/data')->compareExpire($rule->getToDate(),$rule->getToDate()) == true){

                        $group_customers = Mage::getResourceModel('couponRule/coupon')->getCustomerCollections(explode(',',$rule->getCustomerGroupIds()));
                        $rule_coupon = Mage::getResourceModel('couponRule/coupon')->loadPrimaryByCouponCode(new Mage_SalesRule_Model_Coupon(),$rule->getCode());

                        if ($group_customers && $group_customers->getSize()>0 && $rule_coupon->getExpireNoticeSentFlag() != 1){

                                foreach ($group_customers as $customer_info){
                                    $customer_info_arr = $customer_info->getData();

                                    if ($customer_info_arr['is_active'] == 1){
                                        if (Mage::helper('couponRule/data')->validatorCouponForUse($rule,$rule->getCode(),$customer_info->getCustomerId()) == true){
                                            //send message
                                            $this->_sendMessageToCustomer(
                                                array(
                                                    'rulename'      =>   $rule->getName(),
                                                    'description'   =>   $rule->getDescription(),
                                                    'from_date'     =>   $rule->getFromDate(),
                                                    'to_date'       =>   $rule->getToDate(),
                                                    'firstname'     =>   $customer_info_arr['firstname'],
                                                    'nickname'      =>   $customer_info_arr['nickname'],
                                                    'lastname'      =>   $customer_info_arr['lastname'],
                                                    'email'         =>    $customer_info_arr['email'],
                                                    'mobile'        =>    $customer_info_arr['mobile'],
                                                    'dob'           =>    date('Y-m-d',strtotime($customer_info['dob'])),
                                                    'coupon_code'  =>    $rule->getCode(),
                                                    'expire_date'  =>    Mage::getStoreConfig('couponRule/coupon_expire_alert/expire_date')
                                                ));
                                        }
                                    }

                                }
                         }
                    }

                     //save send status
                    try {
                            if ($rule->getCode()){
                                //echo $rule->getCode();
                                $rule_coupon->setExpireNoticeSentFlag(1)->save();
                            }
                        }catch (Mage_Core_Exception $ce) {
                            Mage::logException($ce);
                        }
                    }
                 }


            //send message to auto generate code and automatic code
            $sms_notice_customers = Mage::getResourceModel('couponRule/coupon')->getNeedSendSmsToCustomer();
            $sms_notice_array = array();

            foreach ($sms_notice_customers as $key => $sms_customer){

                if (Mage::helper('couponRule/data')->compareExpire($sms_customer->getExpirationDate(),$sms_customer->getRuleExpireDate()) == true ){
                    //validate coupon code use liimit and per customer
                    if (Mage::helper('couponRule/data')->validatorCouponForUse(Mage::getModel('salesrule/rule')->load($sms_customer->getRuleId()),$sms_customer->getCode(),$sms_customer->getCustomerId()) == true){

                        //send message
                        $customer_expiration_date  = $sms_customer->getExpirationDate();
                        $this->_sendMessageToCustomer(array(
                            'rulename'       =>  $sms_customer->getRuleName(),
                            'description'   =>   $sms_customer->getRuleDescripion(),
                            'from_date'     =>   $sms_customer->getRuleFromDate(),
                            'to_date'       =>   empty($customer_expiration_date) ? $sms_customer->getRuleExpireDate():$sms_customer->getExpirationDate(),
                            'firstname'     =>   $sms_customer->getFirstname(),
                            'nickname'      =>   $sms_customer->getNickname(),
                            'lastname'      =>   $sms_customer->getLastname(),
                            'email'         =>   $sms_customer->getCustomerEmail(),
                            'mobile'        =>   $sms_customer->getMobile(),
                            'dob'           =>   date('Y-m-d',strtotime($sms_customer->getDob())),
                            'coupon_code'  =>   $sms_customer->getCode(),
                            'expire_date'  =>   Mage::getStoreConfig('couponRule/coupon_expire_alert/expire_date')
                        ));

                        //save coupon id
                        $sms_notice_array[$key] = $sms_customer->getCouponId();
                    }
                }
            }

            Mage::log('coupon message, coupon id list: ',null,D1m_Core_Helper_Log::SEND_SMS_MESSAGE_LOG_FILE);
            Mage::log($sms_notice_array,7,D1m_Core_Helper_Log::SEND_SMS_MESSAGE_LOG_FILE);
            //batch update message status
            if (count($sms_notice_array)>0){
                Mage::getResourceModel('couponRule/coupon')->batchUpdateMessageStatus($sms_notice_array);
            }
        }else {
            Mage::log('expire notice message send setting had wrong. it is disable or not choose expire date ',null,D1m_Core_Helper_Log::SEND_SMS_MESSAGE_LOG_FILE);
        }
        Mage::log('############################# expire notice message debug END!############################################# ',null,D1m_Core_Helper_Log::SEND_SMS_MESSAGE_LOG_FILE);

    }

    /**
     * 获得即将过期的优惠券数量
     */
    public function getBeExpiredCouponAmount(){

        //定义数值变量
        $expire_coupon_count = 0;
        //当前登陆用户的CUSTOMER ID
        $current_customer_id = Mage::getSingleton('customer/session')->getId();

        //得到所有已经激活并且有特定coupon的规则
        $coupon_collection = Mage::getResourceModel('couponRule/coupon')->getAllCouponListForCustomer((int)$current_customer_id,join(',', Mage::helper('couponRule')->getActiveRuleForCustomerGroup()));

        foreach ($coupon_collection as $coupon){
            $rule    =   Mage::getModel('salesrule/rule')->load($coupon->getRuleId());

            //验证rule以及coupon的可用性以及过期时间
            if(Mage::helper('couponRule')->validatorCouponForUse($rule,$coupon->getCode(),$current_customer_id)  && Mage::helper('couponRule')->compareExpire($coupon->getExpirationDate(),$coupon->getToDate())) {
                $expire_coupon_count++;
            }
        }

        return $expire_coupon_count;
    }

    /**
     * @param array $varient_obj
     */
    protected function _sendMessageToCustomer(array $varient_arr){
        $varien_obj = new Varien_Object();
        $varien_obj->setData($varient_arr);
        $coupon_content = Mage::getModel('d1m_sms/message')->filterMessageContent('expire_notice_info',$varien_obj,Mage::helper('d1m_sms/data')->getCouponAlertContent());

        try {
           if ($varien_obj->getMobile()){

               $sms_result =  Mage::getModel('d1m_sms/message')->sendSmsMessage($varien_obj->getMobile(),$coupon_content);
               Mage::log('this expire notice message  had sent ! coupon code is '.$varien_obj->getCode().' the mobile is '.$varien_obj->getMobile(),null,D1m_Core_Helper_Log::SEND_SMS_MESSAGE_LOG_FILE);
           }
        }catch (Mage_Core_Exception $e){
            Mage::logException($e);
        }
    }

    /**
     * Generate coupon after register XXX days
     */
    public function generateAfterRegisterDaysCoupon(Mage_Customer_Model_Customer $customer, $days=7)
    {
        // 1)安全性检测
        if (!Mage::helper('couponRule/config')->isAfterRegisterDaysCouponActive($days)){
            Mage::log(__METHOD__ . " generate after register $days days coupon configuration NOT INACTIVE", null, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return ;
        }

        if (!Mage::helper('couponRule/config')->getAfterRegisterDaysCouponRoleId($days)){
            Mage::log(__METHOD__ . " after register $days days rule is not active or exists!", null, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return;
        }

        // 2) 生成优惠券
        $rule = Mage::getModel('salesrule/rule')->load(Mage::helper('couponRule/config')->getAfterRegisterDaysCouponRoleId($days));
        $data = Mage::helper('couponRule/config')->getAfterRegisterDaysCouponGenerateConfig($days);
        $code = Mage::helper('couponRule/generate')->generateCouponCode($data);

        if ($code) {
            //如果此规则不存在，并且邮箱已经存在
            if (Mage::getModel('couponRule/coupon')->checkIsexists(
                    array(
                        'rule_id'=>Mage::helper('couponRule/config')->getAfterRegisterDaysCouponRoleId($days),
                        'customer_email'=>trim($customer->getEmail())
                    )) == true) {

                //set from date and expire date
                if (Mage::registry('couponUsageDate')){
                    $couponUsageDate  = Mage::registry('couponUsageDate');
                }else{
                    $couponUsageDate  = 7;
                }

                //not contain the lastest day
                $expireDateUnixStamp = strtotime("+$couponUsageDate day");

                try {
                    $ruleCouponData  = array(
                        'id'                    =>  NULL,
                        'rule_id'               =>  $data['rule_id'],
                        'code'                  =>  $code,
                        'usage_per_customer'    =>  $rule->getUsesPerCustomer(),
                        'begin_use_date'        =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss'),
                        'expiration_date'       =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss',$expireDateUnixStamp),
                        'created_at'            =>  Mage::helper('d1m_core/date')->formatDatetime(),
                        'type'                  =>  D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER,
                    );

                    //save it
                    if (Mage::helper('couponRule/generate')->saveCouponCode($ruleCouponData,$customer, "afterRegister{$days}Days")){
                        return $code;
                    }

                    //generate success information
                    Mage::log( __METHOD__ . " the register {$days} days coupon information: customer email is ".$customer->getEmail().' the coupon code is '.$code, null, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

                } catch (Exception $e) {
                    Mage::log( __METHOD__ . " generate register days coupon has error occur .the customer infomation as following",null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
                    Mage::log($customer->getData(), 7, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
                    Mage::logException($e);
                }
            } else {
                Mage::log("[-]debug: generate after register coupon failed, customer email has exists in mapping customer: ".$customer->getEmail(),null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            }
        }
    }


    /**
     * Generate coupon no pucharse XXX days
     */
    public function generateNoPurchaseDaysCoupon(Mage_Customer_Model_Customer $customer, $days=30)
    {

        // 1)安全性检测
        if (!Mage::helper('couponRule/config')->isNoPurchaseDaysCouponActive($days)){
            Mage::log("[-]debug:: generate after register $days days coupon configuration NOT INACTIVE", null, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return ;
        }

        if (!Mage::helper('couponRule/config')->getNoPurchaseDaysCouponRoleId($days)){
            Mage::log("[-]debug:: no base after register $days days rule choose. or the rule is not active or exists!", null, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return;
        }

        // 2) 生成优惠券
        $rule = Mage::getModel('salesrule/rule')->load(Mage::helper('couponRule/config')->getNoPurchaseDaysCouponRoleId($days));
        $data = Mage::helper('couponRule/config')->getNoPurchaseDaysCouponGenerateConfig($days);
        $code = Mage::helper('couponRule/generate')->generateCouponCode($data);

        if ($code) {
            $ruleId = Mage::helper('couponRule/config')->getNoPurchaseDaysCouponRoleId($days);
            //因为每隔xxx天没购物就发coupon, 所以只要coupon code不一样，rule_id和customer_email可以一样，检查重复时，3个参数都要.
            $couponInfo = array(
                'rule_id'=> $ruleId,
                'customer_email'=> trim($customer->getEmail()),
                'coupon' => $code,
            );
            if ( true == Mage::getModel('couponRule/coupon')->checkIsexists($couponInfo) ) {

                //set from date and expire date
                if (Mage::registry('couponUsageDate')){
                    $couponUsageDate  = Mage::registry('couponUsageDate');
                }else{
                    $couponUsageDate  = 7;
                }
                
                //not contain the lastest day
                $expireDateUnixStamp = strtotime("+$couponUsageDate day");

                try {
                    $ruleCouponData  = array(
                        'id'                    =>  NULL,
                        'rule_id'               =>  $data['rule_id'],
                        'code'                  =>  $code,
                        'usage_per_customer'    =>  $rule->getUsesPerCustomer(),
                        'begin_use_date'        =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss'),
                        'expiration_date'       =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss',$expireDateUnixStamp),
                        'created_at'            =>  Mage::helper('d1m_core/date')->formatDatetime(),
                        'type'                  =>  D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER,
                    );

                    //save it
                    if (Mage::helper('couponRule/generate')->saveCouponCode($ruleCouponData,$customer, "noPurchase{$days}Days")){
                        return $code;
                    }

                    //generate success information
                    Mage::log("[+] no purchase {$days} days coupon info: customer email is ".$customer->getEmail().' the coupon code is '.$code ,null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

                } catch (Exception $e) {
                    Mage::log("[-] generate no purchase days coupon has error occur .the customer infomation as following", null, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
                    Mage::log($customer->getData(),7, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
                    Mage::logException($e);
                }
            } else {
                Mage::log("[-] generate no purchase coupon failed, this customer email has exists in mapping customer: "
                    .$customer->getEmail() . 'ruleId=' . $ruleId ,null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            }
        }
    }


    public function generateShipmentConfirmCoupon($customer)
    {
        // 1)安全性检测
        if (!Mage::helper('couponRule/config')->isShipmentConfirmCouponActive()){
            Mage::log("[-] shipmentConfirm coupon config is not enabled.",
                 null, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return ;
        }

        if (!Mage::helper('couponRule/config')->getShipmentConfirmCouponRoleId()){
            Mage::log("[-] shipmentConfig coupon rule is not active or exists!", 
                null, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return;
        }

        // 2) 生成新用户注册优惠券
        $rule = Mage::getModel('salesrule/rule')->load(Mage::helper('couponRule/config')->getShipmentConfirmCouponRoleId());
        $data = Mage::helper('couponRule/config')->getShipmentConfirmCouponGenerateConfig();
        $code = Mage::helper('couponRule/generate')->generateCouponCode($data);

        if ($code) {
            //如果此规则不存在，并且邮箱已经存在
            if (Mage::getModel('couponRule/coupon')->checkIsexists(
                    array(
                        'rule_id'=>Mage::helper('couponRule/config')->getShipmentConfirmCouponRoleId(),
                        'customer_email'=>trim($customer->getEmail())
                    )) == true) {

                //set from date and expire date
                if (Mage::registry('couponUsageDate')){
                    $couponUsageDate  = Mage::registry('couponUsageDate');
                }else{
                    $couponUsageDate  = 7;
                }
                //not contain the lastest day
                $expireDateUnixStamp = strtotime("+$couponUsageDate day");

                try {
                    $ruleCouponData  = array(
                        'id'                    =>  NULL,
                        'rule_id'               =>  $data['rule_id'],
                        'code'                  =>  $code,
                        'usage_per_customer'    =>  $rule->getUsesPerCustomer(),
                        'begin_use_date'        =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss'),
                        'expiration_date'       =>  Mage::helper('d1m_core/date')->formatDatetime('yyyy-MM-dd HH:mm:ss',$expireDateUnixStamp),
                        'created_at'            =>  Mage::helper('d1m_core/date')->formatDatetime(),
                        'type'                  =>  D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER,
                    );

                    //save it
                    if (Mage::helper('couponRule/generate')->saveCouponCode($ruleCouponData, $customer, 'shipmentConfirm')){
                        return $code;
                    }

                    //generate success information
                    Mage::log("[+] shipmentConfirm coupon OK, info: customer email is ".$customer->getEmail()
                        .' the coupon code is '.$code, null, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);

                } catch (Exception $e) {
                    Mage::log("[-] generate shipmentConfirm coupon has error: ", null, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
                    Mage::log($customer->getData(),7, D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
                    Mage::logException($e);
                }
            } else {
                Mage::log("[-] generate shipmentConfirm coupon failed, customer email exists in mapping customer. email is "
                    .$customer->getEmail(),null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            }
        }
    }


}
