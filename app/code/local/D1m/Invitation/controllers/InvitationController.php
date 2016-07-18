<?php
class D1m_Invitation_InvitationController extends Mage_Core_Controller_Front_Action
{
    
    /**
     * Action predispatch
     *
     * Check customer authentication for some actions
     */
    public function preDispatch()
    {
        // a brute-force protection here would be nice

        parent::preDispatch();

        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        if (!$this->_getSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
         
    }
    
   
   /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    
    protected function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    
    function viewAction(){
        
        $this->loadLayout();
        
        $this->_initLayoutMessages('checkout/session');
        
        $this->renderLayout();
    }
    
    function sendAction()
    {
    	
         if ($this->getRequest()->isPost()) {
            
            $data = $this->getRequest()->getParam('invitation', array());
            
            $invite = Mage::getModel('d1m_invitation/invitation');
            $errors = $invite->validate($data);
            if(count($errors) == 0)
            {
	            try {
	            	
	                $invite->customer_id = $this->_getSession()->getCustomerId();
	            	$invite->name = $data['name'];
	            	$invite->email = $data['email'];
	            	$invite->phone = $data['phone'];
	            	$invite->note = $data['note'];
	            	$invite->save();
	            	
	            	$invite->sendInvitationEmail($this->_getSession()->getCustomer()->getUsername());
	            	
	            	$this->_getSession()->setInvitationFormData(null);
	            	
	            	$this->_getSession()->addSuccess($this->__('invate succefully.'));
	
	                $this->_redirect('customer/account');
	                return;
	                
	            } catch (Exception $e) {
	            	
	            	//echo $e->getMessage();exit();
	            	
	                $this->_getSession()->setInvitationFormData($data)
	                    ->addException($e, $this->__('Cannot save the invitation.'));
	            }
            	
            }
            else
            {
            	foreach ($errors as $message) {
                    $this->_getSession()->addError($message);
                }
            	$this->_getSession()->setInvitationFormData($data);
            }
            
            
            $this->_redirect('customer/account');
            
         }
         else
         {
         	$message = Mage::helper('d1m_invitation')->__('Data saving problem');
         	$this->_getCheckoutSession()->addError($message);
         	$this->_redirect('customer/account');
         	
         }
         
    }
    

    
    
}