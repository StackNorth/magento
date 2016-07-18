<?php

class D1m_Sandpay_CreditpaymentController extends Mage_Core_Controller_Front_Action
{
	private $_order = null;
	

    public function getOrder()
    {
        if ($this->_order == null)
        {
            $increment_id = trim($this->getRequest()->getParam('credit_order_id'));
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('d1m_credits/order');
			
            if ($session->getLastRealCreditOrderId()){
                $this->_order->load($session->getLastRealCreditOrderId());
            } else{
                if (!empty($increment_id)){
                    $decodeOrderId = base64_decode($increment_id);
                    $this->_order->load($decodeOrderId);
                }
            }
        }        return $this->_order;
    }
    
    public function redirectAction()
    {
		$order = $this->getOrder();
		
		$_ordersTotals = $order->getGrandTotal();
		
		if ($_ordersTotals == 0) {                       
			$this->norouteAction();
			return;
		}
				
		$this->getResponse()
				->setBody($this->getLayout()
				->createBlock('sandpay/creditredirect')
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

        $version=$_POST['version'];
        if ($version=="") die($msg_fail);
        mage::log('credit notify action');
        $var=print_r($_POST,true);        Mage::log($var);

        $charset=$_POST['charset'];
        $trans_type=$_POST['trans_type'];
        $currency=$_POST['currency'];
        $resp_code =$_POST['resp_code'];
        $resp_msg =$_POST['resp_msg'];
        $resp_time =$_POST['resp_time'];
        $order_id =$_POST['order_id'];

        $prefix_order=$model->getConfigData('prefix_creditorder');
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
     //   $plainText="version=$version&charset=$charset&trans_type=$trans_type&resp_code=$resp_code&resp_msg=$resp_msg&resp_time=$resp_time&merchant_id=$merchant_id&order_id=".$_POST['order_id']."&order_amount=$order_amount&currency=$currency&merchant_attach=$merchant_attach";
        $load_result = $sand_plug->LoadKeyFile($merchant_id, $merPrivatePath, $sandPublicPath);
        if (!$load_result)  die($msg_fail);


        $realOrderNo=$order_id;

        $order 	   = Mage::getModel('d1m_credits/order')->load($realOrderNo);
        if(!$order || $order->getId() <= 0 )   die($msg_fail);

        $verify = $sand_plug->VerifySign($plainText, $sign);
        if (!$verify)   die($msg_fail);

        if ($resp_code!='100000')   die($msg_fail);

        $transno =$order_id ;


        if($order->getStatus() == D1m_Credits_Model_Order::STATE_NEW)
        {
            $order->setStatus(D1m_Credits_Model_Order::STATE_COMPLETE);
            $order->placedOrderCreditsToUser ( $order->getQty() + $order->getGiftCredits(),$order->getId());  //购买数量+ 赠送点数
            $cardNum=Mage::helper('sandpay')->getSandCardNum($merchant_attach);
            $order->setSandCardNumber($cardNum);
            $order->save();

            $message = Mage::helper('sandpay')->__('Payment accepted by Sandpay');
            Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
        }
        else
        {
            $message = '支付成功，但是订单状态已经改变，订单状态不再作更改处理';
            Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
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
        mage::log('credit success action');
        $var=print_r($_POST,true);        Mage::log($var);

        $charset=$_POST['charset'];
        $trans_type=$_POST['trans_type'];
        $currency=$_POST['currency'];
        $resp_code =$_POST['resp_code'];
        $resp_msg =$_POST['resp_msg'];
        $resp_time =$_POST['resp_time'];
        $order_id =$_POST['order_id'];
        $prefix_order=$model->getConfigData('prefix_creditorder');
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

        $plainText="version=$version&charset=$charset&trans_type=$trans_type&resp_code=$resp_code&resp_msg=$resp_msg&resp_time=$resp_time&merchant_id=$merchant_id&order_id=$order_id&order_amount=$order_amount&currency=$currency&merchant_attach=$merchant_attach";
        $load_result = $sand_plug->LoadKeyFile($merchant_id, $merPrivatePath, $sandPublicPath);
        if (!$load_result) { echo '加载密钥失败！'; exit;}



        $realOrderNo=$order_id;


        $order 	   = Mage::getModel('d1m_credits/order')->load($realOrderNo);
        if(!$order || $order->getId() <= 0 )
        {
            echo  '找不到对应的订单'; exit;
        }

        $verify = $sand_plug->VerifySign($plainText, $sign);
        if (!$verify)  {echo '验证密钥'; exit;}

        if ($resp_code!='100000')
        {
            echo '返回码非100000'; exit;
        }

        $transno =$order_id ;


       if($order->getStatus() == D1m_Credits_Model_Order::STATE_NEW)
       {
        	$message = Mage::helper('sandpay')->__('Payment accepted by Sandpay');
	    	$order->setStatus(D1m_Credits_Model_Order::STATE_COMPLETE);
            $order->placedOrderCreditsToUser ( $order->getQty() + $order->getGiftCredits(),$order->getId());  //购买数量+ 赠送点数
	        $order->save();
	        Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
        }
       else
       {
        	$message = '支付成功，但是订单状态已经改变，订单状态不再作更改处理';
        	Mage::log('$transno:'.$transno.'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
        }
       	$order->save();
   		$this->_redirect('credits/checkout/success');

	}
	


    public function errorAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $message = Mage::helper('sandpay')->__('There was an error occurred during paying process.');
		
        $transno  = $session->getLastRealCreditOrderId();
		$order 	   = Mage::getModel('d1m_credits/order')->load($transno);
		
        if($order)
        {
        	Mage::log('$transno:'.$order->getId().'; $message: '.$message, null, date('Ymd').'_credit_payemnt.log', $forceLog = true);
        }
        
        $this->loadLayout();
        $this->renderLayout();
        
        $session->unsLastRealOrderId();
    }
}
