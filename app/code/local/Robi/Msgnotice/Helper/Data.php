<?php

class Robi_Msgnotice_Helper_Data extends Mage_Core_Helper_Abstract
{
   
   public function saveFailedActions($actionName,$fulldata, $returndata, $phone='', $trytimes=1, $status=0)
   {
   		try
   		{
   			$model = Mage::getModel('msgnotice/failedaction');
	   		$model->setData('actionname',$actionName);
	   		$model->setData('fulldata',$fulldata);
	   		$model->setData('returndata',$returndata.' | noticephone:'.$phone);
	   		$model->setData('trytimes',$trytimes);
	   		$model->setData('status',$status);
	   		$model->save();
	   		
	   		$phone = ($phone == '') ? '18616509901' : $phone;

            //关闭发短信
	   		// $status = Mage::Helper('robi_checkout/msg')->sendMsg('18616509901','失败ID:'.$model->getId().'|返回:'.$returndata,$addWebsiteName = false);
   			
   			return true;
   			
   		}catch(Exception $e)
   		{
   			Mage::logException($e);
   		}
   		
   		return false;
   }
   

   
   public function resendFailedAction($failedaction)
   {
   	
   		$actionName = $failedaction->getActionname();
   		$client  = Mage::helper('customapi')->getSoapClient();
   		
   		$resultData = false;
   		
   		try
   		{
   		
	   		switch($actionName)
	   		{
	   			case 'CreateOrder':
	   				$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				
	   				var_dump($fulldata);
	   				
	   				$orderInfo = $fulldata['orderInfo'];
	   				$paymentInfo = $fulldata['paymentInfo'];
	   				$shippingInfo = $fulldata['shippinginfo'];
	   				$invoiceInfo = $fulldata['invoiceInfo'];
	   				$orderItems = $fulldata['orderItems'];
	   				$resultData = $client->CreateOrder($orderInfo, $paymentInfo, $shippingInfo, $invoiceInfo, $orderItems);
					if($resultData)
					{
						$increment_id = isset($orderInfo['increment_id']) ? $orderInfo['increment_id'] : 0;
						if($increment_id)
						{
							$order = Mage::getModel('sales/order')->loadByIncrementId($increment_id);
							if($order)
							{
								$order->setData('email_sent','1')->save();
							}
						}
						
						$failedaction->delete();
						return true;
					}
					else
						$failedaction->setReturndata(serialize($resultData))->setTrytimes( $failedaction->getTrytimes() + 1 )->save();
	   				break;
	   			case 'ModifyOrder':
	   				$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				
	   				var_dump($fulldata);
	   				
	   				$orderInfo = $fulldata['orderInfo'];
	   				$paymentInfo = $fulldata['paymentInfo'];
	   				$shippingInfo = $fulldata['shippinginfo'];
	   				$invoiceInfo = $fulldata['invoiceInfo'];
	   				//$orderItems = $fulldata['orderItems'];
	   				$resultData = $client->ModifyOrder($orderInfo, $paymentInfo, $shippingInfo, $invoiceInfo);
					if($resultData)
					{
						$failedaction->delete();
						return true;
					}
					else
						$failedaction->setReturndata(serialize($resultData))->setTrytimes( $failedaction->getTrytimes() + 1 )->save();
	   				break;
	   			
	   			case 'ConfirmOfflinePayment':
	   				$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				
	   				var_dump($fulldata);
	   				
	   				$increment_id = $fulldata['increment_id'];
	   				$data = $fulldata['data'];
	   				
	   				$resultData = $client->ConfirmOfflinePayment($increment_id, $data);
					break;
				
				case 'CreateCustomer':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->CreateCustomer($fulldata);
					break;
				
				case 'DeleteCustomer':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$customer_id = $fulldata['customer_id'];
	   				$resultData = $client->disableCustomer($customer_id);
					break;
				
				case 'notifyOnlinePaymentFailure':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$increment_id = $fulldata['increment_id'];
	   				$remark = $fulldata['remark'];
	   				$resultData = $client->notifyOnlinePaymentFailure($increment_id, $remark);
					break;
				
				case 'createArriveNotice':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->createArriveNotice($fulldata);
					break;
				
				case 'UpdateCustomer':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->UpdateCustomer($fulldata);
					break;
				
				case 'UpdateMobileAndEmail':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->UpdateMobileAndEmail($fulldata);
					break;
				
				case 'UpdateAddress':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->UpdateAddress($fulldata);
					break;
				
				case 'removeAddress':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				$address_id = $fulldata['address_id'];
	   				var_dump($fulldata);
	   				$resultData = $client->removeAddress($address_id);
					break;
				
				case 'removeInvoice':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$invoice_id = $fulldata['invoice_id'];
	   				$resultData = $client->removeInvoice($invoice_id);
					break;
				
				case 'customerUpgrade':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$customer_id = $fulldata['customer_id'];
	   				$group_id = $fulldata['group_id'];
	   				$resultData = $client->customerUpgrade($customer_id, $group_id);;
					break;
				
				case 'CreateAddress':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->CreateAddress($fulldata);
					break;
				
				case 'CreateInvoice':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->CreateInvoice($fulldata);
					break;
				
				case 'UpdateInvoice':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->UpdateInvoice($fulldata);
					break;
				
				case 'CreateWilling':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->CreateWilling($fulldata);
					break;
				
				case 'UpdateWilling':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->UpdateWilling($fulldata);
					break;
				
				case 'CreateComment':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->CreateComment($fulldata);
					break;
				
				case 'UpdateComment':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->UpdateComment($fulldata);
					break;
				
				case 'CreateSuggestion':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->CreateSuggestion($fulldata);
					break;
				
				case 'UpdateSuggestion':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->UpdateSuggestion($fulldata);
					break;
				
				case 'CreatePriceReport':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->CreatePriceReport($fulldata);
					break;
				
				case 'UpdatePriceReport':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->UpdatePriceReport($fulldata);
					break;
				
				case 'CreateCashapply':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->CreateCashapply($fulldata);
					break;
				
				case 'UpdateCashapply':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->UpdateCashapply($fulldata);
					break;
				
				case 'CreateReturnapply':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->CreateReturnapply($fulldata);
					break;
				
				case 'UpdateReturnapply':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->UpdateReturnapply($fulldata);
					break;
				
				case 'CreateInvitation':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->CreateInvitation($fulldata);
					break;
					
				case 'CreateInvitationByBatch':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->CreateInvitationByBatch($fulldata);
					break;				    
				
				case 'UpdateInvitation':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->UpdateInvitation($fulldata);
					break;
				
				case 'createQuestion':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$resultData = $client->createQuestion($fulldata);
					break;
				
				case 'subscribeNewsletter':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$email = $fulldata['email'];
	   				$createdAt = $fulldata['createdAt'];
	   				$resultData = $client->subscribeNewsletter($email, $createdAt);
					break;
						
				case 'subscribeNotification':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$customerId = $fulldata['customerId'];
	   				$str = $fulldata['data'];
	   				$resultData = $client->subscribeNotification($customerId, $str);
					break;
						
				case 'AddItemToOrder':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$incrementId = $fulldata['increment_id'];
	   				$data = $fulldata['data'];
	   				$resultData = $client->AddItemToOrder($incrementId, $data);
					break;
						
				case 'BindCustomerToOrder':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$increment_id = $fulldata['increment_id'];
	   				$customer_id = $fulldata['customer_id'];
	   				$resultData = $client->BindCustomerToOrder($increment_id, $customer_id);
					break;
						
				case 'CancelOrder':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$increment_id = $fulldata['increment_id'];
	   				$resultData = $client->CancelOrder($increment_id);
					break;
						
				case 'ConfirmOnlinePayment':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$increment_id   = $fulldata['increment_id'];
	   				$payment_method = isset($fulldata['payment_method']) ? $fulldata['payment_method'] : '';
	   				$payment_info   = isset($fulldata['payment_info']) ? $fulldata['payment_info'] : array();
	   				$resultData = $client->ConfirmOnlinePayment($increment_id, $payment_method, serialize($payment_info));
					break;
						
				case 'ConfirmOfflinePayment':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$increment_id = $fulldata['increment_id'];
	   				$data = $fulldata['data'];
	   				$resultData = $client->ConfirmOfflinePayment($increment_id, $data);
					break;
						
				case 'recommend':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$customerId = $fulldata['customerId'];
	   				$str = $fulldata['data'];
	   				$resultData = $client->recommend($customerId, $str);
					break;
						
				case 'customerNewsletter':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$customerId = $fulldata['customerId'];
	   				$str = $fulldata['data'];
	   				$resultData = $client->customerNewsletter($customerId, $str);
					break;
						
				case 'addtoBlacklist':
					$fulldata = $failedaction->getFulldata();
	   				$fulldata = unserialize($fulldata);
	   				var_dump($fulldata);
	   				$customerId = $fulldata['customerId'];
	   				$data		= $fulldata['data'];
	   				$resultData = $client->addtoBlacklist($customerId, $data);
					break;
						
	   		}
	   		
	   		
	   		if($resultData)
			{
				$failedaction->setReturndata(serialize($resultData))->setStatus(1)->save();
				return true;
			}
			else
			{
				echo '--------------------------------------'."<br/>";
				echo $failedaction->getId()."<br/>";
				echo $actionName."<br/>";
				echo '返回数据'."<br/>";
				echo '------'."<br/>";
				var_dump($resultData);
				echo "<br/>".'------'."<br/>";
				$failedTimes = $failedaction->getTrytimes() + 1;
				echo '失败记录的ID:'.$failedaction->getId().' ||||  失败次数：'.$failedTimes;
				echo "<br/>".'--------------------------------------'."<br/>";
				$failedaction->setReturndata(serialize($resultData))->setTrytimes( $failedaction->getTrytimes() + 1 )->save();
				
			}
		
		} catch (SoapFault $fault){
			
			//Mage::log('FailedId:'.$failedaction->getId().'失败次数：'.$failedaction->getTrytimes() + 1);
			$failedaction->setReturndata(serialize($resultData))->setTrytimes( $failedaction->getTrytimes() + 1 )->save();
			
		}
	   		
   		return false;
   }

}
