<?php
class D1m_Credits_Block_Adminhtml_Creditorder_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_creditorder';
        $this->_blockGroup = 'd1m_credits';
		
		parent::__construct();
		
         $this->_removeButton('delete');
         $this->_removeButton('reset');
         $this->_removeButton('save');
         
         $templateFile = 'credits/adminhtml/view.phtml';
        $this->setTemplate($templateFile);

    }
    
    public function getOrder()
    {
    	return Mage::registry('credit_order');
    }
    
    public function getCustomer()
    {
    	$order = $this->getOrder();
    	$customer_id = $order->getCustomerId();
    	$customer = Mage::getModel('customer/customer')->load($customer_id);
    	
    	return $customer;
    	
    }

    public function getHeaderText()
    {
        if( Mage::registry('credit_order') && Mage::registry('credit_order')->getId() ) {
            return Mage::helper('d1m_credits')->__("View Credit Order No. %s", $this->htmlEscape(Mage::registry('credit_order')->getId()));
        } else {
            return Mage::helper('d1m_credits')->__('New');
        }
    }
    
    
    

}
