<?php

/**
* Interakting Slider
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@magentocommerce.com and you will be sent a copy immediately.
*
* @category   BusinessDecision
* @package    BusinessDecision_InteraktingSlider
* @author     Business & Decision Picardie - contactmagento@interakting.com
* @copyright  Copyright (c) 2009 Business & Decision (http://www.businessdecision.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

Class BusinessDecision_Interaktingslider_Block_Interaktingslider extends Mage_Core_Block_Template {
    static $_jsAdded;

	public function __construct()
    {
        parent::__construct();
        
        
        $this->setTemplate('interaktingslider/slider.phtml');
    }

    public function getDelay()
    {
        return $this->getData('delay') ? $this->getData('delay') : $this->getModel()->getDelay();
    }

	/**
	 * Retourne le model
	 *
	 * @return BusinessDecision_Interaktingslider_Model_Interaktingslider
	 */
	public function getModel(){
        if (!$this->getData('model')) {
            $this->setData('model', Mage::getModel('interaktingslider/interaktingslider'));
        }
		return $this->getData('model');
	}


	/**
	 * Retourne la collection des slides
	 *
	 * @return array
	 */
	public function getSlides(){
		return $this->getModel()->getSlides($this->getGroup());
	}

    public function getSpeed()
    {
        return $this->getData('speed') ? $this->getData('speed') : $this->getModel()->getSpeed();
    }

	/**
	 * Code d'ajout du fichier Js de l'Interakting Slider
	 *
	 * @return code HTML
	 */
	public function addJs()
    {
        if (self::$_jsAdded) return '';
        self::$_jsAdded = TRUE;
		return '<script type="text/javascript" src="'.Mage::getBaseUrl('js').'jquery/slides.min.jquery.js"></script>';
	}
}
