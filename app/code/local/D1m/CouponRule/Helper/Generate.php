<?php
/*
 * @author song
 * @copyright Copyright (c) 2013 FristSecond.
 */
class D1m_CouponRule_Helper_Generate extends Mage_Core_Helper_Abstract {

     public $generateCouponTag;

    public function __construct() {
        $this->generateCouponTag = array(
            'newCustomer',
            'birthday',
            'reviews',
            'upgrade vip',
            'upgrade silver vip',
            'upgrade gold vip',
            'afterRegister7Days',
            'afterRegister14Days',
            'afterRegister30Days',
            'noPurchase30Days',
            'noPurchase60Days',
            'noPurchase90Days',
            'shipmentConfirm',
        );
    }

    /**
     *  安全性检测
     */
    public function checkSecurity(array $config_data){
        if (!$config_data['is_active']){
            Mage::log(' debug:: generate '.$config_data['tag'].' Coupon  NOT INACTIVE',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return ;
        }

        if (!$config_data['rule_id']){
            Mage::log('debug:: no base '.$config_data['tag'].' rule choose. or the rule is not active or exists!',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return;
        }

        return true;
    }
    /**
     *  生成coupon
     */
    public function generateCouponCode(array $data){
        $rule_id = $data['rule_id'];
        $rule = Mage::getModel('salesrule/rule')->load($rule_id);
        if ($rule) {
            $generator = $rule->getCouponMassGenerator();
            $data[1]  =  1;
            $generator->setData($data);
            $maxAttempts = Mage_SalesRule_Model_Coupon_Massgenerator::MAX_GENERATE_ATTEMPTS;
            $attempt = 0;
            do {
                if ($attempt >= $maxAttempts) {
                    Mage::throwException(Mage::helper('salesrule')->__('Unable to create requested Coupon Qty. Please check settings and try again.'));
                }
                $code = $generator->generateCode();
                $attempt++;
            } while ($generator->getResource()->exists($code));

            return $code;
        } else {
            Mage::log('debug:: no base new Reviews rule choose. or the rule is not active or exists!',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            return;
        }
    }


    /**
     *   保存Coupon Code
     *   生成一个新的coupon code
     *
     */
    public function  saveCouponCode(array $ruleCouponData,Mage_Customer_Model_Customer $customer=NULL,$tag=NULL){

        try {
            $coupon = Mage::getModel('salesrule/coupon');
            $coupon->setData($ruleCouponData)->save();

            if ($customer){
                Mage::getModel('couponRule/coupon')->setData(
                    array(
                        'coupon'             =>  $ruleCouponData['code'],
                        'rule_id'            =>  $ruleCouponData['rule_id'],
                        'customer_id'       =>  $customer->getId(),
                        'customer_email'   =>  trim($customer->getEmail())
                    )
                )->save();
            }

            in_array($tag,$this->generateCouponTag)?$output_tag = $tag :'';

            //generate success information
            Mage::log('the '.$output_tag.'  coupon information! customer email is '.$customer->getEmail().' the coupon code is '.$ruleCouponData['code'] ,null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
        } catch (Exception $e) {
            Mage::log('debug: '.$output_tag.' has error occur .the customer information as following',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::log($customer->getData(),7,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::logException($e);

            return false;
        }
        return true;
    }
}
