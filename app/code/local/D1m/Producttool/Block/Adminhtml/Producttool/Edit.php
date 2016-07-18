<?php
class D1m_Producttool_Block_Adminhtml_Producttool_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_producttool';
        $this->_blockGroup = 'd1m_producttool';

        //$this->_updateButton('save', 'label', Mage::helper('adminhtml')->__('生成'));
        $this->_removeButton('delete');
        $this->_removeButton('back');
        $this->_removeButton('save');
    }

    public function getHeaderText()
    {
        return Mage::helper('adminhtml')->__('导入课程');
    }
}
