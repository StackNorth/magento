<?php
class D1m_Producttool_Block_Adminhtml_Changeqty_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();

        $this->_controller = 'adminhtml_changeqty';
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
