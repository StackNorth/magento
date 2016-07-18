<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Model_Mysql4_Placeholder extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('easybanner/placeholder', 'placeholder_id');
    }
    
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if (null === $object->getDoNotUpdateLayout()) {
            /* layout_update */
            Mage::getModel('easybanner/layout')->buildLayoutUpdateByPlaceholder($object);
        }
        return $this;
    }
    
    protected function _beforeDelete(Mage_Core_Model_Abstract $object)
    {
        /* cleanup layout_update table */
        Mage::getResourceModel('easybanner/layout')->removeUpdatesByPlaceholder($object->getId());
        return $this;
    }
    
    public function loadBannerIds(TM_EasyBanner_Model_Placeholder $object, $activeOnly = false)
    {
        $placeholderId = $object->getId();
        $bannerIds = array();
        if ($placeholderId) {
            $bannerIds = $this->lookupBannerIds($placeholderId, $activeOnly);
        }
        if ($activeOnly) {
            $object->setBannerIdsActive($bannerIds);
        } else {
            $object->setBannerIds($bannerIds);
        }
    }
    
    /**
     * Get banner ids, that linked with placeholder
     *
     * @param int $id placeholder_id
     * @param boolean $activeOnly
     * @return array
     */
    public function lookupBannerIds($id, $activeOnly = false)
    {
        $select = $this->_getReadAdapter()->select()
            ->from(array('banner_placeholder' => $this->getTable('easybanner/banner_placeholder')), 'banner_id')
            ->where("banner_placeholder.placeholder_id = ?", $id);
        
        if ($activeOnly) {
            $select
                ->join(array('banner' => $this->getTable('easybanner/banner')), 
                    'banner.banner_id = banner_placeholder.banner_id')
                ->where("banner.status = 1");
        }
        
        return $this->_getReadAdapter()->fetchCol($select);
    }
}