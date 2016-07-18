<?php
/**
 * Created by Victor Guo
 * Date: 13-8-15
 * Time: 下午3:35
 */
class D1m_Credits_Block_Adminhtml_Credittest_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_credittest';
        $this->_blockGroup = 'd1m_credits';
        parent::__construct();
        $this->_removeButton('delete');
        $this->_removeButton('back');
        // $this->_removeButton('save');
        $this->_updateButton('save', 'label',Mage::helper('adminhtml')->__('确定提交'));
       

    }

    public function getHeaderText()
    {
        return Mage::helper('adminhtml')->__('测试数据');
    }



}