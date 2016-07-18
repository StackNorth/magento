<?php
/**
 * 
 * 接口访问类，包含所有微信支付API列表的封装，
 * 每个接口有默认超时时间（除提交被扫支付为10s，上报超时时间为1s外，其他均为6s）
 * @author widyhu
 *
 */
class D1m_WeChat_Model_Payment_Util_PayApi extends Varien_Object
{
    /***
     *  配置
     *
     * @var
     */
    protected static  $_config;

    /***
     * 记录微信支付的请求以及响应信息
     *
     * */
    protected $_paymentDebug = null;


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
     *  get checkout session order
     *
     * @return Mage_Sales_Model_Order
     */
    protected  function _getOrder()
    {
        /* @var $session Mage_Checkout_Model_Session */
        $session = Mage::getSingleton('checkout/session');
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());
        /* @var  $payment D1m_WeChat_Model_Payment */
        $payment = Mage::getModel('weChat/payment');

        $session->setLastOrderId($order->getId());

        if (!$order || !$order->getId())
        {
            throw new D1m_WeChat_Model_Payment_Exception("支付订单无法找到!");
        }

        //订单状态的判断
        if ($order->getStatus() != self::getConfig()->getConfigData('order_status'))
        {
            throw new D1m_WeChat_Model_Payment_Exception("支付订单状态有误!订单状态必须为".self::getConfig()->getConfigData('order_status'));
        }

        if ($order->getPayment()->getMethod() != $payment->getCode())
        {
            throw new D1m_WeChat_Model_Payment_Exception("此订单为非微信支付订单!");
        }

        return $order;
    }



    /***
     *   获得支付的金额
     *
     * @return int
     */
    protected  function _getOrderTotal()
    {
        //微信支付是以 分为单位，不允许 浮点数类型
        $totalFee = intval($this->_getOrder()->getGrandTotal()*100);

        if (self::getConfig()->getConfigData('debug') &&
            $this->_getOrder()->getCustomerGroupId() == self::getConfig()->getConfigData('debug_group'))
        {
             $totalFee = 1;
        }

        return $totalFee;
    }


    /***
     *
     * @return  D1m_WeChat_Model_Payment_Util_Config
     */
    public  static  function getConfig()
    {
        if (!is_null(self::$_config))
        {
            return self::$_config;
        }

        self::$_config = Mage::getModel('weChat/payment_util_config');

        return self::$_config;
    }


	/**
	 * 
	 * 统一下单，WxPayUnifiedOrder中out_trade_no、body、total_fee、trade_type必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param D1m_WeChat_Model_Payment_Util_UnifiedOrder $inputObj
	 * @param int $timeOut
	 * @return 成功时返回，其他抛异常
	 */
	public  function unifiedOrder($inputObj, $timeOut = 6)
	{
        /* @var  $debugInstance D1m_WeChat_Model_Payment_Debug_Pay */
        $debugInstance = Mage::getModel('weChat/payment_debug_pay');

        $ip = Mage::app()->getRequest()->getClientIp(true);

		$url = self::getConfig()->getGatewayUrl();

        //关联参数
		if($inputObj->GetTrade_type() == D1m_WeChat_Model_Payment_Type::TRADE_TYPE_JSAPI
            && !$inputObj->IsOpenidSet())
        {
			throw new D1m_WeChat_Model_Payment_Exception("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！");
		}

		//异步通知url未设置，则使用配置文件中的url
		if(!$inputObj->IsNotify_urlSet())
        {
			$inputObj->SetNotify_url(self::getConfig()->getNotifyURL());//异步通知url
		}
		
		$inputObj->SetAppid(self::getConfig()->getAppId());//公众账号ID
		$inputObj->SetMch_id(self::getConfig()->getMchId());//商户号
		$inputObj->SetSpbill_create_ip($ip);//终端ip
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串

        try
        {
            //1)set debug
            $this->getDebugInstance()
                ->setOrderIncrementId($this->_getOrder()->getIncrementId());

            //2)set order
            $inputObj->setBody(sprintf('%s-%s',self::getConfig()->getConfigData('subject'),$this->_getOrder()->getIncrementId()));
            $inputObj->SetOut_trade_no($this->_getOrder()->getIncrementId());
            $inputObj->SetTotal_fee($this->_getOrderTotal());

            //3)签名
            $inputObj->SetSign();
            $xml = $inputObj->ToXml();

            $this->getDebugInstance()
                ->setRequestPrepayId(print_r($xml,true));

            $response = self::postXmlCurl($xml, $url, false, $timeOut);
            $result = D1m_WeChat_Model_Payment_Util_PayResults::Init($response);

            //debug response
            $this->getDebugInstance()->setResponsePrepayId(print_r($response,true))
            ->setRequestPackpage(var_export($result,true))->save();
        }catch (Mage_Core_Exception $e)
        {
            D1m_WeChat_Model_PromptMessage::getInstance()->setMessage($e->getMessage());

            $this->getDebugInstance()
                ->setResponsePrepayId($e->getMessage())
                ->setRequestPackpage($e->getMessage())->save();
            Mage::logException($e);
        }

		return $result;
	}
	
	/**
	 * 
	 * 查询订单，WxPayOrderQuery中out_trade_no、transaction_id至少填一个
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayOrderQuery $inputObj
	 * @param int $timeOut
	 * @throws D1m_WeChat_Model_Payment_Exception
	 * @return 成功时返回，其他抛异常
	 */
	public static function orderQuery($inputObj, $timeOut = 6)
	{
		$url = self::getConfig()->getOrderQueryUrl();

		//检测必填参数
		if(!$inputObj->IsOut_trade_noSet() && !$inputObj->IsTransaction_idSet()) {
			throw new D1m_WeChat_Model_Payment_Exception("订单查询接口中，out_trade_no、transaction_id至少填一个！");
		}
		$inputObj->SetAppid(self::getConfig()->getAppId());//公众账号ID
		$inputObj->SetMch_id(self::getConfig()->getMchId());//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
		
		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = D1m_WeChat_Model_Payment_Util_PayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
		
		return $result;
	}

	/**
	 * 提交被扫支付API
	 * 收银员使用扫码设备读取微信用户刷卡授权码以后，二维码或条码信息传送至商户收银台，
	 * 由商户收银台或者商户后台调用该接口发起支付。
	 * WxPayWxPayMicroPay中body、out_trade_no、total_fee、auth_code参数必填
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayWxPayMicroPay $inputObj
	 * @param int $timeOut
	 */
	public static function micropay($inputObj, $timeOut = 10)
	{
		$url = self::getConfig()->getMicroPayUrl();
        $ip = Mage::app()->getRequest()->getClientIp(true);

		//检测必填参数
		if(!$inputObj->IsBodySet()) {
			throw new D1m_WeChat_Model_Payment_Exception("提交被扫支付API接口中，缺少必填参数body！");
		} else if(!$inputObj->IsOut_trade_noSet()) {
			throw new D1m_WeChat_Model_Payment_Exception("提交被扫支付API接口中，缺少必填参数out_trade_no！");
		} else if(!$inputObj->IsTotal_feeSet()) {
			throw new D1m_WeChat_Model_Payment_Exception("提交被扫支付API接口中，缺少必填参数total_fee！");
		} else if(!$inputObj->IsAuth_codeSet()) {
			throw new D1m_WeChat_Model_Payment_Exception("提交被扫支付API接口中，缺少必填参数auth_code！");
		}
		
		$inputObj->SetSpbill_create_ip($ip);//终端ip
		$inputObj->SetAppid(self::getConfig()->getAppId());//公众账号ID
		$inputObj->SetMch_id(self::getConfig()->getMchId());//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
		
		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = D1m_WeChat_Model_Payment_Util_PayResults::Init($response);
		self::reportCostTime($url, $startTimeStamp, $result);//上报请求花费时间
		
		return $result;
	}
	

	/**
	 * 
	 * 生成二维码规则,模式一生成支付二维码
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayBizPayUrl $inputObj
	 * @param int $timeOut
	 * @throws D1m_WeChat_Model_Payment_Exception
	 * @return 成功时返回，其他抛异常
	 */
	public static function bizpayurl($inputObj, $timeOut = 6)
	{
		if(!$inputObj->IsProduct_idSet()){
			throw new D1m_WeChat_Model_Payment_Exception("生成二维码，缺少必填参数product_id！");
		}

        $inputObj->SetAppid(self::getConfig()->getAppId());//公众账号ID
        $inputObj->SetMch_id(self::getConfig()->getMchId());//商户号
		$inputObj->SetTime_stamp(time());//时间戳	 
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
		
		$inputObj->SetSign();//签名
		
		return $inputObj->GetValues();
	}
	
	/**
	 * 
	 * 转换短链接
	 * 该接口主要用于扫码原生支付模式一中的二维码链接转成短链接(weixin://wxpay/s/XXXXXX)，
	 * 减小二维码数据量，提升扫描速度和精确度。
	 * appid、mchid、spbill_create_ip、nonce_str不需要填入
	 * @param WxPayShortUrl $inputObj
	 * @param int $timeOut
	 * @throws D1m_WeChat_Model_Payment_Exception
	 * @return 成功时返回，其他抛异常
	 */
	public static function shorturl($inputObj, $timeOut = 6)
	{
		$url =  self::getConfig()->getShortUrl();
		//检测必填参数
		if(!$inputObj->IsLong_urlSet()) {
			throw new D1m_WeChat_Model_Payment_Exception("需要转换的URL，签名用原串，传输需URL encode！");
		}
        $inputObj->SetAppid(self::getConfig()->getAppId());//公众账号ID
        $inputObj->SetMch_id(self::getConfig()->getMchId());//商户号
		$inputObj->SetNonce_str(self::getNonceStr());//随机字符串
		
		$inputObj->SetSign();//签名
		$xml = $inputObj->ToXml();
		
		$startTimeStamp = self::getMillisecond();//请求开始时间
		$response = self::postXmlCurl($xml, $url, false, $timeOut);
		$result = D1m_WeChat_Model_Payment_Util_PayResults::Init($response);

		return $result;
	}
	
 	/**
 	 * 
 	 * 支付结果通用通知
 	 * @param function $callback
 	 * 直接回调函数使用方法: notify(you_function);
 	 * 回调类成员函数方法:notify(array($this, you_function));
 	 * $callback  原型为：function function_name($data){}
 	 */
	public static function notify($callback, &$msg)
	{
		//获取通知的数据
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'];
		//如果返回成功则验证签名
		try {
			$result = D1m_WeChat_Model_Payment_Util_PayResults::Init($xml);
		} catch (D1m_WeChat_Model_Payment_Exception $e){
			$msg = $e->errorMessage();
			return false;
		}
		
		return call_user_func($callback, $result);
	}
	
	/**
	 * 
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
	}
	
	/**
	 * 直接输出xml
	 * @param string $xml
	 */
	public static function replyNotify($xml)
	{
		echo $xml;
	}

	/**
	 * 以post方式提交xml到对应的接口url
	 * 
	 * @param string $xml  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 * @throws D1m_WeChat_Model_Payment_Exception
	 */
	private  static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
	{
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);

		curl_setopt($ch,CURLOPT_URL, $url);

/*		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验*/
        //开发环境不进行校验
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);

		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);

		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		} else { 
			$error = curl_errno($ch);
			curl_close($ch);
			throw new D1m_WeChat_Model_Payment_Exception("curl出错，错误码:$error");
		}
	}
	
	/**
	 * 获取毫秒级别的时间戳
	 */
	private static function getMillisecond()
	{
		//获取毫秒的时间戳
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
	}
}

