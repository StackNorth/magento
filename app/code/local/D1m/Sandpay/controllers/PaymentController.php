<?php

class D1m_Sandpay_PaymentController extends Mage_Core_Controller_Front_Action
{
	private $_order = null;
	
    public function getOrder()
    {
        if ($this->_order == null)
        {
            $increment_id = trim($this->getRequest()->getParam('increment_id'));
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
			
            if ($session->getLastRealOrderId()){
                $this->_order->loadByIncrementId($session->getLastRealOrderId());
            } else{
                if (!empty($increment_id)){
                    $decodeOrderId = base64_decode($increment_id);
                    $this->_order->loadByIncrementId($decodeOrderId);
                }
            }
        }
        return $this->_order;
    }
    
    public function redirectAction()
    {
		$order = $this->getOrder();
		
		$_ordersTotals = $order->getGrandTotal();
		$_ordersGrandTotal = $order->getBaseGrandTotal();
		
		if ($_ordersTotals == 0 || $_ordersGrandTotal == 0) {                       
			$this->norouteAction();
			return;
		}
				
		$this->getResponse()
				->setBody($this->getLayout()
				->createBlock('sandpay/redirect')
				->setPayment($order)
				->toHtml());
			
    }

    public function notifyAction()
    {
        
        require_once Mage::getBaseDir()."/sandpay/SandPayUtil.php";
        
        $msg_fail='fail';
        $msg_success='success';

        $debugmodel = SD_USE_MODEL;
        $model  = Mage::getModel("sandpay/payment");

        $sand_plug = new SandPayUtil($debugmodel);
        $sandPublicPath =Mage::getBaseDir().DIRECTORY_SEPARATOR ."sandpay".DIRECTORY_SEPARATOR .$model->getConfigData('public_key');
        $merPrivatePath = Mage::getBaseDir().DIRECTORY_SEPARATOR."sandpay".DIRECTORY_SEPARATOR .$model->getConfigData('private_key');
        $merchant_id =$model->getConfigData('partner_id');; //"666001041310001";

        mage::log('notify action',7,'sandpay.log');
        $var=print_r($_POST,true);        Mage::log($var,7,'sandpay.log');

        $version=$_POST['version'];
        if ($version=="") die($msg_fail);
        $charset=$_POST['charset'];
        $trans_type=$_POST['trans_type'];
        $currency=$_POST['currency'];
        $resp_code =$_POST['resp_code'];
        $resp_msg =$_POST['resp_msg'];
        $resp_time =$_POST['resp_time'];
        $order_id =$_POST['order_id'];

        $prefix_order=$model->getConfigData('prefix_order');
        if ($prefix_order!="")
        {
            //去掉前面的字母
            if (substr($order_id,0,strlen($prefix_order))==$prefix_order)
                $order_id =substr($order_id,strlen($prefix_order));
        }
        $order_amount =$_POST['order_amount'];
        $merchant_attach =$_POST['merchant_attach'];
        $sign =$_POST['sign'];
        //$sign_type =$_POST['sign_type'];

        $plainText="version=$version&charset=$charset&trans_type=$trans_type&resp_code=$resp_code&resp_msg=$resp_msg&resp_time=$resp_time&merchant_id=$merchant_id&order_id=".$_POST['order_id']."&order_amount=$order_amount&currency=$currency&merchant_attach=$merchant_attach";
        $load_result = $sand_plug->LoadKeyFile($merchant_id, $merPrivatePath, $sandPublicPath);
        if (!$load_result) die($msg_fail); 

        $realOrderNo   = $order_id;
        $order 	   = Mage::getModel('sales/order')->loadByIncrementId($realOrderNo);
        if(!$order || $order->getId() <= 0 )
            die($msg_fail);
        $verify = $sand_plug->VerifySign($plainText, $sign);
        if (!$verify)  die($msg_fail);
        if ($resp_code!='100000') die($msg_fail);
        
        if($order->getStatus() == 'pending')
        {
            $message = Mage::helper('sandpay')->__('Payment accepted by Sandpay');
            $order->addStatusToHistory($model->getConfigData('order_status_payment_accepted'), $message);
            $cardNum=Mage::helper('sandpay')->getSandCardNum($merchant_attach);
            $order->setSandCardNumber($cardNum);
            if($this->saveInvoice($order))
            {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
            }
            Mage::helper('robi_checkout')->sendOrderSuccessNotice($order);
            Mage::dispatchEvent('payment_accept_notify', array('order' => $order));
        }
        else
        {
            $message = '支付成功，但是订单状态已经改变，订单状态不再作更改处理';
            $order->addStatusHistoryComment($message);
        }
        $order->save();
        die($msg_success);

    }


    /**
     *  Success payment page
     *
     *  @param    none
     *  @return	  void
     */
    public function successAction()
    {

        require_once Mage::getBaseDir()."/sandpay/SandPayUtil.php";

        $debugmodel = SD_USE_MODEL;
        $model  = Mage::getModel("sandpay/payment");

        $sand_plug = new SandPayUtil($debugmodel);
        $sandPublicPath =Mage::getBaseDir().DIRECTORY_SEPARATOR ."sandpay".DIRECTORY_SEPARATOR .$model->getConfigData('public_key');
        $merPrivatePath = Mage::getBaseDir().DIRECTORY_SEPARATOR."sandpay".DIRECTORY_SEPARATOR .$model->getConfigData('private_key');
        $merchant_id =$model->getConfigData('partner_id');; //"666001041310001";

        $version=$_POST['version'];
        if ($version=="") exit;
        mage::log('success action');
        $var=print_r($_POST,true);        Mage::log($var);
        
        $charset=$_POST['charset'];
        $trans_type=$_POST['trans_type'];
        $currency=$_POST['currency'];
        $resp_code =$_POST['resp_code'];
        $resp_msg =$_POST['resp_msg'];
        $resp_time =$_POST['resp_time'];
        $order_id =$_POST['order_id'];
        $prefix_order=$model->getConfigData('prefix_order');
        if ($prefix_order!="")
        {
            //去掉前面的字母
            if (substr($order_id,0,strlen($prefix_order))==$prefix_order)
                $order_id =substr($order_id,strlen($prefix_order));
        }

        $order_amount =$_POST['order_amount'];
        $merchant_attach =$_POST['merchant_attach'];
        $sign =$_POST['sign'];
        // $sign_type =$_POST['sign_type'];

        $plainText="version=$version&charset=$charset&trans_type=$trans_type&resp_code=$resp_code&resp_msg=$resp_msg&resp_time=$resp_time&merchant_id=$merchant_id&order_id=".$_POST['order_id']."&order_amount=$order_amount&currency=$currency&merchant_attach=$merchant_attach";
        $load_result = $sand_plug->LoadKeyFile($merchant_id, $merPrivatePath, $sandPublicPath);
        if (!$load_result) { echo '加载密钥失败！'; exit;}

        $realOrderNo   = $order_id;
        $order 	   = Mage::getModel('sales/order')->loadByIncrementId($realOrderNo);
        if(!$order || $order->getId() <= 0 )
        {
            echo "无效订单号";
            exit;
        }
         $verify = $sand_plug->VerifySign($plainText, $sign);
        if (!$verify)  {echo '验证密钥'; exit;}

        if ($resp_code!='100000')
        {
            echo '返回码非100000'; exit;
        }

      	if($order->getStatus() == 'pending')
       	{
        		$message = Mage::helper('sandpay')->__('Payment accepted by Sandpay');
	    		$order->addStatusToHistory($model->getConfigData('order_status_payment_accepted'), $message);
	            if($this->saveInvoice($order))
	            {
	                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
	            }
	            
	            Mage::helper('robi_checkout')->sendOrderSuccessNotice($order);
       	}
       	else
       	{
        		$message = '支付成功，但是订单状态已经改变，订单状态不再作更改处理';
				$order->addStatusHistoryComment($message);
       	}
       	$order->save();
   		$this->_redirect('checkout/onepage/success');
        
	}
	
	/**
     * Save invoice for order
     *
     * @param    Mage_Sales_Model_Order $order
     * @return	  boolean Can save invoice or not
     */
    protected function saveInvoice(Mage_Sales_Model_Order $order)
    {
        if ($order->canInvoice()) {
            /** @var Mage_Sales_Model_Convert_Order $convertor */
            $convertor = Mage::getModel('sales/convert_order');
            $invoice = $convertor->toInvoice($order);
            foreach ($order->getAllItems() as $orderItem) {
                if (! $orderItem->getQtyToInvoice()) {
                    continue;
                }
                $item = $convertor->itemToInvoiceItem($orderItem);
                $item->setQty($orderItem->getQtyToInvoice());
                $invoice->addItem($item);
            }
            $invoice->collectTotals();
            $invoice->register()->capture();
            Mage::getModel('core/resource_transaction')->addObject($invoice)
                ->addObject($invoice->getOrder())
                ->save();
            return true;
        }

        return false;
    }
	

    public function errorAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $message = Mage::helper('sandpay')->__('There was an error occurred during paying process.');
		
        $transno  = $session->getLastRealOrderId();
		$order 	   = Mage::getModel('sales/order')->loadByIncrementId($transno);
        
        if($order && $order->getId() )
		{
			$order->addStatusHistoryComment($message);
			$order->save();
		}

        $this->loadLayout();
        $this->renderLayout();
        
        $session->unsLastRealOrderId();
    }
}
