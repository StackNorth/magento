<?php


class D1m_Customer_Model_Customer extends Mage_Customer_Model_Customer
{
    
    
    const EXCEPTION_USERNAME_EXISTS              = 5;
    const EXCEPTION_PHONE_EXISTS              = 6;
    
    
    /**
     * Load customer by account ID
     *
     * @param   string $accountID
     * @return  Mage_Customer_Model_Customer
     */
    public function loadByAccountId($accountID)
    {
	Mage::getModel('customer/entity_customer')->loadByAccountId($this, $accountID);
//         $this->_getResource()->loadByAccountId($this, $accountID);
        return $this;
    }
    
     public function getName(){
        return $this->getUsername();
    }
    
    
    /**
     * Retrieve request object
     *
     * @return Mage_Core_Controller_Request_Http
     */
    public function getRequest()
    {
        $controller = Mage::app()->getFrontController();
        if ($controller) {
            $this->_request = $controller->getRequest();
        } else {
            throw new Exception(Mage::helper('core')->__("Can't retrieve request object"));
        }
        return $this->_request;
    }
    
        /**
     * Validate customer attribute values
     *
     * @return bool
     */
    public function validate()
    {
        $errors = array();
        $customerHelper = Mage::helper('customer');
        $addressHelper = Mage::helper('customer/address');
        
        /**
        if (!Zend_Validate::is( trim($this->getFirstname()) , 'NotEmpty')) {
            $errors[] = $customerHelper->__('The first name cannot be empty.');
        }

        if (!Zend_Validate::is( trim($this->getLastname()) , 'NotEmpty')) {
            $errors[] = $customerHelper->__('The last name cannot be empty.');
        }**/
        
        
        if (!Zend_Validate::is( trim($this->getUsername()) , 'NotEmpty')) {
            $errors[] = $customerHelper->__('The username cannot be empty.');
        }

        if (!Zend_Validate::is( trim($this->getPhone()) , 'NotEmpty')) {
            $errors[] = $customerHelper->__('The phone cannot be empty.');
        }

        if (!Zend_Validate::is($this->getEmail(), 'EmailAddress')) {
            $errors[] = $customerHelper->__('Invalid email address "%s".', $this->getEmail());
        }
        
        $password = $this->getPassword();
        if (!$this->getId() && !Zend_Validate::is($password , 'NotEmpty')) {
            $errors[] = $customerHelper->__('The password cannot be empty.');
        }
        if ($password && !Zend_Validate::is($password, 'StringLength', array(6))) {
            $errors[] = $customerHelper->__('The minimum password length is %s', 6);
        }
        $confirmation = $this->getConfirmation();
        if ($password != $confirmation) {
            $errors[] = $customerHelper->__('Please make sure your passwords match.');
        }


        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
    
}
