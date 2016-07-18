<?php
/**
 *
 * Class D1m_WeChat_Model_WeChat
 */
class D1m_WeChat_Model_WeChat extends Mage_Core_Model_Abstract
{
    /**
     *   认证的请求地址
     */
    const WECHAT_OAUTH2_AUTHORIZE_URL   = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    /**
     *   获得access token 的请求地址
     */
    const WECHAT_OAUTH2_ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * refresh token url
     */
    const WECHAT_OAUTH2_REFRESH_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

    /**
     *  各种URL的定义
     *
     * @var
     */
    protected $_authorizeUrl;

    /**
     * @var  $_accessTokenUrl
     */
    protected $_accessTokenUrl;

    /**
     * @var $_refreshTokenUrl
     */
    protected $_refreshTokenUrl;


    /**
     *  请求微信认证URL，Request Url所带有的 code码，用以获取openid
     * @var
     */
    public   $code;

    /**
     *  根据 code码 通过微信认证所生成的 openId
     *
     * @var
     */
    protected $openId;


    /**
     *   construct
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *  获得微信认证成功之后的code值
     *
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * 获得 认证的请求地址
     *
     * @return mixed|string
     */
    public function getAuthorizeUrl()
    {
        if (is_null($this->_authorizeUrl))
        {
            $this->_authorizeUrl or $this->_authorizeUrl = self::WECHAT_OAUTH2_AUTHORIZE_URL;
        }
        return $this->_authorizeUrl;
    }


    /**
     * 获得 access token 的请求地址
     *
     * @return mixed|string
     */
    public function getAccessTokenUrl()
    {
        if (is_null($this->_accessTokenUrl))
        {
            $this->_accessTokenUrl or $this->_accessTokenUrl = self::WECHAT_OAUTH2_ACCESS_TOKEN_URL;
        }
        return $this->_accessTokenUrl;
    }

    /**
     * 获得 refresh token 请求的地址
     *
     * @return mixed|string
     */
    public function getRefreshTokenUrl()
    {
        if (is_null($this->_refreshTokenUrl))
        {
            $this->_refreshTokenUrl or $this->_refreshTokenUrl = self::WECHAT_OAUTH2_REFRESH_TOKEN_URL;
        }
        return $this->_refreshTokenUrl;
    }

    /**
     *  获得微信登陆的URL
     *
     * @param $redirectUrl
     * @return string
     */
    function createOauthUrlForCode($redirectUrl)
    {

        try{
            $urlObj["appid"] =  D1m_WeChat_Model_Config::getAppId();
        }catch (Mage_Core_Exception $e)
        {
            throw $e;
        }

        $urlObj["redirect_uri"] = "$redirectUrl";
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE"."#wechat_redirect";

        $bizString =  Mage::helper('weChat')->formatBizQueryParaMap($urlObj, false);
        return sprintf('%s?%s',$this->getAuthorizeUrl(),$bizString);
    }

    /**
     * 获得 微信登陆认证的 openId 生成地址
     *
     * @return string
     */
    function createOauthUrlForOpenid()
    {
        try{
            $urlObj["appid"] = D1m_WeChat_Model_Config::getAppId();
            $urlObj["secret"] =D1m_WeChat_Model_Config::getAppSecret();
        }catch (Mage_Core_Exception $e)
        {
            throw $e;
        }

        $urlObj["code"] =   $this->getCode();
        $urlObj["grant_type"] = "authorization_code";
        $bizString =  Mage::helper('weChat')->formatBizQueryParaMap($urlObj, false);
        return sprintf('%s?%s',$this->getAccessTokenUrl(),$bizString);
    }

    /**
     * 	作用：通过curl向微信提交code，以获取openid
     *
     */
    function getOpenId()
    {

        return  Mage::helper('D1m_WeixinUser')->getJsOpenid(1);
        /*
        $testOpenId = Mage::getStoreConfig('payment/weChat_payment/test_open_id');
        return $testOpenId;*/
    }
}