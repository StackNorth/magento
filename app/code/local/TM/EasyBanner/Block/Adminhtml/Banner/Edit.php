<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Block_Adminhtml_Banner_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
               
        $this->_objectId = 'id';
        $this->_blockGroup = 'easybanner';
        $this->_controller = 'adminhtml_banner';
 
        $this->_updateButton('save', 'label', Mage::helper('easybanner')->__('Save Banner'));
        $this->_updateButton('delete', 'label', Mage::helper('easybanner')->__('Delete Banner'));
        $this->_addButton('clear_statistic', array(
            'label' => Mage::helper('easybanner')->__('Clear Statistics'),
            'onclick' => 'clearStatistics()',
            'class' => 'delete'
        ));
        
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('easybanner')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
        
        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
            function clearStatistics(){
                new Ajax.Request('".$this->getUrl('*/*/clearStatistics/id/' . Mage::registry('easybanner_banner')->getId())."', {
                    onSuccess: function(){
                        $('banner_display_count').clear();
                        $('banner_clicks_count').clear();
                    }
                });
            }
        ";
    }
 
    public function getHeaderText()
    {
        if (Mage::registry('easybanner_banner') && Mage::registry('easybanner_banner')->getId()) {
            return Mage::helper('easybanner')->__("Edit Banner '%s'", $this->htmlEscape(Mage::registry('easybanner_banner')->getIdentifier()));
        } else {
            return Mage::helper('easybanner')->__('Add Banner');
        }
    }
    
}