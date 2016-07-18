<?php
/*
 * 此类为优惠券相关的发送类
 * @author song
 * @copyright Copyright (c) 2013 D1M.
 */
class D1m_CouponRule_Helper_Message extends Mage_Core_Helper_Abstract {

    /**
     * 信息发送类型的检测
     * @param $rule
     * @param null $sms_notice_template
     * @param null $email_notice_template
     * @param null $message_box_template
     */
    private function _checkSendMethod($rule,$sms_notice_template,$email_notice_template,$message_box_template){
        //信息发送类型判断
        if (empty($sms_notice_template) &&  empty($email_notice_template) &&  empty($message_box_template)){
            Mage::log(Mage::helper('couponRule/data')->__('No Send Message Method Select!'),NULL,D1m_Core_Helper_Log::SALE_COUPON_SEND_MESSAGE_LOG_FILE);
            return ;
        }
    }

    /**
     * 记录没有手机号的用户
     * @param $email
     * @param $mobile
     */
    private function _recordCustomerNomobile($email,$mobile){
        //记录用户没有手机号，则自动的记录
        if (!$mobile){
            Mage::log('this customer no mobile  number, the email address is '.$email,null,D1m_Core_Helper_Log::SALE_COUPON_SEND_MESSAGE_LOG_FILE);
        }
    }
    /**
     * 针对特定优惠券，与用户绑定优惠券的发送
     * @param $rule
     * @param null $sms_notice_template
     * @param null $email_notice_template
     * @param null $message_box_template
     */
    public  function sendSmsMessageByRule($rule,$sms_notice_template=NULL,$email_notice_template=NULL,$message_box_template=NULL){

        //信息发送类型检查
        $this-> _checkSendMethod($rule,$sms_notice_template,$email_notice_template,$message_box_template);

        //处理单一coupon发送给用户组的
        if ($rule->getCouponType() == Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
        {
            $customer_collections = Mage::getResourceModel('couponRule/coupon')->getCustomerCollections($rule->getCustomerGroupIds());

            //get coupon code
            $rule_object        =   new Varien_Object();
            $rule_object_arr    =   $rule->getData();
            foreach (Mage::getModel('salesrule/coupon')->loadPrimaryByRule($rule)->getData() as $key => $val){
                $rule_object_arr[$key] = $val;
            }
            $rule_object->setData($rule_object_arr);

            foreach ($customer_collections as $customer){
                $variables[] = array(
                    'customer' => $customer,
                    'rule'     => $rule_object
                );

                $customer_ids[]            =  $customer->getId();
                $customer_emails[]         =  $customer->getEmail();
                $customer_mobile_numbers[] =  $customer->getMobile()?$customer->getMobile():'';

                //记录无法发送短信的用户
                $this->_recordCustomerNomobile($customer->getEmail(),$customer->getMobile());
            }

               //send message notice
               if ($sms_notice_template){
                   $message_template     = Mage::getModel('d1m_messageTemplate/message')->load($sms_notice_template)->getMessage();
                   //send message
                   $this->_sendSmsForcoupon($customer_mobile_numbers,$variables,$message_template);
               }

                //send mail 统一使用WEBPOWER
                if (!empty($email_notice_template)){
                    Mage::helper('d1m_mail/data')->sendMailBaseTemplate($email_notice_template,$customer_emails,$variables,NULL);
                }

                //send inbox message
                if (!empty($message_box_template)){
                    $this->_sendInboxMessage($customer_ids,$variables,$message_box_template);
                }


        } elseif($rule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER){

            $customer_collections = Mage::getResourceModel('couponRule/coupon')->getCouponInfoByRuleId($rule->getRuleId());

            if ($sms_notice_template){
                $message_template     = Mage::getModel('d1m_messageTemplate/message')->load($sms_notice_template)->getMessage();
            }


            foreach ($customer_collections as $coupon_info_data){
                $coupon_info  =  new Varien_Object();
                $coupon_info->setData($coupon_info_data->getData());

                $variables[] = array(
                    'customer' => $coupon_info,
                    'rule'     =>  $coupon_info
                );

                $customer_ids[]            =  $coupon_info_data->getCustomerId();
                $customer_emails[]         =  $coupon_info->getEmail();
                $customer_mobile_numbers[] =  $coupon_info->getMobile()?$coupon_info->getMobile():'';

                //记录无法发送短信的用户
                $this->_recordCustomerNomobile($coupon_info->getEmail(),$coupon_info->getMobile());
        }

                //send message
                if ($message_template){
                    $this->_sendSmsForcoupon($customer_mobile_numbers,$variables,$message_template);
                }

                //send mail
                if (!empty($email_notice_template)){
                    Mage::helper('d1m_mail/data')->sendMailBaseTemplate($email_notice_template,$customer_emails,$variables,NULL);
                }

                //send inbox message
                if (!empty($message_box_template)){
                    $this->_sendInboxMessage($customer_ids,$variables,$message_box_template);
                }
        }

        return true;
    }


    /**
     * 自动发送--新用户注册发送
     * @param $rule
     * @param null $sms_notice_template
     * @param null $email_notice_template
     * @param null $message_box_template
     * @param null $variables
     */
    public  function sendMessageForNewCustomer($rule,$sms_notice_template=NULL,$email_notice_template=NULL,$message_box_template=NULL,$variables=NULL){

        //信息发送类型检查
        $this->_checkSendMethod($rule,$sms_notice_template,$email_notice_template,$message_box_template);

        //处理单一coupon发送给用户组的
        if ($rule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT){

            if (is_array($variables)){
                $customer = $variables['customer'];
            } else {
                Mage::log('send coupon notice for new customer had error! no data to send',null,D1m_Core_Helper_Log::SALE_COUPON_SEND_MESSAGE_LOG_FILE);
            }

            //针对新用户注册， 重新组织各种变量值
            $variables                  = array($variables);
            $customer_mobile_numbers    = array($customer->getMobile());
            $customer_emails            = array($customer->getEmail());

            //send sms
            if ($sms_notice_template){
                $this->_sendSmsForcoupon($customer_mobile_numbers,$variables,$sms_notice_template);
            }

            //send mail
            if (!empty($email_notice_template)){
                Mage::helper('d1m_mail/data')->sendMailBaseTemplate($email_notice_template,$customer_emails,$variables,NULL);
            }

            //send inbox message
            if (!empty($message_box_template)){
                $this->_sendInboxMessage(array($customer->getId()),$variables,$message_box_template);
            }

            return true;
        }
    }

    /**
     * 发送带有优惠券信息的短信
     * @param $mobile
     * @param $variables
     * @param $message_template
     */
    protected  function _sendSmsForcoupon($mobile_numbers,$variables,$message_template){


        $mobile_contents = array();
        //send message
        if ($message_template){
            foreach ($variables as $variable){
                $mobile_contents[] = Mage::getModel('d1m_messageTemplate/message')->filterMessageContent($variable,$message_template);
            }

            if (!empty($mobile_contents)){
                Mage::helper('d1m_messageTemplate/sendSms')->sendSmsMessage($mobile_numbers,$mobile_contents);
            }
        }
    }

    /**
     * 发送站内信, 依然使用批量的方式来进行发送
     */
    protected function _sendInboxMessage($customer_ids,$variables,$messageInbox_template_id){

        $messageTemplate =   Mage::getModel('advancemsg/template')->load($messageInbox_template_id);

        if (is_array($variables) && is_array($customer_ids)){
           for($i=0;$i<count($variables);$i++){

                $message_data = array(
                    'subject'   =>  $messageTemplate->getProcessedTemplateSubject($variables[$i]),
                    'message_type'   =>  $messageTemplate->getTemplateMessageType(),
                    'content'   =>  $messageTemplate->getProcessedTemplate($variables[$i],false)
                );

                try {
                    if (Mage::helper('advancemsg')->sendMessage($customer_ids[$i],$message_data,$messageTemplate)){
                        return true;
                    }
                }catch (Mage_Exception $e) {
                    Mage::logException($e);
                }

            }
        }

        return false;
   }

}
