<?php

require_once BP.'/app/code/core/Mage/Adminhtml/controllers/Promo/QuoteController.php';

class D1m_CouponRule_Adminhtml_Promo_QuoteController extends Mage_Adminhtml_Promo_QuoteController
{


    public function duplicateAction()
    {
        $oldRuleId = $this->getRequest()->getParam('rule_id');
        $oldRuleModel = Mage::getModel('salesrule/rule')->load($oldRuleId);

        if ($oldRuleModel->getId()) {
            try {
                $newRuleModel = Mage::getModel('salesrule/rule');
                $oldData = $oldRuleModel->getData();
                //delete the items that can't be used on the duplicate
                unset($oldData['rule_id']);
                unset($oldData['coupon_code']);
                $newRuleModel->setData($oldData);
                $newRuleModel->setConditions($oldRuleModel->getConditions());
                $newRuleModel->setActions($oldRuleModel->getActions());
                $newRuleModel->save();

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('catalogrule')->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
            }
        }
        $this->_getSession()->addSuccess(Mage::helper('couponRule/data')->__('The rule has been duplicated.'));
        $this->_redirect('*/*/');
    }

    public function deactivateAction()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        $rule = Mage::getModel('salesrule/rule')->load($ruleId);

        if ($rule->getId()) {
            try {
                $rule->setIsActive(false);
                $rule->save();
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('catalogrule')->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
            }
        }
        $this->_getSession()->addSuccess(Mage::helper('couponRule/data')->__('The rule has been deactivated.'));
        $this->_redirect('*/*/');
    }

    public function activateAction()
    {
        $ruleId = $this->getRequest()->getParam('rule_id');
        $rule = Mage::getModel('salesrule/rule')->load($ruleId);

        if ($rule->getId()) {
            try {
                $rule->setIsActive(true);
                $rule->save();
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('catalogrule')->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
            }
        }
        $this->_getSession()->addSuccess(Mage::helper('couponRule/data')->__('The rule has been activated.'));
        $this->_redirect('*/*/');
    }


    public function massdeleteAction()
    {

        $ruleId = $this->getRequest()->getParam('rule_id');
        $rule = Mage::getModel('salesrule/rule')->load($ruleId);

        if ($rule->getId()) {
            try {
                $rule->delete();

                //if rule type is  automatic and  auto generate
                if ($rule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER || $rule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_CREDITS_AUTO_GENERATE_WITH_CUSTOMER){
                    //direct use sql to dlete rule
                    $write_resource = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $tableName      = Mage::getSingleton('core/resource')->getTableName('couponRule/coupon');
                    $query = "DELETE  FROM {$tableName}  WHERE  rule_id=:rule_id";

                    $write_resource->query($query, array('rule_id'=>$ruleId));
                }

            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('couponRule/data')->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
            }
        }
        $this->_getSession()->addSuccess(Mage::helper('couponRule/data')->__('The rule has been deleted.'));
        $this->_redirect('*/*/');
    }


    /**
     * Generate Coupons action
     */
    /**
     * Generate Coupons action
     */
    public function generateAction()
    {
        $customer_collection = array();

        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noRoute');
            return;
        }
        $result = array();
        $this->_initRule();

        /** @var $rule Mage_SalesRule_Model_Rule */
        $rule = Mage::registry('current_promo_quote_rule');

        if (!$rule->getId()) {
            $result['error'] = Mage::helper('salesrule')->__('Rule is not defined');
        }

        if ($rule->getId()){
            try {
                $data = $this->getRequest()->getParams();

                if (!empty($data['to_date'])) {
                    $data = array_merge($data, $this->_filterDates($data, array('to_date')));
                }

                /** @var $generator Mage_SalesRule_Model_Coupon_Massgenerator */
                $generator = $rule->getCouponMassGenerator();
                if (!$generator->validateData($data) && $rule->getCouponType() != D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT) {
                    $result['error'] = Mage::helper('salesrule')->__('Not valid data provided');

                } else {
                    //define the coupon type
                    $data['coupon_type'] = $rule->getCouponType();

                    if ($rule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER ||  $rule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT){

                        //add group id
                        if ($rule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_WITH_CUSTOMER){
                            if (!empty($data['coupon_customer_group_ids'])){
                                $customer_group_ids = $data['coupon_customer_group_ids'];
                                $customer_collection = Mage::getResourceModel('couponRule/coupon')->getAllActiveAndNoCouponCustomer($customer_group_ids,$rule->getRuleId())->getData();
                            }
                        }

                        if (!empty($data['customer_email_collection'])){
                            foreach (explode(',',$data['customer_email_collection']) as $customer_email){
                                $customerInfo = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('entity_id')->addAttributeToFilter('email', trim($customer_email))->load()->getData();

                                if (!isset($customerInfo[0]['entity_id']) ||  empty($customerInfo[0]['entity_id'])){
                                    $this->_getSession()->addWarning(Mage::helper('couponRule/data')->__('the customer not exists. the email address is '.$customer_email));
                                } else {
                                    if (!array_key_exists($customerInfo[0]['entity_id'],Mage::helper('couponRule/data')->getUnqiueEmail($customer_collection)) && Mage::getModel('couponRule/coupon')->checkIsexists(array('rule_id'=>$rule->getRuleId(),'customer_email'=>trim($customer_email))) == true){
                                        array_push($customer_collection,$customerInfo[0]);
                                    } else {
                                        //add warning tip and debug it to log
                                        $this->_getSession()->addWarning(Mage::helper('couponRule/data')->__('the email had already generate coupon code for this rule! maybe you need to delete this coupon! the customer email address is '.$customer_email));
                                    }
                                }
                            }
                        }

                        $data['qty'] = count($customer_collection);

                        if ($rule->getCouponType() == D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT){

                            $ruleConfig           =   Mage::helper('couponRule/config')->getRuleConfigBaseRuleId($rule->getId());
                            $data['to_date']    =   empty($data['event_to_date'])?NULL:$data['event_to_date'];
                            $data['from_date']  =   empty($data['event_from_date'])?NULL:$data['event_from_date'];
                            $data['uses_per_customer'] = empty($data['event_uses_per_coupon'])?NULL:$data['event_uses_per_coupon'];

                            foreach ($ruleConfig as $key =>$val){
                                $data[$key] = $val;
                            }
                        }
                        $generator->setData($data);
                        $generator->generatePool();
                        $generated = $generator->getGeneratedCount();

                        //bind  customer and coupon code
                        try {
                            $coupon_collections = $generator->getCouponCollections();
                            foreach ($customer_collection as $key =>$customer_info){
                                Mage::getModel('couponRule/coupon')->setData(
                                    array(
                                        'coupon'        =>  $coupon_collections[$key],
                                        'rule_id'       =>  $rule->getRuleId(),
                                        'customer_id'   =>  $customer_info['entity_id'],
                                        'customer_email'=>  trim($customer_info['email'])
                                    )
                                )->save();
                            }

                        } catch (Mage_Core_Exception $e) {
                            Mage::log('coupon generate with customer had errors!',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
                            Mage::logException($e);
                        }

                    } else {
                        $generator->setData($data);
                        $generator->generatePool();
                        $generated = $generator->getGeneratedCount();
                    }

                    $this->_getSession()->addSuccess(Mage::helper('salesrule')->__('%s Coupon(s) have been generated', $generated));
                    $this->_initLayoutMessages('adminhtml/session');
                    $result['messages']  = $this->getLayout()->getMessagesBlock()->getGroupedHtml();

                }
            } catch (Mage_Core_Exception $e) {
                $result['error'] = $e->getMessage();
            } catch (Exception $e) {
                $result['error'] = Mage::helper('salesrule')->__('An error occurred while generating coupons. Please review the log and try again.');
                Mage::logException($e);
            }
        }

        //将后台添加的错误信息添加到日志中
        if (!empty($result['error'])){
            //log error data to log file
            Mage::log('-----------------------------BACKEND AJAX ADD COUPON  DEBUG START-----------------------------------',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::log('request data params',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::log($data,7,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::log('the rule id    '.$rule->getId(),null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::log('error result ',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::log($result['error'],null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
            Mage::log('-----------------------------BACKEND AJAX ADD COUPON  DEBUG END-----------------------------------',null,D1m_Core_Helper_Log::SALE_COUPON_ERROR_LOG_FILE);
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }


    public function dealmessageAction(){

        Mage::log('-----------------------------COUPON SEND MESSAGE DEBUG START-----------------------------------',null,D1m_Core_Helper_Log::SALE_COUPON_SEND_MESSAGE_LOG_FILE);

        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noRoute');
            return;
        }
        $result = array();
        $this->_initRule();

        /** @var $rule Mage_SalesRule_Model_Rule */
        $rule = Mage::registry('current_promo_quote_rule');

        if (!$rule->getId()) {
            $result['error'] = Mage::helper('salesrule')->__('Rule is not defined');
        } else {
            try {
                $data = $rule->getData();

                foreach ($this->getRequest()->getParams() as $key =>$val){
                    $data[$key]  = $val;
                }

                $rule->setData($data)->save();

                //如果为即时发送，则自动发送，并且保存
                if ($rule->getCouponType() != D1m_CouponRule_Model_Rule::COUPON_TYPE_AUTO_GENERATE_FOR_EVENT){

                    $sms_notice_template    =    empty($data['sms_notice_template'])?NULL:$data['sms_notice_template'];
                    $email_notice_template  =    empty($data['email_notice_template'])?NULL:$data['email_notice_template'];
                    $message_box_template   =    empty($data['message_box_template'])?NULL:$data['message_box_template'];

                    if (Mage::helper('couponRule/message')->sendSmsMessageByRule($rule,$sms_notice_template,$email_notice_template,$message_box_template)){
                        Mage::log('Coupon send right now! it send success!',null,D1m_Core_Helper_Log::SALE_COUPON_SEND_MESSAGE_LOG_FILE);
                    }
                    $this->_getSession()->addSuccess(Mage::helper('salesrule')->__('Message had already Sent'));
                } else {
                    $this->_getSession()->addSuccess(Mage::helper('salesrule')->__('Message Send Methods had already saved!'));
                }

                $this->_initLayoutMessages('adminhtml/session');
                $result['messages']  = $this->getLayout()->getMessagesBlock()->getGroupedHtml();

            } catch (Mage_Core_Exception $e) {
                Mage::log($e->getMessage(),7,D1m_Core_Helper_Log::SALE_COUPON_SEND_MESSAGE_LOG_FILE);
                $result['error'] = $e->getMessage();
                Mage::logException($e);

            } catch (Exception $e) {
                $result['error'] = Mage::helper('salesrule')->__('An error occurred send message. Please review the log and try again.');
                Mage::logException($e);
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        Mage::log('-----------------------------COUPON SEND MESSAGE DEBUG END-----------------------------------',null,D1m_Core_Helper_Log::SALE_COUPON_SEND_MESSAGE_LOG_FILE);

    }



    public function couponsGridAction()
    {
        $this->_initRule();
        $this->loadLayout();
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/promo_quote_edit_tab_coupons_grid')->toHtml());
    }


}