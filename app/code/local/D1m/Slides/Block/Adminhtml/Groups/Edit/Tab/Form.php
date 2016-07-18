<?php
/**
 * Created by Victor Guo
 * Date: 13-8-16
 * Time: 上午10:52
 */
class D1m_Slides_Block_Adminhtml_Groups_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('group_form', array(
            'legend' => $this->__('Group Information')
        ));

        $fieldset->addField('group_name', 'text', array(
            'label' => $this->__('Name'),
            'class' => 'required-entry',
            'require' => true,
            'name' => 'group_name'
        ));

        $fieldset->addField('slides_width', 'text', array(
            'label' => $this->__('Width'),
            'required' => true,
            'name' => 'slides_width'
        ));

        $fieldset->addField('slides_height', 'text', array(
            'label' => $this->__('height'),
            'required' => true,
            'name' => 'slides_height'
        ));

        $fieldset->addField('selected_slide_ids', 'hidden', array(
            'name' => 'selected_slide_ids'
        ));

        $fieldset->addField('status', 'select', array(
            'label' => $this->__('Status'),
            'class' => 'required-entry',
            'name' => 'status',
            'values' => array(
                array(
                    'value' => 0,
                    'label' => $this->__('Disabled')
                ),
                array(
                    'value' => 1,
                    'label' => $this->__('Enabled')
                )
            )
        ));

        if(Mage::getSingleton('adminhtml/session')->getGroupData())
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->setGroupData(null));
        }
        elseif(Mage::registry('group_data'))
        {
            $form->setValues(Mage::registry('group_data'));
        }

        return parent::_prepareForm();
    }
}