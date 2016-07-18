<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Block_Adminhtml_Banner_Edit_Tab_Content extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('easybanner_banner');
        
        $form = new Varien_Data_Form();
        
        $form->setHtmlIdPrefix('banner_');
        
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('easybanner')->__('General Information'), 'class' => 'fieldset-wide'));
        $this->_addElementTypes($fieldset); //register own image element
        
        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('easybanner')->__('Title'),
            'title'     => Mage::helper('easybanner')->__('Title'),
            'required'  => true
        ));
        
        $fieldset->addField('url', 'text', array(
            'name'      => 'url',
            'label'     => Mage::helper('easybanner')->__('Url'),
            'title'     => Mage::helper('easybanner')->__('Url'),
            'required'  => false
        ));
        
        $fieldset->addField('mode', 'select', array(
            'label'     => Mage::helper('easybanner')->__('Mode'),
            'title'     => Mage::helper('easybanner')->__('Mode'),
            'name'      => 'mode',
            'options'   => array(
                'image' => Mage::helper('easybanner')->__('Image'),
                'html' => Mage::helper('easybanner')->__('Html')
            ),
            'required'  => true
        ));
        
        $fieldset->addField('image', 'image', array(
            'name'      => 'image',
            'label'     => Mage::helper('easybanner')->__('Image'),
            'title'     => Mage::helper('easybanner')->__('Image')
        ));
        
        $fieldset->addField('html', 'textarea', array(
            'name'      => 'html',
            'label'     => Mage::helper('easybanner')->__('Content'),
            'title'     => Mage::helper('easybanner')->__('Content')
        ));
        
        $form->setValues($model->getData());
        $this->setForm($form);
        
        return parent::_prepareForm();
    }
    
    protected function _getAdditionalElementTypes()
    {
        return array(
            'image' => Mage::getConfig()->getBlockClassName('easybanner/adminhtml_banner_helper_image')
        );
    }
    
}
