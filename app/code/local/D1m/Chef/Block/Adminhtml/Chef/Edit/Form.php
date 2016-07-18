<?php
class D1m_Chef_Block_Adminhtml_Chef_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    public function _construct() {
        parent::_construct();
        $this->setId('chef_chef_form');
        $this->setTitle($this->__('厨师资料'));
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()
            && Mage::helper('catalog')->isModuleEnabled('Mage_Cms')
        ) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    protected function _prepareForm() {
        $model = Mage::registry('chef_chef_data');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'), 'store' => $this->getRequest()->getParam('store', 0))),
            'method' => 'post',
            'enctype' => 'multipart/form-data',
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
