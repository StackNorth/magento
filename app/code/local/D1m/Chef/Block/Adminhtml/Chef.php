<?php
class D1m_Chef_Block_Adminhtml_Chef extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_chef';
        $this->_blockGroup = 'd1m_chef';
        $this->_headerText = Mage::helper('d1m_chef')->__('厨师管理');
        $this->_addButtonLabel = Mage::helper('d1m_chef')->__('添加厨师');
        parent::__construct();
    }
}
