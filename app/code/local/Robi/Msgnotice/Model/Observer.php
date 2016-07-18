<?php

	class Robi_Msgnotice_Model_Observer extends Mage_Core_Model_Abstract {
		
		public function msgnoticeUponRegistration($observer){
                
              $customer = $observer->getEvent()->getCustomer();
              Mage::log('try to send a sms :'.$customer->getFphone());
              if($phone = $customer->getFphone())
              {
					$text   = '恭喜您注册成功，祝您购物愉快! ';
					$status = Mage::getModel('msgnotice/sms')->sendSMS($phone,$text,$addWebsiteName = true);
					Mage::log('send sms ok :'.$phone);
              }  
       }
       

       
       public function scheduledResendFailedActions($observer){
       	
       	Mage::log('scheduledResendFailedActions:start');
       	
       	$failedActions = Mage::getModel('msgnotice/failedaction')->getCollection()->addFieldToFilter('status', '0');
       	if($failedActions->count())
       	{
       		foreach($failedActions as $failedAction)
       		{
       			$status = Mage::helper('msgnotice')->resendFailedAction($failedAction);
				if($status)
					Mage::log('FailedAction:'.$failedAction->getId().' , 批量处理成功。');
       		}
       	}
       	
       	Mage::log('scheduledResendFailedActions:end');
       	
       }
       
       
}