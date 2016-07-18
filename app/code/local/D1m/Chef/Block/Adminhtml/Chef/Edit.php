<?php
class D1m_Chef_Block_Adminhtml_Chef_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'd1m_chef';
        $this->_controller = 'adminhtml_chef';
        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('保存'));
        $this->_updateButton('delete', 'label', $this->__('删除'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('保存并继续编辑'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('chef_chef_data') && Mage::registry('chef_chef_data')->getId()) {
            return $this->__("编辑厨师资料,编号 %s", $this->escapeHtml(Mage::registry('chef_chef_data')->getId()));
        } else {
            return $this->__('添加厨师');
        }
    }
}
