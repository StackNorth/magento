<?php
class D1m_Chef_Block_Adminhtml_Chef_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::_construct();
        $this->setId('chef_chef_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('厨师'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('form', array(
            'label' => $this->__('基本信息'),
            'title' => $this->__('基本信息'),
            'content' => $this->getLayout()->createBlock('d1m_chef/adminhtml_chef_edit_tab_form')->initForm()->toHtml(),
        ));
/*
        $this->addTab('additional', array(
            'label' => $this->__('描述介绍'),
            'title' => $this->__('描述介绍'),
            'content' => $this->getLayout()->createBlock('d1m_chef/adminhtml_chef_edit_tab_additional')->initForm()->toHtml(),
        ));
*/

        return parent::_beforeToHtml();
    }
}
