<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Model_Banner extends Mage_Rule_Model_Rule
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('easybanner/banner');
    }
    
    public function getConditionsInstance()
    {
        return Mage::getModel('easybanner/rule_condition_combine');
    }
    
    /**
     * Return true if banner status = 1
     * and banner linked to active placeholder
     * 
     * @return boolean 
     */
    public function isActive()
    {
        if ($this->getStatus() && count($this->getPlaceholderIds(true))) {
            return true;
        }
        return false;
    }
    
    public function getPlaceholderIds($isActive = false)
    {
        $key = $isActive ? 'placeholder_ids_active' : 'placeholder_ids';
        $ids = $this->_getData($key);
        if (null === $ids) {
            $this->_getResource()->loadPlaceholderIds($this, $isActive);
            $ids = $this->_getData($key);
        }
        return $ids;
    }
    
    public function getStoreIds()
    {
        $ids = $this->_getData('store_ids');
        if (null === $ids) {
            $this->_getResource()->loadStoreIds($this);
            $ids = $this->_getData('store_ids');
        }
        return $ids;
    }
    
    public function getClicksCount()
    {
        return $this->getStatistics('clicks_count');
    }
    
    public function getDisplayCount()
    {
        return $this->getStatistics('display_count');
    }
    
    public function getStatistics($key)
    {
        $stat = $this->_getData($key);
        if (null === $stat) {
            $this->_getResource()->loadStatistics($this);
            $stat = $this->_getData($key);
        }
        return $stat;
    }
    
    /**
     * Checks is banner is active for requested store
     * Used to check is it possible to click on banner
     * 
     * @param int $store
     * @return mixed int|boolean
     */
    public function check($store)
    {
        return $this->isActive() && (in_array($store, $this->getStoreIds()) || in_array(0, $this->getStoreIds()));
    }
}