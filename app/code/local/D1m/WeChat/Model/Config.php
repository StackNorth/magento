<?php
/**
 *
 * 微信公众平台相关的配置信息
 */
class D1m_WeChat_Model_Config extends Mage_Core_Model_Abstract
{
    //XML PATH 微信公共配置
    const XML_PATH_WECHAT_APPID       = 'payment/weChat_payment/appId';
    const XML_PATH_WECHAT_APPSECRET  = 'payment/weChat_payment/appsecret';
    const XML_PATH_WECHAT_TOKEN       = 'payment/weChat_payment/token';


    //微信支付相关的配置

    /**
     *
     * @var
     */
    static  protected $_appId;

    /**
     *
     * @var
     */
    static protected $_appSecret;

    /**
     * @var
     */
    static protected $_token;

    /**
     *  微信支付的具体实例
     *
     * @var
     */
    static protected $_payment;

    /**
     * get app id
     *
     * @return mixed
     */
    static function getAppId()
    {
        if (!strlen(self::$_appId))
        {
            $appId = Mage::getStoreConfig(self::XML_PATH_WECHAT_APPID);

            if (!strlen($appId))
            {
                Mage::throwException('微信 APP ID没有定义');
            }
            self::$_appId =  Mage::getStoreConfig(self::XML_PATH_WECHAT_APPID);

        }else{
            return self::$_appId;
        }

        return self::$_appId;
    }

    /**
     *  get app secret
     *
     * @return mixed
     */
    static function getAppSecret()
    {
        if (is_null(self::$_appSecret))
        {
            $appSecret = Mage::getStoreConfig(self::XML_PATH_WECHAT_APPSECRET);

            if (!strlen($appSecret))
            {
                Mage::throwException('微信 APP Secret没有定义');
            }
            self::$_appSecret =  Mage::getStoreConfig(self::XML_PATH_WECHAT_APPSECRET);
        }else{
            return self::$_appSecret;
        }

        return self::$_appSecret;
    }

    /**
     *  获得微信的token
     *
     * @return mixed
     */
    static function getToken()
    {
        if (is_null(self::$_token))
        {
            $token = Mage::getStoreConfig(self::XML_PATH_WECHAT_TOKEN);

            if (!strlen($token))
            {
                Mage::throwException('微信 Token 没有定义');
            }
            self::$_token =  Mage::getStoreConfig(self::XML_PATH_WECHAT_TOKEN);
        }else{
            return self::$_token;
        }

        return self::$_token;
    }

}