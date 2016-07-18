<?php
/**
 * Class D1m_WeChat_PaymentController
 */
class D1m_WeChat_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     *
     * @var  $_order D1m_Sales_Model_Order
     */
    protected $_order;

    /**
     *
     * @var  $_notifyDebug D1m_WeChat_Model_Payment_Debug_Notify
     */
    protected $_notifyDebug;

    /**
     *
     * @return  Mage_Sales_Model_Order
     */
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

    /**
     *
     *  获得提示信息的 instance
     *
     * @return D1m_Message_Model_SMS_PromptMessage|null
     */
    private  function _getMessageInstance()
    {
        return D1m_WeChat_Model_PromptMessage::getInstance();
    }

    /****
     *
     *   微信扫码之后成功之后的跳转以及输出
     *
     */
    public function checkQrCodePayAction()
    {
        /* @var $session Mage_Customer_Model_Session */
        $session = Mage::getSingleton('customer/session');

        /* @var $messageInstance D1m_Common_Model_Message */
        $messageInstance = Mage::getModel('d1m_common/message');

        /* @var $payment D1m_WeChat_Model_Payment */
        $payment        = Mage::getModel('weChat/payment');

        $order = $this->getOrder();

        if (!$session->getId() || !$session->getCustomerId())
        {
            return $messageInstance->setMessage(false,'请重新登陆')
                    ->setKeyValue('redirect_url',Mage::getUrl('customer/account/login'))->getResultToJson();
        }

        if (!$order || !$order->getId())
        {
            return $messageInstance->setMessage(false,'找不到订单')
                    ->setKeyValue('redirect_url',Mage::getUrl('checkout/cart/index'))->getResultToJson();
        }

        if ($session->getCustomerId() != $order->getCustomerId())
        {
            return $messageInstance->setMessage(false,'信息失效,请重新支付')
                    ->setKeyValue('redirect_url',Mage::getUrl('sales/order/view',array('order_id'=>$order->getId())))->getResultToJson();
        }

        if ($order->getStatus() == $payment->getConfigData('order_status_payment_accepted'))
        {
            return $messageInstance->setMessage(true,'支付成功')
                    ->setKeyValue('redirect_url',Mage::getUrl('checkout/onepage/success'))->getResultToJson();
        }
    }

    /**
     * 微信支付的页面
     *
     */
    public function redirectAction()
    {
        $session = Mage::getSingleton('checkout/session');

        $session->setWeChatPaymentQuoteId($session->getQuoteId());

        //1) 获得订单
        if ($incrementId = $this->getRequest()->getParam('increment_id'))
        {
            $decodeOrderId = base64_decode($incrementId);
            $session->setLastRealOrderId($decodeOrderId);
        }

        if (!$this->getOrder() || !$this->getOrder()->getId())
        {
            die('订单不存在');
        }

        /* @var $paymentModel D1m_WeChat_Model_Payment */
        $paymentModel =  $this->getOrder()->getPayment()->getMethodInstance();

        if (!$paymentModel instanceof D1m_WeChat_Model_Payment)
        {
            $this->_getMessageInstance()->setMessage('此订单非微信支付付款,请联系客服修改成微信支付方式!');
        }

        //2）扫码支付
        if (!$paymentModel->enableJsApiPay())
        {
            /* @var  $redirectBlock D1m_WeChat_Block_Payment_QrCode */
            $redirectBlock = $this->getLayout()->createBlock('weChat/payment_qrCode');

        //3） JS API
        }else{
            $openid= Mage::getModel('weChat/weChat')->getOpenId(1);
            if(empty($openid)){
                 $loginWeixinUrl=Mage::helper('D1m_WeixinUser')->getArvatoWechatUrl();
                 $this->getResponse()->setRedirect($loginWeixinUrl);
                 return;
            }

            /* @var  $redirectBlock D1m_WeChat_Block_Payment_JsApi */
            $redirectBlock = $this->getLayout()->createBlock('weChat/payment_jsApi');

            try{
                $paymentModel->enableJsApiPay();
            }catch (Mage_Core_Exception $e)
            {
                $this->_getMessageInstance()->setMessage($e->getMessage());
            }
        }

         $redirectBlock->setPayment($paymentModel);

         $this->getResponse()->setBody($redirectBlock->toHtml());

        $session->unsQuoteId();
        $session->unsRedirectUrl();
    }



    /**
     *  即时查询 微信支付的订单状态
     */
    public function queryOrderAction()
    {
        $incrementId = $this->getRequest()->getPost('increment_id');
        /* @var $order  D1m_Sales_Model_Order */
        $order       = Mage::getModel('sales/order')->loadByIncrementId($incrementId);

        /* @var $messageInstance D1m_Common_Model_Message */
        $messageInstance = Mage::getModel('d1m_common/message');

        if (!$order || !$order->getId())
        {
            $messageInstance->setMessage(false,'订单号不存在')->getResultToJson();
            return ;
        }

        /* @var $paymentModel D1m_WeChat_Model_Payment */
        $paymentModel =  $this->getOrder()->getPayment()->getMethodInstance();
        $result = $paymentModel->queryOrder($order);

        if (strlen($result))
        {
            $messageInstance->setMessage(true,'微信支付成功')->setKeyValue('returnUrl',Mage::getUrl('checkout/onepage/success'))->getResultToJson();
            return ;
        }

        return $messageInstance->setMessage(false,'支付失败,请客服客服查询支付状态')->getResultToJson();
    }


    /**
     *
     */
    public function confirmAction()
    {
        if (! $this->getRequest()->isPost()) {
            $this->_redirect('');
            return;
        }

        Mage::getSingleton('weChat/payment')->confirmPayment($this->getRequest()
            ->getPost());
    }

    /**
     * notify action
     * 手机版以XML的格式来返回
     */
    public function notifyAction()
    {
        /* @var  $payment D1m_WeChat_Model_Payment */
        $payment       = Mage::getModel('weChat/payment');

        /*  @var $returnParameter 定义返回值 */
        $returnParameter = array('return_code'=>'SUCCESS');

        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];

        $this->_notifyDebug = Mage::getModel('weChat/payment_debug_notify');
        $this->_notifyDebug->setNotifyData($xml);

        //使用通用通知接口
        $notifyData = $payment->xml2array($xml);

        //验证签名，并回应微信。 数据的交换格式为XML
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($payment->checkSign($notifyData) == FALSE)
        {
            if (empty($notifyData['out_trade_no']))
            {
                return ;
            }

            /* @var $order D1m_Sales_Model_Order */
            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($notifyData['out_trade_no']);

            if (!$order || !$order->getId())
            {
                return ;
            }

            //debug order increment id
            $this->_notifyDebug->setOrderIncrementId($notifyData['out_trade_no']);

            $returnResult = $payment->updateOrder($notifyData,$order);
            if ($returnResult  == false)
            {
                $returnParameter = array('return_code'=>'FAIL','return_msg'=>'notify 失败');
            }
        }else{
            $returnParameter = array('return_code'=>'FAIL','return_msg'=>'签名失败');
        }

        //record return data
        $this->_notifyDebug->setReturnData($payment->array2xml($returnParameter))->save();

        echo $payment->array2xml($returnParameter);
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


    /**
     * Failure payment page
     *
     * @param    none
     * @return	  void
     */
    public function errorAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $errorMsg = Mage::helper('weChat')->__(' There was an error occurred during paying process.');

        $order = $this->getOrder();

        if (!$order->getId())
        {
            $this->norouteAction();
            return;
        }
        if ($order instanceof Mage_Sales_Model_Order && $order->getId())
        {
            $order->addStatusHistoryComment(
                Mage::helper('weChat')->__('Customer returned from  WeChat Payment.').$errorMsg
            );

            $order->save();
        }

        Mage::getSingleton('checkout/session')->unsLastRealOrderId();

        $this->_redirect('checkout/onepage/fail');
    }
}
