<?php
class D1m_Invitation_Model_Invitation extends Mage_Core_Model_Abstract 
{
     public $historyDesc = null;
     public $historyOrderNo = null;
     
     public $_historyRec = null;
     
     const XML_PATH_REGISTER_EMAIL_TEMPLATE = 'invitation_account_email_template';
    const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';
    
    protected function _construct()
    {
    	
        $this->_init('d1m_invitation/invitation');
    }
    
    public function checkCustomerIdExists($customer_id, $id)
    {
        return $this->_getResource()->checkCustomerIdExists($customer_id, $id);
    }
    
    /**
     * Send email with new customer password
     *
     */
    public function sendInvitationEmail($sender_name=null)
    {
        $storeId = $this->getStoreId();
        
        if (!$storeId) {
            $storeId = Mage::app()->getStore()->getId();
        }
		
		$url = Mage::getUrl('customer/account/create', array('rid'=>$this->getId()));
		
        $this->_sendEmailTemplate(self::XML_PATH_REGISTER_EMAIL_TEMPLATE, self::XML_PATH_REGISTER_EMAIL_IDENTITY,
            array('invate' => $this, 'invite_url'=> $url,
			'sender_name'=>$sender_name,
			'invite_name'=>$this->name,
			'invite_phone'=>$this->phone,
			'invite_email'=>$this->email,
			'invite_note'=>$this->note,
			), $storeId);

        return $this;
    }

    /**
     * Send corresponding email template
     *
     * @param string $emailTemplate configuration path of email template
     * @param string $emailSender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @return Mage_Customer_Model_Customer
     */
    protected function _sendEmailTemplate($template, $sender, $templateParams = array(), $storeId = null)
    {
        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getEmail(), $this->getName());
        $mailer->addEmailInfo($emailInfo);

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig($sender, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($template);
        $mailer->setTemplateParams($templateParams);
        
        //var_dump($templateParams);exit();
        
        $mailer->send();
        return $this;
    }
    
    
    
	 public function validate($data)
    {
    	
        $errors = array();
        if (!Zend_Validate::is( trim($data['name']) , 'NotEmpty')) {
            $errors[] = Mage::helper('d1m_invitation')->__('The name cannot be empty.');
        }

		if (!Zend_Validate::is($data['email'], 'EmailAddress')) {
            $errors[] = Mage::helper('d1m_invitation')->__('Invalid email address "%s".', $data['email']);
        }
        
        if (!Zend_Validate::is( trim($data['phone']) , 'NotEmpty')) {
            $errors[] = Mage::helper('d1m_invitation')->__('The phone cannot be empty.');
        }


        /**
        if (!Zend_Validate::is( trim($data['note']) , 'NotEmpty')) {
            $errors[] = Mage::helper('d1m_invitation')->__('The note cannot be empty.');
        }
        **/
        
        
        return $errors;
        
    }
    
}
