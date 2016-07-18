<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Helper_Banner extends Mage_Core_Helper_Abstract
{
    /**
     * @var TM_EasyBanner_Block_Banner
     */
    private $_instance = null;
    
    public function load($data)
    {
        if (null === $this->_instance) {
            $this->_instance = new TM_EasyBanner_Block_Banner();
        }
        return $this->_instance->setData($data);
    }
}