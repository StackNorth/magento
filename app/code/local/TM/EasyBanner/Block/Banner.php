<?php
/**
 * DO NOT REMOVE OR MODIFY THIS NOTICE
 * 
 * EasyBanner module for Magento - flexible banner management
 * 
 * @author Templates-Master Team <www.templates-master.com>
 */

class TM_EasyBanner_Block_Banner extends Mage_Core_Block_Template
{
    public function getTemplate()
    {
        if (!$this->hasData('template')) {
            $this->setData('template', "easybanner/banner/{$this->getMode()}.phtml");
        }
        return $this->_getData('template');
    }
    
    protected function _toHtml()
    {
        $html = parent::_toHtml();        
        $statRes = Mage::getResourceModel('easybanner/banner_statistic')
            ->incrementDisplayCount($this->getBannerId());
        return $html;
    }
    
    public function getBannerUrl()
    {
        $url = 'click/id/' . $this->_getData('banner_id');
        if (!$this->getHideUrl()) {
            return $url . '/url/' . $this->_getData('url');
        } else {
            return $url;
        }
    }
}
