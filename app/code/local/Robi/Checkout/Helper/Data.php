<?php
class Robi_Checkout_Helper_Data extends Mage_Core_Helper_Abstract
{	
	public function getDefaultPaymentMethod()
	{
		return 'chinapay_payment';
	}
	
	public function isHomepage($mark=1)
	{
		$routeName = Mage::app()->getRequest()->getRouteName();
		$identifier = Mage::getSingleton('cms/page')->getIdentifier();
		if ($routeName == 'cms' && $identifier == 'home') 
		{
			$url = '';
		}
		else
		{
			$url = "location.href='".Mage::getBaseUrl()."#".$mark."'";
		}
		return $url;
	}
	
	public function sendOrderSuccessNotice($order)
	{
		$customer_id = $order->getCustomerId();
		$customer = Mage::getModel('customer/customer')->load($customer_id);

        $customerApi = Mage::getModel('customapi/orderservice');
        $customerApi->addClassOrder($order);

		if($customer)
		{
			$phone = $customer->getPhone();
			foreach ($order->getAllItems() as $orderItem) {
				
				$product = Mage::getModel('catalog/product')->setStoreId($orderItem->getStoreId())->load($orderItem->getProductId());
				$persons = $orderItem->getQtyOrdered();
				if($product)
				{
					$classname = $product->getName();
                    //gao
                    $time = $product->getData('n_classtime1').'-'.$product->getData('n_classtime2');


					$classplace = $product->getClassAddress();
					$classdate = substr($product->getClassDate(),0,10).' '.$time;
					Mage::helper('robi_checkout/msg')->sendOrderMsg($phone, $classname, $classdate, $classplace, $persons);
				}
			}
		}
	}
	
	
	public function getDefaultAddressArray($street=false)
    {
    	$resultdata = array();
		$resultdata['save_in_address_book']	= 0;
		$resultdata['city']		= 'unknown';
		$resultdata['district'] 	= 'unknown';
		$resultdata['country_id'] = 'CN';
		$resultdata['customer_address_id'] = '';
		$resultdata['firstname'] 	= 'unknown';
		$resultdata['lastname'] 	= 'unknown';
		$resultdata['postcode']	= '201103';
		$resultdata['telephone']	= 'unknown';
		$resultdata['region']	 	= 'unknown';
		$resultdata['region_id']	= '487';
		if($street)
			$resultdata['street']	= 'unknown';
		else
			$resultdata['street']	= array('unknown');
		$resultdata['email']		= 'unknown@unknown.com';
    	return $resultdata;
    }
	
}
