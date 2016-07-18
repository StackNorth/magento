<?php

class Robi_Checkout_Block_Available extends Mage_Catalog_Block_Product_Abstract
{
    /**
     * Default value for products count that will be shown
     */
    const DEFAULT_PRODUCTS_COUNT = 10;

    /**
     * Products count
     *
     * @var null
     */
    protected $_productsCount;

    /**
     * Initialize block's cache
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addColumnCountLayoutDepend('empty', 6)
            ->addColumnCountLayoutDepend('one_column', 5)
            ->addColumnCountLayoutDepend('two_columns_left', 4)
            ->addColumnCountLayoutDepend('two_columns_right', 4)
            ->addColumnCountLayoutDepend('three_columns', 3);

        $this->addData(array('cache_lifetime' => 86400));
        $this->addCacheTag(Mage_Catalog_Model_Product::CACHE_TAG);
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
           'CATALOG_PRODUCT_NEW',
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate(),
           $this->getProductsCount()
        );
    }

    /**
     * Prepare and return product collection
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection|Object|Varien_Data_Collection
     */
    protected function _getProductCollection()
    {
        $todayStartOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('00:00:00')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        $todayEndOfDayDate  = Mage::app()->getLocale()->date()
            ->setTime('23:59:59')
            ->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        //$collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());


        $collection = $this->_addProductAttributesAndPrices($collection)
        //添加课程地址 by gao
            ->addAttributeToSelect('province')
            ->addAttributeToSelect('class_address')
            //->addStoreFilter()
            ->addAttributeToSort('class_date', 'desc')
            //moreq
            ->addFieldToFilter('class_date', array("gteq"=>$todayStartOfDayDate))
            ->setPageSize($this->getProductsCount())
            ->setCurPage(1)
        ;
        
        //echo $collection->getSelect();        die();

        return $collection;
    }

    /**
     * Prepare collection with new products
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        $this->setProductCollection($this->_getProductCollection());
        return parent::_beforeToHtml();
    }
    
    public function getRatingSummary($product_id){
    	$reviewSummary =  Mage::getModel('rating/rating')->getEntitySummary($product_id);
    	$sum = $reviewSummary->getData('sum');
    	$count = $reviewSummary->getData('count');
    	
    	if($count)
    	{
    		return floor(($sum/20)/$count);
    	}
    	else
    	{
    		return 0;
    	}
    }

    /**
     * Set how much product should be displayed at once.
     *
     * @param $count
     * @return Mage_Catalog_Block_Product_New
     */
    public function setProductsCount($count)
    {
        $this->_productsCount = $count;
        return $this;
    }

    /**
     * Get how much products should be displayed at once.
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (null === $this->_productsCount) {
            $this->_productsCount = self::DEFAULT_PRODUCTS_COUNT;
        }
        return $this->_productsCount;
    }
}
