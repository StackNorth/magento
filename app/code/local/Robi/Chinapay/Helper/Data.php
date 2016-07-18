<?php

class Robi_Chinapay_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getTransactionId($increment_id)
	{
		
		if(strlen($increment_id) == 16)
			return $increment_id;
		
		//20117 11 511109888
		
		$tmps = explode('-',$increment_id);
		if(count($tmps) == 2)
		{
			$vid = $tmps[0];
			$vno = (int)$tmps[1];
			
			if($vno<=0)
			{
				return $increment_id;
			}
			
			$newTranId = substr($vid, 0, 5).substr($vid, 7);
			if($vno >= 10)
			{
				$newTranId = $newTranId.$vno;
			}
			else
			{
				$newTranId = $newTranId.'0'.$vno;
			}
			
			return $newTranId;
		}
		
		return $increment_id;
		
	}
}
