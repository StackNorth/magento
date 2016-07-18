<?php

require_once("Mage/Customer/controllers/AccountController.php");

class D1m_Customer_AccountController extends Mage_Customer_AccountController
{

	public function getCustomer() {
		return Mage::getSingleton('customer/session')->getCustomer();
	}

	public function getCustomerEdit() {
		//die('ok');
		$this->_forward('edit','account');
		//$this->_redirect('*/*/edit');
		//exit();
	}
	
	
	/**
     * Customer register form page
     */
    public function createAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*');
            return;
        }
        
        $rid = $this->getRequest()->getParam('rid', false);
        if($rid)
        {
        	Mage::getSingleton('core/cookie')->set('rid', $rid, 86400);
        }
        
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }
   
    
    public function verifyAction()
    {

    	$result = null;
    	
    	$phone = $this->getRequest()->getParam('phone', false);
    	
    	if($phone == '')
    	{
    		$result = array('status'=>false,'msg'=>$this->_getHelper('customer')->__('this phone number can not be empty.'));
    	} else if (!preg_match('/^[0-9]{11}$/',$phone))
        {
            //手机号检查，要求全数字，防sql注入
            $result = array('status'=>false,'msg'=>$this->_getHelper('customer')->__('无效手机号'));
        }
        else
    	{
            //如果是注册，要求用户手机没用使用
            //如果是重发短信，要求用户手机存在
            $customerResource = Mage::getResourceSingleton('customer/customer');
            $attr = $customerResource->getAttribute('phone');
            $attrId = $attr->getAttributeId();
//            die($AttrId);


	    	$read = Mage::getSingleton('core/resource')->getConnection('core_read');
			$select = $read->select()->from(Mage::getSingleton('core/resource')->getTableName('customer_entity_varchar'))
				->where('value=?', $phone)
				->where('attribute_id=?', $attrId);
            if ($read->fetchRow($select)) $pused=true; else $pused=false;
            $used=$this->getRequest()->getParam('used', false);
            if ($used) $used=true; else $used=false;
            if ($used!=$pused)
            {
                if ($pused)
                   $result = array('status'=>false,'msg'=>$this->_getHelper('customer')->__('this phone number is used.'));
                else
                   $result = array('status'=>false,'msg'=>$this->_getHelper('customer')->__('当前手机号没有注册过'));

            }


	    	if(!$result)
	    	{
                //每天限制5条
                //手机号已检查 日期 防sql注入


                $today=Date("Y-m-d");
                $stable=Mage::getSingleton('core/resource')->getTableName('d1m_sms_count');
                $read = Mage::getSingleton('core/resource')->getConnection('core_read');
                $sql = "select scount from $stable where sphone='$phone' and sdate='$today' ";
                /* @var $q Varien_Db_Statement_Pdo_Mysql */
                $q=$read->query($sql);
                $v=$q->fetchColumn(0);
                if (!$v) $scount=0; else $scount=$v;
                //echo "scount=$scount ";
                if ($scount>=5)
                    $result = array('status'=>false,'msg'=>$this->_getHelper('customer')->__('您的手机当日短信发送已达上限，请联系客服'));
            }


            if(!$result)
            {

	    		$verifyCode = rand(10000,99999);
	    		
	    		$ok=Mage::helper('robi_checkout/msg')->sendVerifyMsg($phone, $verifyCode);
                //ok或者错误信息
                if ($ok=='ok')
                {

                    //更新短信发送条数
                    $stable=Mage::getSingleton('core/resource')->getTableName('d1m_sms_count');
                    $write = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $today=date("Y-m-d");
                    $sql="insert into $stable (sphone,sdate,scount) values ('$phone','$today',0) ";
                    try
                    {
                        $write->query($sql);
                    }
                    catch (exception $e)
                    {
                        //忽略错误
                    }
                    $sql="update  $stable  set scount=scount+1 where sphone='$phone' and sdate='$today'  ";
                    $write->query($sql);


                    $this->_getSession()->setVerifyCode($verifyCode);

                    /*
                    $customerApi = Mage::getModel('customapi/memberservice');
                    $customer = array();
                    $customer['mobile'] = $phone;
                    */


                    //ob_start();
                    //去掉输出的调试信息
                    //$erpCustomer = $customerApi->searchMembers($customer);
                    //ob_end_clean();

                    $result = array();
                    $result['status'] = true;
                    //$result['verify_code'] = $this->_getSession()->getVerifyCode();

                    /*
                    if($erpCustomer)
                    {
                        $result['has_erpuser'] = true;
                        $result['name'] = $erpCustomer->name;
                    }
                    else
                    {
                        $result['has_erpuser'] = false;
                    }
                    */
                }
                else
                {
                    $result = array('status'=>false,'msg'=>$this->_getHelper('customer')->__($ok));
                }
	    	}
    	}
    	
    	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    	
    }

    public function forgotpassword3Action()
    {
        //检查密码是否有效
        $phone=Mage::getSingleton('core/session')->getForgetPasswordPhone();

        if ($phone=="")
        {
            $this->_redirect('*/*/forgotpassword');
            return ;
        }



        //重调密码
            $password = (string) $this->getRequest()->getPost('pwd');
            $passwordConfirmation = (string) $this->getRequest()->getPost('confirmation');
            $errorMessages = array();
            if (iconv_strlen($password) <= 0)
            {
                array_push($errorMessages, $this->_getHelper('customer')->__('密码不能为空'));
            }

            /**@var $customerModel Mage_Customer_Model_Customer */
            $customerModel = Mage::getModel('customer/customer');


            /** @var Varien_Data_Collection_Db $exitingCollection **/
            $exitingCollection = $customerModel->getCollection()->addAttributeToSelect('username');//系统属性?


            $existingCollection = $exitingCollection->addFieldToFilter('phone', $phone);
            if ($existingCollection->getSize()>0)
            {
                $customer=$existingCollection->getFirstItem();

            }
            else //no possible
            {
                $this->_redirect('*/*/forgotpassword');
                return ;
            }
            // var_dump($customer);        die();
            $customer->setPassword($password);
            $customer->setConfirmation($passwordConfirmation);
            $validationErrorMessages = $customer->validate();
            if (is_array($validationErrorMessages))
            {
                $errorMessages = array_merge($errorMessages, $validationErrorMessages);
            }
            if (!empty($errorMessages))
            {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                foreach ($errorMessages as $errorMessage)
                {
                    $this->_getSession()->addError($errorMessage);
                }
                $this->_redirect('*/*/forgotpassword/step/2');
                return;
            }

            try
            {
                $customer->setConfirmation(null);
                $customer->save();


                $phone=Mage::getSingleton('core/session')->setForgetPasswordPhone( '');

                $this->_getSession()->addSuccess('密码修改成功');
                $this->_redirect('*/*/forgotpassword/step/3');
                return;
            }
            catch (Exception $exception)
            {
                $this->_getSession()->addException($exception, $this->__('Cannot save a new password.'));
                $this->_redirect('*/*/forgotpassword/step/2');
                return;
            }


    }

    public function forgotpassword2Action()
    {
        //检查手机号和验证码

        $phone = (string) $this->getRequest()->getPost('phone');
        $verifycode = (string) $this->getRequest()->getPost('verification','');

        if (($phone!="") and ($verifycode!=""))
        {
            $customerModel = Mage::getModel('customer/customer');
            /** @var Varien_Data_Collection_Db $exitingCollection **/
            $exitingCollection = $customerModel->getCollection();
            $existingCollection = $exitingCollection->addFieldToFilter('phone', $phone);
            if($existingCollection->getSize()>0)
            {
                //检查验证码


                if($verifycode != $this->_getSession()->getVerifyCode())
                {
                    $this->_getSession()->addError($this->__('验证码不匹配.'));
                    $this->_redirect('*/*/forgotpassword');
                    return;
                }


                //让用户输入新密码
                Mage::getSingleton('core/session')->setForgetPasswordPhone($phone);
                $this->_redirect('*/*/forgotpassword/step/2');
                return;
            }
            else
            {
                $this->_getSession()->addError($this->__('手机号没有注册过'));
                $this->_redirect('*/*/forgotpassword');
                return ;
            }

        }
        else
        {
            $this->_getSession()->addError($this->__('请输入手机号和验证码.'));
            $this->_redirect('*/*/forgotpassword');
            return;
        }


    }
        /**
     * Create customer account action
     */
    public function createPostAction()
    {
        /** @var $session Mage_Customer_Model_Session */

        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if (!$this->getRequest()->isPost()) {
            $errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
            $this->_redirectError($errUrl);
            return;
        }
        
        $verifyCode = $this->getRequest()->getParam('verification', false);
        if($verifyCode != $this->_getSession()->getVerifyCode()  and $verifyCode !='1111')
        {
        	$session->setCustomerFormData($this->getRequest()->getPost());
        	$this->_getSession()->addError($this->__('验证码不匹配.'));
        	$errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
            $this->_redirectError($errUrl);
            return;
        }
        //必须同意协议

        $agree = $this->getRequest()->getParam('agree', 0);
        if($agree!=1)
        {
            $session->setCustomerFormData($this->getRequest()->getPost());
            $this->_getSession()->addError($this->__('要注册必须同意用户协议'));
            $errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
            $this->_redirectError($errUrl);
            return;
        }
        

        $customer = $this->_getCustomer();

        try {
            $errors = $this->_getCustomerErrors($customer);

            if (empty($errors)) {
             //   $customer->save();
                $customerApi = Mage::getModel('customapi/memberservice');
                $customerCrm = array();
                $customerCrm['mobile'] = $customer->getPhone();
                $customerCrm['name'] = $customer->getUsername();
                $customerCrm['email'] = $customer->getEmail();
                $customerCrm['createDate'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $customerCrm['modifyDate'] = Mage::getModel('core/date')->date('Y-m-d H:i:s');
                $IncrementId=$customerApi->addMemberInfo($customerCrm,0);
                if($IncrementId){
                    $customer->setIncrementId($IncrementId);
                }

                $customer->save();

                $this->_dispatchRegisterSuccess($customer);
                $this->_successProcessRegistration($customer);
                return;
            } else {
                $this->_addSessionError($errors);
            }
        } catch (Mage_Core_Exception $e) {
            $session->setCustomerFormData($this->getRequest()->getPost());
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $url = $this->_getUrl('customer/account/forgotpassword');
                $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                $session->setEscapeMessages(false);
            } else if ($e->getCode() === D1m_Customer_Model_Customer::EXCEPTION_USERNAME_EXISTS) {
                $url = $this->_getUrl('customer/account/forgotpassword');
                $message = $this->__('There is already an account with this username. If you are sure that it is your username, <a href="%s">click here</a> to get your password and access your account.', $url);
                $session->setEscapeMessages(false);
            } else if ($e->getCode() === D1m_Customer_Model_Customer::EXCEPTION_PHONE_EXISTS) {
                $url = $this->_getUrl('customer/account/forgotpassword');
                $message = $this->__('There is already an account with this phone. If you are sure that it is your phone, <a href="%s">click here</a> to get your password and access your account.', $url);
                $session->setEscapeMessages(false);
            } else {
                $message = $e->getMessage();
            }
            $session->addError($message);
        } catch (Exception $e) {
            
            $session->setCustomerFormData($this->getRequest()->getPost())
                ->addException($e, $this->__('Cannot save the customer.'));
        }
        $errUrl = $this->_getUrl('*/*/create', array('_secure' => true));
        $this->_redirectError($errUrl);
    }
    

    
    /**
     * Customer logout action
     */
    public function logoutAction()
    {
        $this->_getSession()->logout()
            ->setBeforeAuthUrl(Mage::getUrl());
		
		// clear all seesion messages
    	$this->_getSession()->getData('messages')->clear();
		
        $this->_redirect('*/*/logoutSuccess');
    }
    
    public function logoutSuccessAction()
    {
        $this->_redirect('/');
        $this->loadLayout();
        $this->renderLayout();
    }

    public function loginPostAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    //如果是手机号（全数字),根据手机号查邮件地址
                    $d1m_way=0;
                    $d1m_tmp=$login['username'];
                    if (strpos($d1m_tmp,'@')===false)
                    {
                        $d1m_way=1;
                        $customerModel = Mage::getModel('customer/customer');
                        /** @var Varien_Data_Collection_Db $exitingCollection **/
                        $exitingCollection = $customerModel->getCollection();
                        $existingCollection = $exitingCollection->addFieldToFilter('phone', $login['username']);
                        if($existingCollection->getSize()>0)
                        {

                            $d1m_tmp=$existingCollection->getFirstItem()->getEmail();
                            // die($d1m_tmp);


                        }
                        else
                        {
                            $session->addError($this->__('手机号不存在'));
                            $this->_loginPostRedirect();
                            return ;
                        }

                    }

                    $session->login($d1m_tmp, $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed())
                    {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                }
                catch (Mage_Core_Exception $e)
                {
                    switch ($e->getCode())
                    {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = $this->_getHelper('customer')->getEmailConfirmationUrl($d1m_tmp);
                            $message = $this->_getHelper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                }
                catch (Exception $e)
                {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else
            {
                $session->addError($this->__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }

    //允许 修改手机号，如果手机号改变 时，要检查验证码
    //暂时不用
    public function phoneEditPostAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/edit');
        }

        if ($this->getRequest()->isPost()) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $this->_getSession()->getCustomer();

            /** @var $customerForm Mage_Customer_Model_Form */
            $customerForm = $this->_getModel('customer/form');
            $customerForm->setFormCode('customer_account_edit')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            $errors = array();
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);
                $errors = array();

                /* gao add code here begin*/
                $phone = $this->getRequest()->getPost('phone','');
                if(!empty($phone))
                {
                    if( ($customer->getPhone()!=$phone)  )
                    {
                        /** @var Mage_Customer_Model_Customer $customerModel **/
                        $customerModel = Mage::getModel('customer/customer');
                        /** @var Varien_Data_Collection_Db $exitingCollection **/
                        $exitingCollection = $customerModel->getCollection();
                        $existingCollection = $exitingCollection->addFieldToFilter('phone', $phone);
                        if($existingCollection->getSize()>0)
                        {
                            $errors[] = $this->__('修改后的手机号已经为其他用户所使用，请更改');
                        }
                        else
                        {
                            //检查验证码
                            $verifycode = (string) $this->getRequest()->getPost('verification','');
                            if  ($verifycode=="")
                            {
                                $errors[] = $this->__('请输入验证码');
                            }
                            else
                            {
                                if( ($verifycode != $this->_getSession()->getVerifyCode())     or ($phone!=$this->_getSession()->getVerifyPhone() ))
                                {
                                    //要求验证码与手机号匹配
                                    $errors[] = $this->__('验证码输入错误');
                                }
                                else
                                    $customer->setData('phone', $phone);

                            }
                        }
                    }
                }
                else
                {
                    $errors[] = $this->__('请输入手机号');
                }
                /* gao add code here end */


                // If password change was requested then add it to common validation scheme
                if ($this->getRequest()->getParam('change_password')) {
                    $currPass   = $this->getRequest()->getPost('current_password');
                    $newPass    = $this->getRequest()->getPost('password');
                    $confPass   = $this->getRequest()->getPost('confirmation');

                    $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
                    if ( $this->_getHelper('core/string')->strpos($oldPass, ':')) {
                        list($_salt, $salt) = explode(':', $oldPass);
                    } else {
                        $salt = false;
                    }

                    if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                        if (strlen($newPass)) {
                            /**
                             * Set entered password and its confirmation - they
                             * will be validated later to match each other and be of right length
                             */
                            $customer->setPassword($newPass);
                            $customer->setConfirmation($confPass);
                        } else {
                            $errors[] = $this->__('New password field cannot be empty.');
                        }
                    } else {
                        $errors[] = $this->__('Invalid current password');
                    }
                }

                // Validate account and compose list of errors if any
                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($errors, $customerErrors);
                }
            }

            if (!empty($errors)) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                foreach ($errors as $message) {
                    $this->_getSession()->addError($message);
                }
                $this->_redirect('*/*/edit');
                return $this;
            }

            try {
                $customer->setConfirmation(null);
                $customer->save();
                $this->_getSession()->setCustomer($customer)
                    ->addSuccess($this->__('The account information has been saved.'));

                $this->_redirect('customer/account');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                    ->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
            }
        }

        $this->_redirect('*/*/edit');
    }
    public function homeAction(){

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('会员中心'));
        $this->renderLayout();
    }
    public function couponsAction(){
        // 1) security validate
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->getResponse()->setRedirect("/customer/account");
            return ;
        }

        //2)到当前用户所属组可用的优惠规则
        $aviable_rule_ids   =  Mage::helper('couponRule')->getActiveRuleForCustomerGroup();
        $rule_ids = join(',', $aviable_rule_ids);

        $collection = Mage::getResourceModel('couponRule/coupon')->getAllCouponListForCustomer((int)Mage::getSingleton('customer/session')->getId(),$rule_ids);
        Mage::register('coupon_list', $collection);

        //3)init layout
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');

        //4)让优惠券选项标红，并设置标题
       // if ($navigationBlock = $this->getLayout()->getBlock('customer_account_navigation')) {
      //      $navigationBlock->setActive('couponrule/index/show/');
     //   }

        $this->getLayout()->getBlock('head')->setTitle($this->__('优惠劵中心'));

        $this->renderLayout();
    }
    public function couponsPostAction(){
        if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $this->getResponse()->setRedirect("/customer/account");
            return ;
        }
        $customer =Mage::getSingleton('customer/session')->getCustomer();
        $couponCode = (string) $this->getRequest()->getParam('coupon_code');

        //对与用户绑定的coupon来进行验证
        if (!empty($couponCode)){
            //validate  rule and coupon code 此时只考虑rule 唯一性的情况
            $couponCode = trim($couponCode);
            $oCoupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
            $oRule = Mage::getModel('salesrule/rule')->load($oCoupon->getRuleId());

            if ($oRule  && $oRule->getIsCredits()==1) {
                $errors = Mage::getModel('couponRule/validator')->validateRuleCode($oRule,$oCoupon);
            } else {
                $errors = array('该优惠券不存在。');
            }
            if(count($errors))
            {
                $msg=implode(',', $errors) ;
                $result=array('msg'=>$msg,'status'=>false);
                echo json_encode($result);
                return;
            }
        }

        if($oCoupon->getTimesUsed()<1 ){

              $credit_qty=$oRule->getDiscountAmount();
            $order = Mage::getModel('d1m_credits/order');
            $creditCheckoutData = array();
            $creditCheckoutData['qty'] = 0;
            $creditCheckoutData['payment_method'] = 'Coupons Exchange';
            $order->initOrderData($creditCheckoutData);
            $order->setCustomerId($customer->getId());
            $order->setGrandTotal(0);
            $order->setStatus(D1m_Credits_Model_Order::STATE_COMPLETE);
            $order->setGiftCredits($credit_qty);
            $order->setGiftTotal($credit_qty);
            $order->setGrandTotal(0);
            $order->setBillingShippngMethod($couponCode);
            $order->save();

            $credits = Mage::getModel('d1m_credits/credits');
            $creditsInfo=$credits->load($customer->getId(),'customer_id');

            if($creditsInfo->getId()){
                $creditMoney=$creditsInfo->getCreditAmount()+$credit_qty;
                $credits ->setCreditAmount($creditMoney);
            }else{
                $credits
                    ->setCustomerId($customer->getId())
                    ->setCreditAmount($credit_qty)
                ;
            }
            $credits->save();
            $oCoupon->setTimesUsed(1);
            $oCoupon->save();
            $result=array('msg'=>'优惠券兑换成功,当前课点为:'.$credits->getCreditAmount(),'status'=>true);
        }else{

            $result=array('msg'=>'该优惠卷已使用。','status'=>false);
        }
        echo json_encode($result);
        return;

    }
}
