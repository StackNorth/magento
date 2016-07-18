<?php

class D1m_Invitation_Block_Adminhtml_Invitation_List extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_invitation';
        $this->_blockGroup = 'd1m_invitation';
        $this->_headerText = Mage::helper('d1m_invitation')->__('Manage Invitation');
       
        $this->_addButtonLabel = Mage::helper('d1m_invitation')->__('Add');
        
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
