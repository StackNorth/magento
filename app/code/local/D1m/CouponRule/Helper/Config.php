<?php
/**
 * Helper
 * Changed file name
 */
class D1m_CouponRule_Helper_Config extends Mage_Core_Helper_Abstract
{
    const XML_CONFIG_BASE_PATH                    =  'couponRule/';
    const AUTO_BRITHDAY_COUPON_RULE_NAME        =  'brithday_rule_tag';
    const AUTO_NEW_CUSTOMER_COUPON_RULE_NAME    =  'newcustomer_rule_tag';
    const AUTO_REVIEWS_COUPON_RULE_NAME           =  'reviews_rule_tag';
    const AUTO_UPGRADE_VIP_COUPON_RULE_NAME     =  'upgrade_vip_rule_tag';
    const AUTO_UPGRADE_SILVERVIP_COUPON_RULE_NAME= 'upgrade_silvervip_rule_tag';
    const AUTO_UPGRADE_GOLDVIP_COUPON_RULE_NAME  = 'upgrade_goldrvip_rule_tag';

    //generate brithday coupon setting
    public function isBrithdayCouponActive()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'coupon/isactive');
    }

    public function getBrithdayCouponRoleId()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'coupon/roleid');
    }

    public function getBrithdayCouponLength()
    {

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'coupon/length');
    }

    public function getBrithdayCouponFormat()
    {

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'coupon/format');
    }

    public function getBrithdayCouponPrefix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'coupon/prefix');
    }

    public function getBrithdayCouponSuffix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'coupon/suffix');
    }

    public function getBrithdayCouponDash()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'coupon/dash');
    }

    //enable brithday coupon setting
    public function isBrithdayCouponSettingActive()
    {
        return $this->isBrithdayCouponActive() && Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'brithday_coupon_setting/send_date_isactive');
    }

    public function isBrithdayCouponSettingUseageDateIsactive()
    {
        return $this->isBrithdayCouponActive() && Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'brithday_coupon_setting/useage_date_isactive');
    }

    public function getBrithdayCouponSettingSendDate()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'brithday_coupon_setting/send_date');
    }

    public function getBrithdayCouponSettingUsageDate()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'brithday_coupon_setting/useage_date');
    }

    //new customer register
    public function isNewCustomerCouponActive()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'new_customer_coupon/isactive');
    }

    public function getNewCustomerCouponCouponRoleId()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'new_customer_coupon/roleid');
    }

    public function getNewCustomerCouponCouponLength()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'new_customer_coupon/length');
    }

    public function getNewCustomerCouponCouponFormat()
    {

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'new_customer_coupon/format');
    }

    public function getNewCustomerCouponCouponPrefix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'new_customer_coupon/prefix');
    }

    public function getNewCustomerCouponCouponSuffix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'new_customer_coupon/suffix');
    }

    public function getNewCustomerCouponCouponDash()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'new_customer_coupon/dash');
    }



    //setting new customer coupon setting
    public function isNewCustomerCouponSettingUseageDateIsactive()
    {
        return $this->isBrithdayCouponActive() && Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'new_customer_coupon_setting/useage_date_isactive');
    }

    public function getNewCustomerCouponSettingUsageDate()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'new_customer_coupon_setting/useage_date');
    }


    //get upgrade vip
    public function isUpgradeVipCouponActive()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_normal_vip/isactive');
    }

    public function getUpgradeVipCouponCouponRoleId()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_normal_vip/roleid');
    }

    public function getUpgradeVipCouponCouponLength()
    {

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_normal_vip/length');
    }

    public function getUpgradeVipCouponCouponFormat()
    {

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_normal_vip/format');
    }

    public function getUpgradeVipCouponCouponPrefix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_normal_vip/prefix');
    }

    public function getUpgradeVipCouponCouponSuffix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_normal_vip/suffix');
    }

    public function getUpgradeVipCouponCouponDash()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_normal_vip/dash');
    }



    //get upgrade silver  vip

    public function isUpgradeSilverVipCouponActive()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_silver_vip/isactive');
    }

    public function getUpgradeSilverVipCouponCouponRoleId()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_silver_vip/roleid');
    }

    public function getUpgradeSilverVipCouponCouponLength()
    {

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_silver_vip/length');
    }

    public function getUpgradeSilverVipCouponCouponFormat()
    {

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_silver_vip/format');
    }

    public function getUpgradeSilverVipCouponCouponPrefix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_silver_vip/prefix');
    }

    public function getUpgradeSilverVipCouponCouponSuffix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_silver_vip/suffix');
    }

    public function getUpgradeSilverVipCouponCouponDash()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_silver_vip/dash');
    }


    //get upgrade gold   vip

    public function isUpgradeGoldVipCouponActive()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_gold_vip/isactive');
    }

    public function getUpgradeGoldVipCouponCouponRoleId()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_gold_vip/roleid');
    }

    public function getUpgradeGoldVipCouponCouponLength()
    {

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_gold_vip/length');
    }

    public function getUpgradeGoldVipCouponCouponFormat()
    {

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_gold_vip/format');
    }

    public function getUpgradeGoldVipCouponCouponPrefix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_gold_vip/prefix');
    }

    public function getUpgradeGoldVipCouponCouponSuffix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_gold_vip/suffix');
    }

    public function getUpgradeGoldVipCouponCouponDash()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_gold_vip/dash');
    }


    /**
     *  settting reviews  coupon
     */
    public function isReviewsCouponActive()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'reviews_coupon/isactive');
    }

    public function getReviewsCouponRoleId()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'reviews_coupon/roleid');
    }

    public function getReviewsCouponLength()
    {

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'reviews_coupon/length');
    }

    public function getReviewsCouponFormat()
    {

        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'reviews_coupon/format');
    }

    public function getReviewsCouponPrefix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'reviews_coupon/prefix');
    }

    public function getReviewsCouponSuffix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'reviews_coupon/suffix');
    }

    public function getReviewsCouponDash()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'reviews_coupon/dash');
    }



    //setting reviews  coupon setting
    public function isReviewsCouponUseageDateIsactive()
    {
        return $this->isBrithdayCouponActive() && Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'reviews_coupon_setting/useage_date_isactive');
    }

    public function getReviewsCouponSettingUsageDate()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'reviews_coupon_setting/useage_date');
    }


    //setting upgrade vip
    public function isVipCouponUseageDateIsactive()
    {
        return  Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_setting/useage_date_isactive');
    }

    public function getVipCouponSettingUsageDate()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'upgrade_customer_level_setting/useage_date');
    }

    /**
     *  setting enable release coupon
     * @return mixed
     */
    public function getCancelorderToReleaseCouponActive()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'releaseCoupon/cancel_order_release_coupon_enable');
    }

    public function getcreditmemoRefundToReleaseCouponActive()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'releaseCoupon/release_coupon_creditmemoRefund_enable');
    }


    //setting coupon expire
    public function  getExpireDateSettingActive(){
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'coupon_expire_alert/enable_coupon_expire_alert');
    }

    public function getExpireDate(){
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . 'coupon_expire_alert/expire_date');
    }



    /**
     * get event rule id
     */
    public function  getAllRuleId(){
        $rule_ids = array(
           self::AUTO_BRITHDAY_COUPON_RULE_NAME         => $this->getBrithdayCouponRoleId(),
           self::AUTO_NEW_CUSTOMER_COUPON_RULE_NAME    => $this->getNewCustomerCouponCouponRoleId(),
           self::AUTO_UPGRADE_VIP_COUPON_RULE_NAME     => $this->getUpgradeVipCouponCouponRoleId(),
           self::AUTO_REVIEWS_COUPON_RULE_NAME          => $this->getReviewsCouponRoleId(),
           self::AUTO_UPGRADE_SILVERVIP_COUPON_RULE_NAME => $this->getUpgradeSilverVipCouponCouponRoleId(),
           self::AUTO_UPGRADE_GOLDVIP_COUPON_RULE_NAME => $this->getUpgradeGoldVipCouponCouponRoleId(),
        );
    }

    /**
     * 获得VIP升级优惠券的统一有效时间
     */
    public function getVipUsageDate(){
        //set from date and expire date
        if ($this->getExpireDateSettingActive()){
            $couponUsageDate = $this->getExpireDate();
        } else {
            $couponUsageDate  = 30;
        }
        return $couponUsageDate;
    }

    /** 得到相关的生成配置 */
    //brithday coupon setting
    public  function getBrithdayGenerateConfig(){
        $config_arr = array(
               'rule_id' =>  $this->getBrithdayCouponRoleId(),
               'length'  =>  $this->getBrithdayCouponLength(),
               'format'  =>  $this->getBrithdayCouponFormat(),
               'prefix'  =>  $this->getBrithdayCouponPrefix(),
               'suffix'  =>  $this->getBrithdayCouponSuffix(),
               'dash'    =>  $this->getBrithdayCouponDash()
        );

       return $config_arr;
    }

    // new customer register
    public function getNewCustomerGenerateConfig(){

        $config_arr = array(
            'rule_id' =>  $this->getNewCustomerCouponCouponRoleId(),
            'length'  =>  $this->getNewCustomerCouponCouponLength(),
            'format'  =>  $this->getNewCustomerCouponCouponFormat(),
            'prefix'  =>  $this->getNewCustomerCouponCouponPrefix(),
            'suffix'  =>  $this->getNewCustomerCouponCouponSuffix(),
            'dash'    =>  $this->getNewCustomerCouponCouponDash()
        );

        return $config_arr;
    }

    // 针对评论的优惠券生成
    public function getReviewsCouponGenerateConfig(){

        $config_arr = array(
            'rule_id' =>  $this->getReviewsCouponRoleId(),
            'length'  =>  $this->getReviewsCouponLength(),
            'format'  =>  $this->getReviewsCouponFormat(),
            'prefix'  =>  $this->getReviewsCouponPrefix(),
            'suffix'  =>  $this->getReviewsCouponSuffix(),
            'dash'    =>  $this->getReviewsCouponDash(),
        );

        return $config_arr;
    }

    /**
     *  升级到VIP的配置信息
     */
    public function getUpgradeVipGenerateConfig(){
        $config_arr = array(
            'rule_id' =>  $this->getUpgradeVipCouponCouponRoleId(),
            'length'  =>  $this->getUpgradeVipCouponCouponLength(),
            'format'  =>  $this->getUpgradeVipCouponCouponFormat(),
            'prefix'  =>  $this->getUpgradeVipCouponCouponPrefix(),
            'suffix'  =>  $this->getUpgradeVipCouponCouponSuffix(),
            'dash'    =>  $this->getUpgradeVipCouponCouponDash(),
        );
        return $config_arr;
    }

    /**
     *  升级silver VIP
    * @return array
     */
    public function getUpgradeSilverVipGenerateConfig(){
        $config_arr = array(
            'rule_id' =>  $this->getUpgradeSilverVipCouponCouponRoleId(),
            'length'  =>  $this->getUpgradeSilverVipCouponCouponLength(),
            'format'  =>  $this->getUpgradeSilverVipCouponCouponFormat(),
            'prefix'  =>  $this->getUpgradeSilverVipCouponCouponPrefix(),
            'suffix'  =>  $this->getUpgradeSilverVipCouponCouponSuffix(),
            'dash'    =>  $this->getUpgradeSilverVipCouponCouponDash(),
        );
        return $config_arr;
    }

    /**
     *  升级到GOLD VIP
     * @return array
     */
    public function getUpgradeGoldVipGenerateConfig(){
        $config_arr = array(
            'rule_id' =>  $this->getUpgradeGoldVipCouponCouponRoleId(),
            'length'  =>  $this->getUpgradeGoldVipCouponCouponLength(),
            'format'  =>  $this->getUpgradeGoldVipCouponCouponFormat(),
            'prefix'  =>  $this->getUpgradeGoldVipCouponCouponPrefix(),
            'suffix'  =>  $this->getUpgradeGoldVipCouponCouponSuffix(),
            'dash'    =>  $this->getUpgradeGoldVipCouponCouponDash(),
        );
        return $config_arr;
    }

    /**
     * 根据规则ID来找到相对应的配置
     */
    public function getRuleConfigBaseRuleId($ruleId){
        if (!is_numeric($ruleId) && !preg_match('/^[1-9][0-9]*$/isu',$ruleId))  return ;

        switch($ruleId) {
            case $this->getBrithdayCouponRoleId():
                return $this->getBrithdayGenerateConfig();
                break;
            case $this->getNewCustomerCouponCouponRoleId():
                return $this->getNewCustomerGenerateConfig();
                break;
            case $this->getReviewsCouponRoleId():
                return $this->getReviewsCouponGenerateConfig();
                break;
            case $this->getUpgradeVipCouponCouponRoleId():
                return $this->getUpgradeVipGenerateConfig();
                break;
            case $this->getUpgradeSilverVipCouponCouponRoleId():
                return $this->getUpgradeSilverVipGenerateConfig();
                break;
            case $this->getUpgradeGoldVipCouponCouponRoleId():
                return $this->getUpgradeGoldVipGenerateConfig();
                break;
            default:
                return array();
                break;
        }
   }


    //after register XXX days coupon
    public function isAfterRegisterDaysCouponActive($days=7)
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . "after_register_{$days}days_coupon/isactive");
    }

    public function getAfterRegisterDaysCouponRoleId($days=7)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "after_register_{$days}days_coupon/roleid");
    }

    public function getAfterRegisterDaysCouponLength($days=7)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "after_register_{$days}days_coupon/length");
    }

    public function getAfterRegisterDaysCouponFormat($days=7)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "after_register_{$days}days_coupon/format");
    }

    public function getAfterRegisterDaysCouponPrefix($days=7)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "after_register_{$days}days_coupon/prefix");
    }

    public function getAfterRegisterDaysCouponSuffix($days=7)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "after_register_{$days}days_coupon/suffix");
    }

    public function getAfterRegisterDaysCouponDash($days=7)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "after_register_{$days}days_coupon/dash");
    }


    // get coupon config
    public function getAfterRegisterDaysCouponGenerateConfig($days=7){
        $configArr = array(
            'rule_id' =>  $this->getAfterRegisterDaysCouponRoleId($days),
            'length'  =>  $this->getAfterRegisterDaysCouponLength($days),
            'format'  =>  $this->getAfterRegisterDaysCouponFormat($days),
            'prefix'  =>  $this->getAfterRegisterDaysCouponPrefix($days),
            'suffix'  =>  $this->getAfterRegisterDaysCouponSuffix($days),
            'dash'    =>  $this->getAfterRegisterDaysCouponDash($days),
        );
        return $configArr;
    }


    //no purchase XXX days coupon
    public function isNoPurchaseDaysCouponActive($days=30)
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . "no_purchase_{$days}days_coupon/isactive");
    }

    public function getNoPurchaseDaysCouponRoleId($days=30)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "no_purchase_{$days}days_coupon/roleid");
    }

    public function getNoPurchaseDaysCouponLength($days=30)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "no_purchase_{$days}days_coupon/length");
    }

    public function getNoPurchaseDaysCouponFormat($days=30)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "no_purchase_{$days}days_coupon/format");
    }

    public function getNoPurchaseDaysCouponPrefix($days=30)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "no_purchase_{$days}days_coupon/prefix");
    }

    public function getNoPurchaseDaysCouponSuffix($days=30)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "no_purchase_{$days}days_coupon/suffix");
    }

    public function getNoPurchaseDaysCouponDash($days=30)
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "no_purchase_{$days}days_coupon/dash");
    }

    // get coupon config
    public function getNoPurchaseDaysCouponGenerateConfig($days=30){
        $configArr = array(
            'rule_id' =>  $this->getNoPurchaseDaysCouponRoleId($days),
            'length'  =>  $this->getNoPurchaseDaysCouponLength($days),
            'format'  =>  $this->getNoPurchaseDaysCouponFormat($days),
            'prefix'  =>  $this->getNoPurchaseDaysCouponPrefix($days),
            'suffix'  =>  $this->getNoPurchaseDaysCouponSuffix($days),
            'dash'    =>  $this->getNoPurchaseDaysCouponDash($days),
        );
        return $configArr;
    }


    // shipmentConfirm coupon
    public function isShipmentConfirmCouponActive()
    {
        return Mage::getStoreConfigFlag(self::XML_CONFIG_BASE_PATH . "shipment_confirm_coupon/isactive");
    }

    public function getShipmentConfirmCouponRoleId()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "shipment_confirm_coupon/roleid");
    }

    public function getShipmentConfirmCouponLength()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "shipment_confirm_coupon/length");
    }

    public function getShipmentConfirmCouponFormat()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "shipment_confirm_coupon/format");
    }

    public function getShipmentConfirmCouponPrefix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "shipment_confirm_coupon/prefix");
    }

    public function getShipmentConfirmCouponSuffix()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "shipment_confirm_coupon/suffix");
    }

    public function getShipmentConfirmCouponDash()
    {
        return Mage::getStoreConfig(self::XML_CONFIG_BASE_PATH . "shipment_confirm_coupon/dash");
    }

    // get coupon config
    public function getShipmentConfirmCouponGenerateConfig(){
        $configArr = array(
            'rule_id' =>  $this->getShipmentConfirmCouponRoleId(),
            'length'  =>  $this->getShipmentConfirmCouponLength(),
            'format'  =>  $this->getShipmentConfirmCouponFormat(),
            'prefix'  =>  $this->getShipmentConfirmCouponPrefix(),
            'suffix'  =>  $this->getShipmentConfirmCouponSuffix(),
            'dash'    =>  $this->getShipmentConfirmCouponDash(),
        );
        return $configArr;
    }
}
