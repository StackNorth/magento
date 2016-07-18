<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Block_Adminhtml_Banner_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('banner_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('easybanner')->__('Banner Information'));
    }

    protected function _beforeToHtml()
    {
        $this->addTab('main_section', array(
            'label'     => Mage::helper('easybanner')->__('General Information'),
            'title'     => Mage::helper('easybanner')->__('General Information'),
            'content'   => $this->getLayout()->createBlock('easybanner/adminhtml_banner_edit_tab_main')->toHtml(),
            'active'    => true
        ));
        
        $this->addTab('content_section', array(
            'label'     => Mage::helper('easybanner')->__('Content'),
            'title'     => Mage::helper('easybanner')->__('Content'),
            'content'   => $this->getLayout()->createBlock('easybanner/adminhtml_banner_edit_tab_content')->toHtml()
        ));

        $this->addTab('conditions_section', array(
            'label'     => Mage::helper('easybanner')->__('Conditions'),
            'title'     => Mage::helper('easybanner')->__('Conditions'),
            'content'   => $this->getLayout()->createBlock('easybanner/adminhtml_banner_edit_tab_conditions')->toHtml()
        ));
       
       $this->addTab('statistics_section', array(
            'label'     => Mage::helper('easybanner')->__('Statistics'),
            'title'     => Mage::helper('easybanner')->__('Statistics'),
            'content'   => $this->getLayout()->createBlock('easybanner/adminhtml_banner_edit_tab_statistics')->toHtml()
        ));
        return parent::_beforeToHtml();
    }

}
