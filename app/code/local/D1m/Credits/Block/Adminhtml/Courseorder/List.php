<?php

class D1m_Credits_Block_Adminhtml_Courseorder_List extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_courseorder';
        $this->_blockGroup = 'd1m_credits';
        $this->_headerText = Mage::helper('d1m_credits')->__('Manage Course Order');
       
        
        //$this->setTemplate('widget/grid/container.phtml');
        
        parent::__construct();
        
        $this->_removeButton('add');
        
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
