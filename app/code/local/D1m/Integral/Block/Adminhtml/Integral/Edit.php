<?php
class D1m_Integral_Block_Adminhtml_Integral_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_integral';
        $this->_blockGroup = 'd1m_integral';
        $this->_objectId = 'id';

        $this->_updateButton('save', 'label', Mage::helper('d1m_integral')->__('Save'));
        $this->_updateButton('delete', 'label', Mage::helper('d1m_integral')->__('Delete'));

    }

    public function getHeaderText()
    {
        if( Mage::registry('integral') && Mage::registry('integral')->getId() ) {
            return Mage::helper('d1m_integral')->__("Edit", $this->htmlEscape(Mage::registry('integral')->getName()));
        } else {
            return Mage::helper('d1m_integral')->__('New');
        }
    }
    
    
    /*
    * Overrided method because the way the name of the block form is constructed is wrong for local/community modules
    * Eg: $this->_blockGroup . '/' . $this->_controller . '_' . $this->_mode . '_form' => adminhtml/productqa_edit_form
    * we need 'pws_productqa/adminhtml_productqa_edit_form'
    */    
    //protected function _prepareLayout()
    //{
      //  if ($this->_blockGroup && $this->_controller && $this->_mode) {
//            $this->setChild('form', $this->getLayout()->createBlock('d1m_integral/adminhtml_integral_edit_form'));
//        }
//        return parent::_prepareLayout();
//    }
}
