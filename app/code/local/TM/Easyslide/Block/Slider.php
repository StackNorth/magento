<?php

class TM_Easyslide_Block_Slider extends Mage_Core_Block_Template
{
    public function _die($id) {
    	
    	$this->setId($id);
       	$this->getTemplate();
    	$this->_toHtml();
    }
	
	public function getTemplate()
    {
        if (!$this->hasData('template')) {
            $this->setData('template', 'easyslide/slider.phtml');
        }
        return $this->getData('template');
    }
    
    public function _toHtml()
    {
      	if (!$this->_beforeToHtml() || !$sliderId = $this->getId()) {
            return '';
        }
        try {
        $slider = Mage::getModel('easyslide/easyslide')->load($this->getId());
     
        } catch (Exception $e) {
        	echo($e->getMessage());
        }
   
        $slider->loadSlides(true);
        $this->setSlider($slider);
        return parent::_toHtml();
    }
}
