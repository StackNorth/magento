<?php
class D1m_Credits_Model_Order extends Mage_Core_Model_Abstract 
{
     public $historyDesc = null;
     public $historyOrderNo = null;
     
     const STATE_NEW             = 'new';
     const STATE_PENDING_PAYMENT = 'pending_payment';
     const STATE_PROCESSING      = 'processing';
     const STATE_COMPLETE        = 'complete';
     const STATE_CLOSED          = 'closed';
     const STATE_CANCELED        = 'canceled';
     const STATE_HOLDED          = 'holded';
     const STATE_PAYMENT_REVIEW  = 'payment_review';
    
    protected function _construct()
    {
    	
        $this->_init('d1m_credits/order');
    }
    
    
    protected function _beforeSave()
    {
        parent::_beforeSave();
        
        if ($this->getCustomer()) {
            $this->setCustomerId($this->getCustomer()->getId());
        }
        
        
        $this->setData('protect_code', substr(md5(uniqid(mt_rand(), true) . ':' . microtime(true)), 5, 6));
        
        
    }
    
    public function placedOrderCreditsToUser($amount,$orderid=0)
    {

//        $date = Mage::app()->getLocale()->utcDate($store, $value, true, Varien_Date::DATETIME_INTERNAL_FORMAT);
//        $this->setData('date_start', $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));

        //$object->setCreatedAt($this->formatDate(time()));


    	//$this->setPaymentAt( Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));

        //modify by gao
        $this->setPaymentAt($this->getResource()->formatDate(time(),true));

	    $this->setTotalPaid($amount);//包括赠送的
    	
    	$orderCredit = $amount; //$this->qty;
    	if($orderCredit)
    	{
    		$customer_id = $this->customer_id;
            /* @var $credit D1m_Credits_Model_Credits */
    		$credit = Mage::getModel('d1m_credits/credits')->load($customer_id, 'customer_id');
    		if(!$credit || $credit->getId() <= 0)
    		{
    			$credit = Mage::getModel('d1m_credits/credits');
    			$credit->customer_id = $customer_id;
    			$credit->credit_amount = 0;
    		}
    		$credit->credit_amount = $credit->credit_amount + $orderCredit;
            if ($orderid!=0)
            {
                $credit->historyDesc ='购买课点';
                $credit->historyOrderNo=$orderid;
            }
    		$credit->save();
    	}
    	return $this;
    }
    
    
    public function getStatusLabel()
    {
    	return $this->status;
    }
    
    public function getRealOrderId()
    {
    	return $this->id;
    }
    
    public function initOrderData($data)
    {
    	
    	$this->status = D1m_Credits_Model_Order::STATE_NEW;
    	
    	if ($remoteAddr = Mage::helper('core/http')->getRemoteAddr()) {
            $this->setRemoteIp($remoteAddr);
            //$xForwardIp = Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR');
            //$this->setXForwardedFor($xForwardIp);
        }
        
        $this->qty = (int)$data['qty'];
        
        $this->payment_method = $data['payment_method'];
        
        $this->unit_price = Mage::helper('d1m_credits')->getFeeByCredit();
        //此参数忽略gao
        $this->free_credits_per_fixed_credits = 0; //(int)Mage::getStoreConfig('d1m_credits/general/free_credits_per_fixed_credits');

        //新优惠参数
        $this->creditsparam = Mage::getStoreConfig('d1m_credits/general/creditparam');
//        die($this->creditparam);
        $this->gift_credits = Mage::helper('d1m_credits')->getGiftCredits($this->qty);
        $this->gift_total   = $this->gift_credits * $this->unit_price;

        $this->grand_total  =  $this->qty  * $this->unit_price; //by gao
        // $this->grand_total  = ( $this->qty - $this->gift_credits ) * $this->unit_price;
        
        /**
        $this->firstname = $data['billing']['firstname'];
        $this->lastname = $data['billing']['lastname'];
        $this->email = $data['billing']['email'];
        $this->city = $data['billing']['city'];
        $this->company = $data['billing']['company'];
        $this->zipcode = $data['billing']['zipcode'];
        $this->telephone = $data['billing']['telephone'];
        $this->street_address = $data['billing']['street_address'];
        **/
        
    	return $this;
    }
    
    
    public function getPaymentPlacedUrl()
    {
    	if($this->payment_method)
    	{
    		switch($this->payment_method)
    		{
    			case 'chinapay_payment':
    				return Mage::getModel('chinapay/creditspayment')->getOrderPlaceRedirectUrl();
    				break;

                case 'sandpay_payment':
                    return Mage::getModel('sandpay/creditspayment')->getOrderPlaceRedirectUrl();
                    break;
    			
    			case 'alipay_payment':
    			default:
    				return Mage::getModel('alipay/payment')->getCreditOrderPlaceRedirectUrl();
    				break;
    		}
    			
    	}
    	
    	return '';
    	
    	
    }
    public function getCustomerCredits($customer_id,$startDate,$endDate)
    {
        $list= $this->_getResource()->getCustomerCredits($customer_id,$startDate,$endDate);
        $money=0;
        if(count($list)){
            foreach($list as $item){
                $money+=$item['financial_money'];
            }
        }
        return $money;
    }
}
