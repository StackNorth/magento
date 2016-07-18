<?php
/**
 * Robi_Settleaccount_Model
 */
class Robi_Settleaccount_Model_Quote_Total_Credit extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    
     public function __construct()
    {
        $this->setCode('credit');
    }
    
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
    	parent::collect($address);
    	
    	$this->_setAmount(0)
            ->_setBaseAmount(0);
            
         $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }
    	
        $quote = $address->getQuote();
        $store = $address->getQuote()->getStore();
        
        $address->setCreditAmount(0);
		$address->setBaseCreditAmount(0);
		
		//Mage::helper('rewardpoints/event')->setPrepareUsedCredit(0);
		
		$credit_apply = (float)Mage::helper('settleaccount/event')->getPrepareUsedCredit();
		
		$credit_apply = $credit_apply * Mage::helper('d1m_credits')->getFeeByCredit();
		
		//Mage::log('current order $Credit_apply:'.$Credit_apply,null, date('Ymd').'_order_totals.log',true);
		
		if($credit_apply <= 0 )
			return $this;
		
        $this->_quote  = $address->getQuote();
        $this->_address = $address;
        $customer     = $this->_quote->getCustomer();
        $customerId   = $customer->getId();
        
		$amount = 0;
		
		$CreditTotal = Mage::helper('settleaccount/credits')->getCreditsByCustomer();
		
        if ($credit_apply > 0 && $CreditTotal > 0 && $customerId != null){
            
            
            if ($address->getAllBaseTotalAmounts()) {
	            $baseTotal = array_sum($address->getAllBaseTotalAmounts());
	        } else {
	            $baseTotal = $address->getBaseGrandTotal();
	        }
	        
            
            $max_Credit 	= $this->checkMaxCreditToApply($credit_apply, $this->_quote);
            $qBaseCredit   = $this->_quote->getBaseCredit();
            
            //echo '$max_Credit:'.$max_Credit.'<br/>';
            //echo '$credit_apply:'.$credit_apply.'<br/>';
            //echo '$qBaseCredit:'.$qBaseCredit.'<br/>';
            //exit();
            
            if ($credit_apply > $max_Credit){
                $credit_apply  = $max_Credit;
                $credit_apply  = min($credit_apply, $CreditTotal);
                
                $creditUsed = floor( $credit_apply / Mage::helper('d1m_credits')->getFeeByCredit() );
                
                $credit_apply = $creditUsed * Mage::helper('d1m_credits')->getFeeByCredit();
                
                Mage::helper('settleaccount/event')->setPrepareUsedCredit($creditUsed);
            }
            
            
            //echo '$credit_apply:'.$credit_apply.'<br/>';
            
            $bbLeft 	= $credit_apply - $qBaseCredit;
            $address_applied = min($bbLeft, $baseTotal);
            
            $amount = $address_applied;
            
            //echo date('Y-m-d H:i:s').' : '.$amount.' : '.count($items).'<br/>';
            
         }
         
         
         //echo '$amount'.$amount;exit();

 		if ( $amount > 0 ) {
			
			$address->addTotalAmount($this->getCode(),-$amount);
        	$address->addBaseTotalAmount($this->getCode(), -$amount);
        	$this->_quote->setBaseCredit( $this->_quote->getBaseCredit() + $amount );
		}
        
        return $this;
    }
    
     public function checkMaxCreditToApply($Credit, $quote){
        
        
        $allTotalFromAllAddresses = 0;
        $addresses = $quote->getAllAddresses();
        foreach($addresses as $_address)
        {
        	$totalPrices   = $_address->getTotals();
        	
        	$subtotalPrice = $totalPrices['subtotal'];
	        $order_details = $subtotalPrice->getData('value');
			
			$discountPrice  = isset($totalPrices['discount']) ? $totalPrices['discount'] : null;
			$order_discount = $discountPrice ? $discountPrice->getData('value') : 0;
			
			$rewardpoints = isset($totalPrices['rewardpoints']) ? $totalPrices['rewardpoints'] : null;
			$order_points = $rewardpoints ? $rewardpoints->getData('value') : 0;
			
			$order_details = $order_details + $order_discount + $order_points  ;
        	
        	$allTotalFromAllAddresses = $order_details + $allTotalFromAllAddresses;
        	
        }
        
        $maxpoints = min($allTotalFromAllAddresses, $Credit);
		$maxpoints = round($maxpoints,2);
		
        return $maxpoints;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
         $amount = $address->getCreditAmount();
         
         if($amount != 0)
         {
         	$title = Mage::helper('settleaccount')->__('Credits');
            $address->addTotal(array(
                'code'=>  $this->getCode(),
                'title'=> $title,
                'value'=> $amount
            ));
         }
        
        return $this;
    }

}
