<?php
class D1m_Invitation_Block_Adminhtml_Invitation_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_controller = 'invitation';

        $this->_updateButton('save', 'label', Mage::helper('d1m_invitation')->__('Save'));
        //$this->_updateButton('delete', 'label', Mage::helper('d1m_invitation')->__('Delete'));
		
		$this->removeButton('delete');
    }

    public function getHeaderText()
    {
        if( Mage::registry('invitation') && Mage::registry('invitation')->getId() ) {
            return Mage::helper('d1m_invitation')->__("Edit", $this->htmlEscape(Mage::registry('invitation')->getName()));
        } else {
            return Mage::helper('d1m_invitation')->__('New');
        }
    }
    
    
    /*
    * Overrided method because the way the name of the block form is constructed is wrong for local/community modules
    * Eg: $this->_blockGroup . '/' . $this->_controller . '_' . $this->_mode . '_form' => adminhtml/productqa_edit_form
    * we need 'pws_productqa/adminhtml_productqa_edit_form'
    */    
    protected function _prepareLayout()
    { 
        if ($this->_blockGroup && $this->_controller && $this->_mode) {
            $this->setChild('form', $this->getLayout()->createBlock('d1m_invitation/adminhtml_invitation_edit_form'));
        }
        return parent::_prepareLayout();
    }
}
