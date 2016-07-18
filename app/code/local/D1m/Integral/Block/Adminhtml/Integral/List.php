<?php

class D1m_Integral_Block_Adminhtml_Integral_List extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_integral';
        $this->_blockGroup = 'd1m_integral';
        $this->_headerText = Mage::helper('d1m_integral')->__('Manage Integral');
       
        $this->_addButtonLabel = Mage::helper('d1m_integral')->__('Add');
        
        //$this->setTemplate('widget/grid/container.phtml');
        
        parent::__construct();
    }
    
    protected function _addBackButton()
    {
        $this->_addButton('back', array(
            'label'     => $this->getBackButtonLabel(),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() .'\')',
            'class'     => 'back',
        ));
    }
    
    public function getCreateUrl()
    {
        return $this->getUrl('*/*/new');
    }

    public function getHeaderCssClass()
    {
        return 'icon-head ' . parent::getHeaderCssClass();
    }

    public function getHeaderWidth()
    {
        return 'width:50%;';
    }
}
