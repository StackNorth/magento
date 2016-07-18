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
* @package    BusinessDecision_Interaktingslider
* @author     Business & Decision Picardie - contactmagento@interakting.com
* @copyright  Copyright (c) 2009 Business & Decision (http://www.businessdecision.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/**
 * Model du interaktingslider
 *
 */
Class BusinessDecision_Interaktingslider_Model_Interaktingslider {
    protected $_slides;

	/**
	 * Recupere le modele de slide
	 *
	 * @return BusinessDecision_Interaktingslider_Model_Slide
	 */
	protected function getSlideModel(){
		return Mage::getModel('interaktingslider/slide');
	}

	/**
	 * Liste des slides
	 *
	 * @return BusinessDecision_Interaktingslider_Model_Mysql4_Slide_Collection
	 */
	protected function getSlideCollection($group = NULL){
        if ($this->_slides === NULL) {

            $vo_Collection = $this->getSlideModel()->getCollection();
            $vo_Collection->addPositionFilter(Mage::app()->getStore());
            $vo_Collection->addFilter('is_active',1);
            $group === NULL or $vo_Collection->addFilter('`group`',$group);
            $vo_Collection->addNowFilter();
            $vo_Collection->addOrder('position','ASC');
            $vo_Collection->setPageSize($this->getMaxSlide());
            $this->_slides = $vo_Collection;
        }

		return $this->_slides;
	}

	/**
	 * Retourne les slides à afficher
	 *
	 * @return array
	 */
	public function getSlides($group = NULL){
		return $this->getSlideCollection($group)->getItems();
	}

	/**
	 * Existance de slide
	 *
	 * @return vrai si le interaktingslider possede au moins un slide
	 */
	public function hasSlides(){
		return $this->getSlideCollection()->count();
	}

	/**
	 * Interaktingslider actif
	 *
	 * @return vrai si le interaktingslider est actif
	 */
	public function isVisible(){

		if(!Mage::getStoreConfig('interaktingslider/config/active')){
			return false;
		}

		if(!Mage::getStoreConfig('interaktingslider/config/show_no_slide')){
			if(!$this->hasSlides()){
				return false;
			}
		}

		return true;
	}

	/**
	 * Texte en absence de slide
	 *
	 * @return unknown
	 */
    public function getNoSlideText(){
    	return Mage::getStoreConfig('interaktingslider/config/no_slide_text');
    }

    /**
	 * Délai de transition
	 *
	 * @return unknown
	 */
	public function getDelay(){
		return Mage::getStoreConfig('interaktingslider/transition/delay');
	}

	/**
	 * Effet de Transition
	 *
	 * @return unknown
	 */
	public function getTransition(){
		return Mage::getStoreConfig('interaktingslider/transition/effect');
	}

	/**
     * Vitesse de défilement
     *
     * @return unknown
     */
	public function getSpeed(){
		return Mage::getStoreConfig('interaktingslider/transition/delayeffect');
	}

	/**
	 * Skin
	 *
	 * @return unknown
	 */
    public function getSkin(){
    	return Mage::getStoreConfig('interaktingslider/style/skin');

    }

     /**
     * Mode de transition
     *
     * @return unknown
     */
    public function getMode(){
    	return Mage::getStoreConfig('interaktingslider/config/mode');
    }

     /**
     * Nombre maximum de slide
     *
     * @return unknown
     */
    public function getMaxSlide(){
    	return Mage::getStoreConfig('interaktingslider/config/max_slide');
    }


}
