<?php
class D1m_Producttool_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_order';
        $this->_blockGroup = 'd1m_producttool';
        $this->_headerText = Mage::helper('d1m_producttool')->__('课程统计');
        parent::__construct();
        $this->removeButton('add');

    }
}