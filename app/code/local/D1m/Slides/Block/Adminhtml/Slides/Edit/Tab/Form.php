<?php
/**
 * Created by Victor Guo
 * Date: 13-8-15
 * Time: 下午4:32
 */
class D1m_Slides_Block_Adminhtml_Slides_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset('slide_form', array(
            'legend' => $this->__('Slide Information')
        ));

        $fieldset->addField('title', 'text', array(
            'label' => $this->__('Title'),
            'class' => 'required-entry',
            'require' => true,
            'name' => 'title'
        ));

        $fieldset->addField('filename', 'image', array(
            'label' => $this->__('Image'),
            'required' => true,
            'name' => 'filename'
        ));

        $fieldset->addField('link', 'text', array(
            'label' => $this->__('Link'),
            'name' =>'link'
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

        if(Mage::getSingleton('adminhtml/session')->getSlideData())
        {
            $form->setValues(Mage::getSingleton('adminhtml/session')->setSlideData(null));
        }
        elseif(Mage::registry('slide_data'))
        {
            $form->setValues(Mage::registry('slide_data'));
        }

        return parent::_prepareForm();
    }
}