<?php
/**
 *  微信的认证类
 *
 * Class D1m_WeChat_Model_Auth
 */
class D1m_WeChat_Model_Auth extends Mage_Core_Model_Abstract
{
    /**
     *  获得 access token的 超时时间
     *
     *  获得 client access token的基本原理： 将access token保存到数据库中，如果access token没有超时，则自动从数据库中取出access token,如果超时则获得新的access token
     *
     */
    const  NETWORK_PROCESS_TIMEOUT                     =  20;

    /**
     *   认证的请求地址
     */
    const WECHAT_OAUTH2_AUTHORIZE_URL               = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    /**
     *   获得 认证 access token 的请求地址
     */
    const WECHAT_OAUTH2_AUTHORIZE_ACCESS_TOKEN_URL = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    /**
     * refresh token url
     */
    const WECHAT_OAUTH2_REFRESH_TOKEN_URL           = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

    /**
     * 获得操作的 access token URl
     *
     */
    const WECHAT_CLIENT_ACCESS_TOKEN_TOKEN_URL      = 'https://api.weixin.qq.com/cgi-bin/token';

    /**
     *  各种URL的定义
     *
     * @var
     */
    static protected  $_authorizeUrl = self::WECHAT_OAUTH2_AUTHORIZE_URL;

    /**
     * @var  $_accessTokenUrl
     */
    static protected $_authorizeAccessTokenUrl = self::WECHAT_OAUTH2_AUTHORIZE_ACCESS_TOKEN_URL;

    /**
     * @var $_refreshTokenUrl
     */
    static protected $_refreshTokenUrl = self::WECHAT_OAUTH2_REFRESH_TOKEN_URL;

    /**
     *  API操作时所需要使用 access url
     *
     * @var string
     */
    static protected $_accessTokenUrl   = self::WECHAT_CLIENT_ACCESS_TOKEN_TOKEN_URL;

    /**
     * Set  authorize Url
     *
     * @param string  $authorizeUrl
     * @return bool
     */
    public static function setAuthorizeUrl($authorizeUrl)
    {
        self::$_authorizeUrl = $authorizeUrl;
        return self::$_authorizeUrl;
    }

    /**
     * Retrieve  authorize Url
     *
     * @return bool
     */
    public static function getAuthorizeUrl()
    {
        return self::$_authorizeUrl;
    }

    /**
     * Set  access token Url
     *
     * @param $accessTokenUrl
     * @return mixed
     */
    public static function setAuthorizeAccessTokenUrl($accessTokenUrl)
    {
        self::$_authorizeAccessTokenUrl = $accessTokenUrl;
        return self::$_authorizeAccessTokenUrl;
    }

    /**
     * Retrieve  access token Url
     *
     * @return string
     */
    public static function getAuthorizeAccessTokenUrl()
    {
        return self::$_authorizeAccessTokenUrl;
    }

    /**
     * Set  refresh token Url
     *
     * @return string
     */
    public static function setRefreshTokenUrl($refreshTokenUrl)
    {
        self::$_refreshTokenUrl = $refreshTokenUrl;
        return self::$_refreshTokenUrl;
    }

    /**
     * Set  refresh token Url
     *
     * @return string
     */
    public static function getRefreshTokenUrl()
    {
        return self::$_refreshTokenUrl;
    }

    /**
     * 设置 客户端调用所使用的 access token url
     *
     * @return string
     */
    public static function setClientAccessUrl($accessUrl)
    {
        self::$_accessTokenUrl = $accessUrl;
        return self::$_accessTokenUrl;
    }

    /**
     * 获得 客户端调用所使用的 access token url
     *
     * @return string
     */
    public static function getClientAccessUrl()
    {
        return self::$_accessTokenUrl;
    }

    /**
     * 获得微信登陆的URL
     * Description: 获取CODE
     * @param $scope snsapi_base不弹出授权页面，只能获得OpenId;snsapi_userinfo弹出授权页面，可以获得所有信息
     * 将会跳转到redirect_uri/?code=CODE&state=STATE 通过GET方式获取code和state
     */
    public static function createOauthUrlForCode($redirectUrl,$scope)
    {
        try{
            $urlObj["appid"] =  D1m_WeChat_Model_Config::getAppId();
        }catch (Mage_Core_Exception $e)
        {
            throw $e;
        }

        $urlObj["redirect_uri"] = urlencode($redirectUrl);
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = $scope;
        $urlObj["state"] = "STATE"."#wechat_redirect";

        $bizString =  Mage::helper('weChat')->formatBizQueryParaMap($urlObj, false);
        $url       = sprintf('%s?%s',self::getAuthorizeUrl(),$bizString);
        header('Location: '.$url, true, 301);
        exit();
    }


    /**
     * Description: 认证时所使用---通过code换取网页授权access_token
     *
     * 首先请注意，这里通过code换取的网页授权access_token,与基础支持中的access_token不同。
     * 公众号可通过下述接口来获取网页授权access_token。
     * 如果网页授权的作用域为snsapi_base，则本步骤中获取到网页授权access_token的同时，也获取到了openid，snsapi_base式的网页授权流程即到此为止。
     * @param $code getCode()获取的code参数
     *
     * @return Array(access_token, expires_in, refresh_token, openid, scope)
     */
    public static function getAuthorizeAccessTokenAndOpenId($code)
    {
        //填写为authorization_code
        $grant_type = 'authorization_code';
        //构造请求微信接口的URL
        $url = self::getAuthorizeAccessTokenUrl().'?'.http_build_query(
                array('appid'=>D1m_WeChat_Model_Config::getAppId(),'secret'=>D1m_WeChat_Model_Config::getAppSecret(),'code'=>$code,'grant_type'=>$grant_type)
            );

        $res  = Mage::helper('weChat/common')->get($url);

        return  Mage::helper('core')->jsonDecode($res);
    }

    /**
     * 刷新access_token（如果需要）
     * 由于access_token拥有较短的有效期，当access_token超时后，可以使用refresh_token进行刷新，refresh_token拥有较长的有效期（7天、30天、60天、90天），当refresh_token失效的后，需要用户重新授权。
     * @param $refreshToken 通过本类的第二个方法getAccessTokenAndOpenId可以获得一个数组，数组中有一个字段是refresh_token，就是这里的参数
     *
     * @return array(
    "access_token"=>"网页授权接口调用凭证,注意：此access_token与基础支持的access_token不同",
    "expires_in"=>access_token接口调用凭证超时时间，单位（秒）,
    "refresh_token"=>"用户刷新access_token",
    "openid"=>"用户唯一标识",
    "scope"=>"用户授权的作用域，使用逗号（,）分隔")
     */
    public static function refreshToken($refreshToken)
    {
        $url = self::getRefreshTokenUrl().'?'.http_build_query(
                array('appid'=>D1m_WeChat_Model_Config::getAppId(),'grant_type'=>'refresh_token','refresh_token'=>$refreshToken)
            );

        $res  = Mage::helper('weChat/common')->get($url);

        return  Mage::helper('core')->jsonDecode($res);
    }


    /**
     * 获取微信Access_Token
     */
    public static function getAccessToken()
    {
        //检测本地是否已经拥有access_token，并且检测access_token是否过期
        $accessToken = self::_checkAccessToken();
        if($accessToken === false){
            $accessToken = self::_getAccessToken();
        }
        return $accessToken['access_token'];
    }

    /**
     *
     * @descrpition 从微信服务器获取微信ACCESS_TOKEN
     * 同时保存到数据库中
     *
     * @return Ambigous|bool
     */
    private function _getAccessToken()
    {
        $url = self::getClientAccessUrl(). '?'.http_build_query(
                array('grant_type'=>'client_credential','appid'=>D1m_WeChat_Model_Config::getAppId(),'secret'=>D1m_WeChat_Model_Config::getAppSecret())
            );
        $res  = Mage::helper('weChat/common')->get($url);
        $accessToken = Mage::helper('core')->jsonDecode($res);

        if(!isset($accessToken['access_token']))
        {
            Mage::throwException('获取ACCESS_TOKEN失败');
        }

        $accessToken['time'] = time();
        $accessTokenJson = json_encode($accessToken);

        //存入数据库
        /**
         * 这里通常我会把access_token存起来，然后用的时候读取，判断是否过期，如果过期就重新调用此方法获取，存取操作请自行完成
         *
         * 请将变量$accessTokenJson给存起来，这个变量是一个字符串
         */
        $f = fopen('access_token', 'w+');
        fwrite($f, $accessTokenJson);
        fclose($f);
        return $accessToken;
    }

    /**
     * @descrpition 检测微信ACCESS_TOKEN是否过期
     *              -10是预留的网络延迟时间
     * @return bool
     */
    private static function _checkAccessToken()
    {
        //获取access_token。是上面的获取方法获取到后存起来的。
//        $accessToken = YourDatabase::get('access_token');
        $data = file_get_contents('access_token');
        $accessToken['value'] = $data;
        if(!empty($accessToken['value']))
        {
            $accessToken = json_decode($accessToken['value'], true);

            if(time() - $accessToken['time'] < $accessToken['expires_in']-self::NETWORK_PROCESS_TIMEOUT)
            {
                return $accessToken;
            }
        }
        return false;
    }


    /**
     * 判断验证请求的签名信息是否正确
     * @param  string $token 验证信息
     * @return boolean
     */
    private function validateSignature($token)
    {
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        $signatureArray = array($token, $timestamp, $nonce);
        sort($signatureArray, SORT_STRING);
        return sha1(implode($signatureArray)) == $signature;
    }


    public function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = WECHAT_TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            echo $_GET['echostr'];
            return true;
        }else{
            return false;
        }
    }
}