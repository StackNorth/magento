<?php

class D1m_CouponRule_Model_Improvesalerule extends Mage_Core_Model_Abstract
{
	//the follow two const is for table "salesrule" field "just_for_original_price"
	//this filed is set for rule action is just for original price
	const AVAILABLE_ALL_PRICE=0;
	const AVAILABLE_ORIGINAL_PRICE=1;
	
	//the follow two const is for table "salesrule" field "just_for_original_price"
	//this filed is set for rule action is just for original price
	const CONDITION_AVAILABLE_ALL_PRICE=0;
	const CONDITION_AVAILABLE_ORIGINAL_PRICE=1;

	const YES=1;
	const NO=0;
	
	/*
	 * result for original price product
	 * key: product id
	 * value: boolean
	 */
	protected $_productResult=array();
	

	/*
	 * cache for product object by product id
	 * key: product id
	 * value: prduct object
	 */
	protected $productArray=array();
	
	/*
	 * cache for category object by category id
	 * key: category id
	 * value: category object
	 */
	protected $categoryArray=array();	
	
	/*
	 * cache category id for product
	 * key: product id
	 * value: category ids
	 */
	protected $_categoryIds=array();
	
	/*
	 * check product's price is original price
	 */
	public function isOriginalPrice( $productId ){
		if( !isset( $this->_productResult[$productId] ) ){
			$product=$this->getProductById($productId);

			$flag=true;
			if( $product ){
				if( $product->getPrice()!=$product->getFinalPrice() ){ //this product is discount product
		        	$flag= false;
		        }
			}
			$this->_productResult[$productId]=$flag;
		}
		return $this->_productResult[$productId];
	}

    public function getProductById( $productId ){
        if( !isset( $this->productArray[$productId] ) ){
            $this->productArray[$productId]=  Mage::getModel('catalog/product')->load( $productId );
        }

        return $this->productArray[$productId];
    }

    //@return category object
    public function getCategoryById( $categoryId ){
        if( !isset( $this->categoryArray[$categoryId] ) ){
            $this->categoryArray[$categoryId]=  Mage::getModel('catalog/category')->load( $categoryId );
        }

        return $this->categoryArray[$categoryId];
    }

    /*
     * get all category ids for a product
     */
    public function getCategoryIdsByProductId( $productId ){
        if( !isset( $this->_categoryIds[$productId] ) ){
            $this->_categoryIds[$productId]=$this->getProductById($productId)->getAvailableInCategories();
        }
        return $this->_categoryIds[$productId];
    }

    /*
     * get all original price products in order
     */
    public function originalPriceForOrder($order){
        $amount=0.0;
        foreach( $order->getAllVisibleItems() as $item ){
            if( $this->isOriginalPrice( $item->getData("product_id") )==true ){
//				echo $item->getData("name")." ".$item->getData("row_total")."<br/>";
                $amount+=$item->getData("row_total");
            }
        }
        return $amount;
    }
}
