<?php
class D1m_Credits_Block_Adminhtml_Credits_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_credits';
        $this->_blockGroup = 'd1m_credits';
        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('d1m_credits')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('d1m_credits')->__('Delete'));

    }

    public function getHeaderText()
    {
        if( Mage::registry('credits') && Mage::registry('credits')->getId() ) {
            return Mage::helper('d1m_credits')->__("Edit", $this->htmlEscape(Mage::registry('credits')->getName()));
        } else {
            return Mage::helper('d1m_credits')->__('New');
        }
    }
    
    
    /*
    * Overrided method because the way the name of the block form is constructed is wrong for local/community modules
    * Eg: $this->_blockGroup . '/' . $this->_controller . '_' . $this->_mode . '_form' => adminhtml/productqa_edit_form
    * we need 'pws_productqa/adminhtml_productqa_edit_form'
    */
/*
    protected function _prepareLayout()
    {

        if ($this->_blockGroup && $this->_controller && $this->_mode) {
             $this->setChild('form', $this->getLayout()->createBlock('d1m_credits/adminhtml_credits_edit_form'));
        }
        return parent::_prepareLayout();
    }
*/
}
