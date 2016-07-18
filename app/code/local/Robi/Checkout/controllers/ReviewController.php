<?php

class Robi_Checkout_ReviewController extends Mage_Core_Controller_Front_Action
{
	
    public function preDispatch()
    {
        parent::preDispatch();
        return $this;
    }
	

	
	/**
     * Load product model with data by passed id.
     * Return false if product was not loaded or has incorrect status.
     *
     * @param int $productId
     * @return bool|Mage_Catalog_Model_Product
     */
    protected function _loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
        

        Mage::register('current_product', $product);
        Mage::register('product', $product);

        return $product;
    }
    
    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }
    
    
    public function getCustomer()
    {
    	if (!$this->_getCustomerSession()->isLoggedIn()) {
    		return null;	
    	}
    	
    	return $this->_getCustomerSession()->getCustomer();
    }
    
    
    /**
     * Submit new review action
     *
     */
    public function postbyajaxAction()
    {
        
        $order_item_id = (int) $this->getRequest()->getParam('iid');
        $rate = (int) $this->getRequest()->getParam('rate');
        
        if(!in_array($rate,array(1,2,3,4,5)))
        {
        	$result = array('status'=>false,'msg'=>Mage::helper('customer')->__('param error,please try again.'));
        	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        	return;
        }
        
        $_customer = $this->getCustomer();
        
        if(is_null($_customer))
        {
        	$result = array('status'=>false,'msg'=>Mage::helper('customer')->__('please log in at first.'));
        	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        	return;
        }
        else
        {
        	$orderItem = Mage::getModel('sales/order_item')->load($order_item_id);
        	
        	if(!$orderItem || $orderItem->getId() <= 0)
        	{
        		$result = array('status'=>false,'msg'=>Mage::helper('customer')->__('param error,please try again.'));
	        	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	        	return;
        	}
        	
        	if($orderItem->getIsReviewed())
        	{
        		$result = array('status'=>false,'msg'=>Mage::helper('customer')->__('the order is reviewed.'));
	        	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	        	return;
        	}
        	
        	$order_id = $orderItem->getOrderId();
        	$order = Mage::getModel('sales/order')->load($order_id);
        	if(!$order || $order->getId() <= 0 )
        	{
        		$result = array('status'=>false,'msg'=>Mage::helper('customer')->__('param error,please try again.'));
	        	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	        	return;
        	}
        	
        	if($order->getCustomerId() != $_customer->getId())
        	{
        		$result = array('status'=>false,'msg'=>Mage::helper('customer')->__('param error,please try again.'));
	        	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
	        	return;
        	}
        	
        	$data = array();
        	$data['nickname'] = $_customer->getUsername();
        	$data['title'] = 'quality';
        	$data['detail'] = 'quality';
        	
        	$rating = array();
        	$rating['1'] = $rate;
        	
        	$storeIds = array();
        	$stores = Mage::app()->getStores();
        	foreach($stores as $storeid => $store)
        	{
        		$storeIds[] = $storeid;
        	}
	
	        if (($product = $this->_loadProduct($orderItem->getProductId())) && !empty($data)) {
	            $session    = Mage::getSingleton('core/session');
	            /* @var $session Mage_Core_Model_Session */
	            $review     = Mage::getModel('review/review')->setData($data);
	            /* @var $review Mage_Review_Model_Review */
	
	            $validate = $review->validate();
	            if ($validate === true) {
	                try {
	                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
	                        ->setEntityPkValue($product->getId())
	                        ->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
	                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
	                        ->setStoreId(Mage::app()->getStore()->getId())
	                        ->setStores($storeIds)
	                        ->save();
	
	                    foreach ($rating as $ratingId => $optionId) {
	                        Mage::getModel('rating/rating')
	                        ->setRatingId($ratingId)
	                        ->setReviewId($review->getId())
	                        ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
	                        ->addOptionVote($optionId, $product->getId());
	                    }
	
	                    $review->aggregate();
	                    
	                    $orderItem->is_reviewed = 1;
	                    $orderItem->save();
	                    
	                    $msg = $this->__('Your review has been accepted for moderation.');
	                    $result = array('status'=>true,'msg'=>$msg);
	                }
	                catch (Exception $e) {
	                    $session->setFormData($data);
	                    $msg = $this->__('Unable to post the review.');
	                    $result = array('status'=>false,'msg'=>$msg);
	                }
	            }
	            else {
	                    $msg = $this->__('Unable to post the review.');
	                    $result = array('status'=>false,'msg'=>$msg);
	            }
	        }
        	
        	$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        	return;
        	
        }
        
        

        
    }
    
	
}


?>
