<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Block_Adminhtml_Banner extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_banner';
        $this->_blockGroup = 'easybanner';
        $this->_headerText = Mage::helper('easybanner')->__('Manage Banners');
        $this->_addButtonLabel = Mage::helper('easybanner')->__('Add Banner');
        parent::__construct();
    }
}