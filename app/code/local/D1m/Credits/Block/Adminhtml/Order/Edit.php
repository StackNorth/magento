<?php
/**
 * User: ahsw@qq.com
 * caeate Time: 2016/5/611:41
 */
class D1m_Credits_Block_Adminhtml_Order_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {

        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_order';
        $this->_blockGroup = 'd1m_credits';
        parent::__construct();
        //$this->_removeButton('delete');
        $this->_removeButton('back');
        // $this->_removeButton('save');
        $this->_updateButton('save', 'label',Mage::helper('adminhtml')->__('确定提交'));
        // $this->_updateButton('delete', 'label', Mage::helper('d1m_credits')->__('Delete'));

    }

    public function getHeaderText()
    {
        if( Mage::registry('current_order') && Mage::registry('current_order')->getId() ) {
            return Mage::helper('adminhtml')->__("Edit Order ID:".Mage::registry('current_order')->getIncrementId());
        } else {
            return Mage::helper('adminhtml')->__('设置订单价格');
        }


    }

}
