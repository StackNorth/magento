<?php
/**
 *  微信支付相关的配置扩展
 *
 */
class D1m_WeChat_Model_Payment_Util_Config extends Varien_Object
{
    /**
     *  支付的payment
     *
     * @var
     */
    protected static  $_payment;

    /***
     *  get App Id
     *
     * @return mixed
     */
    public function getAppId()
   {
       return D1m_WeChat_Model_Config::getAppId();
   }

    /**
     *  get App Secret
     *
     *  @return mixed
     */
    public function getAppSecret()
    {
        return D1m_WeChat_Model_Config::getAppSecret();
    }

    /***
     *   get Token
     *
     * @return mixed
     */
    public function getToken()
    {
        return D1m_WeChat_Model_Config::getToken();
    }

    /***
     * 微信支付的示例
     *
     * @return D1m_WeChat_Model_Payment
     */
     public static  function getPayment()
    {
        if (is_null(self::$_payment))
        {
            self::$_payment = Mage::getModel('weChat/payment');
            return self::$_payment;
        }

        return self::$_payment;
    }

    /***
     *  获得支付网关(可以替换)
     *
     * @return mixed|string
     */
    public static  function getGatewayUrl()
    {
        return self::getPayment()->getGatewayUrl();
    }

    /***
     *  获得支付的notify url
     *
     * @return string
     */
    public function getNotifyURL()
    {
        return $this->getPayment()->getNotifyURL();
    }

    /***
     *  获得支付的商户ID
     *
     * @return mixed
     */
    public function getMchId()
    {
        return $this->getPayment()->getConfigData('Mchid');
    }

    /**
     *  订单查询网关
     *
     * @return string
     */
    public function getOrderQueryUrl()
    {
        return D1m_WeChat_Model_Payment::QUERY_GATEWAY;
    }

    /**
     *
     *  提交扫码支付的API 网关
     *
     * @return string
     */
    public function getMicroPayUrl()
    {
        return D1m_WeChat_Model_Payment::MICRO_PAY_GATEWAY;
    }

    /***
     *  生成短链接的URL
     *
     * @return string
     */
    public function getShortUrl()
    {
        return D1m_WeChat_Model_Payment::SHORT_URL;
    }

    /***
     *  get config data
     *
     * @param $key
     * @return mixed
     */
    public function getConfigData($key)
    {
        return $this->getPayment()->getConfigData($key);
    }
}