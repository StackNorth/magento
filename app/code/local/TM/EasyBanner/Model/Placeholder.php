<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Model_Placeholder extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('easybanner/placeholder');
    }
    
    public function getBannerIds($isActive = false)
    {
        $key = $isActive ? 'banner_ids_active' : 'banner_ids';
        $ids = $this->_getData($key);
        if (is_null($ids)) {
            $this->_getResource()->loadBannerIds($this, $isActive);
            $ids = $this->getData($key);
        }
        return $ids;
    }
}