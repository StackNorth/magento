<?php
class D1m_Credits_Helper_Data extends Mage_Core_Helper_Abstract
{	
    

	public function getCreditAmountByCustomerId($customer_id)
    {
    	return Mage::getModel('d1m_credits/credits')->getCustomerCredits($customer_id);
    }
    
    public function getFeeByCredit()
    {
        //从参数中取
          $creditunit = Mage::getStoreConfig('d1m_credits/general/creditunit');

        if ($creditunit<=0) $creditunit=280; //default 280
        return $creditunit;

    }
	
	public function getCreditStatuses()
	{
		
		$statuses = array();
		$statuses[D1m_Credits_Model_Order::STATE_NEW] = $this->__(D1m_Credits_Model_Order::STATE_NEW);
		$statuses[D1m_Credits_Model_Order::STATE_PENDING_PAYMENT] = $this->__(D1m_Credits_Model_Order::STATE_PENDING_PAYMENT);
		$statuses[D1m_Credits_Model_Order::STATE_CANCELED] = $this->__(D1m_Credits_Model_Order::STATE_CANCELED);
		$statuses[D1m_Credits_Model_Order::STATE_PAYMENT_REVIEW] = $this->__(D1m_Credits_Model_Order::STATE_PAYMENT_REVIEW);
		$statuses[D1m_Credits_Model_Order::STATE_COMPLETE] = $this->__(D1m_Credits_Model_Order::STATE_COMPLETE);
		$statuses[D1m_Credits_Model_Order::STATE_CLOSED] = $this->__(D1m_Credits_Model_Order::STATE_CLOSED);
		
		return $statuses;
	}
	
	public function getGiftCredits($qty)
    {
    	//$freeCreditPerFixCredits = (int)Mage::getStoreConfig('d1m_credits/general/free_credits_per_fixed_credits');
        $creditparam = Mage::getStoreConfig('d1m_credits/general/creditparam');
        $freeQty=0;
        if ($creditparam!="")
    	{
    		//$freeQty = floor( ($qty/10) * $freeCreditPerFixCredits );
            // 10/1,20/3,30/5
            $creditparam=str_replace('，',',',$creditparam);
            $creditparam=str_replace(' ','',$creditparam);
            $arr=explode(',',$creditparam);
            for ($i=0;$i<count($arr);$i++)
            {
                if ($arr[$i]=="") continue;
                $brr=explode('/',$arr[$i]);
                $j=(int) $brr[0];
                $k=(int) $brr[1];
                if (($qty>=$j) and ($freeQty<$k)) $freeQty=$k; //取最大的优惠
            }
            if ($freeQty<0) $freeQty=0;
    		return $freeQty;
    	}
    	else
    	{
    		return false;
    	}
    }
	
	
	
}
