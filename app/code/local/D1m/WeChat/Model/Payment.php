<?php
/**
 *
 * JS_API支付demo
 * ====================================================
 * 在微信浏览器里面打开H5网页中执行JS调起支付。接口输入输出数据格式为JSON。
 * 成功调起支付需要三个步骤：
 * 步骤1：网页授权获取用户openid
 * 步骤2：使用统一支付接口，获取prepay_id
 * 步骤3：使用jsapi调起支付
 *
 * Class D1m_WeChat_Model_Payment
 */
class D1m_WeChat_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    /**
     *  定义默认的支付URL
     */
    const GATEWAY_URL = "https://api.mch.weixin.qq.com/pay/unifiedorder";

    /**
     * 订单查询接口
     */
    const QUERY_GATEWAY = "https://api.mch.weixin.qq.com/pay/orderquery";

    /***
     * 提交扫码支付的API网关
     */
    const  MICRO_PAY_GATEWAY = 'https://api.mch.weixin.qq.com/pay/micropay';

    /**
     *  支付时需要生成的短链接
     */
    const  SHORT_URL = 'https://api.mch.weixin.qq.com/tools/shorturl';

    /***
     *  biz pay url
     */
    const  BIZ_PAY_URL = 'weixin://wxpay/bizpayurl';

    /**
     *  微信支付所支持的版本号
     */
    const  WECHAT_PAYMENT_VERSION  = 5;

    /**
     * 订单的状态
     * $param out_trade_no 商户订单号
     * @return 字符串，交易状态
     *          SUCCESS     支付成功
     *          REFUND      转入退款
     *          NOTPAY      未支付
     *          CLOSED      已关闭
     *          REVOKED     已撤销
     *          USERPAYING  用户支付中
     *          NOPAY       未支付
     *          PAYERROR    支付失败
     *          null        订单不存在或其它错误，错误描述$this->error
     */
    const WECHAT_ORDER_STATUS_SUCCESS = 'SUCCESS';
    const WECHAT_ORDER_STATUS_REFUND  = 'REFUND';
    const WECHAT_ORDER_STATUS_NOTPAY  = 'NOTPAY';
    const WECHAT_ORDER_STATUS_CLOSED  = 'CLOSED';
    const WECHAT_ORDER_STATUS_REVOKED = 'REVOKED';
    /**
     * 用户支付中
     */
    const WECHAT_ORDER_STATUS_USERPAYING= 'USERPAYING';
    const WECHAT_ORDER_STATUS_NOPAY     = 'NOPAY';
    const WECHAT_ORDER_STATUS_PAYERROR  =  'PAYERROR';

    /***
     *  选择支付方式时的FORM
     *
     * @var string
     */
    protected $_formBlockType = 'weChat/payment_form';

    /**
     *  支付方式的代编号,必须以payment结尾(极为重要)
     *
     * @var string
     */
    protected $_code = 'weChat_payment';


    /**
     * 默认支付网关
     *
     * @var
     */
    protected $_gatewayUrl;

    /**
     *  Payment configuration
     *  以下的配置极度重要
     * @var bool
     */
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    // Order instance
    protected $_order = null;


    // 记录微信支付的请求以及响应信息
    protected $_paymentDebug = null;

    // 记录微信支付 查询
    protected $_queryDebug    = null;

    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     *
     * @return $this|Mage_Payment_Model_Abstract
     */
    public function cancel(Varien_Object $payment)
    {
        return $this->void($payment);
    }

    /**
     * Capture payment
     *
     * @param   Varien_Object $orderPayment
     * @return  Mage_Payment_Model_Abstract
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $payment->setStatus(self::STATUS_APPROVED)
            ->setLastTransId($this->getTransactionId());

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('weChat/payment/redirect');
    }

    /**
     *  获得支付成功的状态码
     *
     * @return array
     */
    static  function getPaySuccessStatus()
    {
        return array(
            self::WECHAT_ORDER_STATUS_SUCCESS,
            self::WECHAT_ORDER_STATUS_USERPAYING
        );
    }

    /**
     *  目前只有微信5.0版本后才加入微信支付模块， 低版本用户调用微信支付功能将无效
     *
     *  使用user-agent来处理
     *
     * @return bool
     */
    public function enableJsApiPay()
    {
        $userAgent =  Mage::helper('core/http')->getHttpUserAgent();
        $pattern = '/.+(?<version>MicroMessenger\/[0-9.]+).+/isu';

        if(preg_match($pattern,$userAgent, $matches))
        {
            $versions = explode('/',$matches['version']) ;

            if ($versions[1] >= self::WECHAT_PAYMENT_VERSION)
                return true;
        }

        return false;
    }

    /**
     *  获得 微信的公用实例 D1m_WeChat_Model_WeChat 实例
     *
     * @return D1m_WeChat_Model_WeChat
     */
    public function getWeChatInstance()
    {
        if (Mage::registry('weChat'))
        {
            return Mage::registry('weChat');
        }

        Mage::register('weChat',Mage::getModel('weChat/weChat'));
        return Mage::registry('weChat');
    }

    /**
     *  get payment debug instance  获得微信的调试实例
     *
     * @return D1m_WeChat_Model_Payment_Debug_Pay|false|Mage_Core_Model_Abstract
     */
    public function getDebugInstance()
    {
        if (is_null($this->_paymentDebug) ||
            !$this->_paymentDebug instanceof D1m_WeChat_Model_Payment_Debug_Pay)
        {
            $this->_paymentDebug = Mage::getModel('weChat/payment_debug_pay');
        }

        return $this->_paymentDebug;
    }

    /**
     *  get payment
     *
     * @return D1m_WeChat_Model_Payment_Debug_Query|false|Mage_Core_Model_Abstract
     */
    public function getQueryDebugInstance()
    {
        if (is_null($this->_queryDebug) ||
            !$this->_queryDebug instanceof D1m_WeChat_Model_Payment_Debug_Query)
        {
            $this->_queryDebug = Mage::getModel('weChat/payment_debug_query');
        }

        return $this->_queryDebug;
    }

    /**
     *
     * @return mixed|string
     */
    public function getGatewayUrl()
    {
        if ($this->_gatewayUrl === NULL) {
            $this->_gatewayUrl = $this->getConfigData('gateway');
            $this->_gatewayUrl or $this->_gatewayUrl = self::GATEWAY_URL;
        }
        return $this->_gatewayUrl;
    }

    /**
     *  Return back URL
     *
     *  @return	  string URL
     */
    protected function getReturnURL($increment_id=null)
    {
        if (is_null($increment_id))
        {
            return Mage::getUrl('weChat/payment/success', array('_secure' => true));
        }
        return Mage::getUrl('weChat/payment/success', array('_secure' => true,'increment_id'=>$increment_id));
    }

    /**
     *  Return URL for WeChat Payment success response
     *
     *  @return	  string URL
     */
    public  function getSuccessURL()
    {
        return Mage::getUrl('checkout/onepage/success', array('_secure' => true));
    }

    /**
     *  Return URL for WeChat failure response
     *
     *  @return	  string URL
     */
    public  function getErrorURL()
    {
        return Mage::getUrl('weChat/payment/error', array('_secure' => true));
    }

    /**
     *  微信 支付的请求地址
     *  微信强烈建议商户使用“微信安全支付”标题
     * @return string
     */
    public function getRedirectUrl($incrementId)
    {
        $redirectUrl    = Mage::getUrl('weChat/payment/redirect');

        if ($this->getConfigData('showwxpaytitle'))
        {
            $urlParams = http_build_query(array('showwxpaytitle'=>1,'increment_id'=>base64_encode($incrementId)));
        }else{
            $urlParams = http_build_query(array('increment_id'=>base64_encode($incrementId)));
        }

        return $redirectUrl.'?'.$urlParams;
    }

    /**
     *  Return URL for Wechat notify response
     *
     *  @return	  string URL
     */
    public  function getNotifyURL()
    {
        return Mage::getUrl('weChat/payment/notify/', array('_secure' => true));
    }

    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     *  get checkout session order
     *
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrder()
    {
        $session = Mage::getSingleton('checkout/session');
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($this->getCheckout()->getLastRealOrderId());

        $this->getCheckout()->setLastOrderId($order->getId());

        return $order;
    }


    /**
     *  支付的第一步，获得 以获取openid
     *
     */
    function getOpenid()
    {
       return  $this->getWeChatInstance()->getOpenId();
    }
    public function getPaymentType(){
        if($this->enableJsApiPay()){
            return D1m_WeChat_Model_Payment_Type::TRADE_TYPE_JSAPI;
        }else{
          return  D1m_WeChat_Model_Payment_Type::TRADE_TYPE_NATIVE;
        }
    }

    /**
     * 支付的第二步，根据open id 来获得prepay id
     *
     * @return null
     */
    public function getPrepayId()
    {
        $order = $this->_getOrder();

        if (!($order instanceof Mage_Sales_Model_Order))
        {
            Mage::throwException($this->_getHelper()->__('Cannot retrieve order object'));
        }

        //微信支付是以 分为单位，不允许 浮点数类型
        $totalFee = intval($order->getGrandTotal()*100);

        if ($this->getConfigData('debug') && $order->getCustomerGroupId() == $this->getConfigData('debug_group'))
        {
            $totalFee = 1;
        }

        //微信 open id
        $openId = $this->getOpenid();
        if (!strlen($openId))
        {
            Mage::throwException('统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！');
        }

        $data = array();
        $data["appid"]        = $this->getConfigData('appId');
        $data["openid"]        = $this->getOpenid();
        $data["mch_id"]       = $this->getConfigData('Mchid');
        $data["nonce_str"]    = $this->getNonceString();
        $data["body"]         =  sprintf('%s-%s',$this->getConfigData('subject'),$order->getIncrementId());
        $data["out_trade_no"] = $order->getIncrementId();
        $data["total_fee"]    =  $totalFee;
        $data["spbill_create_ip"] = Mage::app()->getRequest()->getClientIp(true);
        $data["notify_url"]   = $this->getNotifyURL();
        $data["trade_type"]   =$this->getPaymentType();// D1m_WeChat_Model_Payment_Type::TRADE_TYPE_JSAPI;
        $data["input_charset"]   =  'UTF-8';

        //save debug data
        $this->getDebugInstance()->setType( $this->getPaymentType())->setOrderIncrementId($order->getIncrementId())->save();

        $result = $this->post($this->getGatewayUrl(), $data);

        if (isset($result["result_code"]) && $result["result_code"] == "SUCCESS")
        {
            return $result["prepay_id"];
        } else {
            $errorMsg = sprintf('获得prepay_id失败!错误代码:%s 错误信息为%s',$result["err_code"],$result["err_code_des"]);
            Mage::throwException($errorMsg);
        }
    }

    /***
     *  获得地维码生成的图片URL
     *
     * @return mixed
     */
    public function getGrCodeUrl()
    {
        /* @var $nativePay D1m_WeChat_Model_Payment_QrCode_NativePay */
        $nativePay =  Mage::getModel('weChat/payment_qrCode_nativePay');

        /* @var $unifiedOrder D1m_WeChat_Model_Payment_Util_UnifiedOrder  */
        $unifiedOrder = Mage::getModel('weChat/payment_util_unifiedOrder');
        $unifiedOrder->SetTrade_type(D1m_WeChat_Model_Payment_Type::TRADE_TYPE_NATIVE);

        $result = $nativePay->GetPayUrl($unifiedOrder);

        if (isset($result['code_url']))
        {
            return $result['code_url'];
        }

        return ;
    }

    /**
     * 获得当前扫码支付的 二维码
     *
     * @return string
     */
    public function getQrCodeUrl()
    {
        $result  = $this->getGrCodeUrl();

        return  strlen($result)?Mage::getUrl('weChat/weixin/qrCode').'?data='.urlencode($result):null;
    }


    /**
     *  第三步： 组织HTTP的参数
     *
     * 获取js支付使用的第二个参数
     *
     */
    public function getPackage($prepayId)
    {
        $data = array();

        $data["appId"] = $this->getConfigData('appId');
        $time   = time();
        $data["timeStamp"] = "$time";
        $data["nonceStr"]  = $this->getNonceString();
        $data["package"]   = "prepay_id=$prepayId";
        $data["signType"]  = "MD5";
        $data["paySign"]   = $this->sign($data);

        return $data;
    }

    /**
     *  return weChat payment data
     *
     * @return string
     */
    public function getStandardCheckoutFormFields()
    {
        try
        {
            $prepayId = $this->getPrepayId();
        }catch (Mage_Core_Exception $e)
        {
            throw $e;
        }

        if (!strlen($prepayId))
        {
           Mage::throwException('weChat payment failed,no prepay Id');
        }

        $apiPackage = $this->getPackage($prepayId);

        //debug record packPage
        $this->getDebugInstance()->setRequestPackpage(var_export($apiPackage,true))->save();

        return Mage::helper('core')->jsonEncode($apiPackage);
    }

    //public function


    /**
     * 订单查询接口
     * $param  商户订单号
     * @return 字符串，交易状态
     *          SUCCESS     支付成功
     *          REFUND      转入退款
     *          NOTPAY      未支付
     *          CLOSED      已关闭
     *          REVOKED     已撤销
     *          USERPAYING  用户支付中
     *          NOPAY       未支付
     *          PAYERROR    支付失败
     *          null        订单不存在或其它错误，错误描述$this->error
     */
    public function queryOrder(Mage_Sales_Model_Order $order)
    {
        //record query set order increment id
        $this->getQueryDebugInstance()->setOrderIncrementId($order->getIncrementId());

        $result = $this->getQueryOrderInfo($order);

        if (isset($result["result_code"]) && $result["result_code"] == "SUCCESS")
        {
            return $result["trade_state"];
        } else {
            return null;
        }
    }

    /***
     *  获得 查询订单的所有返回信息
     *
     * @return array
     */
    public function getQueryOrderInfo(Mage_Sales_Model_Order $order)
    {
        $data = array();
        $data["appid"]        = $this->getConfigData('appId');
        $data["mch_id"]       = $this->getConfigData('Mchid');
        $data["out_trade_no"] = $order->getIncrementId();
        $data["nonce_str"]    = $this->getNonceString();

        //record query set order increment id
        $this->getQueryDebugInstance()->setOrderIncrementId($order->getIncrementId());

        $result = $this->post(self::QUERY_GATEWAY, $data);

        return $result;
    }

    /****
     * update order status
     *
     * @param $notifyData
     * @param $order Mage_Sales_Model_Order
     * @return bool
     */
    public function updateOrder($notifyData,$order)
    {
        $result = true;

        if (isset($notifyData['result_code']) &&
            $notifyData['result_code'] == D1m_WeChat_Model_Payment::WECHAT_ORDER_STATUS_SUCCESS
            && $order->getStatus() == $this->getConfigData('order_status'))
        {
            /* @var  $orderStatus Mage_Sales_Model_Order_Status */
            $orderStatus =  Mage::helper('weChat/payment')->getOrderState($this->getConfigData('order_status_payment_accepted'));

            $order->setState($this->getConfigData('order_status_payment_accepted'));
            if (!is_null($orderStatus))$order->setStatus($orderStatus->getState());

            //save order pay trade no
            $order->getPayment()->setPayTradeNo($notifyData['transaction_id']);
            $order->setPayTradeNo($notifyData['transaction_id'])
                 ->setPaidAt($order->getResource()->formatDate(time()));
            $order->save();

            try{
                $order->addStatusHistoryComment(sprintf('微信付款成功,微信付款类型为【%s】',D1m_WeChat_Model_Payment_Type::getType($notifyData['trade_type'])));
                $order->save();
                $order->addStatusHistoryComment(sprintf('获得微信支付的流水号成功,流水号为: %s',$order->getPayTradeNo()));
                $order->save();

                //支付成功 notify success
                Mage::dispatchEvent('payment_accept_notify', array('order' => $order));
                Mage::dispatchEvent('d1m_order_send_to_erp_ready', array('order' => $order));

            } catch(Exception $e)
            {
                $result  = false;
                Mage::logException($e);
            }
        }

        return $result;
    }

    /**
     *   微信支付工具 验证方法
     *
     * @param $array
     * @return string
     */
    public function array2xml($array)
    {
        $xml = "<xml>" . PHP_EOL;
        foreach ($array as $k => $v)
        {
            if (is_numeric($v))
            {
                $xml.="<".$k.">".$v."</".$k.">".PHP_EOL;
            }else{
                $xml .= "<$k><![CDATA[$v]]></$k>" . PHP_EOL;
            }
        }
        $xml .= "</xml>";

        return $xml;
    }

    /**
     *   微信支付工具 验证方法
     *
     * @param $xml
     * @return array
     */
    public function xml2array($xml)
    {
        if ($xml) {
            $postObj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            if (! is_object($postObj)) {
                return false;
            }
            $array = json_decode(json_encode($postObj), true); // xml对象转数组
            return array_change_key_case($array, CASE_LOWER); // 所有键小写
        } else {
            return false;
        }
    }

    /**
     *  微信支付工具 post支付请求
     *
     * @param $url
     * @param $data
     * @return array
     */
    public function post($url, $data)
    {
        if (!strlen($this->getConfigData('Key')))
        {
            Mage::throwException('微信商户支付KEY没有定义!');
        }

        $data["sign"] = $this->sign($data);

        $content       = Mage::helper('weChat/common')->post($url,$this->array2xml($data));
        $array = $this->xml2array($content);

        //debug record request data
        if ($url == $this->getGatewayUrl())
        {
            $this->getDebugInstance()->setRequestPrepayId($this->array2xml($data))
                                     ->setResponsePrepayId($content)->save();
        }elseif($url == self::QUERY_GATEWAY)
        {
            $this->getQueryDebugInstance()->setQueryData($this->array2xml($data))
                                          ->setReturnData($content)->save();
        }

        return $array;
    }

    /**
     * 微信支付工具 sign
     * 作用：生成签名
     * @param $data
     * @return string
     */
    public function sign($Obj)
    {
        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = Mage::helper('weChat')->formatBizQueryParaMap($Parameters, false);

        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".$this->getConfigData('Key');
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**
     *  return unique code
     *
     * @return string
     */
    public function getNonceString( $length = 16 )
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * 微信支付工具 验证是否是腾讯服务器推送数据
     *
     * @param $data 数据数组
     * @return 布尔值
     */
    public function signValidate($data)
    {
        if (!isset($data["sign"]))
        {
            return false;
        }

        $sign = $data["sign"];
        unset($data["sign"]);

        return $this->sign($data) == $sign;
    }


    /**
     *
     *  以下相关的几个类为 notify class
     *  notify  处理
     * @return bool
     */
    function checkSign($data)
    {
        unset($data['sign']);
        $sign = $this->sign($data);//本地签名
        if ($data['sign'] == $sign)
        {
            return TRUE;
        }
        return FALSE;
    }


    /**
     *  修改订单的状态，根据支付宝的结果
     *
     * @param Mage_Sales_Model_Order $order
     * @param null $paymentStatus
     */
    public function changeOrderStatus(Mage_Sales_Model_Order $order,$paymentStatus=null)
    {
        //add history status
        switch($paymentStatus) {
            case self::WECHAT_ORDER_STATUS_SUCCESS:
                $order->addStatusToHistory(
                    Mage_Sales_Model_Order::STATE_PROCESSING, '支付成功');
                break;
            case self::WECHAT_ORDER_STATUS_USERPAYING:
                $order->addStatusToHistory(Mage_Sales_Model_Order::STATE_PROCESSING,'用户支付中');
                break;
            case self::WECHAT_ORDER_STATUS_REFUND:
                $order->addStatusToHistory($order->getStatus(),'转入退款');
                break;
            case self::WECHAT_ORDER_STATUS_NOTPAY:
                $order->addStatusToHistory($order->getStatus(),'未支付');
                break;
            case self::WECHAT_ORDER_STATUS_CLOSED:
                $order->addStatusToHistory($order->getStatus(),'已关闭');
                break;
            case self::WECHAT_ORDER_STATUS_REVOKED:
                $order->addStatusToHistory($order->getStatus(),'已撤销');
                break;
            case self::WECHAT_ORDER_STATUS_PAYERROR:
                $order->addStatusToHistory($order->getStatus(),'支付失败');
                break;
            default:
                $order->addStatusToHistory($order->getStatus(),'未支付');
                break;
        }

        //save order history
        try{
            $order->save();
        } catch(Exception $e){
            Mage::logException($e);
        }
    }
}