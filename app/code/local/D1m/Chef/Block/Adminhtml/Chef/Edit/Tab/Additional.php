<?php
class D1m_Chef_Block_Adminhtml_Chef_Edit_Tab_Additional extends Mage_Adminhtml_Block_Widget_Form {

    public function initForm() {
        $model = Mage::registry('chef_chef_data');
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldSet = $form->addFieldset('chef_chef_additional_form', array(
            'legend' => $this->__('厨师介绍'),
        ));

        //文章的短描述 description
        $description = array(
            'name' => 'cshort',
            'label' => $this->__('短描述'),
            'title' => $this->__('短描述'),
            'style' => 'width:100%;height:200px;',
             'config' => Mage::getSingleton('d1m_chef/wysiwyg_config')->getConfig(array('tab_id' => $this->getTabId())),
             'wysiwyg' => Mage::getSingleton('cms/wysiwyg_config')->isEnabled()?true:false
        );
        $fieldSet->addField('cshort', 'editor', $description);

        //文章全文
        $fullContext = array(
            'name' => 'clong',
            'label' => $this->__('详细介绍'),
            'title' => $this->__('详细介绍'),
            'style' => 'width:100%;height:300px;',
            'config' => Mage::getSingleton('d1m_chef/wysiwyg_config')->getConfig(array('tab_id' => $this->getTabId())),
            'wysiwyg' => Mage::getSingleton('cms/wysiwyg_config')->isEnabled()?true:false
        );
        $fieldSet->addField('clong', 'editor', $fullContext);

        if (Mage::registry('chef_chef_data'))
        {
            $form->setValues(Mage::registry('chef_chef_data')->getData());
        }
        $this->setForm($form);

        return $this;
    }

}
